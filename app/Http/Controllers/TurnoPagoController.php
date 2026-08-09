<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesMedicoFromAuth;
use App\TurnoPagoIntent;
use App\TurnoRegistrado;
use App\MedicoMercadoPagoAccount;
use App\Medico;
use App\Paciente;
use App\Consultorio;
use App\Services\MercadoPago\MercadoPagoWebhookService;
use App\Services\MercadoPago\TurnoPagoIntentService;
use App\Services\MercadoPago\MercadoPagoMarketplaceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TurnoPagoController extends Controller
{
    use ResolvesMedicoFromAuth;

    protected $intentService;

    public function __construct()
    {
        $this->intentService = new TurnoPagoIntentService();
    }

    protected function parseFechaTurno($raw)
    {
        if (strpos($raw, '/') !== false) {
            $parts = explode('/', $raw);
            return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
        }

        return $raw;
    }

    public function previewReserva(Request $request)
    {
        $this->intentService->expireStaleIntents();

        $medicoId = (int) $request->input('medico_id');
        $pacienteId = (int) $request->input('paciente_id');
        $esVideollamada = (int) $request->input('esVideollamada', 0);
        $fechaTurnoRaw = $request->input('fechaTurno');
        $fechaTurno = $fechaTurnoRaw ? $this->parseFechaTurno($fechaTurnoRaw) : null;

        $blocked = $this->intentService->medicoPaymentBlockedReason($medicoId, $esVideollamada, $pacienteId ?: null, $fechaTurno);
        if ($blocked) {
            return response()->json([
                'ok' => false,
                'blocked' => true,
                'message' => $blocked,
            ]);
        }

        $resolved = $this->intentService->resolveReservaParaPaciente($medicoId, $pacienteId, $esVideollamada, $fechaTurno);

        return response()->json([
            'ok' => true,
            'requires_payment' => $resolved['requires_payment'],
            'importe_reserva' => $resolved['importe_reserva'],
            'nombre_obra' => $resolved['nombre_obra'],
        ]);
    }

    public function iniciarPago(Request $request)
    {
        $this->intentService->expireStaleIntents();

        $fechaTurno = $this->parseFechaTurno($request->input('fechaTurno'));
        $dia = app(TurnoController::class)->getDiaSeleccionado2($fechaTurno);

        $primerControl = ((int) $request->input('primerControl') === 0) ? 'NO' : 'SI';
        $espFlujoRaw = $request->input('especialidad_nombre_flujo');
        $especialidad = ($espFlujoRaw !== null && trim((string) $espFlujoRaw) !== '') ? trim((string) $espFlujoRaw) : null;

        $medicoId = (int) $request->input('medico_id');
        $consultorioId = (int) $request->input('consultorio');
        $horario = $request->input('horario');
        $esVideollamada = (int) $request->input('esVideollamada', 0);
        $pacienteId = (int) $request->input('paciente_id');

        $blocked = $this->intentService->medicoPaymentBlockedReason($medicoId, $esVideollamada, $pacienteId, $fechaTurno);
        if ($blocked) {
            return response()->json(['ok' => false, 'message' => $blocked]);
        }

        if (!$this->intentService->medicoRequiresPayment($medicoId, $esVideollamada, $pacienteId, $fechaTurno)) {
            return response()->json(['ok' => false, 'message' => 'Este turno no requiere pago online.']);
        }

        $turnoController = app(TurnoController::class);
        $checkTurno = $turnoController->validarTurnoLibre($medicoId, $consultorioId, $dia, $horario, $fechaTurno);
        if ($checkTurno->count() > 0) {
            return response()->json(['ok' => false, 'message' => 'El horario ya no está disponible.']);
        }

        try {
            $result = $this->intentService->createIntent([
                'paciente_id' => (int) $request->input('paciente_id'),
                'medico_id' => $medicoId,
                'consultorio_id' => $consultorioId,
                'dia' => $dia,
                'horario' => $horario,
                'fecha_turno' => $fechaTurno,
                'primer_control' => $primerControl,
                'tipo_turno' => (int) $request->input('tipoTurno', 0),
                'especialidad' => $especialidad,
            ]);

            return response()->json([
                'ok' => true,
                'init_point' => $result['init_point'],
                'intent_id' => $result['intent']->id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()]);
        }
    }

    public function cancelarPagoPendiente(Request $request)
    {
        $intentId = (int) $request->input('intent_id');
        $pacienteId = (int) $request->input('paciente_id');

        if (!$intentId) {
            return response()->json(['ok' => false, 'message' => 'Intent inválido.']);
        }

        $cancelled = $this->intentService->cancelPendingIntentById($intentId, $pacienteId ?: null);

        return response()->json([
            'ok' => $cancelled,
            'message' => $cancelled ? null : 'No se encontró un pago pendiente para cancelar.',
        ]);
    }

    public function pagoExito(Request $request)
    {
        $intent = TurnoPagoIntent::find((int) $request->input('intent'));
        if (!$intent) {
            return view('turnos.pago_turno_resultado', [
                'status' => 'error',
                'message' => 'No se encontró la reserva de pago.',
            ]);
        }

        if ($request->filled('payment_id') && !$intent->payment_id) {
            try {
                $webhook = new MercadoPagoWebhookService();
                $webhook->processPaymentNotification($request->input('payment_id'));
                $intent->refresh();
            } catch (\Exception $e) {
                // webhook may process async
            }
        }

        $turno = TurnoRegistrado::find($intent->turno_registrado_id);
        if ($turno && (int) $turno->activo === 1 && (int) $turno->pago === 1) {
            $fechaAux = explode('-', $turno->fechaTurno);
            $fecha = $fechaAux[2] . '/' . $fechaAux[1] . '/' . $fechaAux[0];
            return view('turnos.turno_registrado')
                ->with('fechaSolicitada', $turno->fechaTurno)
                ->with('horario', $turno->horario)
                ->with('pagoOnline', true)
                ->with('importe', $turno->importe_reserva);
        }

        return view('turnos.pago_turno_resultado', [
            'status' => 'pending',
            'message' => 'Estamos confirmando su pago. Si ya abonó, el turno quedará registrado en unos instantes.',
            'intent' => $intent,
        ]);
    }

    public function pagoError(Request $request)
    {
        if (!\Auth::check() || (int) \Auth::user()->usuario_tipo !== 2) {
            session()->forget(['mp_prueba_medico', 'mp_prueba_compartida', 'mp_prueba_volver_url']);
        }

        $intent = TurnoPagoIntent::find((int) $request->input('intent'));
        if ($intent && $intent->isPending()) {
            $this->intentService->cancelIntent($intent, 'cancelled');
        }

        return view('turnos.pago_turno_resultado', [
            'status' => 'error',
            'message' => 'El pago no se completó. El horario fue liberado; puede intentar nuevamente.',
        ]);
    }

    public function pagoPendiente(Request $request)
    {
        return view('turnos.pago_turno_resultado', [
            'status' => 'pending',
            'message' => 'Su pago está pendiente de acreditación. Le avisaremos cuando se confirme el turno.',
            'intent' => TurnoPagoIntent::find((int) $request->input('intent')),
        ]);
    }

    public function webhook(Request $request)
    {
        $webhook = new MercadoPagoWebhookService();
        $result = $webhook->handle($request);

        if (env('MERCADOPAGO_WEBHOOK_SYNC', true)) {
            return response()->json($result);
        }

        return response()->json(['ok' => true, 'queued' => true]);
    }

    public function reembolsarReservaTurno(Request $request)
    {
        $medico = $this->getMedico();
        if (!$medico) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo identificar el médico.',
            ], 403);
        }

        $turnoId = (int) $request->input('turno_id');
        $cancelarTurno = (int) $request->input('cancelar_turno', 0) === 1;

        if (!$turnoId) {
            return response()->json([
                'success' => false,
                'message' => 'Turno inválido.',
            ]);
        }

        $user = Auth::user();
        if ($user && (int) $user->usuario_tipo === 3) {
            $account = MedicoMercadoPagoAccount::where('medico_id', $medico->id)->first();
            if (!$account || !$account->secretariaPuedeReembolso()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permiso para reembolsar reservas de este médico.',
                ], 403);
            }
        }

        try {
            $result = $this->intentService->refundTurnoReserva($turnoId, (int) $medico->id, $cancelarTurno);

            return response()->json([
                'success' => true,
                'message' => $cancelarTurno && $result['cancelado']
                    ? 'Reembolso realizado y turno cancelado.'
                    : 'Reembolso realizado correctamente.',
                'pago_estado' => $result['pago_estado'],
                'cancelado' => $result['cancelado'],
                'refund_id' => $result['refund_id'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
