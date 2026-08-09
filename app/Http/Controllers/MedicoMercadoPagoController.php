<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesMedicoFromAuth;
use App\MedicoMercadoPagoAccount;
use App\MercadoPagoPlatformSetting;
use App\Paciente;
use App\Helpers\EspecialidadFlujoHelper;
use App\Services\MercadoPago\MercadoPagoOAuthService;
use App\ObraSocialMedico;
use App\Services\MercadoPago\TurnoPagoHelper;
use App\Services\MercadoPago\TurnoPagoIntentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;

class MedicoMercadoPagoController extends Controller
{
    use ResolvesMedicoFromAuth;

    public function medicoAdminPagos()
    {
        return redirect()->route('medicoconfigpagos');
    }

    public function adminPagosMercadoPago()
    {
        $medico = $this->getMedico();
        if (!$medico) {
            return redirect()->route('secretaria_home');
        }

        $denied = $this->assertPuedeEditarPanelMp($medico);
        if ($denied) {
            return $denied;
        }

        $moduloActivo = $this->moduloExiste($medico->id, TurnoPagoHelper::MODULO_COBRO_TURNOS_MP)->count() > 0;
        $account = MedicoMercadoPagoAccount::firstOrCreate(
            ['medico_id' => $medico->id],
            ['cobro_activo' => 0, 'importe_reserva' => 0]
        );
        $platformSettings = MercadoPagoPlatformSetting::current();
        $redirectUri = (new MercadoPagoOAuthService($platformSettings))->redirectUri();
        $intentService = new TurnoPagoIntentService();
        $obrasSocialesMedico = DB::table('obra_social_medicos')
            ->join('obra_socials', 'obra_socials.id', '=', 'obra_social_medicos.obra_social')
            ->select(
                'obra_social_medicos.id as osmid',
                'obra_socials.nombre',
                'obra_social_medicos.importe_reserva',
                'obra_social_medicos.activo'
            )
            ->where('obra_social_medicos.medico', $medico->id)
            ->where('obra_social_medicos.activo', 1)
            ->where('obra_socials.activo', 1)
            ->orderBy('obra_socials.nombre', 'asc')
            ->get();
        $tieneObrasSociales = $obrasSocialesMedico->count() > 0;
        $puedeProbarReserva = $moduloActivo
            && $account->hasCobroReservaConfigurado()
            && $intentService->medicoTieneObraSocialConCobroReserva($medico->id);

        $esSecretaria = (int) Auth::user()->usuario_tipo === 3;

        return view('turnos_admin_medico.admin_pagos_mercadopago', compact(
            'medico',
            'moduloActivo',
            'account',
            'platformSettings',
            'redirectUri',
            'puedeProbarReserva',
            'obrasSocialesMedico',
            'tieneObrasSociales',
            'esSecretaria'
        ));
    }

    public function guardarPagosConfig(Request $request)
    {
        $medico = $this->getMedico();
        if (!$medico) {
            return redirect()->route('secretaria_home');
        }

        $denied = $this->assertPuedeEditarPanelMp($medico);
        if ($denied) {
            return $denied;
        }

        if ($this->moduloExiste($medico->id, TurnoPagoHelper::MODULO_COBRO_TURNOS_MP)->count() === 0) {
            return redirect()->route('medicoconfigpagos')
                ->with('error', 'El módulo de cobro de turnos no está activo para su perfil.');
        }

        $rules = [
            'cobro_desde' => 'nullable|date',
            'mensaje_aviso_cobro_titulo' => 'nullable|string|max:255',
            'mensaje_aviso_cobro' => 'nullable|string',
        ];

        if ($request->has('reembolso_cancelacion_activo')) {
            $rules['reembolso_cancelacion_dias_previos'] = 'required|integer|min:1|max:365';
        } else {
            $rules['reembolso_cancelacion_dias_previos'] = 'nullable|integer|min:1|max:365';
        }

        $request->validate($rules);

        $account = MedicoMercadoPagoAccount::firstOrCreate(['medico_id' => $medico->id]);
        $account->cobro_activo = $request->has('cobro_activo') ? 1 : 0;
        $account->cobro_desde = $request->filled('cobro_desde') ? $request->input('cobro_desde') : null;
        $account->mensaje_aviso_cobro_titulo = $request->filled('mensaje_aviso_cobro_titulo')
            ? trim($request->input('mensaje_aviso_cobro_titulo'))
            : null;
        $account->mensaje_aviso_cobro = $request->filled('mensaje_aviso_cobro')
            ? trim($request->input('mensaje_aviso_cobro'))
            : null;
        $account->reembolso_cancelacion_activo = $request->has('reembolso_cancelacion_activo') ? 1 : 0;
        $account->reembolso_cancelacion_dias_previos = $request->has('reembolso_cancelacion_activo')
            ? (int) $request->input('reembolso_cancelacion_dias_previos')
            : null;

        if ((int) Auth::user()->usuario_tipo === 2) {
            $account->secretaria_puede_reembolso = $request->has('secretaria_puede_reembolso') ? 1 : 0;
            $account->secretaria_puede_ver_panel_mp = $request->has('secretaria_puede_ver_panel_mp') ? 1 : 0;
        }

        $account->save();

        return redirect()->route('medicoconfigpagos')
            ->with('success', 'Configuración de pagos guardada.');
    }

