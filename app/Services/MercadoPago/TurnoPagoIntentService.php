<?php

namespace App\Services\MercadoPago;

use App\TurnoPagoIntent;
use App\TurnoRegistrado;
use App\Paciente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TurnoPagoIntentService
{
    protected $marketplace;

    public function __construct(MercadoPagoMarketplaceService $marketplace = null)
    {
        $this->marketplace = $marketplace ?: new MercadoPagoMarketplaceService();
    }

    /**
     * @return array{requires_payment: bool, importe_reserva: float, blocked: bool, message: ?string, nombre_obra: ?string}
     */
    public function resolveReservaParaPaciente($medicoId, $pacienteId, $esVideollamada = 0, $fechaTurno = null)
    {
        $gratis = [
            'requires_payment' => false,
            'importe_reserva' => 0.0,
            'blocked' => false,
            'message' => null,
            'nombre_obra' => null,
        ];

        if ((int) $esVideollamada === 1) {
            return $gratis;
        }

        if (!$this->moduloActivo($medicoId, TurnoPagoHelper::MODULO_COBRO_TURNOS_MP)) {
            return $gratis;
        }

        $account = \App\MedicoMercadoPagoAccount::where('medico_id', $medicoId)->first();
        if (!$account || (int) $account->cobro_activo !== 1) {
            return $gratis;
        }

        if (!empty($account->cobro_desde) && $fechaTurno !== null) {
            $cobroDesde = $account->cobro_desde instanceof \Carbon\Carbon
                ? $account->cobro_desde->format('Y-m-d')
                : (string) $account->cobro_desde;
            if ($fechaTurno < $cobroDesde) {
                return $gratis;
            }
        }

        $importeReserva = $this->getImporteReservaParaPaciente($medicoId, $pacienteId);
        if ($importeReserva['importe'] <= 0) {
            return array_merge($gratis, [
                'nombre_obra' => $importeReserva['nombre_obra'],
            ]);
        }

        if (!$account->isLinked()) {
            return [
                'requires_payment' => false,
                'importe_reserva' => $importeReserva['importe'],
                'blocked' => true,
                'message' => 'Este profesional requiere pago online pero aún no tiene Mercado Pago configurado. Contacte al consultorio.',
                'nombre_obra' => $importeReserva['nombre_obra'],
            ];
        }

        return [
            'requires_payment' => true,
            'importe_reserva' => $importeReserva['importe'],
            'blocked' => false,
            'message' => null,
            'nombre_obra' => $importeReserva['nombre_obra'],
        ];
    }

    public function medicoRequiresPayment($medicoId, $esVideollamada = 0, $pacienteId = null, $fechaTurno = null)
    {
        if ($pacienteId) {
            $resolved = $this->resolveReservaParaPaciente($medicoId, $pacienteId, $esVideollamada, $fechaTurno);
            return $resolved['requires_payment'];
        }

        if ((int) $esVideollamada === 1) {
            return false;
        }

        if (!$this->moduloActivo($medicoId, TurnoPagoHelper::MODULO_COBRO_TURNOS_MP)) {
            return false;
        }

        $account = \App\MedicoMercadoPagoAccount::where('medico_id', $medicoId)->first();
        if (!$account || !$account->hasCobroReservaConfigurado()) {
            return false;
        }

        return $this->medicoTieneObraSocialConCobroReserva($medicoId);
    }

    public function medicoPaymentBlockedReason($medicoId, $esVideollamada = 0, $pacienteId = null, $fechaTurno = null)
    {
        if ($pacienteId) {
            $resolved = $this->resolveReservaParaPaciente($medicoId, $pacienteId, $esVideollamada, $fechaTurno);
            if ($resolved['blocked']) {
                return $resolved['message'];
            }
            return null;
        }

        if ((int) $esVideollamada === 1) {
            return null;
        }

        if (!$this->moduloActivo($medicoId, TurnoPagoHelper::MODULO_COBRO_TURNOS_MP)) {
            return null;
        }

        $account = \App\MedicoMercadoPagoAccount::where('medico_id', $medicoId)->first();
        if (!$account) {
            return null;
        }

        if ((int) $account->cobro_activo !== 1) {
            return null;
        }

        if (!$this->medicoTieneObraSocialConCobroReserva($medicoId)) {
            return null;
        }

        if (!$account->isLinked()) {
            return 'Este profesional requiere pago online pero aún no tiene Mercado Pago configurado. Contacte al consultorio.';
        }

        return null;
    }

    public function getPaymentInfo($medicoId, $pacienteId = null)
    {
        $account = \App\MedicoMercadoPagoAccount::where('medico_id', $medicoId)->first();
        if (!$account) {
            return null;
        }

        $importe = 0.0;
        if ($pacienteId) {
            $resolved = $this->resolveReservaParaPaciente($medicoId, $pacienteId, 0);
            $importe = $resolved['importe_reserva'];
        }

        return [
            'importe_reserva' => $importe,
            'cobro_activo' => (int) $account->cobro_activo,
        ];
    }

    public function createIntent(array $data)
    {
        $account = \App\MedicoMercadoPagoAccount::where('medico_id', $data['medico_id'])->first();
        if (!$account || !$account->hasCobroReservaConfigurado()) {
            throw new \RuntimeException('Este turno no requiere pago online.');
        }

        $pacienteId = (int) $data['paciente_id'];
        $fechaTurno = isset($data['fecha_turno']) ? (string) $data['fecha_turno'] : null;
        $resolved = $this->resolveReservaParaPaciente($data['medico_id'], $pacienteId, 0, $fechaTurno);
        if (!$resolved['requires_payment']) {
            throw new \RuntimeException('Este turno no requiere pago online.');
        }

        $amount = isset($data['amount']) ? (float) $data['amount'] : $resolved['importe_reserva'];
        if ($amount <= 0) {
            throw new \RuntimeException('Importe de reserva inválido.');
        }

        $this->expireStaleIntents();
        $this->cancelPendingIntentsForPacienteSlot(
            $pacienteId,
            (int) $data['medico_id'],
            (int) $data['consultorio_id'],
            (int) $data['dia'],
            $data['horario'],
            $data['fecha_turno']
        );

        if ($this->slotTienePagoPendiente(
            (int) $data['medico_id'],
            (int) $data['consultorio_id'],
            (int) $data['dia'],
            $data['horario'],
            $data['fecha_turno'],
            $pacienteId
        )) {
            throw new \RuntimeException('El horario ya no está disponible.');
        }

        $intent = TurnoPagoIntent::create([
            'paciente_id' => $pacienteId,
            'medico_id' => $data['medico_id'],
            'consultorio_id' => $data['consultorio_id'],
            'dia' => $data['dia'],
            'horario' => $data['horario'],
            'fecha_turno' => $data['fecha_turno'],
            'primer_control' => $data['primer_control'] ?? 'NO',
            'tipo_turno' => $data['tipo_turno'] ?? 0,
            'especialidad' => $data['especialidad'] ?? null,
            'amount' => $amount,
            'status' => 'pending',
            'expires_at' => now()->addMinutes(15),
        ]);

        try {
            $preference = $this->marketplace->createCheckoutPreference($intent, $account);
        } catch (\Exception $e) {
            $this->cancelIntent($intent, 'cancelled');
            throw $e;
        }

        return [
            'intent' => $intent,
            'init_point' => $preference['init_point'],
            'preference_id' => $preference['preference_id'],
        ];
    }

    public function cancelPendingIntentById($intentId, $pacienteId = null)
    {
        $intent = TurnoPagoIntent::find((int) $intentId);
        if (!$intent || !$intent->isPending()) {
            return false;
        }

        if ($pacienteId !== null && (int) $intent->paciente_id !== (int) $pacienteId) {
            return false;
        }

        $this->cancelIntent($intent, 'cancelled');
        return true;
    }

    public function cancelPendingIntentsForPacienteSlot($pacienteId, $medicoId, $consultorioId, $dia, $horario, $fechaTurno)
    {
        $intents = TurnoPagoIntent::where('paciente_id', $pacienteId)
            ->where('medico_id', $medicoId)
            ->where('consultorio_id', $consultorioId)
            ->where('dia', $dia)
            ->where('horario', $horario)
            ->where('fecha_turno', $fechaTurno)
            ->where('status', 'pending')
            ->get();

        foreach ($intents as $intent) {
            $this->cancelIntent($intent, 'cancelled');
        }
    }

    public function slotTienePagoPendiente($medicoId, $consultorioId, $dia, $horario, $fechaTurno, $excludePacienteId = null)
    {
        $this->expireStaleIntents();

        $query = TurnoPagoIntent::where('medico_id', $medicoId)
            ->where('consultorio_id', $consultorioId)
            ->where('dia', $dia)
            ->where('horario', $horario)
            ->where('fecha_turno', $fechaTurno)
            ->where('status', 'pending')
            ->where('expires_at', '>', now());

        if ($excludePacienteId !== null) {
            $query->where('paciente_id', '!=', (int) $excludePacienteId);
        }

        return $query->exists();
    }

    /**
     * Horarios bloqueados temporalmente por checkout MP pendiente.
     */
    public function getHorariosBloqueadosPorPagoPendiente($medicoId, $consultorioId, $dia, $fechaTurno, $tipoTurno = null)
    {
        $this->expireStaleIntents();

        $query = TurnoPagoIntent::where('medico_id', $medicoId)
            ->where('consultorio_id', $consultorioId)
            ->where('dia', $dia)
            ->where('fecha_turno', $fechaTurno)
            ->where('status', 'pending')
            ->where('expires_at', '>', now());

        if ($tipoTurno !== null && (int) $medicoId !== 13) {
            $query->where('tipo_turno', (int) $tipoTurno);
        }

        return $query->pluck('horario')->all();
    }

    /**
     * @return array{importe: float, nombre_obra: ?string, cobro_como_particular: bool}
     */
    protected function getImporteReservaParaPaciente($medicoId, $pacienteId)
    {
        $sinImporte = ['importe' => 0.0, 'nombre_obra' => null, 'cobro_como_particular' => false];

        $paciente = Paciente::find($pacienteId);
        if (!$paciente) {
            return $sinImporte;
        }

        $nombreOs = trim((string) ($paciente->obra_social ?? ''));
        if ($nombreOs === '') {
            return $sinImporte;
        }

        $row = $this->findObraSocialMedicoRow($medicoId, $nombreOs);
        if (!$row) {
            $particular = $this->getImporteReservaParticular($medicoId);
            return [
                'importe' => $particular['importe'],
                'nombre_obra' => $nombreOs,
                'cobro_como_particular' => true,
            ];
        }

        return [
            'importe' => (float) $row->importe_reserva,
            'nombre_obra' => $row->nombre,
            'cobro_como_particular' => false,
        ];
    }

    /**
     * Valor de reserva particular: importe_reserva de PARTICULAR, o importe de consulta si reserva es 0.
     *
     * @return array{importe: float}
     */
    protected function getImporteReservaParticular($medicoId)
    {
        $row = DB::table('obra_social_medicos')
            ->select('obra_social_medicos.importe_reserva', 'obra_social_medicos.importe')
            ->join('obra_socials', 'obra_socials.id', '=', 'obra_social_medicos.obra_social')
            ->where('obra_social_medicos.medico', $medicoId)
            ->where('obra_socials.nombre', 'PARTICULAR')
            ->where('obra_socials.activo', 1)
            ->where('obra_social_medicos.activo', 1)
            ->first();

        if (!$row) {
            return ['importe' => 0.0];
        }

        $importe = (float) $row->importe_reserva;
        if ($importe <= 0) {
            $importe = (float) $row->importe;
        }

        return ['importe' => $importe];
    }

    protected function findObraSocialMedicoRow($medicoId, $nombreOs)
    {
        $row = DB::table('obra_social_medicos')
            ->select('obra_socials.nombre', 'obra_social_medicos.importe_reserva')
            ->join('obra_socials', 'obra_socials.id', '=', 'obra_social_medicos.obra_social')
            ->where('obra_social_medicos.medico', $medicoId)
            ->where('obra_socials.activo', 1)
            ->where('obra_social_medicos.activo', 1)
            ->where('obra_socials.nombre', $nombreOs)
            ->first();

        if ($row) {
            return $row;
        }

        return DB::table('obra_social_medicos')
            ->select('obra_socials.nombre', 'obra_social_medicos.importe_reserva')
            ->join('obra_socials', 'obra_socials.id', '=', 'obra_social_medicos.obra_social')
            ->where('obra_social_medicos.medico', $medicoId)
            ->where('obra_socials.activo', 1)
            ->where('obra_social_medicos.activo', 1)
            ->whereRaw('LOWER(obra_socials.nombre) = ?', [mb_strtolower($nombreOs, 'UTF-8')])
            ->first();
    }

    public function medicoTieneObraSocialConCobroReserva($medicoId)
    {
        return DB::table('obra_social_medicos')
            ->where('medico', $medicoId)
            ->where('activo', 1)
            ->where('importe_reserva', '>', 0)
            ->exists();
    }

    public function approveIntent(TurnoPagoIntent $intent, $paymentId, $paymentStatus = 'approved')
    {
        if ($intent->isApproved()) {
            return $intent->turnoRegistrado;
        }

        if ($paymentStatus !== 'approved') {
            $intent->payment_id = $paymentId;
            $intent->status = $paymentStatus;
            $intent->save();
            return null;
        }

        DB::transaction(function () use ($intent, $paymentId, $paymentStatus) {
            $intent->payment_id = $paymentId;
            $intent->status = 'approved';
            $intent->save();

            $turno = null;
            if ($intent->turno_registrado_id) {
                $turno = TurnoRegistrado::find($intent->turno_registrado_id);
            }

            if ($turno && (int) $turno->activo === 2) {
                $turno->activo = 1;
                $turno->pago = 1;
                $turno->pago_estado = 'approved';
                $turno->mercadopago_payment_id = $paymentId;
                $turno->mercadopago_preference_id = $intent->preference_id;
                $turno->comentario = '';
                $turno->save();
            } else {
                if ($turno && (int) $turno->activo === 1 && (int) $turno->pago === 1) {
                    return;
                }

                $turno = $this->createTurnoConfirmadoDesdeIntent($intent, $paymentId);
                $intent->turno_registrado_id = $turno->id;
                $intent->save();
            }

            app(\App\Http\Controllers\TurnoController::class)->vincularMedicoPaciente($intent->medico_id, $intent->paciente_id);
            app(\App\Http\Controllers\TurnoController::class)->vincularSecretariaPaciente($intent->paciente_id, $intent->consultorio_id);
        });

        return TurnoRegistrado::find($intent->turno_registrado_id);
    }

    protected function createTurnoConfirmadoDesdeIntent(TurnoPagoIntent $intent, $paymentId)
    {
        $ocupado = TurnoRegistrado::where('medico', $intent->medico_id)
            ->where('consultorio', $intent->consultorio_id)
            ->where('dia', $intent->dia)
            ->where('horario', $intent->horario)
            ->where('fechaTurno', $intent->fecha_turno)
            ->whereIn('activo', [1, 2])
            ->exists();

        if ($ocupado) {
            throw new \RuntimeException('El horario ya no está disponible para confirmar el pago.');
        }

        $turno = new TurnoRegistrado();
        $turno->paciente = $intent->paciente_id;
        $turno->medico = $intent->medico_id;
        $turno->consultorio = $intent->consultorio_id;
        $turno->dia = $intent->dia;
        $turno->horario = $intent->horario;
        $turno->fechaTurno = $intent->fecha_turno;
        $turno->asistio = 0;
        $turno->sobreturno = 0;
        $turno->primerControl = $intent->primer_control;
        $turno->caja = 0;
        $turno->comentario = '';
        $turno->tipo_turno = $intent->tipo_turno;
        $turno->especialidad = $intent->especialidad;
        $turno->cancelado_por = '';
        $turno->otorgado_por = 'Paciente';
        $turno->msj_enviado = 0;
        $turno->activo = 1;
        $turno->pago = 1;
        $turno->pago_estado = 'approved';
        $turno->mercadopago_payment_id = $paymentId;
        $turno->mercadopago_preference_id = $intent->preference_id;
        $turno->importe_reserva = $intent->amount;
        $turno->turno_pago_intent_id = $intent->id;
        $turno->google_calendar_event_id = '';
        $turno->save();

        return $turno;
    }

    public function cancelIntent(TurnoPagoIntent $intent, $status = 'cancelled')
    {
        $intent->status = $status;
        $intent->save();

        if ($intent->turno_registrado_id) {
            $turno = TurnoRegistrado::find($intent->turno_registrado_id);
            if ($turno && (int) $turno->activo === 2) {
                $turno->activo = 0;
                $turno->pago_estado = $status;
                $turno->save();
            }
        }
    }

    public function expireStaleIntents()
    {
        $stale = TurnoPagoIntent::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($stale as $intent) {
            $this->cancelIntent($intent, 'expired');
        }
    }

    public function findByPaymentId($paymentId)
    {
        return TurnoPagoIntent::where('payment_id', $paymentId)->first();
    }

    public function findByPreferenceOrReference($externalReference, $paymentId = null)
    {
        if ($paymentId) {
            $byPayment = $this->findByPaymentId($paymentId);
            if ($byPayment) {
                return $byPayment;
            }
        }

        if ($externalReference) {
            return TurnoPagoIntent::find((int) $externalReference);
        }

        return null;
    }

    protected function moduloActivo($medicoId, $moduloId)
    {
        return DB::table('modulo_medicos')
            ->where('medico', $medicoId)
            ->where('modulo', $moduloId)
            ->where('activo', 1)
            ->exists();
    }

    public function refundTurnoReserva($turnoId, $medicoId, $cancelarTurno = false)
    {
        $turno = TurnoRegistrado::find((int) $turnoId);
        if (!$turno) {
            throw new \RuntimeException('No se encontró el turno.');
        }

        if ((int) $turno->medico !== (int) $medicoId) {
            throw new \RuntimeException('No tiene permiso para reembolsar este turno.');
        }

        if ((int) $turno->pago !== 1) {
            throw new \RuntimeException('Este turno no tiene un pago de reserva registrado.');
        }

        if ($turno->pago_estado === 'refunded') {
            throw new \RuntimeException('La reserva ya fue reembolsada.');
        }

        if ($turno->pago_estado !== 'approved') {
            throw new \RuntimeException('Solo se pueden reembolsar pagos confirmados.');
        }

        if (empty($turno->mercadopago_payment_id)) {
            throw new \RuntimeException('No hay ID de pago de Mercado Pago para reembolsar.');
        }

        $account = \App\MedicoMercadoPagoAccount::where('medico_id', $medicoId)->first();
        if (!$account || !$account->isLinked()) {
            throw new \RuntimeException('El médico no tiene Mercado Pago vinculado. No se puede procesar el reembolso.');
        }

        $refund = $this->marketplace->refundPayment(
            $turno->mercadopago_payment_id,
            $account
        );

        $refundId = isset($refund['id']) ? (string) $refund['id'] : null;
        $cancelado = false;

        DB::transaction(function () use ($turno, $cancelarTurno, $refundId, &$cancelado) {
            $turno->pago_estado = 'refunded';
            if ($refundId) {
                $turno->mercadopago_refund_id = $refundId;
            }

            if ($cancelarTurno && (int) $turno->activo === 1) {
                $turno->activo = 0;
                $turno->comentario = 'Reembolso reserva online';
                $cancelado = true;
            }

            $turno->save();

            if ($turno->turno_pago_intent_id) {
                $intent = TurnoPagoIntent::find($turno->turno_pago_intent_id);
                if ($intent) {
                    $intent->status = 'refunded';
                    $intent->save();
                }
            }
        });

        return [
            'refund_id' => $refundId,
            'pago_estado' => 'refunded',
            'cancelado' => $cancelado,
        ];
    }

    /**
     * Intenta reembolsar automáticamente la reserva cuando el paciente cancela.
     *
     * @return array{attempted: bool, success: bool, message: ?string}
     */
    public function tryAutoRefundOnPatientCancel(TurnoRegistrado $turno)
    {
        $sinIntento = [
            'attempted' => false,
            'success' => false,
            'message' => null,
        ];

        if ((int) $turno->pago !== 1 || $turno->pago_estado !== 'approved') {
            return $sinIntento;
        }

        $account = \App\MedicoMercadoPagoAccount::where('medico_id', $turno->medico)->first();
        if (!$account || !$account->tieneReembolsoCancelacionActivo()) {
            return $sinIntento;
        }

        if (!$account->permiteReembolsoCancelacionPaciente($turno->fechaTurno)) {
            return [
                'attempted' => false,
                'success' => false,
                'message' => 'El turno fue cancelado. La reserva no es reembolsable por cancelación fuera del plazo.',
            ];
        }

        try {
            $this->refundTurnoReserva($turno->id, $turno->medico, false);

            return [
                'attempted' => true,
                'success' => true,
                'message' => 'La reserva fue reembolsada automáticamente.',
            ];
        } catch (\Exception $e) {
            Log::warning('Auto-refund on patient cancel failed', [
                'turno_id' => $turno->id,
                'medico_id' => $turno->medico,
                'error' => $e->getMessage(),
            ]);

            return [
                'attempted' => true,
                'success' => false,
                'message' => 'El turno fue cancelado. No pudimos procesar el reembolso automático; contacte al consultorio.',
            ];
        }
    }
}