    public function guardarCobroObraSocial(Request $request)
    {
        $medico = $this->getMedico();
        if (!$medico) {
            return response()->json(['ok' => false, 'message' => 'No autorizado.'], 403);
        }

        $denied = $this->assertPuedeEditarPanelMp($medico);
        if ($denied) {
            return response()->json(['ok' => false, 'message' => 'No autorizado.'], 403);
        }

        $request->validate([
            'obra_social_id' => 'required|integer',
            'importe_reserva' => 'required|numeric|min:0',
        ]);

        $osm = ObraSocialMedico::where('id', $request->input('obra_social_id'))
            ->where('medico', $medico->id)
            ->first();

        if (!$osm) {
            return response()->json(['ok' => false, 'message' => 'Obra social no encontrada.'], 404);
        }

        $osm->importe_reserva = $request->input('importe_reserva');
        $osm->save();

        return response()->json(['ok' => true]);
    }

    public function aplicarCobroTodasObraSociales(Request $request)
    {
        $medico = $this->getMedico();
        if (!$medico) {
            return redirect()->route('secretaria_home');
        }

        $denied = $this->assertPuedeEditarPanelMp($medico);
        if ($denied) {
            return $denied;
        }

        $request->validate([
            'importe_reserva' => 'required|numeric|min:0',
        ]);

        ObraSocialMedico::where('medico', $medico->id)
            ->where('activo', 1)
            ->whereIn('obra_social', function ($query) {
                $query->select('id')
                    ->from('obra_socials')
                    ->where('activo', 1);
            })
            ->update(['importe_reserva' => $request->input('importe_reserva')]);

        return redirect()->route('medicoconfigpagos')
            ->with('success', 'Importe de reserva aplicado a todas las obras sociales activas.');
    }

    public function vincularTodasObraSociales(Request $request)
    {
        $medico = $this->getMedico();
        if (!$medico) {
            return redirect()->route('secretaria_home');
        }

        $denied = $this->assertPuedeEditarPanelMp($medico);
        if ($denied) {
            return $denied;
        }

        $obrasSociales = DB::table('obra_socials')
            ->where('obra_socials.activo', 1)
            ->orderBy('obra_socials.nombre')
            ->get();

        $existentes = DB::table('obra_social_medicos')
            ->where('obra_social_medicos.medico', $medico->id)
            ->pluck('obra_social')
            ->all();

        foreach ($obrasSociales as $os) {
            if (in_array($os->id, $existentes, true)) {
                continue;
            }
            $osm = new ObraSocialMedico();
            $osm->medico = $medico->id;
            $osm->obra_social = $os->id;
            $osm->importe = 0;
            $osm->importe_reserva = 0;
            $osm->activo = 1;
            $osm->save();
        }

        return redirect()->route('medicoconfigpagos')
            ->with('success', 'Obras sociales vinculadas correctamente.');
    }

    public function connect()
    {
        $this->assertMedicoOnly();

        $medico = $this->getMedico();
        if (!$medico) {
            return redirect()->route('secretaria_home');
        }

        if ($this->moduloExiste($medico->id, TurnoPagoHelper::MODULO_COBRO_TURNOS_MP)->count() === 0) {
            return redirect()->route('medicoconfigpagos')
                ->with('error', 'El módulo de cobro de turnos no está activo.');
        }

        try {
            $oauth = new MercadoPagoOAuthService();
            return redirect()->away($oauth->buildAuthorizeUrl($medico->id));
        } catch (\Exception $e) {
            return redirect()->route('medicoconfigpagos')
                ->with('error', $e->getMessage());
        }
    }

    public function oauthCallback(Request $request)
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        try {
            $oauth = new MercadoPagoOAuthService();
            $medicoId = $oauth->validateState($request->input('state'));

            $medico = $this->getMedico();
            if (!$medico || (int) $medico->id !== $medicoId) {
                throw new \RuntimeException('No tiene permiso para vincular esta cuenta.');
            }

            if ($request->filled('error')) {
                throw new \RuntimeException('Mercado Pago rechazó la autorización: ' . $request->input('error'));
            }

            $oauth->exchangeCode($request->input('code'), $medicoId);

            return redirect()->route('medicoconfigpagos')
                ->with('success', 'Cuenta de Mercado Pago vinculada correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('medicoconfigpagos')
                ->with('error', $e->getMessage());
        }
    }

    public function disconnect()
    {
        $this->assertMedicoOnly();

        $medico = $this->getMedico();
        if (!$medico) {
            return redirect()->route('secretaria_home');
        }

        (new MercadoPagoOAuthService())->disconnect($medico->id);

        return redirect()->route('medicoconfigpagos')
            ->with('success', 'Cuenta de Mercado Pago desvinculada.');
    }

    /**
     * Genera un link firmado para que otra persona complete el pago de prueba.
     */
    public function generarLinkPruebaReserva(Request $request)
    {
        $medico = $this->getMedico();
        if (!$medico) {
            return redirect()->route('secretaria_home');
        }

        $denied = $this->assertPuedeEditarPanelMp($medico);
        if ($denied) {
            return $denied;
        }

        $error = $this->validarPuedeProbarReserva($medico);
        if ($error) {
            return redirect()->route('medicoconfigpagos')->with('error', $error);
        }

        $request->validate([
            'dni' => 'required|numeric',
        ], [
            'dni.required' => 'Ingresá el DNI del paciente de prueba.',
            'dni.numeric' => 'El DNI debe contener solo números.',
        ]);

        if (!$this->pacienteActivoPorDni($request->input('dni'))) {
            return redirect()->route('medicoconfigpagos')
                ->with('error', 'No se encontró un paciente activo con DNI ' . $request->input('dni') . '.');
        }

        $link = URL::temporarySignedRoute(
            'turno.prueba.pago.compartida',
            now()->addHours(72),
            [
                'medico' => (int) $medico->id,
                'dni' => $request->input('dni'),
            ]
        );

        return redirect()->route('medicoconfigpagos')
            ->with('link_prueba_pago', $link)
            ->with('dni_prueba_pago', $request->input('dni'))
            ->with('success', 'Link de prueba generado. Compartilo con quien va a realizar el pago (válido 72 hs).');
    }

    /**
     * Abre el flujo de prueba (médico logueado).
     */
    public function probarReservaTurno(Request $request)
    {
        $medico = $this->getMedico();
        if (!$medico) {
            return redirect()->route('secretaria_home');
        }

        $denied = $this->assertPuedeEditarPanelMp($medico);
        if ($denied) {
            return $denied;
        }

        $error = $this->validarPuedeProbarReserva($medico);
        if ($error) {
            return redirect()->route('medicoconfigpagos')->with('error', $error);
        }

        $request->validate([
            'dni' => 'required|numeric',
        ], [
            'dni.required' => 'Ingresá el DNI del paciente de prueba.',
            'dni.numeric' => 'El DNI debe contener solo números.',
        ]);

        try {
            return $this->renderVistaPruebaReservaTurno((int) $medico->id, $request->input('dni'), false);
        } catch (\RuntimeException $e) {
            return redirect()->route('medicoconfigpagos')->with('error', $e->getMessage());
        }
    }

    /**
     * Link público firmado — sin login; lo abre quien va a pagar.
     */
    public function probarReservaTurnoPublico(Request $request)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'El link de prueba expiró o no es válido. Pedile al médico que genere uno nuevo.');
        }

        $medicoId = (int) $request->query('medico');
        $dni = $request->query('dni');

        $medico = DB::table('medicos')->where('id', $medicoId)->where('activo', 1)->first();
        if (!$medico) {
            abort(404, 'Médico no encontrado.');
        }

        $error = $this->validarPuedeProbarReserva($medico);
        if ($error) {
            abort(403, $error);
        }

        try {
            return $this->renderVistaPruebaReservaTurno($medicoId, $dni, true);
        } catch (\RuntimeException $e) {
            abort(403, $e->getMessage());
        }
    }

    protected function assertMedicoOnly()
    {
        if (Auth::check() && (int) Auth::user()->usuario_tipo === 3) {
            abort(403, 'Solo el médico puede vincular o desvincular Mercado Pago.');
        }
    }

    /**
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function assertPuedeEditarPanelMp($medico)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        if ((int) $user->usuario_tipo === 2) {
            return null;
        }

        if ((int) $user->usuario_tipo === 3) {
            $account = MedicoMercadoPagoAccount::where('medico_id', $medico->id)->first();
            if ($account && $account->secretariaPuedeVerPanelMp()) {
                return null;
            }

            return redirect()->route('secretaria_home')
                ->with('error', 'No tiene acceso a la sección de Mercado Pago. Debe ser habilitada por el especialista.');
        }

        return redirect()->route('secretaria_home')->with('error', 'No autorizado.');
    }

    protected function validarPuedeProbarReserva($medico)
    {
        $account = MedicoMercadoPagoAccount::where('medico_id', $medico->id)->first();
        $intentService = new TurnoPagoIntentService();
        if (!$account || !$account->hasCobroReservaConfigurado()) {
            return 'Activá el cobro, definí importes por obra social y vinculá Mercado Pago antes de probar.';
        }
        if (!$intentService->medicoTieneObraSocialConCobroReserva($medico->id)) {
            return 'Definí al menos una obra social con importe de reserva mayor a cero.';
        }
        if ($this->moduloExiste($medico->id, TurnoPagoHelper::MODULO_COBRO_TURNOS_MP)->count() === 0) {
            return 'El módulo de cobro de turnos no está activo.';
        }

        return null;
    }

    protected function pacienteActivoPorDni($dni)
    {
        return DB::table('pacientes')
            ->where('dni', $dni)
            ->where('activo', 1)
            ->exists();
    }

    protected function renderVistaPruebaReservaTurno($medicoId, $dni, $linkCompartido)
    {
        if (!$this->pacienteActivoPorDni($dni)) {
            throw new \RuntimeException('No se encontró un paciente activo con DNI ' . $dni . '.');
        }

        $medico = DB::table('medicos')->where('id', $medicoId)->where('activo', 1)->first();
        if (!$medico) {
            throw new \RuntimeException('Médico no encontrado.');
        }

        $paciente = Paciente::where('dni', $dni)->where('activo', 1)->first();
        $consultorio = DB::table('consultorios')
            ->where('id', $medico->consultorio)
            ->where('activo', 1)
            ->first();

        if (!$consultorio) {
            throw new \RuntimeException('No se encontró el consultorio del médico.');
        }

        /** @var PacienteController $pacienteCtrl */
        $pacienteCtrl = app(PacienteController::class);
        /** @var TurnoController $turnoCtrl */
        $turnoCtrl = app(TurnoController::class);

        $primerControl = 0;
        $tipoTurno = 1;
        $esVideollamada = 0;

        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $aPartirDia = date('Y-m-d');

        $fechaLibreDisponible_aux1 = $pacienteCtrl->turnoLibreMasCercano($medicoId, $primerControl, 0, $aPartirDia);
        if (empty($fechaLibreDisponible_aux1)) {
            throw new \RuntimeException('No hay turnos disponibles para probar en este momento.');
        }

        $fechaLibreDisponible1Aux = explode('-', $fechaLibreDisponible_aux1);
        $fechaLibreDisponible1 = $pacienteCtrl->convertirFechaMostrar($fechaLibreDisponible_aux1);

        if ($medicoId != 9) {
            $siguienteDia = date('Y-m-d', strtotime('+1 day', strtotime($fechaLibreDisponible_aux1)));
            $fechaLibreDisponible_aux = $pacienteCtrl->turnoLibreMasCercano($medicoId, $primerControl, 0, $siguienteDia);
            $fechaLibreDisponible2 = $pacienteCtrl->convertirFechaMostrar($fechaLibreDisponible_aux);

            $siguienteDia = date('Y-m-d', strtotime('+1 day', strtotime($fechaLibreDisponible_aux)));
            $fechaLibreDisponible_aux = $pacienteCtrl->turnoLibreMasCercano($medicoId, $primerControl, 0, $siguienteDia);
            $fechaLibreDisponible3 = $pacienteCtrl->convertirFechaMostrar($fechaLibreDisponible_aux);
        } else {
            $fechaLibreDisponible2 = null;
            $fechaLibreDisponible3 = null;
        }

        $dias_habilitados = $pacienteCtrl->diasHabilitados($medicoId, $consultorio->id, 0, $tipoTurno);
        $diasAtencion = $pacienteCtrl->diasAtencion($medicoId, $consultorio->id, 0, $tipoTurno);
        $dias_deshabilitados = $pacienteCtrl->diasDeshabilitados($diasAtencion);

        $moduloRecetas = $pacienteCtrl->moduloActivo($medicoId, 5);
        $moduloVideollamadas = $pacienteCtrl->moduloActivo($medicoId, 6);
        $medicoConfigAux = $pacienteCtrl->getMedicoConfig($medicoId, 9);

        $valorConsulta = 0;
        if ($medicoConfigAux != null) {
            $end_date = $medicoConfigAux->valor_string;
            $end_date_integer = $medicoConfigAux->valor_integer;
            $valorConsulta = $medicoConfigAux->valor_consulta;
        } else {
            $end_date = '+180d';
            $end_date_integer = '180';
        }

        $diaHoy = date('Y-m-d');
        $ventanaTiempoTope = date('Y-m-d', strtotime('+' . $end_date_integer . ' day', strtotime($diaHoy)));
        $fechaLibreDisponibleAux = $fechaLibreDisponible1Aux[2] . '-' . $fechaLibreDisponible1Aux[1] . '-' . $fechaLibreDisponible1Aux[0];
        $diferenciaDias = $pacienteCtrl->getDiferenciaDiasFechas($fechaLibreDisponibleAux, $ventanaTiempoTope);

        $obrasSociales = $pacienteCtrl->getDiferencialObraSocial($medicoId);
        $diferencialPaciente = $turnoCtrl->getImporteDiferencialParaPaciente($medicoId, $paciente);
        $especialidadNombreFlujo = EspecialidadFlujoHelper::nombreParaTurno(null, null, $medico->especialidad, $medico->especialidad);

        session([
            'mp_prueba_medico' => true,
            'mp_prueba_compartida' => $linkCompartido,
            'mp_prueba_volver_url' => $linkCompartido ? null : route('medicoconfigpagos'),
        ]);

        return view('turnos.seleccionar_dia')
            ->with('esVideollamada', $esVideollamada)
            ->with('tipoTurno', $tipoTurno)
            ->with('end_date', $end_date)
            ->with('obrasSociales', $obrasSociales)
            ->with('diferencialPaciente', $diferencialPaciente)
            ->with('diferenciaDias', $diferenciaDias)
            ->with('medico', $medico)
            ->with('consultorio', $consultorio)
            ->with('diasAtencion', $diasAtencion)
            ->with('especialidad_nombre_flujo', $especialidadNombreFlujo)
            ->with('especialidad_id', $medico->especialidad)
            ->with('paciente', $paciente)
            ->with('valorConsulta', $valorConsulta)
            ->with('fechaLibreDisponible', $fechaLibreDisponible1)
            ->with('fechaLibreDisponible1', $fechaLibreDisponible2)
            ->with('fechaLibreDisponible2', $fechaLibreDisponible3)
            ->with('primerControl', $primerControl)
            ->with('dias_habilitados', $dias_habilitados)
            ->with('moduloRecetas', $moduloRecetas)
            ->with('moduloVideollamadas', $moduloVideollamadas)
            ->with('dias_deshabilitados', $dias_deshabilitados)
            ->with('modoPruebaMedico', true)
            ->with('modoPruebaCompartida', $linkCompartido);
    }
}
