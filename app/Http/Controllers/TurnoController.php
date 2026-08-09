<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
//use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use App\HorarioMedico;
use App\HorarioMedicoVideollamada;
use App\HorarioMedicoDhsVideollamada;
use App\TurnoRegistradoVideollamada;
use App\Consultorio;
use App\Medico;
use App\TurnoRegistrado;
use DateTime;
use Mail;
use Image;
use App\Mail\SendMailable;
use App\Mail\EnviarMensajePacientes;
use App\Mail\EnviarConsentimientoTelemedicina;
use App\Mail\EnviarConfirmacionTurno;
use App\Feriado;
use App\Paciente;
use App\HorarioMedicoDH;
use App\MedicoPrimerControl;
use App\Videollamada;
use App\ObraSocialMedico;
use App\Helpers\EspecialidadFlujoHelper;
use App\Helpers\TurnoTestMedicoHelper;
use App\MedicoPaciente;
use App\User;
use App\PacienteSecretaria;
use App\Services\OneSignalService;
use App\Services\GoogleCalendarService;
use App\Services\MercadoPago\TurnoPagoIntentService;
use App\ListaNegra;

//use App\especialidad;

class TurnoController extends Controller
{
    /** Mostrar a pacientes solo el par de turnos vigente (ventana de 2 slots consecutivos). */
    const MODULO_MOSTRAR_DOS = 11;
    const MODULO_COBRO_TURNOS_MP = 12;

    public function getMensajeMedicoEspecial(Request $request)
    {
        $medicoId = (int) $request->input('medico_id');
        $fecha = $request->input('fecha'); // DD/MM/YYYY o YYYY-MM-DD

        if (!$medicoId) {
            return response()->json(['mensajes' => []]);
        }

        if (!$fecha) {
            $fecha = date('Y-m-d');
        }

        if (strpos($fecha, '/') !== false) {
            $aux = explode('/', $fecha);
            if (count($aux) === 3) {
                $fecha = $aux[2] . '-' . $aux[1] . '-' . $aux[0];
            }
        }

        $mensajes = DB::table('medico_mensajes_especiales')
            ->where('medico_mensajes_especiales.medico_id', $medicoId)
            ->where('medico_mensajes_especiales.activo', 1)
            ->where(function ($q) use ($fecha) {
                $q->whereNull('medico_mensajes_especiales.valido_desde')
                    ->orWhere('medico_mensajes_especiales.valido_desde', '<=', $fecha);
            })
            ->where(function ($q) use ($fecha) {
                $q->whereNull('medico_mensajes_especiales.valido_hasta')
                    ->orWhere('medico_mensajes_especiales.valido_hasta', '>=', $fecha);
            })
            ->orderByDesc('medico_mensajes_especiales.id')
            ->get(['id', 'titulo', 'descripcion', 'valido_desde', 'valido_hasta']);

        $avisoCobro = null;
        $account = \App\MedicoMercadoPagoAccount::where('medico_id', $medicoId)->first();
        if ($account
            && (int) $account->cobro_activo === 1
            && trim((string) $account->mensaje_aviso_cobro) !== ''
        ) {
            $cobroDesde = null;
            if (!empty($account->cobro_desde)) {
                $cobroDesde = $account->cobro_desde instanceof \Carbon\Carbon
                    ? $account->cobro_desde->format('Y-m-d')
                    : (string) $account->cobro_desde;
            }
            $fechaFmt = $cobroDesde
                ? \Carbon\Carbon::parse($cobroDesde)->format('d/m/Y')
                : '';
            $descripcion = str_replace(['[fecha]', '{fecha}'], $fechaFmt, $account->mensaje_aviso_cobro);
            $avisoCobro = [
                'titulo' => $account->mensaje_aviso_cobro_titulo ?: 'Aviso sobre reservas de turnos',
                'descripcion' => $descripcion,
                'cobro_desde' => $cobroDesde,
            ];
        }

        return response()->json([
            'mensajes' => $mensajes,
            'aviso_cobro' => $avisoCobro,
        ]);
    }

    
    public function adminTurnosIndex()
    {
    	$medicos = DB::select('select * from medicos where activo=1');
    	$consultorios = DB::select('select * from consultorios where activo=1');
    	
    	return view('turnos_admin.admin_turnos')->with('medicos',$medicos)->with('consultorios',$consultorios);
    }

    function turnoLibreMasCercanoTipoTurno($medico, $primerControl, $esVideollamada, $dia, $tipoTurno){
        // date_default_timezone_set('America/Argentina/Buenos_Aires');
        // $dia= date("Y-m-d");        
        $encontre = 0;                

        if($esVideollamada == 0){
            // 3 corresponde a Primer Control Doble
            $moduloPrimerControlDoble = $this->moduloActivo($medico, 3);
            if($moduloPrimerControlDoble == 0)
                $primerControl = 0;
        }

        while($encontre == 0){
            if($this->esFeriado($dia) == 0) // devuelve 1 si es feriado, 0 caso contrario
                $hayTurnoLibre = $this->checkTurnoLibreTipoTurno($medico, $dia, $primerControl, $esVideollamada, $tipoTurno);
            else
               $hayTurnoLibre = 0; 
            if($hayTurnoLibre == 0){ // es decir no hay turno libre, avanzo de dia.            
                $siguienteDia = $dia;              
                $siguienteDia = date('Y-m-d', strtotime('+1 day' , strtotime ( $siguienteDia )));            
                $dia = $siguienteDia;
            } else {
                $encontre = 1;
            }
        }
        return $dia;
    }

    // retorna 1 si hay al menos 1 turno libre, sino retorna 0.
    // Unifica la lógica con la de createJson/createJsonTurnosDobles para evitar inconsistencias
    // (por ejemplo, horarios especiales, fechas agregadas, etc.).
    function checkTurnoLibreTipoTurno($medico_id, $fecha, $primerControl, $esVideollamada, $tipoTurno, $consultorio_id = null){
        $medico = medico::find($medico_id);
        if(!$medico){
            return 0;
        }

        if($consultorio_id === null){
            $consultorio_id = $medico->consultorio;
        }

        $diaSeleccionado = $this->getDiaSeleccionado2($fecha);

        // Videollamada usa su propio set de horarios
        if($esVideollamada == 1){
            $dataJson = $this->createJsonVideollamada($medico_id, $consultorio_id, $diaSeleccionado, $fecha);
            $cantidadPrimerControlPermitido = true;
        } else {
            // Modulo id: 3 | corresponde a Primer Control Doble
            $moduloPrimerControlDoble = $this->moduloActivo($medico_id, 3);
            if (($primerControl == 0) || ($moduloPrimerControlDoble == 0)){
                // turnos comunes
                $dataJson = $this->createJson($medico_id, $consultorio_id, $diaSeleccionado, $fecha, $tipoTurno);
                $cantidadPrimerControlPermitido = true;
            } else {
                // turnos dobles (primer control)
                $cantidadPrimerControlPermitido = $this->controlarCantidadPrimerControl($medico_id, $diaSeleccionado, $consultorio_id, $fecha);
                if($cantidadPrimerControlPermitido){
                    $dataJson = $this->createJsonTurnosDobles($medico_id, $consultorio_id, $diaSeleccionado, $fecha, $tipoTurno);
                } else {
                    $dataJson = null;
                }
            }
        }

        if(!$cantidadPrimerControlPermitido || !$dataJson){
            return 0;
        }

        $turnosArray = json_decode($dataJson, true);
        if(!is_array($turnosArray)){
            return 0;
        }

        $turnosArray = $this->aplicarMostrarDosPaciente($medico_id, $turnosArray);

        foreach ($turnosArray as $turno) {
            if ($this->turnoFilaEsReservablePaciente($turno)) {
                return 1;
            }
        }

        return 0;
    }


    function seleccionarDiaTurnoOnline($request) {
        $opcion = $request->tipo_turno;
        $medico = medico::find($request->medico_id);        
        $consultorio = DB::select('select * from consultorios where id = ? and activo=1', [$request->consultorio_id]);        
        $paciente = paciente::find($request->paciente_id);
        $primerControl = $request->primerControl;
        $moduloRecetas = $request->moduloRecetas;
        $tipoTurno = 0;
        $especialidadNombreFlujo = EspecialidadFlujoHelper::nombreParaTurno(
            $request->input('especialidad_nombre_flujo'),
            $request->input('especialidad_txt'),
            $request->input('especialidad_id'),
            $medico->especialidad
        );
        
        $modulo = 9; // corresponde a ventana dias
        $medicoConfigAux = $this->getMedicoConfig($medico->id, $modulo);

        if($medicoConfigAux != null){
            $end_date = $medicoConfigAux->valor_string;                    
            $end_date_integer = $medicoConfigAux->valor_integer;
        } else {
            $end_date = '+180d'; 
            $end_date_integer = '180';       
        }

        $diaHoy = date("Y-m-d");
        $ventanaTiempoTope = date('Y-m-d', strtotime('+'.$end_date_integer.' day' , strtotime ( $diaHoy )));  
        $esVideollamada = 0;
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $aPartirDia = date("Y-m-d");

        $fechaLibreDisponible_aux1 = $this->turnoLibreMasCercanoTipoTurno($request->get('medico_id'), $primerControl, 0, $aPartirDia, $opcion); 
        
        $fechaLibreDisponible1Aux = explode('-', $fechaLibreDisponible_aux1);     
        $fechaLibreDisponible1 = $this->convertirFechaMostrar($fechaLibreDisponible_aux1);

        if($request->get('medico_id') != 9 && $request->get('medico_id') != 11){
            $siguienteDia = date('Y-m-d', strtotime('+1 day' , strtotime ( $fechaLibreDisponible_aux1 )));            
            $fechaLibreDisponible_aux = $this->turnoLibreMasCercanoTipoTurno($request->get('medico_id'), $primerControl, 0, $siguienteDia, $opcion);
            $fechaLibreDisponible2 = $this->convertirFechaMostrar($fechaLibreDisponible_aux);

            $siguienteDia = date('Y-m-d', strtotime('+1 day' , strtotime ( $fechaLibreDisponible_aux )));            
            $fechaLibreDisponible_aux = $this->turnoLibreMasCercanoTipoTurno($request->get('medico_id'), $primerControl, 0, $siguienteDia, $opcion);
            $fechaLibreDisponible3 = $this->convertirFechaMostrar($fechaLibreDisponible_aux);
        } else {
            $fechaLibreDisponible2 = null;
            $fechaLibreDisponible3 = null;
        }

        $dias_habilitados = $this->diasHabilitados($medico->id, $consultorio[0]->id, 0, $opcion);            
        $diasAtencion = $this->diasAtencion($medico->id, $consultorio[0]->id, 0, $opcion);
        
        $dias_deshabilitados = $this->diasDeshabilitados($diasAtencion);

        $fechaLibreDisponible2Aux= $fechaLibreDisponible1Aux[2].'-'.$fechaLibreDisponible1Aux[1].'-'.$fechaLibreDisponible1Aux[0];                          
        $diferenciaDias = $this->getDiferenciaDiasFechas($fechaLibreDisponible2Aux, $ventanaTiempoTope);

        $obrasSociales = $this->getDiferencialObraSocial($medico->id);
        $diferencialPaciente = $this->getImporteDiferencialParaPaciente($medico->id, $paciente);
        return view('turnos.seleccionar_dia')
                        ->with('esVideollamada', $esVideollamada)
                        ->with('tipoTurno', $opcion)
                        ->with('obrasSociales', $obrasSociales)
                        ->with('diferencialPaciente', $diferencialPaciente)
                        ->with('end_date', $end_date)
                        ->with('medico', $medico)
                        ->with('consultorio',$consultorio[0])
                        ->with('diasAtencion',$diasAtencion)
                        ->with('paciente',$paciente)
                        ->with('fechaLibreDisponible',$fechaLibreDisponible1)
                        ->with('fechaLibreDisponible1',$fechaLibreDisponible2)
                        ->with('fechaLibreDisponible2',$fechaLibreDisponible3)
                        ->with('diferenciaDias',$diferenciaDias)
                        ->with('primerControl',$primerControl)
                        ->with('dias_habilitados',$dias_habilitados)
                        ->with('moduloRecetas',$moduloRecetas)
                        ->with('dias_deshabilitados',$dias_deshabilitados)
                        ->with('especialidad_nombre_flujo', $especialidadNombreFlujo)
                        ->with('especialidad_id', $request->input('especialidad_id'));
    }

    function getDiferencialObraSocial($medico_id) {        
        
        $particularAux = DB::table('obra_social_medicos')
                    ->select('obra_socials.nombre', 'obra_social_medicos.importe')
                    ->join('obra_socials', 'obra_socials.id', '=', 'obra_social_medicos.obra_social')
                    ->where('obra_social_medicos.medico', $medico_id)
                    ->where('obra_socials.nombre', 'PARTICULAR')            
                    ->where('obra_socials.activo', 1)            
                    ->where('obra_social_medicos.activo', 1)
                    ->first();
                    
        $particular = $particularAux->importe;
        $query_os = DB::table('obra_social_medicos')
        ->select('obra_socials.nombre', 'obra_social_medicos.importe')
        ->join('obra_socials', 'obra_socials.id', '=', 'obra_social_medicos.obra_social')
        ->where('obra_social_medicos.medico', $medico_id)
        ->where('obra_socials.activo', 1)
        ->where('obra_social_medicos.activo', 1)
        ->get()
        ->map(function ($item) use ($particular) {
            if ($item->importe == 0) {
                $item->importe = $particular;
            }
            return $item;
        });

        return $query_os;
    }

    /**
     * Diferencial de consulta para la obra social del paciente (obra_social_medicos + nombre en pacientes).
     * Misma regla que getDiferencialObraSocial: importe 0 se reemplaza por el de PARTICULAR.
     *
     * @param  int|\stdClass|\App\Paciente  $paciente
     * @return object{encontrado: bool, nombre_obra: ?string, importe: ?float, mensaje: ?string}
     */
    public function getImporteDiferencialParaPaciente($medico_id, $paciente)
    {
        $sinObra = (object) [
            'encontrado' => false,
            'nombre_obra' => null,
            'importe' => null,
            'mensaje' => 'sin_obra',
        ];

        if (!$paciente) {
            return $sinObra;
        }

        $nombreOs = trim((string) (is_object($paciente) ? ($paciente->obra_social ?? '') : ''));
        if ($nombreOs === '') {
            return $sinObra;
        }

        $particularAux = DB::table('obra_social_medicos')
            ->select('obra_social_medicos.importe')
            ->join('obra_socials', 'obra_socials.id', '=', 'obra_social_medicos.obra_social')
            ->where('obra_social_medicos.medico', $medico_id)
            ->where('obra_socials.nombre', 'PARTICULAR')
            ->where('obra_socials.activo', 1)
            ->where('obra_social_medicos.activo', 1)
            ->first();

        $particular = $particularAux ? (float) $particularAux->importe : null;

        $row = DB::table('obra_social_medicos')
            ->select('obra_socials.nombre', 'obra_social_medicos.importe')
            ->join('obra_socials', 'obra_socials.id', '=', 'obra_social_medicos.obra_social')
            ->where('obra_social_medicos.medico', $medico_id)
            ->where('obra_socials.activo', 1)
            ->where('obra_social_medicos.activo', 1)
            ->where('obra_socials.nombre', $nombreOs)
            ->first();

        if (!$row) {
            $row = DB::table('obra_social_medicos')
                ->select('obra_socials.nombre', 'obra_social_medicos.importe')
                ->join('obra_socials', 'obra_socials.id', '=', 'obra_social_medicos.obra_social')
                ->where('obra_social_medicos.medico', $medico_id)
                ->where('obra_socials.activo', 1)
                ->where('obra_social_medicos.activo', 1)
                ->whereRaw('LOWER(obra_socials.nombre) = ?', [mb_strtolower($nombreOs, 'UTF-8')])
                ->first();
        }

        if (!$row) {
            return (object) [
                'encontrado' => false,
                'nombre_obra' => $nombreOs,
                'importe' => null,
                'mensaje' => 'no_tabla',
            ];
        }

        $importe = (float) $row->importe;
        if ($importe == 0 && $particular !== null) {
            $importe = (float) $particular;
        }

        return (object) [
            'encontrado' => true,
            'nombre_obra' => $row->nombre,
            'importe' => $importe,
            'mensaje' => null,
        ];
    }

    public function selectDia(Request $request)
    {   
        session()->forget(['mp_prueba_medico', 'mp_prueba_compartida', 'mp_prueba_volver_url']);

        if($request->tipo_turno == 22) {
            return $this->seleccionarDiaTurnoOnline($request);
        }    
        $tipoTurno = $request->tipoTurno;
        $esVideollamada = $request->get('esVideollamada');
        $primerControl = $request->get('primer_control'); 	            
        $paciente = DB::table('pacientes')->find($request->paciente_id);
        $dni_paciente = $request->get('dni_paciente');
    	$medicoRow = TurnoTestMedicoHelper::resolverMedicoFlujoPaciente((int) $request->get('medico_id'));
        if (!$medicoRow) {
            abort(404);
        }
        $medico = [$medicoRow];
    	$consultorio = DB::select('select * from consultorios where id = ? and activo=1', [$medico[0]->consultorio]);
    	$especialidadNombreFlujo = EspecialidadFlujoHelper::nombreParaTurno(
            $request->input('especialidad_nombre_flujo'),
            $request->input('especialidad_txt'),
            $request->input('especialidad_id'),
            $medico[0]->especialidad ?? null
        );
    	
        if($esVideollamada == 0){
            $esVideollamada = 0;

            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $aPartirDia = date("Y-m-d");
            
            $fechaLibreDisponible_aux1 = $this->turnoLibreMasCercanoTipoTurno($request->get('medico_id'), $primerControl, 0, $aPartirDia, 1); 
            
            $fechaLibreDisponible1Aux = explode('-', $fechaLibreDisponible_aux1);     
            $fechaLibreDisponible1 = $this->convertirFechaMostrar($fechaLibreDisponible_aux1);

            if($request->get('medico_id') != 9) {
                $siguienteDia = date('Y-m-d', strtotime('+1 day' , strtotime ( $fechaLibreDisponible_aux1 )));            
                $fechaLibreDisponible_aux = $this->turnoLibreMasCercanoTipoTurno($request->get('medico_id'), $primerControl, 0, $siguienteDia, 1);
                $fechaLibreDisponible2 = $this->convertirFechaMostrar($fechaLibreDisponible_aux);

                $siguienteDia = date('Y-m-d', strtotime('+1 day' , strtotime ( $fechaLibreDisponible_aux )));            
                $fechaLibreDisponible_aux = $this->turnoLibreMasCercanoTipoTurno($request->get('medico_id'), $primerControl, 0, $siguienteDia, 1);
                $fechaLibreDisponible3 = $this->convertirFechaMostrar($fechaLibreDisponible_aux);
            } else {
                $fechaLibreDisponible2 = null;
                $fechaLibreDisponible3 = null;
            }

            // $fechaLibreDisponible_aux = $this->turnoLibreMasCercano($medico[0]->id, $primerControl, 0);
            // $fecha_aux = explode('-',$fechaLibreDisponible_aux);        
            // $fechaLibreDisponible= $fecha_aux[2].'/'.$fecha_aux[1].'/'.$fecha_aux[0];                          
            // Se calcula más abajo (necesita ventana de fecha para aplicar vigencia valido_desde/valido_hasta).
            $dias_habilitados = '';
            $diasAtencion = [];
            $dias_deshabilitados = '';
        } else {
            $esVideollamada = 1;
            $fechaLibreDisponible_aux = $this->turnoLibreMasCercano($medico[0]->id, $primerControl, 1);
            $fecha_aux = explode('-',$fechaLibreDisponible_aux);                    
            $fechaLibreDisponible1 = $fecha_aux[2].'/'.$fecha_aux[1].'/'.$fecha_aux[0];                                      
            // Se calcula más abajo (necesita ventana de fecha para aplicar vigencia valido_desde/valido_hasta).
            $dias_habilitados = '';
            $diasAtencion = [];
            $dias_deshabilitados = '';
            $fechaLibreDisponible2 = null;
            $fechaLibreDisponible3 = null;     
        }    

        $moduloRecetas = $this->moduloActivo($request->get('medico_id'), 5);  // el 5 corresponde a las recetas               
        
        $modulo = 9; // corresponde a ventana dias
        $medicoConfigAux = $this->getMedicoConfig($medico[0]->id, $modulo);
        $valorConsulta = 0;
        if($medicoConfigAux != null){
            $end_date = $medicoConfigAux->valor_string;                    
            $end_date_integer = $medicoConfigAux->valor_integer;
            $valorConsulta = $medicoConfigAux->valor_consulta;
        } else {
            $end_date = '+180d'; 
            $end_date_integer = '180';       
        }

        $diaHoy = date("Y-m-d");
        $ventanaTiempoTope = date('Y-m-d', strtotime('+'.$end_date_integer.' day' , strtotime ( $diaHoy )));

        // Días habilitados / atención: aplicar vigencia en la ventana (no solo desde "hoy").
        $fechaInicioDias = date("Y-m-d");
        $dias_habilitados = $this->diasHabilitados($medico[0]->id, $consultorio[0]->id, $esVideollamada, $tipoTurno, $fechaInicioDias, $ventanaTiempoTope);
        $diasAtencion = $this->diasAtencion($medico[0]->id, $consultorio[0]->id, $esVideollamada, $tipoTurno, $fechaInicioDias, $ventanaTiempoTope);
        $dias_deshabilitados = $this->diasDeshabilitados($diasAtencion);

        $fechaLibreDisponible22= $fechaLibreDisponible1Aux[2].'-'.$fechaLibreDisponible1Aux[1].'-'.$fechaLibreDisponible1Aux[0];                          
        $diferenciaDias = $this->getDiferenciaDiasFechas($fechaLibreDisponible22, $ventanaTiempoTope);
        
        $obrasSociales = $this->getDiferencialObraSocial($medico[0]->id);
        $diferencialPaciente = $this->getImporteDiferencialParaPaciente($medico[0]->id, $paciente);
    	return view('turnos.seleccionar_dia')
        ->with('esVideollamada',$esVideollamada)
        ->with('tipoTurno',$tipoTurno)
        ->with('obrasSociales', $obrasSociales)
        ->with('diferencialPaciente', $diferencialPaciente)
        ->with('primerControl',$primerControl)
        ->with('end_date',$end_date)
        ->with('diferenciaDias',$diferenciaDias)
        ->with('medico',$medico[0])
        ->with('fechaLibreDisponible',$fechaLibreDisponible1)
        ->with('fechaLibreDisponible1',$fechaLibreDisponible2)
        ->with('fechaLibreDisponible2',$fechaLibreDisponible3)
        ->with('consultorio',$consultorio[0])
    	->with('diasAtencion',$diasAtencion)
        ->with('dni_paciente',$dni_paciente)
        ->with('paciente',$paciente)
        ->with('valorConsulta',$valorConsulta)
        ->with('moduloRecetas',$moduloRecetas)
        ->with('dias_habilitados',$dias_habilitados)
        ->with('dias_deshabilitados',$dias_deshabilitados)
        ->with('especialidad_nombre_flujo', $especialidadNombreFlujo)
        ->with('especialidad_id', $request->input('especialidad_id'));    
    }

    // input 2000-10-01 output = 01/10/2000
    function convertirFechaMostrar($fecha) {
        $fecha_aux = explode('-', $fecha);        
        $fecha_res = $fecha_aux[2].'/'.$fecha_aux[1].'/'.$fecha_aux[0];                  
        return $fecha_res;
    }

    function getMedicoConfig($medico_id, $modulo_id){
         $aux = DB::table('medico_configs')                                                       
                    ->where('medico_configs.medico',$medico_id)
                    ->where('medico_configs.modulo',$modulo_id)
                    ->where('medico_configs.activo', 1)
                    ->first();
        return $aux;
    }

    function getDiferenciaDiasFechas($fecha_1, $fecha_2){
        // ejemplo $fecha1= new DateTime("2017-08-01");
        $fecha1 = new DateTime($fecha_1);
        $fecha2 = new DateTime($fecha_2);
        $diff = $fecha1->diff($fecha2);
        if($fecha1 == $fecha2)
            return 0;
        if($fecha1 > $fecha2)
            return -1 * $diff->days;
        else            
            return $diff->days;
    }

    /**
     * Retorna un string con los dias habilitados (ej: "1,3,5") para la pantalla de selección.
     * Si se envía un rango de fechas, se aplica vigencia por fecha en horario_medicos (valido_desde/valido_hasta).
     */
    public function diasHabilitados($medico_id, $consultorio_id, $esVideollamada, $tipoTurno, $fechaInicio = null, $fechaFin = null) {
        $diasAtencionAux = [];
        if($esVideollamada == 1){
            $diasAtencionAux = DB::select('select DISTINCT(dia) from horario_medico_videollamadas where consultorio= ? and medico = ? and activo=1 order by dia',    
                [$consultorio_id , $medico_id]);
        } else {
            if ($fechaInicio && $fechaFin && Schema::hasColumn('horario_medicos', 'valido_desde')) {
                $horarios = DB::table('horario_medicos')
                    ->where('horario_medicos.consultorio', $consultorio_id)
                    ->where('horario_medicos.medico', $medico_id)
                    ->where('horario_medicos.tipo_turno', $tipoTurno)
                    ->where('horario_medicos.activo', 1)
                    ->select('dia', 'valido_desde', 'valido_hasta')
                    ->get();

                $porDia = [];
                foreach ($horarios as $h) {
                    $porDia[(int)$h->dia][] = $h;
                }

                $diasValidos = [];
                $fecha = new DateTime($fechaInicio);
                $fin = new DateTime($fechaFin);
                while ($fecha <= $fin) {
                    $f = $fecha->format('Y-m-d');
                    $diaNum = $this->getDiaSeleccionado2($f);

                    if (!isset($diasValidos[$diaNum]) && isset($porDia[$diaNum])) {
                        foreach ($porDia[$diaNum] as $row) {
                            $desdeOk = empty($row->valido_desde) || $row->valido_desde <= $f;
                            $hastaOk = empty($row->valido_hasta) || $row->valido_hasta >= $f;
                            if ($desdeOk && $hastaOk) {
                                $diasValidos[$diaNum] = true;
                                break;
                            }
                        }
                    }

                    if (count($diasValidos) >= 7) {
                        break;
                    }
                    $fecha->modify('+1 day');
                }

                $diasValidosKeys = array_keys($diasValidos);
                sort($diasValidosKeys, SORT_NUMERIC);
                foreach ($diasValidosKeys as $d) {
                    $diasAtencionAux[] = (object)['dia' => $d];
                }
            } else {
                $diasAtencionAux = DB::select('select DISTINCT(dia) from horario_medicos where consultorio= ? and medico = ? and tipo_turno = ? and activo=1 order by dia',    
                    [$consultorio_id , $medico_id, $tipoTurno]);
            }
        }
        $diasAtencion = array();
        $dias_habilitados = "";
        foreach ($diasAtencionAux as $valor){
            if($valor->dia == 1){
                $desdeHasta = $this->diaAtencionDesdeHasta($medico_id, $valor->dia, $esVideollamada, $tipoTurno);
                array_push($diasAtencion, 'Lunes: '.$desdeHasta);
                $dias_habilitados = $dias_habilitados.$valor->dia.',';                                 
            } 
            if($valor->dia == 2){
                $desdeHasta = $this->diaAtencionDesdeHasta($medico_id, $valor->dia, $esVideollamada, $tipoTurno);
                array_push($diasAtencion, 'Martes: '.$desdeHasta);
                $dias_habilitados = $dias_habilitados.$valor->dia.',';
            } 
            if($valor->dia == 3){
                $desdeHasta = $this->diaAtencionDesdeHasta($medico_id, $valor->dia, $esVideollamada, $tipoTurno);
                array_push($diasAtencion, 'Miercoles: '.$desdeHasta);
                $dias_habilitados = $dias_habilitados.$valor->dia.',';
            } 
            if($valor->dia == 4){
                $desdeHasta = $this->diaAtencionDesdeHasta($medico_id, $valor->dia, $esVideollamada, $tipoTurno);
                array_push($diasAtencion, 'Jueves: '.$desdeHasta);
                $dias_habilitados = $dias_habilitados.$valor->dia.',';
            } 
            if($valor->dia == 5){
                $desdeHasta = $this->diaAtencionDesdeHasta($medico_id, $valor->dia, $esVideollamada, $tipoTurno);
                array_push($diasAtencion, 'Viernes: '.$desdeHasta);
                $dias_habilitados = $dias_habilitados.$valor->dia.',';
            } 
            if($valor->dia == 6){
                array_push($diasAtencion, 'Sabado');
            } 
            if($valor->dia == 7){
                array_push($diasAtencion, 'Domingo');
            } 
        }

        $dateHoy = date("Y-m-d");
        $fechasAgregadasQuery = DB::table('fechas_agregadas')
            ->select('dia')
            ->where('fechas_agregadas.medico',$medico_id)
            ->where('fechas_agregadas.consultorio', $consultorio_id)
            ->where('fechas_agregadas.activo', 1)
            ->distinct();

        if ($fechaInicio) {
            $fechasAgregadasQuery->where('fechas_agregadas.fecha', '>=', $fechaInicio);
        } else {
            $fechasAgregadasQuery->where('fechas_agregadas.fecha', '>', $dateHoy);
        }
        if ($fechaFin) {
            $fechasAgregadasQuery->where('fechas_agregadas.fecha', '<=', $fechaFin);
        }

        $fechasAgregadas = $fechasAgregadasQuery->get();

        foreach ($fechasAgregadas as $valor){
            if($valor->dia == 1){
                $desdeHasta = $this->diaAtencionDesdeHastaAgregadas($medico_id, $consultorio_id, 1);
                array_push($diasAtencion, 'Lunes: '.$desdeHasta);  
                $dias_habilitados = $dias_habilitados.$valor->dia.',';              
            } 
            if($valor->dia == 2){
                $desdeHasta = $this->diaAtencionDesdeHastaAgregadas($medico_id, $consultorio_id, 2);
                array_push($diasAtencion, 'Martes: '.$desdeHasta);  
                $dias_habilitados = $dias_habilitados.$valor->dia.',';              
            } 
            if($valor->dia == 3){
                $desdeHasta = $this->diaAtencionDesdeHastaAgregadas($medico_id, $consultorio_id, 3);
                array_push($diasAtencion, 'Miercoles: '.$desdeHasta); 
                $dias_habilitados = $dias_habilitados.$valor->dia.',';               
            } 
            if($valor->dia == 4){
                $desdeHasta = $this->diaAtencionDesdeHastaAgregadas($medico_id, $consultorio_id, 4);
                array_push($diasAtencion, 'Jueves: '.$desdeHasta);    
                $dias_habilitados = $dias_habilitados.$valor->dia.',';    
            } 
            if($valor->dia == 5){
                $desdeHasta = $this->diaAtencionDesdeHastaAgregadas($medico_id, $consultorio_id, 5);
                array_push($diasAtencion, 'Viernes: '.$desdeHasta);   
                $dias_habilitados = $dias_habilitados.$valor->dia.',';             
            } 
            if($valor->dia == 6){
                $desdeHasta = $this->diaAtencionDesdeHastaAgregadas($medico_id, $consultorio_id, 6);
                array_push($diasAtencion, 'Sabado: '.$desdeHasta);    
                $dias_habilitados = $dias_habilitados.$valor->dia.',';    
            } 
            if($valor->dia == 7){
                array_push($diasAtencion, 'Domingo');
            } 
        }

        $dias_habilitados = substr($dias_habilitados, 0, -1);

        return $dias_habilitados;
    }

    public function diaAtencionDesdeHastaAgregadas($medico_id, $consultorio_id, $dia){        
        $desdeHasta = DB::table('horarios_medicos_agregados')
                ->where('horarios_medicos_agregados.medico','=', $medico_id)
                ->where('horarios_medicos_agregados.consultorio','=', $consultorio_id)
                ->where('horarios_medicos_agregados.dia','=', $dia)
                ->where('horarios_medicos_agregados.activo','=', 1)
                ->orderby('horarios_medicos_agregados.horario')
                ->get();        

        if($desdeHasta->count()>0){
            $aux = $desdeHasta[0]->horario.' a '.$desdeHasta[$desdeHasta->count()-1]->horario;
            return $aux;
        } else {
            return '';
        }
    }

    // $esVideollamada 1 quiere decir que si, 0 que no
    public function diasAtencion($medico_id, $consultorio_id, $esVideollamada, $tipoTurno, $fechaInicio = null, $fechaFin = null) {
        $diasAtencionAux = [];
        if($esVideollamada == 1 || $tipoTurno == 4){
            $diasAtencionAux = DB::select('select DISTINCT(dia) from horario_medico_videollamadas where consultorio= ? and medico = ? and activo=1 order by dia',    
                [$consultorio_id , $medico_id]);
        } else {
            if ($fechaInicio && $fechaFin && Schema::hasColumn('horario_medicos', 'valido_desde')) {
                $horarios = DB::table('horario_medicos')
                    ->where('horario_medicos.consultorio', $consultorio_id)
                    ->where('horario_medicos.medico', $medico_id)
                    ->where('horario_medicos.tipo_turno', $tipoTurno)
                    ->where('horario_medicos.activo', 1)
                    ->select('dia', 'valido_desde', 'valido_hasta')
                    ->get();

                $porDia = [];
                foreach ($horarios as $h) {
                    $porDia[(int)$h->dia][] = $h;
                }

                $diasValidos = [];
                $fecha = new DateTime($fechaInicio);
                $fin = new DateTime($fechaFin);
                while ($fecha <= $fin) {
                    $f = $fecha->format('Y-m-d');
                    $diaNum = $this->getDiaSeleccionado2($f);

                    if (!isset($diasValidos[$diaNum]) && isset($porDia[$diaNum])) {
                        foreach ($porDia[$diaNum] as $row) {
                            $desdeOk = empty($row->valido_desde) || $row->valido_desde <= $f;
                            $hastaOk = empty($row->valido_hasta) || $row->valido_hasta >= $f;
                            if ($desdeOk && $hastaOk) {
                                $diasValidos[$diaNum] = true;
                                break;
                            }
                        }
                    }

                    if (count($diasValidos) >= 7) {
                        break;
                    }
                    $fecha->modify('+1 day');
                }

                $diasValidosKeys = array_keys($diasValidos);
                sort($diasValidosKeys, SORT_NUMERIC);
                foreach ($diasValidosKeys as $d) {
                    $diasAtencionAux[] = (object)['dia' => $d];
                }
            } else {
                $diasAtencionAux = DB::select('select DISTINCT(dia) from horario_medicos where consultorio= ? and medico = ? and tipo_turno = ? and activo=1 order by dia',    
                    [$consultorio_id , $medico_id, $tipoTurno]);
            }
        }

        $diasAtencion = array();        
        foreach ($diasAtencionAux as $valor){
            if($valor->dia == 1){
                $desdeHasta = $this->diaAtencionDesdeHasta($medico_id, $valor->dia, $esVideollamada, $tipoTurno);
                array_push($diasAtencion, 'Lunes: '.$desdeHasta);                
            } 
            if($valor->dia == 2){
                $desdeHasta = $this->diaAtencionDesdeHasta($medico_id, $valor->dia, $esVideollamada, $tipoTurno);
                array_push($diasAtencion, 'Martes: '.$desdeHasta);                
            } 
            if($valor->dia == 3){
                $desdeHasta = $this->diaAtencionDesdeHasta($medico_id, $valor->dia, $esVideollamada, $tipoTurno);
                array_push($diasAtencion, 'Miercoles: '.$desdeHasta);                
            } 
            if($valor->dia == 4){
                $desdeHasta = $this->diaAtencionDesdeHasta($medico_id, $valor->dia, $esVideollamada, $tipoTurno);
                array_push($diasAtencion, 'Jueves: '.$desdeHasta);        
            } 
            if($valor->dia == 5){
                $desdeHasta = $this->diaAtencionDesdeHasta($medico_id, $valor->dia, $esVideollamada, $tipoTurno);
                array_push($diasAtencion, 'Viernes: '.$desdeHasta);                
            } 
            if($valor->dia == 6){
                array_push($diasAtencion, 'Sabado');
            } 
            if($valor->dia == 7){
                array_push($diasAtencion, 'Domingo');
            } 
        }   

        $dateHoy = date("Y-m-d");
        $fechasAgregadasQuery = DB::table('fechas_agregadas')
            ->select('dia')
            ->where('fechas_agregadas.medico',$medico_id)
            ->where('fechas_agregadas.consultorio', $consultorio_id)
            ->where('fechas_agregadas.activo', 1)
            ->distinct();

        if ($fechaInicio) {
            $fechasAgregadasQuery->where('fechas_agregadas.fecha', '>=', $fechaInicio);
        } else {
            $fechasAgregadasQuery->where('fechas_agregadas.fecha', '>', $dateHoy);
        }
        if ($fechaFin) {
            $fechasAgregadasQuery->where('fechas_agregadas.fecha', '<=', $fechaFin);
        }

        $fechasAgregadas = $fechasAgregadasQuery->get();

        foreach ($fechasAgregadas as $valor){
            if($valor->dia == 1){
                $desdeHasta = $this->diaAtencionDesdeHastaAgregadas($medico_id, $consultorio_id, 1);
                array_push($diasAtencion, 'Lunes: '.$desdeHasta);                
            } 
            if($valor->dia == 2){
                $desdeHasta = $this->diaAtencionDesdeHastaAgregadas($medico_id, $consultorio_id, 2);
                array_push($diasAtencion, 'Martes: '.$desdeHasta);                
            } 
            if($valor->dia == 3){
                $desdeHasta = $this->diaAtencionDesdeHastaAgregadas($medico_id, $consultorio_id, 3);
                array_push($diasAtencion, 'Miercoles: '.$desdeHasta);                
            } 
            if($valor->dia == 4){
                $desdeHasta = $this->diaAtencionDesdeHastaAgregadas($medico_id, $consultorio_id, 4);
                array_push($diasAtencion, 'Jueves: '.$desdeHasta);        
            } 
            if($valor->dia == 5){
                $desdeHasta = $this->diaAtencionDesdeHastaAgregadas($medico_id, $consultorio_id, 5);
                array_push($diasAtencion, 'Viernes: '.$desdeHasta);                
            } 
            if($valor->dia == 6){
                $desdeHasta = $this->diaAtencionDesdeHastaAgregadas($medico_id, $consultorio_id, 6);
                array_push($diasAtencion, 'Sabado: '.$desdeHasta);        
            } 
            if($valor->dia == 7){
                array_push($diasAtencion, 'Domingo');
            } 
        } 

        return $diasAtencion;    
    }

    // Retorno 1 si el modulo esta activo
    public function moduloActivo($medico_id, $modulo_id){
         $moduloActivoAux = DB::table('modulo_medicos')
                        ->where('modulo_medicos.medico', $medico_id)
                        ->where('modulo_medicos.modulo', $modulo_id) // 1 corresponde a ACtivar pacientes
                        ->where('modulo_medicos.activo', 1)
                        ->first();
        $moduloActivo = 0;
        if($moduloActivoAux != null)
            $moduloActivo = 1;
    
        return $moduloActivo;
    }

    /**
     * Índice de par activo (0 => ítems 0-1, 1 => 2-3, …). Null si todos los pares están llenos.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    protected function indiceParActivoMostrarDosPaciente(array $items): ?int
    {
        $n = count($items);
        if ($n === 0) {
            return null;
        }

        $pairIndex = 0;
        $numPairs = (int) ceil($n / 2);

        while ($pairIndex < $numPairs) {
            $i = $pairIndex * 2;
            $j = $i + 1;

            $libreI = isset($items[$i]['libre']) ? (int) $items[$i]['libre'] : 0;
            if ($j < $n) {
                $libreJ = isset($items[$j]['libre']) ? (int) $items[$j]['libre'] : 0;
                $bothOccupied = ($libreI === 0) && ($libreJ === 0);
            } else {
                $bothOccupied = ($libreI === 0);
            }

            if (!$bothOccupied) {
                return $pairIndex;
            }
            $pairIndex++;
        }

        return null;
    }

    /**
     * Marca reserva_online: 1 solo en el par de ventana actual; el resto visible pero no reservable online.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    public function marcarReservaOnlineMostrarDosPaciente(array $items)
    {
        $n = count($items);
        if ($n === 0) {
            return $items;
        }

        $activePair = $this->indiceParActivoMostrarDosPaciente($items);
        $out = [];
        foreach ($items as $idx => $row) {
            $row = is_array($row) ? $row : [];
            if ($activePair === null) {
                $row['reserva_online'] = 0;
            } else {
                $pairOfRow = (int) floor(((int) $idx) / 2);
                $row['reserva_online'] = ($pairOfRow === $activePair) ? 1 : 0;
            }
            $out[] = $row;
        }

        return $out;
    }

    protected function turnoFilaEsReservablePaciente(array $row)
    {
        $libre = isset($row['libre']) ? (int) $row['libre'] : 0;
        $ro = array_key_exists('reserva_online', $row) ? (int) $row['reserva_online'] : 1;

        return $libre === 1 && $ro === 1;
    }

    /**
     * Con módulo «mostrar de a dos»: mostrar grilla si hay filas de horario aunque ninguna sea reservable aún.
     */
    protected function debeMostrarGrillaHorariosPaciente($medico_id, array $someArray)
    {
        if ($this->moduloActivo($medico_id, self::MODULO_MOSTRAR_DOS) === 1) {
            return count($someArray) > 0;
        }

        foreach ($someArray as $v) {
            if ($this->turnoFilaEsReservablePaciente($v)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, array<string, mixed>>|null  $turnosArray
     * @return array<int, array<string, mixed>>|null
     */
    protected function aplicarMostrarDosPaciente($medico_id, $turnosArray)
    {
        if ($turnosArray === null || !is_array($turnosArray)) {
            return $turnosArray;
        }

        if ($this->moduloActivo($medico_id, self::MODULO_MOSTRAR_DOS) !== 1) {
            $out = [];
            foreach ($turnosArray as $row) {
                $row = is_array($row) ? $row : [];
                $row['reserva_online'] = 1;
                $out[] = $row;
            }

            return $out;
        }

        return $this->marcarReservaOnlineMostrarDosPaciente($turnosArray);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function obtenerTurnosArraySinFiltrarMostrarDos(
        $medico_id,
        $consultorio_id,
        $diaSeleccionado,
        $fechaSolicitada,
        $tipoTurno,
        $primerControl,
        $esVideollamada
    ) {
        if ((int) $esVideollamada === 1) {
            $raw = $this->createJsonVideollamada($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);
            $arr = json_decode($raw, true);
            return is_array($arr) ? $arr : [];
        }

        $moduloPrimerControlDoble = $this->moduloActivo($medico_id, 3);
        if (((int) $primerControl === 0) || $moduloPrimerControlDoble === 0) {
            $raw = $this->createJson($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada, $tipoTurno);
            $arr = json_decode($raw, true);
            return is_array($arr) ? $arr : [];
        }

        if (!$this->controlarCantidadPrimerControl($medico_id, $diaSeleccionado, $consultorio_id, $fechaSolicitada)) {
            return [];
        }
        $raw = $this->createJsonTurnosDobles($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada, $tipoTurno);
        $arr = json_decode($raw, true);
        return is_array($arr) ? $arr : [];
    }

    /**
     * Validación servidor: el horario debe estar en la ventana visible para pacientes.
     */
    protected function horarioEsVisibleMostrarDosPaciente(
        $medico_id,
        $consultorio_id,
        $diaSeleccionado,
        $fechaSolicitada,
        $tipoTurno,
        $primerControl,
        $horario,
        $horarioPar,
        $esVideollamada
    ) {
        if ($this->moduloActivo($medico_id, self::MODULO_MOSTRAR_DOS) !== 1) {
            return true;
        }

        $full = $this->obtenerTurnosArraySinFiltrarMostrarDos(
            $medico_id,
            $consultorio_id,
            $diaSeleccionado,
            $fechaSolicitada,
            $tipoTurno,
            $primerControl,
            $esVideollamada
        );
        $marked = $this->aplicarMostrarDosPaciente($medico_id, $full);

        $primerControl = (int) $primerControl;
        $moduloPrimerControlDoble = $this->moduloActivo($medico_id, 3);
        $usaBloquesDobles = ((int) $esVideollamada === 0) && $primerControl === 1 && $moduloPrimerControlDoble === 1;

        if ($usaBloquesDobles && $horarioPar !== null) {
            foreach ($marked as $row) {
                if (!isset($row['horario'], $row['horario2'])) {
                    continue;
                }
                if ((int) ($row['reserva_online'] ?? 1) !== 1) {
                    continue;
                }
                if ((string) $row['horario'] === (string) $horario && (string) $row['horario2'] === (string) $horarioPar) {
                    return true;
                }
            }

            return false;
        }

        foreach ($marked as $row) {
            if (!isset($row['horario'])) {
                continue;
            }
            if ((int) ($row['reserva_online'] ?? 1) !== 1) {
                continue;
            }
            if ((string) $row['horario'] === (string) $horario) {
                return true;
            }
        }

        return false;
    }

    public function diasDeshabilitados($diasAtencion){
        $dias_deshabilitados = '';
        
        $sietedias = array('Domingo','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado');
        $diaauxcont = 0;
        foreach($sietedias as $diaaux){           
            $encontreAux=0;
            for($cont=0;$cont<count($diasAtencion); $cont++){ 
                $dia_aux = explode(':',$diasAtencion[$cont]);                    
                if(strcmp ($diaaux, $dia_aux[0]) == 0){
                    $encontreAux=1;                    
                }
            }
            if($encontreAux==0){                
                if(strlen($dias_deshabilitados)<1){
                    $dias_deshabilitados=$diaauxcont;                
                }
                else{
                    $dias_deshabilitados=$dias_deshabilitados.','.$diaauxcont;                    
                }
            }
            $diaauxcont++;
        }

        return $dias_deshabilitados;
    }


    public function diaAtencionDesdeHasta($medicoId, $dia, $esVideollamada, $tipoTurno){
        if($tipoTurno == 22) {
            $desdeHasta = DB::table('horario_medico_d_h_s')
                        ->where('horario_medico_d_h_s.medico','=', $medicoId)
                        ->where('horario_medico_d_h_s.dia','=', $dia)
                        ->where('horario_medico_d_h_s.tipo_turno', '=', $tipoTurno)
                        ->where('horario_medico_d_h_s.activo','=', 1)
                        ->get();
        } else {
            if($esVideollamada == 1){
                $desdeHasta = DB::table('horario_medico_dhs_videollamadas')
                        ->where('horario_medico_dhs_videollamadas.medico','=', $medicoId)
                        ->where('horario_medico_dhs_videollamadas.dia','=', $dia)
                        ->where('horario_medico_dhs_videollamadas.activo','=', 1)
                        ->get();
            } else {
                $desdeHasta = DB::table('horario_medico_d_h_s')
                        ->where('horario_medico_d_h_s.medico','=', $medicoId)
                        ->where('horario_medico_d_h_s.dia','=', $dia)
                        ->where('horario_medico_d_h_s.activo','=', 1)
                        ->get();
            }
        }

        if($desdeHasta->count()>0){
            $aux = $desdeHasta[0]->desde.' a '.$desdeHasta[0]->hasta;
            if($desdeHasta->count()>1){
                $aux = $aux.' - '.$desdeHasta[1]->desde.' a '.$desdeHasta[1]->hasta;           
            }
            return $aux;
        } else {
            return '';
        }
    }

    
    public function storeTurnosDia(Request $request)
    {
		$consultorio_aux = explode('-',$request->get('consultorio'));
		$medico_aux = explode('-',$request->get('medico'));
        $dias_horarios = explode(',',$request->get('dia_horario_doble'));
        $videollamadaCheck = $request->videollamadaCheck;
    
        $max = sizeof($dias_horarios);
        for($i = 0; $i < $max; $i++){
            $dia_horario = explode('-', $dias_horarios[$i]);
            $dia = $dia_horario[0];
            $horario = $dia_horario[1];                 
            $doble = $dia_horario[2];                 
            if($videollamadaCheck) {
                $horarioMedico = new horarioMedicoVideollamada;
            } else {
            	$horarioMedico = new horarioMedico;
            }
            $horarioMedico->medico = $medico_aux[0];
            $horarioMedico->consultorio = $consultorio_aux[0];
            $horarioMedico->dia = $dia;//$request->get('dia');
            $horarioMedico->horario = $horario;//$request->get('horario');       
            $horarioMedico->doble = $doble;
            $horarioMedico->tipo_turno = 1;
            $horarioMedico->activo = 1;                   
            $horarioMedico->save();            
        }

		$medicos = DB::select('select * from medicos where activo=1');
    	$consultorios = DB::select('select * from consultorios where activo=1');
    	
    	return view('turnos_admin.admin_turnos')->with('medicos',$medicos)->with('consultorios',$consultorios);
    }

    public function storeTurnosDiaDh(Request $request){
        $horarioDesde = $request->desde;
        $horarioHasta = $request->hasta;
        $medico_id = $request->medico_id;
        $consultorio_id = $request->consultorio_id;
        $dia = $request->dia;
        $videollamadaCheck = $request->videollamadaCheck;
        if($videollamadaCheck == 1) {
            $registrarTurnoDiaDh = new HorarioMedicoDhsVideollamada;    
        } else { 
            $registrarTurnoDiaDh = new HorarioMedicoDH;
            $registrarCantidadPrimerControl = new MedicoPrimerControl;
            $registrarCantidadPrimerControl->medico = $medico_id;
            $registrarCantidadPrimerControl->dia = $dia;
            $registrarCantidadPrimerControl->consultorio = $consultorio_id;
            $registrarCantidadPrimerControl->cantidadPrimerControl = 1;
            $registrarCantidadPrimerControl->activo = 1;
            $registrarCantidadPrimerControl->save();
        }

        $registrarTurnoDiaDh->medico = $medico_id;
        $registrarTurnoDiaDh->desde = $horarioDesde;
        $registrarTurnoDiaDh->hasta = $horarioHasta;
        $registrarTurnoDiaDh->consultorio = $consultorio_id;
        $registrarTurnoDiaDh->dia = $dia;
        $registrarTurnoDiaDh->tipo_turno = 1;
        $registrarTurnoDiaDh->activo = 1;
        $registrarTurnoDiaDh->save();
        
        return response()->json(array('registrarTurnoDiaDh'=>$registrarTurnoDiaDh));

    }

    public function turnoSeleccionadoCardiologia($request){
        $especialidad = $request->especialidad;        
        $tipoTurno = $request->tipoTurno;
        $paciente= DB::table('pacientes')->find($request->paciente_id);
        $primerControl = $request->get('primer_control');
        $dias_deshabilitados = $request->get('dias_deshabilitados');
        $esVideollamada = $request->get('esVideollamada');
        $dni_paciente = $paciente->dni;
        $fechaSolicitada = $request->get('fecha_seleccionada');
        $fechaAux = explode('/',$fechaSolicitada);
        $dia = $fechaAux[0];
        $mes = $fechaAux[1];
        $anio = $fechaAux[2];
        $nuevaFecha=$anio.'-'.$mes.'-'.$dia;
        $date = new DateTime($nuevaFecha);      
        //return date_format($date, 'g:ia \o\n l jS F Y');   //12:00am on Friday 7th June 2019  
        $diaLetras = date_format($date, 'l');        
        // me devuelve el numero del dia. Lunes = 1
        $diaSeleccionado = $this->getDiaSeleccionado2($nuevaFecha);
        
        $medico_id = $request->get('medico');
        $medico= DB::table('medicos')->find($medico_id);
        $consultorio_id = $request->get('consultorio'); 

        $consultorio = consultorio::find($consultorio_id);  

        // En caso de ser 1 es su primer control. Tengo que mostrar turnos dobles sino de a un turno.
        
        // Modulo id: 3 | corresponde a Primer Control Doble
        $moduloPrimerControlDoble = $this->moduloActivo($medico->id, 3);
        if (($primerControl == 0)||($moduloPrimerControlDoble == 0)){
            //turnos comunes cada media hora.
            if($tipoTurno == 4){
                $data = $this-> createJsonVideollamada($medico_id, $consultorio_id, $diaSeleccionado, $nuevaFecha); 
                $cantidadPrimerControlPermitido = true; 
            } else {
                $data = $this-> createJson($medico_id, $consultorio_id, $diaSeleccionado, $nuevaFecha, $tipoTurno);
                $cantidadPrimerControlPermitido = true;
            }
        } else {
            if($tipoTurno == 4){
                $data = $this-> createJsonVideollamada($medico_id, $consultorio_id, $diaSeleccionado, $nuevaFecha);
                $cantidadPrimerControlPermitido = true;
            } else {
                $cantidadPrimerControlPermitido = $this->controlarCantidadPrimerControl($medico_id, $diaSeleccionado, $consultorio_id, $nuevaFecha);
                if($cantidadPrimerControlPermitido) {
                    $data = $this-> createJsonTurnosDobles($medico_id, $consultorio_id, $diaSeleccionado, $nuevaFecha, $tipoTurno);
                }
            }
        }

        $turnosLibres=0;        
        if($cantidadPrimerControlPermitido){
            $someArray = json_decode($data, true);
            if (!is_array($someArray)) {
                $someArray = [];
            }
            $someArray = $this->aplicarMostrarDosPaciente($medico_id, $someArray);
            $turnosLibres = $this->debeMostrarGrillaHorariosPaciente($medico_id, $someArray) ? 1 : 0;
        } else {
            $someArray = array();
        }

        $validarFeriado = DB::table('feriados')                                        
                        ->where('feriados.fecha', '=', $nuevaFecha)                        
                        ->get();        
        if($validarFeriado->count()>0)
            $turnosLibres = 2;                                

        // el 4 corresponde al modulo de Solo un turno por mes.
        $cantTurnosMes = 0;
        /*if($this->moduloActivo($medico_id, 4) == 1){
            $cantTurnosMes = $this->cantidadTurnosPacienteMes($medico_id, $consultorio_id, $paciente->id, $nuevaFecha);            
            if($cantTurnosMes > 0)
                $turnosLibres = 3;
        } */
        $obraSocialCargada = 0;
        if(($paciente->obra_social_foto!=null)||(strcmp($paciente->obra_social_foto, '') != 0))
            $obraSocialCargada = 1;

        $especialidadNombreFlujo = EspecialidadFlujoHelper::nombreParaTurno(
            $request->input('especialidad_nombre_flujo'),
            null,
            $request->input('especialidad_id'),
            $medico->especialidad ?? null
        );

        return view('turnos.seleccionar_turno')
        ->with('esVideollamada',$esVideollamada)
        ->with('primerControl',$primerControl)
        ->with('turnos',$someArray)
        ->with('medico',$medico)
        ->with('tipoTurno',$tipoTurno)
        ->with('obraSocialCargada',$obraSocialCargada)
        ->with('dia',$diaSeleccionado)
        ->with('fechaSolicitada',$fechaSolicitada)
        ->with('hayTurnoLibre',$turnosLibres)
        ->with('paciente',$paciente)
        ->with('moduloPrimerControlDoble',$moduloPrimerControlDoble)
        ->with('validarFeriado',$validarFeriado)
        ->with('cantTurnosMes',$cantTurnosMes)
        ->with('consultorio',$consultorio)
        ->with('dias_deshabilitados',$dias_deshabilitados)
        ->with('especialidad_nombre_flujo', $especialidadNombreFlujo);    
    }


    public function getHorariosMedico(Request $request)
    {        
        $medico_id = $request->get('medico');
        $fechaSolicitada = $request->get('fecha_seleccionada');
        $tipoTurno = $request->tipoTurno;
        $paciente= DB::table('pacientes')->find($request->paciente_id);
        $primerControl = $request->get('primer_control');
        $dias_deshabilitados = $request->get('dias_deshabilitados');
        $esVideollamada = $request->get('esVideollamada');
        $consultorio_id = $request->get('consultorio'); 

        $dni_paciente = $paciente->dni;
        
        $fechaAux = explode('/',$fechaSolicitada);
        $dia = $fechaAux[0];
        $mes = $fechaAux[1];
        $anio = $fechaAux[2];
        $nuevaFecha=$anio.'-'.$mes.'-'.$dia;
        $date = new DateTime($nuevaFecha);      
        //return date_format($date, 'g:ia \o\n l jS F Y');   //12:00am on Friday 7th June 2019  
        $diaLetras = date_format($date, 'l');        
        // me devuelve el numero del dia. Lunes = 1
        $diaSeleccionado = $this->getDiaSeleccionado2($nuevaFecha);
        
        
        $medico= DB::table('medicos')->find($medico_id);
        

        $consultorio = consultorio::find($consultorio_id);  

        if($medico_id == 13) {
            $query = DB::table('horario_medicos')->where('medico', $medico_id)->where('consultorio', $consultorio_id)->where('dia', $diaSeleccionado)->where('activo', 1);
            $turnos = HorarioMedico::aplicarVigenciaQuery($query, $nuevaFecha)->get();

            $turnosRegistrados = DB::select('select * from turno_registrados where medico = ? and consultorio = ? and dia = ?  and fechaTurno = ? and activo in (1,2)',
            [$medico_id, $consultorio_id, $diaSeleccionado, $nuevaFecha]);
        } else {
            $query = DB::table('horario_medicos')->where('medico', $medico_id)->where('consultorio', $consultorio_id)->where('dia', $diaSeleccionado)->where('tipo_turno', $tipoTurno)->where('activo', 1);
            $turnos = HorarioMedico::aplicarVigenciaQuery($query, $nuevaFecha)->get();

            $turnosRegistrados = DB::select('select * from turno_registrados where medico = ? and consultorio = ? and dia = ?  and fechaTurno = ? and  tipo_turno = ? and activo in (1,2)',
            [$medico_id, $consultorio_id, $diaSeleccionado, $nuevaFecha, $tipoTurno]);
        }
        

        $testRodri = '';
        // En caso de ser 1 es su primer control. Tengo que mostrar turnos dobles sino de a un turno.
        
        // Modulo id: 3 | corresponde a Primer Control Doble
        $moduloPrimerControlDoble = $this->moduloActivo($medico->id, 3);
        if (($primerControl == 0)||($moduloPrimerControlDoble == 0)){
            //turnos comunes cada media hora.
            $data = $this-> createJson($medico_id, $consultorio_id, $diaSeleccionado, $nuevaFecha, $tipoTurno);            
            $cantidadPrimerControlPermitido = true;
            //$testRodri = 'test 1';
        } else {
            //turnos especiales cada 1 hora.
            $testRodri = 'test 2';
            $cantidadPrimerControlPermitido = $this->controlarCantidadPrimerControl($medico_id, $diaSeleccionado, $consultorio_id, $nuevaFecha);
            if($cantidadPrimerControlPermitido)
                $data = $this-> createJsonTurnosDobles($medico_id, $consultorio_id, $diaSeleccionado, $nuevaFecha, $tipoTurno);
        }   

        $turnosLibres = 0;        
        if($cantidadPrimerControlPermitido) {
            $someArray = json_decode($data, true);
            if (!is_array($someArray)) {
                $someArray = [];
            }
            $someArray = $this->aplicarMostrarDosPaciente($medico_id, $someArray);
            $turnosLibres = $this->debeMostrarGrillaHorariosPaciente($medico_id, $someArray) ? 1 : 0;
        } else {
            $someArray = array();
        }

        $validarFeriado = DB::table('feriados')                                        
                        ->where('feriados.fecha', '=', $nuevaFecha)                        
                        ->get();        
        if($validarFeriado->count()>0)
            $turnosLibres = 2;                
        
        $moduloPrimerControlDobleAux = DB::table('modulo_medicos')
                        ->where('modulo_medicos.medico', $medico->id)
                        ->where('modulo_medicos.modulo', 3) // 3 corresponde a Primer Control Doble
                        ->where('modulo_medicos.activo', 1)
                        ->first();
        $moduloPrimerControlDoble = 0;
        if($moduloPrimerControlDobleAux != null)
            $moduloPrimerControlDoble = 1;

        // el 4 corresponde al modulo de Solo un turno por mes.
        $cantTurnosMes = 0;
        if($this->moduloActivo($medico_id, 4) == 1){
            $cantTurnosMes = $this->cantidadTurnosPacienteMes($medico_id, $consultorio_id, $paciente->id, $nuevaFecha);            
            if($cantTurnosMes > 0)
                $turnosLibres = 3;
        } 
        $obraSocialCargada = 0;
        if(($paciente->obra_social_foto!=null)||(strcmp($paciente->obra_social_foto, '') != 0))
            $obraSocialCargada = 1;


        return response()->json(array('testRodri'=>$testRodri,'esVideollamada'=>$esVideollamada, 'tipoTurno'=>$tipoTurno, 'primerControl'=>$primerControl, 'turnos'=>$someArray, 'medico'=>$medico, 'obraSocialCargada'=>$obraSocialCargada, 'dia'=>$diaSeleccionado, 'fechaSolicitada'=>$nuevaFecha, 'hayTurnoLibre'=>$turnosLibres, 'paciente'=>$paciente, 'moduloPrimerControlDoble'=>$moduloPrimerControlDoble, 'validarFeriado'=>$validarFeriado, 'cantTurnosMes'=>$cantTurnosMes, 'consultorio'=>$consultorio, 'dias_deshabilitados'=>$dias_deshabilitados, 'cantidadPrimerControlPermitido'=>$cantidadPrimerControlPermitido));    
    }

    public function seleccionarTurnoHorario(Request $request)
    {   
        $especialidad = $request->especialidad;

        if($especialidad == 2){
           return $this->turnoSeleccionadoCardiologia($request);
        }
        //return $especialidad;
        $tipoTurno = $request->tipoTurno;
        $paciente= DB::table('pacientes')->find($request->paciente_id);
        $primerControl = $request->get('primer_control');
        $dias_deshabilitados = $request->get('dias_deshabilitados');
        $esVideollamada = $request->get('esVideollamada');
        $dni_paciente = $paciente->dni;
		$fechaSolicitada = $request->get('fecha_seleccionada');
		$fechaAux = explode('/',$fechaSolicitada);
		$dia = $fechaAux[0];
		$mes = $fechaAux[1];
		$anio = $fechaAux[2];
		$nuevaFecha=$anio.'-'.$mes.'-'.$dia;
		$date = new DateTime($nuevaFecha);		
		//return date_format($date, 'g:ia \o\n l jS F Y');	 //12:00am on Friday 7th June 2019	
		$diaLetras = date_format($date, 'l');        
        // me devuelve el numero del dia. Lunes = 1
        $diaSeleccionado = $this->getDiaSeleccionado2($nuevaFecha);
		
		$medico_id = $request->get('medico');
        $medico= DB::table('medicos')->find($medico_id);
		$consultorio_id = $request->get('consultorio');	

        $consultorio = consultorio::find($consultorio_id);  

		$turnos = DB::select('select * from horario_medicos where medico = ? and consultorio = ? and dia = ? and activo=1',
			[$medico_id , $consultorio_id, $diaSeleccionado]);

        $turnosRegistrados = DB::select('select * from turno_registrados where medico = ? and consultorio = ? and dia = ?  and fechaTurno = ? and activo=1',
            [$medico_id, $consultorio_id, $diaSeleccionado, $nuevaFecha]);
        // En caso de ser 1 es su primer control. Tengo que mostrar turnos dobles sino de a un turno.
        
        // Modulo id: 3 | corresponde a Primer Control Doble
        $moduloPrimerControlDoble = $this->moduloActivo($medico->id, 3);
        if (($primerControl == 0)||($moduloPrimerControlDoble == 0)){
            //turnos comunes cada media hora.
            $data = $this-> createJson($medico_id, $consultorio_id, $diaSeleccionado, $nuevaFecha, 0);
            $cantidadPrimerControlPermitido = true;
        } else {
            //turnos especiales cada 1 hora.
            $cantidadPrimerControlPermitido = $this->controlarCantidadPrimerControl($medico_id, $diaSeleccionado, $consultorio_id, $nuevaFecha);
            if($cantidadPrimerControlPermitido)
                $data = $this-> createJsonTurnosDobles($medico_id, $consultorio_id, $diaSeleccionado, $nuevaFecha, $tipoTurno);
        }   

        $turnosLibres=0;        
        if($cantidadPrimerControlPermitido){
            $someArray = json_decode($data, true);
            if (!is_array($someArray)) {
                $someArray = [];
            }
            $someArray = $this->aplicarMostrarDosPaciente($medico_id, $someArray);
            $turnosLibres = $this->debeMostrarGrillaHorariosPaciente($medico_id, $someArray) ? 1 : 0;
        } else {
            $someArray = array();
        }

        $validarFeriado = DB::table('feriados')                                        
                        ->where('feriados.fecha', '=', $nuevaFecha)                        
                        ->get();        
        if($validarFeriado->count()>0)
            $turnosLibres = 2;                
        
        $moduloPrimerControlDobleAux = DB::table('modulo_medicos')
                        ->where('modulo_medicos.medico', $medico->id)
                        ->where('modulo_medicos.modulo', 3) // 3 corresponde a Primer Control Doble
                        ->where('modulo_medicos.activo', 1)
                        ->first();
        $moduloPrimerControlDoble = 0;
        if($moduloPrimerControlDobleAux != null)
            $moduloPrimerControlDoble = 1;

        // el 4 corresponde al modulo de Solo un turno por mes.
        $cantTurnosMes = 0;
        if($this->moduloActivo($medico_id, 4) == 1){
            $cantTurnosMes = $this->cantidadTurnosPacienteMes($medico_id, $consultorio_id, $paciente->id, $nuevaFecha);            
            if($cantTurnosMes > 0)
                $turnosLibres = 3;
        } 
        $obraSocialCargada = 0;
        if(($paciente->obra_social_foto!=null)||(strcmp($paciente->obra_social_foto, '') != 0))
            $obraSocialCargada = 1;

        $especialidadNombreFlujo = EspecialidadFlujoHelper::nombreParaTurno(
            $request->input('especialidad_nombre_flujo'),
            null,
            $request->input('especialidad_id'),
            $medico->especialidad ?? null
        );

    	return view('turnos.seleccionar_turno')
        ->with('esVideollamada',$esVideollamada)
        ->with('tipoTurno', $tipoTurno)
        ->with('primerControl',$primerControl)
        ->with('turnos',$someArray)
        ->with('medico',$medico)
        ->with('obraSocialCargada',$obraSocialCargada)
        ->with('dia',$diaSeleccionado)
        ->with('fechaSolicitada',$fechaSolicitada)
        ->with('hayTurnoLibre',$turnosLibres)
        ->with('paciente',$paciente)
        ->with('moduloPrimerControlDoble',$moduloPrimerControlDoble)
        ->with('validarFeriado',$validarFeriado)
        ->with('cantTurnosMes',$cantTurnosMes)
        ->with('consultorio',$consultorio)
        ->with('dias_deshabilitados',$dias_deshabilitados)
        ->with('especialidad_nombre_flujo', $especialidadNombreFlujo);    
    }

    public function seleccionarTurnoHorarioVideollamada(Request $request)
    {   
        $paciente= DB::table('pacientes')->find($request->paciente_id);
        $primerControl = $request->get('primer_control');
        $dias_deshabilitados = $request->get('dias_deshabilitados');
        $esVideollamada = $request->get('esVideollamada');
        $dni_paciente = $paciente->dni;
        $fechaSolicitada = $request->get('fecha_seleccionada');
        $fechaAux = explode('/',$fechaSolicitada);
        $dia = $fechaAux[0];
        $mes = $fechaAux[1];
        $anio = $fechaAux[2];
        $nuevaFecha=$anio.'-'.$mes.'-'.$dia;
        $date = new DateTime($nuevaFecha);      
        //return date_format($date, 'g:ia \o\n l jS F Y');   //12:00am on Friday 7th June 2019  
        $diaLetras = date_format($date, 'l');        
        // me devuelve el numero del dia. Lunes = 1
        $diaSeleccionado = $this->getDiaSeleccionado2($nuevaFecha);
        
        $medico_id = $request->get('medico');
        $medico= DB::table('medicos')->find($medico_id);
        $consultorio_id = $request->get('consultorio'); 

        $consultorio = consultorio::find($consultorio_id);  

        $turnos = DB::select('select * from horario_medico_videollamadas where medico = ? and consultorio = ? and dia = ? and activo=1',
            [$medico_id , $consultorio_id, $diaSeleccionado]);

        $turnosRegistrados = DB::select('select * from turno_registrado_videollamadas where medico = ? and consultorio = ? and dia = ?  and fechaTurno = ? and activo=1',
            [$medico_id, $consultorio_id, $diaSeleccionado, $nuevaFecha]);
        // En caso de ser 1 es su primer control. Tengo que mostrar turnos dobles sino de a un turno.
                    
        //turnos comunes cada media hora.
        $data = $this-> createJsonVideollamada($medico_id, $consultorio_id, $diaSeleccionado, $nuevaFecha);        
        
        $turnosLibres=0;                
        $someArray = json_decode($data, true);
        if (!is_array($someArray)) {
            $someArray = [];
        }
        $someArray = $this->aplicarMostrarDosPaciente($medico_id, $someArray);
        $turnosLibres = $this->debeMostrarGrillaHorariosPaciente($medico_id, $someArray) ? 1 : 0;

        $validarFeriado = DB::table('feriados')                                        
                        ->where('feriados.fecha', '=', $nuevaFecha)                        
                        ->get();        
        if($validarFeriado->count()>0)
            $turnosLibres = 2;                
                
        $moduloPrimerControlDoble = 0;        
        
        $cantTurnosMes = 0; 

        $tipoTurno = 0;
        if($medico->especialidad == 2){
            $tipoTurno = 4;
        }

        if(($request->foto != null) &&(strcmp($request->foto, '') != 0)){
            $this->cargarFotoObraSocial($request, $request->paciente_id);
        }
           $obraSocialCargada = 0;
        if(($paciente->obra_social_foto!=null)||(strcmp($paciente->obra_social_foto, '') != 0))
            $obraSocialCargada = 1;

        $especialidadNombreFlujo = EspecialidadFlujoHelper::nombreParaTurno(
            $request->input('especialidad_nombre_flujo'),
            null,
            $request->input('especialidad_id'),
            $medico->especialidad ?? null
        );

        return view('turnos.seleccionar_turno')
        ->with('esVideollamada',$esVideollamada)
        ->with('tipoTurno', $tipoTurno)
        ->with('primerControl',$primerControl)
        ->with('turnos',$someArray)
        ->with('obraSocialCargada',$obraSocialCargada)
        ->with('medico',$medico)
        ->with('dia',$diaSeleccionado)
        ->with('fechaSolicitada',$fechaSolicitada)
        ->with('hayTurnoLibre',$turnosLibres)
        ->with('paciente',$paciente)
        ->with('moduloPrimerControlDoble',$moduloPrimerControlDoble)
        ->with('validarFeriado',$validarFeriado)
        ->with('cantTurnosMes',$cantTurnosMes)
        ->with('consultorio',$consultorio)
        ->with('dias_deshabilitados',$dias_deshabilitados)
        ->with('especialidad_nombre_flujo', $especialidadNombreFlujo);    
    }

    public function cargarFotoObraSocial($request, $paciente_id) {
        $paciente = paciente::find($paciente_id);
        if(strcmp($request->foto, '') != 0) {
            if($request->hasfile('foto')){
                $pathName = $request->file('foto')->store('images/obra_social_carnet/');
                $name = collect(explode('/', $pathName))->last();
                $image = $request->file('foto');
                //$name  = $image->getClientOriginalName().time().'.'.$image->getClientOriginalExtension();
                $path = 'images/obra_social_carnet/'.$name;        
                Image::make($image->getRealPath())->resize(1980, 1920)->save($path);            
                $paciente->obra_social_foto = $name;
                $paciente->save();
            } 
        }        
    }

    // Los medicos pueden definir tener una cantidad x de primeros controles cada dia.
    // Ejemplo pueden querer tener solo 2 primeros controles un miercoles.
    // Devuelvo true en caso de que se pueda sacar un turno ese dia.
    public function controlarCantidadPrimerControl($medico_id, $dia, $consultorio_id, $fechaTurno){
        $cantidadPrimerControlTable = DB::table('medico_primer_controls')
                                            ->where('medico_primer_controls.medico', $medico_id)
                                            ->where('medico_primer_controls.dia', $dia)
                                            ->where('medico_primer_controls.consultorio', $consultorio_id)
                                            ->where('medico_primer_controls.activo', 1)
                                            ->first();
        // contiene la cantidad posible de primeros controles.
        if($cantidadPrimerControlTable!=null){                                      
            $cantidadPrimerControl = $cantidadPrimerControlTable->cantidadPrimerControl;
            
            // contiene la cantidad actual de primeros controles sacados para ese dia.
            $cantidadPrimerControlActual = DB::table('turno_registrados')
                                            ->where('turno_registrados.medico', $medico_id)
                                            ->where('turno_registrados.consultorio', $consultorio_id)
                                            ->where('turno_registrados.fechaTurno', $fechaTurno)
                                            ->where('turno_registrados.activo', 1)
                                            ->where('turno_registrados.primerControl', 'SI')
                                            ->count();  
            // Lo multiplico por dos, porque en turno registrado filtro por primerControl SI y me cuenta repetidos.
            $cantidadPrimerControl = $cantidadPrimerControl * 2;
            //return '$cantidadPrimerControl: '.$cantidadPrimerControl.' $cantidadPrimerControlActual '.$cantidadPrimerControlActual;
            if($cantidadPrimerControl > $cantidadPrimerControlActual)
                return true;
            else
                return false;
        } else {
            return false;
        }

    }

    public function createJsonVideollamada($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada){
        $turnos = DB::table('horario_medico_videollamadas')                  
                        ->where('horario_medico_videollamadas.medico',$medico_id)
                        ->where('horario_medico_videollamadas.consultorio', $consultorio_id)
                        ->where('horario_medico_videollamadas.dia', $diaSeleccionado)
                        ->where('horario_medico_videollamadas.activo', 1)                        
                        ->orderBy('horario_medico_videollamadas.horario')                        
                        ->get();

        $turnosRegistrados = DB::table('turno_registrado_videollamadas')
                        ->where('turno_registrado_videollamadas.medico',$medico_id)
                        ->where('turno_registrado_videollamadas.consultorio', $consultorio_id)
                        ->where('turno_registrado_videollamadas.dia', $diaSeleccionado)
                        ->where('turno_registrado_videollamadas.fechaTurno', $fechaSolicitada)
                        ->where('turno_registrado_videollamadas.activo', 1)                        
                        ->get();        
        $json = array();
        $contador=0;
        $encontre=0;
        foreach($turnos as $turno) {                                    
            while (($encontre < 1) && ($contador<count($turnosRegistrados))){            
                if(strcmp ($turno->horario , $turnosRegistrados[$contador]->horario ) == 0){
                    $encontre=1;
                    $json['horario'] = $turno->horario;
                    $json['libre'] = 0;                    
                    $data[] = $json;
                                                                                                
                } else {                                                            
                    $encontre=0;                    
                }
                $contador++;
            }
            if($encontre == 0){
                $json['horario'] = $turno->horario;
                $json['libre'] = 1;                    
                $data[] = $json;
            }            
            $encontre=0;
            $contador=0;
        }
        return json_encode($data);
        
    }

      function checkTurnoLibreEspecialMagali($medico_id, $consultorio_id, $diaSeleccionado, $fechaSeleccionada){
       // Antes de enero de 14 a 18 hs va los viernes
        // despues de enero de 15 a 19 hs  va los miercoles
        $turnos = null;
        $mostrarDespuesDeFecha = '2025-01-01';
        if($fechaSeleccionada >= $mostrarDespuesDeFecha) {            
            if($diaSeleccionado == 5 ){
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario', '==', '14:99')                        
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }                            
        } else {            
            if($diaSeleccionado == 3) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario', '==', '14:99')                        
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }                            
        }
        if($turnos != null)
            return $turnos;
        else {
            $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.activo', 1)                        
                        ->orderby('horario_medicos.horario')
                        ->get();
            return $turnos;
        }
    }

    function checkTurnoLibreEspecialSole($medico_id, $consultorio_id, $diaSeleccionado, $fechaSeleccionada){
        $turnos = null;
        $mostrarDespuesDeFecha = '2026-02-01';
        $mostrarAntesDeFecha = '2025-03-01';
        if($fechaSeleccionada >= $mostrarDespuesDeFecha) {            
            if($diaSeleccionado == 2 ) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario', '==', '99:99')                        
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }
            if($diaSeleccionado == 3 ) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario', '!=', '15:00')                        
                        ->where('horario_medicos.horario', '!=', '15:40')
                        ->where('horario_medicos.horario', '!=', '16:20')
                        ->where('horario_medicos.horario', '!=', '17:00')
                        ->where('horario_medicos.horario', '!=', '17:40')
                        ->where('horario_medicos.horario', '!=', '18:20')                        
                        ->where('horario_medicos.horario', '!=', '19:00')
                        ->where('horario_medicos.horario', '!=', '19:40')
                        ->where('horario_medicos.horario', '!=', '20:20')
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }
            if($diaSeleccionado == 5 ) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario', '!=', '10:20')                        
                        ->where('horario_medicos.horario', '!=', '11:00')
                        ->where('horario_medicos.horario', '!=', '11:40')
                        ->where('horario_medicos.horario', '!=', '12:20')                        
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }                            
        } else {    
            if($diaSeleccionado == 3 ) {
                    $turnos = DB::table('horario_medicos')
                            ->where('horario_medicos.medico',$medico_id)
                            ->where('horario_medicos.consultorio', $consultorio_id)
                            ->where('horario_medicos.dia', $diaSeleccionado)
                            ->where('horario_medicos.horario', '!=', '08:30')                        
                            ->where('horario_medicos.horario', '!=', '09:10')
                            ->where('horario_medicos.horario', '!=', '09:50')
                            ->where('horario_medicos.horario', '!=', '10:30')                                               
                            ->where('horario_medicos.horario', '!=', '11:10')                                               
                            ->where('horario_medicos.horario', '!=', '11:50')                                               
                            ->where('horario_medicos.horario', '!=', '12:30')                                               
                            ->where('horario_medicos.horario', '!=', '14:00')                                               
                            ->where('horario_medicos.horario', '!=', '14:40')
                            ->where('horario_medicos.horario', '!=', '15:20')                                               
                            ->where('horario_medicos.activo', 1)         
                            ->orderby('horario_medicos.horario')               
                            ->get();
            }         
            if($diaSeleccionado == 4 ) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario', '==', '99:99')                        
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }            
            if($diaSeleccionado == 5 ) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario', '!=', '10:30')                        
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }                         
        }
        if($turnos != null)
            return $turnos;
        else {
            $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.activo', 1)                        
                        ->orderby('horario_medicos.horario')
                        ->get();
            return $turnos;
        }
    }

    function checkTurnoLibreEspecialEli($medico_id, $consultorio_id, $diaSeleccionado, $fechaSeleccionada){       
        if($diaSeleccionado == 5 ) {
            $turnos = DB::table('horario_medicos')
                    ->where('horario_medicos.medico',$medico_id)
                    ->where('horario_medicos.consultorio', $consultorio_id)
                    ->where('horario_medicos.dia', $diaSeleccionado)
                    ->where('horario_medicos.horario', '==', '99:00')  
                    ->where('horario_medicos.activo', 1)                        
                    ->orderby('horario_medicos.horario')
                    ->get();    
        } else {
            $turnos = DB::table('horario_medicos')
                    ->where('horario_medicos.medico',$medico_id)
                    ->where('horario_medicos.consultorio', $consultorio_id)
                    ->where('horario_medicos.dia', $diaSeleccionado)
                    ->where('horario_medicos.activo', 1)                        
                    ->orderby('horario_medicos.horario')
                    ->get();    
        }        
        return $turnos;        
    }

    function checkTurnoLibreEspecialAnto($medico_id, $consultorio_id, $diaSeleccionado, $fechaSeleccionada){       
        if($diaSeleccionado == 5 ) {
            $turnos = DB::table('horario_medicos')
                    ->where('horario_medicos.medico',$medico_id)
                    ->where('horario_medicos.consultorio', $consultorio_id)
                    ->where('horario_medicos.dia', $diaSeleccionado)
                    ->where('horario_medicos.horario', '==', '99:00')  
                    ->where('horario_medicos.activo', 1)                        
                    ->orderby('horario_medicos.horario')
                    ->get();    
        } else {
            $turnos = DB::table('horario_medicos')
                    ->where('horario_medicos.medico',$medico_id)
                    ->where('horario_medicos.consultorio', $consultorio_id)
                    ->where('horario_medicos.dia', $diaSeleccionado)
                    ->where('horario_medicos.activo', 1)                        
                    ->orderby('horario_medicos.horario')
                    ->get();    
        }        
        return $turnos;      
    }

    function checkTurnoLibreEspecialPabloPrado($medico_id, $consultorio_id, $diaSeleccionado, $fechaSeleccionada){       
        $turnos = DB::table('horario_medicos')
                    ->where('horario_medicos.medico',$medico_id)
                    ->where('horario_medicos.consultorio', $consultorio_id)
                    ->where('horario_medicos.dia', $diaSeleccionado)
                    ->where('horario_medicos.activo', 1)                        
                    ->orderby('horario_medicos.horario')
                    ->get();
        return $turnos;        
    }

    function checkTurnoLibreEspecialNataliaFerrari($medico_id, $consultorio_id, $diaSeleccionado, $fechaSeleccionada){        
        $turnos = null;                
        if($fechaSeleccionada >= '2025-09-01') {
            // mostrar MARTES 16:00 a 19:00
            if($diaSeleccionado == 2) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)                                                
                        ->where('horario_medicos.horario','!=' ,'14:30')
                        ->where('horario_medicos.horario','!=' ,'15:00')
                        ->where('horario_medicos.horario','!=' ,'15:30')                        
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get(); 
            }                    
        } else {       
            // mostrar MARTES 14:30 a 18:00
            if($diaSeleccionado == 2) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)                                                
                        ->where('horario_medicos.horario','!=' ,'18:30')                    
                        ->where('horario_medicos.horario','!=' ,'19:00')                    
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get(); 
            }                            
        }        

        if($turnos != null)
            return $turnos;
        else {
            $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.activo', 1)                        
                        ->orderby('horario_medicos.horario')
                        ->get();
            return $turnos;
        } 
    }

    function checkTurnoLibreEspecialLucasGili($medico_id, $consultorio_id, $diaSeleccionado, $fechaSeleccionada){        
        $turnos = null;                
        if($fechaSeleccionada >= '2025-07-01') {
            // mostrar LUNES 14:30 a 17:30.  y miercoles 09:00 a 14:00

            if($diaSeleccionado == 1) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)                                                
                        ->where('horario_medicos.horario','!=' ,'17:30')
                        ->where('horario_medicos.horario','!=' ,'18:00')
                        ->where('horario_medicos.horario','!=' ,'18:30')                        
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get(); 
            }                    
        } else {       
            if($diaSeleccionado == 1) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)                                                
                        ->where('horario_medicos.horario','!=' ,'14:30')
                        ->where('horario_medicos.horario','!=' ,'15:00')                        
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get(); 
            }
            if($diaSeleccionado == 3) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)                                                                        
                        ->where('horario_medicos.horario','!=' ,'09:00')                                                
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get(); 
            }                        
        }        

        if($turnos != null)
            return $turnos;
        else {
            $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.activo', 1)                        
                        ->orderby('horario_medicos.horario')
                        ->get();
            return $turnos;
        } 
    }

    function checkTurnoLibreEspecialMarina($medico_id, $consultorio_id, $diaSeleccionado, $fechaSeleccionada){        
        $turnos = null;                
        if($fechaSeleccionada >= '2026-03-01') {
            // mostrar 9.30 10.00 10.30 11.00 11.30
            if($diaSeleccionado == 2) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario','!=' ,'09:00')
                        ->where('horario_medicos.horario','!=' ,'09:30')
                        ->where('horario_medicos.horario','!=' ,'10:00')
                        ->where('horario_medicos.horario','!=' ,'10:30')
                        ->where('horario_medicos.horario','!=' ,'11:00')
                        ->where('horario_medicos.horario','!=' ,'11:30')
                        ->where('horario_medicos.horario','!=' ,'12:00')
                        ->where('horario_medicos.horario','!=' ,'12:30')
                        ->where('horario_medicos.horario','!=' ,'13:00')
                        ->where('horario_medicos.horario','!=' ,'13:30')
                        ->where('horario_medicos.horario','!=' ,'16:10')
                        ->where('horario_medicos.horario','!=' ,'16:50')                        
                        ->where('horario_medicos.horario','!=' ,'18:10')
                        ->where('horario_medicos.horario','!=' ,'18:50')
                        ->where('horario_medicos.activo', 1)                        
                        ->orderby('horario_medicos.horario')
                        ->get();
            }            
        } else {       
            if($diaSeleccionado == 2) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)    
                        ->where('horario_medicos.horario','!=' ,'15:00')                       
                        ->where('horario_medicos.horario','!=' ,'16:00')                        
                        ->where('horario_medicos.horario','!=' ,'16:30')
                        ->where('horario_medicos.horario','!=' ,'17:00')                                        
                        ->where('horario_medicos.horario','!=' ,'18:00')                        
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get(); 
            }                           
        }        

        if($turnos != null)
            return $turnos;
        else {
            $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.activo', 1)                        
                        ->orderby('horario_medicos.horario')
                        ->get();
            return $turnos;
        } 
    }

    function checkTurnoLibreEspecialMonica($medico_id, $consultorio_id, $diaSeleccionado, $fechaSeleccionada){        
        $turnos = null;                
        if($fechaSeleccionada >= '2026-04-01') {
            
            if($diaSeleccionado == 3) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario','==' ,'99:00')                        
                        ->where('horario_medicos.activo', 1)                        
                        ->orderby('horario_medicos.horario')
                        ->get();
            }            
        }                          
            
        if($turnos != null)
            return $turnos;
        else {
            $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.activo', 1)                        
                        ->orderby('horario_medicos.horario')
                        ->get();
            return $turnos;
        } 
    }

    function checkTurnoLibreEspecialFlor($medico_id, $consultorio_id, $diaSeleccionado, $fechaSeleccionada) {
         // a partir de febrero
        $turnos = null;
        $mostrarDespuesDeFecha = '2025-02-01';
        if($fechaSeleccionada >= $mostrarDespuesDeFecha) {            
            if($diaSeleccionado == 3) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario', '==', '99:00')                        
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }                    
        } else {            
            if($diaSeleccionado == 4) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario', '==', '99:00')                        
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }            
        }
        if($diaSeleccionado == 5) {
            $turnos = DB::table('horario_medicos')
                    ->where('horario_medicos.medico',$medico_id)
                    ->where('horario_medicos.consultorio', $consultorio_id)
                    ->where('horario_medicos.dia', $diaSeleccionado)
                    ->where('horario_medicos.horario', '==', '99:00')                        
                    ->where('horario_medicos.activo', 1)                                 
                    ->get();
        }
        if($turnos != null)
            return $turnos;
        else {
            $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.activo', 1)                        
                        ->orderby('horario_medicos.horario')
                        ->get();
            return $turnos;
        } 
    }

     // Los turnos del martes, los paso al lunes desde las 15 hs. Cada 40 min. Ultimo 18:20 hs
    // Los del Jueves en Garibaldi 44 dejar 2. Uno a las 15:30  y otro 16:10 hs. Solo esos dos
    function checkTurnoLibreEspecialEricaPacheco($medico_id, $consultorio_id, $diaSeleccionado, $fechaSeleccionada){
        $turnos = null;
        $mostrarDespuesDeFecha = '2023-01-01';
        $mostrarHastaFecha = '2023-02-01';
        if($fechaSeleccionada >= $mostrarDespuesDeFecha && $fechaSeleccionada <= $mostrarHastaFecha){
            if($diaSeleccionado == 2){
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado) 
                        ->where('horario_medicos.horario', '!=', '14:00')
                        ->where('horario_medicos.horario', '!=', '14:30')
                        ->where('horario_medicos.horario', '!=', '15:00')                       
                        ->where('horario_medicos.horario', '!=', '15:30')
                        ->where('horario_medicos.horario', '!=', '16:00')
                        ->where('horario_medicos.horario', '!=', '16:40')
                        ->where('horario_medicos.horario', '!=', '17:20')
                        ->where('horario_medicos.horario', '!=', '18:00')
                        ->where('horario_medicos.horario', '!=', '18:40')                          
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }
            if($diaSeleccionado == 3){
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)                                                
                        ->where('horario_medicos.horario', '!=', '14:00')
                        ->where('horario_medicos.horario', '!=', '14:30')
                        ->where('horario_medicos.horario', '!=', '15:00')
                        ->where('horario_medicos.horario', '!=', '15:30')
                        ->where('horario_medicos.horario', '!=', '16:00')                                                
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }           
        } else {
            if($diaSeleccionado == 2){
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado) 
                        ->where('horario_medicos.horario', '!=', '09:00')
                        ->where('horario_medicos.horario', '!=', '09:30')
                        ->where('horario_medicos.horario', '!=', '10:00')
                        ->where('horario_medicos.horario', '!=', '10:30')
                        ->where('horario_medicos.horario', '!=', '11:00')
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }
            if($diaSeleccionado == 3){
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)                                                
                        ->where('horario_medicos.horario', '!=', '09:00')
                        ->where('horario_medicos.horario', '!=', '09:30')
                        ->where('horario_medicos.horario', '!=', '10:00')
                        ->where('horario_medicos.horario', '!=', '10:30')
                        ->where('horario_medicos.horario', '!=', '11:00')                                                
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }           
        }
        if($turnos != null)
            return $turnos;
        else {
            $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.activo', 1)                        
                        ->orderby('horario_medicos.horario')
                        ->get();
            return $turnos;
        }
    }

    function checkTurnoLibreEspecialLucasSosa($medico_id, $consultorio_id, $diaSeleccionado, $fechaSeleccionada, $tipoTurno){        
        $turnos = null;
        $mostrarDespuesDeFecha = '2023-08-01';
        $fechaSeleccionada = str_replace("/", "-", $fechaSeleccionada);
        if($fechaSeleccionada >= $mostrarDespuesDeFecha){
            // A partir de agosto mostrar lo siguiente
            // martes y jueves 13.40 hs cada 20 min 9 turnos
            if($diaSeleccionado == 2) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        //->where('horario_medicos.tipo_turno', $tipoTurno)
                        ->where('horario_medicos.horario', '!=', '16:40')
                        ->where('horario_medicos.horario', '!=', '17:00')
                        ->where('horario_medicos.horario', '!=', '17:20')                        
                        ->where('horario_medicos.horario', '!=', '17:40')
                        ->where('horario_medicos.horario', '!=', '18:00')
                        ->where('horario_medicos.horario', '!=', '18:20')
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }
            if($diaSeleccionado == 4) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        //->where('horario_medicos.tipo_turno', $tipoTurno)
                        ->where('horario_medicos.horario', '!=', '16:40')
                        ->where('horario_medicos.horario', '!=', '17:00')
                        ->where('horario_medicos.horario', '!=', '17:20')                        
                        ->where('horario_medicos.horario', '!=', '17:40')
                        ->where('horario_medicos.horario', '!=', '18:00')
                        ->where('horario_medicos.horario', '!=', '18:20')
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }                    
        } else {
            // Antes de agosto

            if($diaSeleccionado == 2) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        //->where('horario_medicos.tipo_turno', $tipoTurno)
                        ->where('horario_medicos.horario', '!=', '13:40')
                        ->where('horario_medicos.horario', '!=', '14:00')
                        ->where('horario_medicos.horario', '!=', '14:20')                        
                        ->where('horario_medicos.horario', '!=', '14:40')
                        ->where('horario_medicos.horario', '!=', '15:00')
                        ->where('horario_medicos.horario', '!=', '15:20')
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }
            if($diaSeleccionado == 4) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        //->where('horario_medicos.tipo_turno', $tipoTurno)
                        ->where('horario_medicos.horario', '!=', '13:40')
                        ->where('horario_medicos.horario', '!=', '14:00')
                        ->where('horario_medicos.horario', '!=', '14:20')                        
                        ->where('horario_medicos.horario', '!=', '14:40')
                        ->where('horario_medicos.horario', '!=', '15:00')
                        ->where('horario_medicos.horario', '!=', '15:20')
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            } 
            if($diaSeleccionado == 3) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        //->where('horario_medicos.tipo_turno', $tipoTurno)
                        ->where('horario_medicos.horario', '=', '99:99')                        
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }          
        }
        if($turnos != null)
            return $turnos;
        else {
            $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        //->where('horario_medicos.tipo_turno', $tipoTurno)
                        ->where('horario_medicos.activo', 1)                        
                        ->orderby('horario_medicos.horario')
                        ->get();
            return $turnos;
        }
    }
    
    function checkTurnoLibreEspecialPatriciaSosa($medico_id, $consultorio_id, $diaSeleccionado, $fechaSeleccionada){
        // a partir del 9 de marzo, cada 20 min, de las 16 a las 18:20 8 turnos, lunes miercoles viernes
        $turnos = null;
        $mostrarDespuesDeFecha = '2022-03-09';
        if($fechaSeleccionada >= $mostrarDespuesDeFecha){            
            if($diaSeleccionado == 1 || $diaSeleccionado == 3 || $diaSeleccionado == 5){
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario', '!=', '16:30')
                        ->where('horario_medicos.horario', '!=', '17:30')                                                                    
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }            
        } else {            
            if($diaSeleccionado == 1 || $diaSeleccionado == 3 || $diaSeleccionado == 5){
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario', '!=', '16:20')
                        ->where('horario_medicos.horario', '!=', '16:40')
                        ->where('horario_medicos.horario', '!=', '17:20')                        
                        ->where('horario_medicos.horario', '!=', '17:40')
                        ->where('horario_medicos.horario', '!=', '18:20')                        
                        ->where('horario_medicos.horario', '!=', '18:40')                        
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }            
        }
        if($turnos != null)
            return $turnos;
        else {
            $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.activo', 1)                        
                        ->orderby('horario_medicos.horario')
                        ->get();
            return $turnos;
        }
    }

    function checkTurnoLibreEspecialCeleste($medico_id, $consultorio_id, $diaSeleccionado, $fechaSeleccionada) {
        // a partir del 9 de marzo, cada 20 min, de las 16 a las 18:20 8 turnos, lunes miercoles viernes
        $turnos = null;
        $mostrarDespuesDeFecha = '2025-03-01';
        if($fechaSeleccionada >= $mostrarDespuesDeFecha) {            
            // no debo mostrar  14.45  16.15 
            if($diaSeleccionado == 5) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario', '!=', '14:45')
                        ->where('horario_medicos.horario', '!=', '16:15')                    
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }            
        } else {            
            if($diaSeleccionado == 5) {
                // no debo mostrar 14:30, 15,  16, 16:30 
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario', '!=', '14:30')
                        ->where('horario_medicos.horario', '!=', '15:00')
                        ->where('horario_medicos.horario', '!=', '16:30')                                            
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }            
        }
        if($turnos != null)
            return $turnos;
        else {
            $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.activo', 1)                        
                        ->orderby('horario_medicos.horario')
                        ->get();
            return $turnos;
        }
    }

    function checkTurnoLibreEspecialFonseca($medico_id, $consultorio_id, $diaSeleccionado, $fechaSeleccionada) {        
        $turnos = null;
        $mostrarDespuesDeFecha = '2025-06-18';
        if($fechaSeleccionada >= $mostrarDespuesDeFecha) {
            // no debo mostrar 15:20 15:50 16:10 16:50
            if($diaSeleccionado == 3) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario', '!=', '15:20')
                        ->where('horario_medicos.horario', '!=', '15:50')                    
                        ->where('horario_medicos.horario', '!=', '16:10')                    
                        ->where('horario_medicos.horario', '!=', '16:50')                    
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }            
        } else {            
            // no debo mostrar 15:30 16:00 16:40
            if($diaSeleccionado == 3) {                
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario', '!=', '15:30')
                        ->where('horario_medicos.horario', '!=', '16:00')
                        ->where('horario_medicos.horario', '!=', '16:40')                                            
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }            
        }
        if($turnos != null)
            return $turnos;
        else {
            $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.activo', 1)                        
                        ->orderby('horario_medicos.horario')
                        ->get();
            return $turnos;
        }
    }

    // lunes a viernes a partir de las 9 cada 20 min 7 pacientes... 
    function checkTurnoLibreEspecialAmilcarSosa($medico_id, $consultorio_id, $diaSeleccionado, $fechaSeleccionada){
        // a partir del 22 de diciembre miercoles a la mañana igual que lunes y viernes
        $turnos = null;
        $mostrarDespuesDeFecha = '2022-06-10';
        if($fechaSeleccionada >= $mostrarDespuesDeFecha){
            // A partir de julio mostrar lo siguiente
            // Lunes y viernes            
            if($diaSeleccionado == 1 || $diaSeleccionado == 5){
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario', '!=', '08:30')
                        ->where('horario_medicos.horario', '!=', '09:00')
                        ->where('horario_medicos.horario', '!=', '09:20')
                        ->where('horario_medicos.horario', '!=', '09:30')                        
                        ->where('horario_medicos.horario', '!=', '09:40')
                        ->where('horario_medicos.horario', '!=', '10:00')                        
                        ->where('horario_medicos.horario', '!=', '10:20')                        
                        ->where('horario_medicos.horario', '!=', '10:30')                        
                        ->where('horario_medicos.horario', '!=', '10:40') 
                        ->where('horario_medicos.horario', '!=', '11:00')
                        ->where('horario_medicos.horario', '!=', '11:30')
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }
            if($diaSeleccionado == 3) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)                        
                        ->where('horario_medicos.horario', '!=', '09:00')
                        ->where('horario_medicos.horario', '!=', '09:20')
                        ->where('horario_medicos.horario', '!=', '09:40')                                                
                        ->where('horario_medicos.horario', '!=', '10:00')                        
                        ->where('horario_medicos.horario', '!=', '10:20')                        
                        ->where('horario_medicos.horario', '!=', '10:40')                                                
                        ->where('horario_medicos.horario', '!=', '11:00')
                        ->where('horario_medicos.horario', '!=', '16:30')
                        ->where('horario_medicos.horario', '!=', '17:30')
                        ->where('horario_medicos.horario', '!=', '18:30')
                        ->where('horario_medicos.horario', '!=', '19:00')
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }                        
        } else {
            // Antes de diciembre 22
            // Lunes            
            if($diaSeleccionado == 1){
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario', '!=', '16:00')
                        ->where('horario_medicos.horario', '!=', '16:20')
                        ->where('horario_medicos.horario', '!=', '16:40')                        
                        ->where('horario_medicos.horario', '!=', '17:00')
                        ->where('horario_medicos.horario', '!=', '17:20')
                        ->where('horario_medicos.horario', '!=', '17:40')                        
                        ->where('horario_medicos.horario', '!=', '18:00')                        
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }
            if($diaSeleccionado == 3) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario', '!=', '16:00')
                        ->where('horario_medicos.horario', '!=', '16:20')
                        ->where('horario_medicos.horario', '!=', '16:30')
                        ->where('horario_medicos.horario', '!=', '16:40')                        
                        ->where('horario_medicos.horario', '!=', '17:00')
                        ->where('horario_medicos.horario', '!=', '17:20')
                        ->where('horario_medicos.horario', '!=', '17:30')
                        ->where('horario_medicos.horario', '!=', '17:40')                        
                        ->where('horario_medicos.horario', '!=', '18:00')
                        ->where('horario_medicos.horario', '!=', '18:30')
                        ->where('horario_medicos.horario', '!=', '19:00')                        
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();                
            }
            if($diaSeleccionado == 5) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario', '!=', '08:30')
                        ->where('horario_medicos.horario', '!=', '09:30')
                        ->where('horario_medicos.horario', '!=', '10:30')
                        ->where('horario_medicos.horario', '!=', '11:30')
                        ->where('horario_medicos.horario', '!=', '16:00')
                        ->where('horario_medicos.horario', '!=', '16:20')
                        ->where('horario_medicos.horario', '!=', '16:40')                        
                        ->where('horario_medicos.horario', '!=', '17:00')
                        ->where('horario_medicos.horario', '!=', '17:20')
                        ->where('horario_medicos.horario', '!=', '17:40')                        
                        ->where('horario_medicos.horario', '!=', '18:00')                      
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();                
            }
        }       
        if($turnos != null)
            return $turnos;
        else {
            $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.activo', 1)                        
                        ->orderby('horario_medicos.horario')
                        ->get();
            return $turnos;
        }
    }

    function checkTurnoLibreEspecialLuciaDiomedi($medico_id, $consultorio_id, $diaSeleccionado, $fechaSeleccionada){        
        $turnos = DB::table('horario_medicos')
                    ->where('horario_medicos.medico',$medico_id)
                    ->where('horario_medicos.consultorio', $consultorio_id)
                    ->where('horario_medicos.dia', $diaSeleccionado)
                    ->where('horario_medicos.activo', 1)                        
                    ->orderby('horario_medicos.horario')
                    ->get();
        return $turnos;        
    }

    function checkTurnoLibreEspecialCeciCorti($medico_id, $consultorio_id, $diaSeleccionado, $fechaSeleccionada){       
        $turnos = null;                
        if($fechaSeleccionada >= '2026-02-01') {
            // Lunes 11- 11:45- 12:30 13:15 - 16 - 16:45- 17:30 - 18:15
            // Martes  8:15 -  9: 00 - 9:45 - 10:30 - 11:15 - 12  - 12:45 - 13:30  ==== 16 - 16:45 - 17:30 - 18:15 hs
            // Miercoles  8:15 -  9: 00 - 9:45 - 10:30 - 11:15 - 12  - 12:45   ==== 16 - 16:45 - 17:30 - 18:15 - 19 hs
            // Jueves 8:15 - 9- 9:45 - 10:30 - 11:15 - 12 y 12:45
            if($diaSeleccionado == 1) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)                                                
                        ->where('horario_medicos.horario','!=' ,'09:00')
                        ->where('horario_medicos.horario','!=' ,'09:45')
                        ->where('horario_medicos.horario','!=' ,'10:30')
                        ->where('horario_medicos.horario','!=' ,'11:15')                        
                        ->where('horario_medicos.horario','!=' ,'12:00')                        
                        ->where('horario_medicos.horario','!=' ,'12:45')                        
                        ->where('horario_medicos.horario','!=' ,'13:30')                        
                        ->where('horario_medicos.horario','!=' ,'14:15')                        
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get(); 
            }
            if($diaSeleccionado == 2) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)                                                
                        ->where('horario_medicos.horario','!=' ,'14:00')
                        ->where('horario_medicos.horario','!=' ,'14:45')
                        ->where('horario_medicos.horario','!=' ,'15:30')
                        ->where('horario_medicos.horario','!=' ,'16:15')                        
                        ->where('horario_medicos.horario','!=' ,'17:00')                        
                        ->where('horario_medicos.horario','!=' ,'17:45')                                                
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get(); 
            }  
            if($diaSeleccionado == 3) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado) 
                        ->where('horario_medicos.horario','!=' ,'13:30')                                                                                                                                             
                        ->where('horario_medicos.horario','!=' ,'14:15')   
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get(); 
            }    
            if($diaSeleccionado == 4) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)                                                
                        ->where('horario_medicos.horario','!=' ,'14:00')
                        ->where('horario_medicos.horario','!=' ,'14:45')
                        ->where('horario_medicos.horario','!=' ,'15:30')
                        ->where('horario_medicos.horario','!=' ,'16:15')                        
                        ->where('horario_medicos.horario','!=' ,'17:00')                        
                        ->where('horario_medicos.horario','!=' ,'17:45')                                                
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get(); 
            }     
            if($diaSeleccionado == 5) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)                                                
                        ->where('horario_medicos.horario','==' ,'99:00')                                                                       
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get(); 
            }              
        } else {       
            // Lunes y miercoles 9- 9:45 - 10:30 - 11:15 -  12 - 12:45 - 13:30 y 14:15
            // Martes, jueves y viernes solo en estas fechas 14 - 14:45 - 15:30 - 16:15 - 17 - 17:45
            if($diaSeleccionado == 1) {
                // Lunes 11- 11:45- 12:30 13:15 - 16 - 16:45- 17:30 - 18:15
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)                                                
                        ->where('horario_medicos.horario','!=' ,'11:00')                    
                        ->where('horario_medicos.horario','!=' ,'11:45')                    
                        ->where('horario_medicos.horario','!=' ,'12:30')                    
                        ->where('horario_medicos.horario','!=' ,'13:15')                    
                        ->where('horario_medicos.horario','!=' ,'16:00')                    
                        ->where('horario_medicos.horario','!=' ,'16:45')                    
                        ->where('horario_medicos.horario','!=' ,'17:30')                    
                        ->where('horario_medicos.horario','!=' ,'18:15')                    
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get(); 
            }   
            if($diaSeleccionado == 2) {
                // Martes  8:15 -  9: 00 - 9:45 - 10:30 - 11:15 - 12  - 12:45 - 13:30  ==== 16 - 16:45 - 17:30 - 18:15 hs
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)                                                
                        ->where('horario_medicos.horario','!=' ,'08:15')                    
                        ->where('horario_medicos.horario','!=' ,'09:00')                    
                        ->where('horario_medicos.horario','!=' ,'09:45')                    
                        ->where('horario_medicos.horario','!=' ,'10:30')                    
                        ->where('horario_medicos.horario','!=' ,'11:15')                    
                        ->where('horario_medicos.horario','!=' ,'12:00')                    
                        ->where('horario_medicos.horario','!=' ,'12:45')                    
                        ->where('horario_medicos.horario','!=' ,'13:30')                    
                        ->where('horario_medicos.horario','!=' ,'13:15')                    
                        ->where('horario_medicos.horario','!=' ,'16:00')                    
                        ->where('horario_medicos.horario','!=' ,'16:45')                    
                        ->where('horario_medicos.horario','!=' ,'17:30')                    
                        ->where('horario_medicos.horario','!=' ,'18:15')                    
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get(); 
            }                          
            if($diaSeleccionado == 3) {
                // Miercoles  8:15 -  9: 00 - 9:45 - 10:30 - 11:15 - 12  - 12:45   ==== 16 - 16:45 - 17:30 - 18:15 - 19 hs              
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)                                                
                        ->where('horario_medicos.horario','!=' ,'08:15')                                            
                        ->where('horario_medicos.horario','!=' ,'16:00')                    
                        ->where('horario_medicos.horario','!=' ,'16:45')                    
                        ->where('horario_medicos.horario','!=' ,'17:30')                    
                        ->where('horario_medicos.horario','!=' ,'18:15')                    
                        ->where('horario_medicos.horario','!=' ,'19:00')                    
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get(); 
            }              
            if($diaSeleccionado == 4) {
                // Jueves 8:15 - 9- 9:45 - 10:30 - 11:15 - 12 y 12:45
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)                                                
                        ->where('horario_medicos.horario','!=' ,'08:15')                    
                        ->where('horario_medicos.horario','!=' ,'09:00')                    
                        ->where('horario_medicos.horario','!=' ,'09:45')                    
                        ->where('horario_medicos.horario','!=' ,'10:30')                    
                        ->where('horario_medicos.horario','!=' ,'11:15')                    
                        ->where('horario_medicos.horario','!=' ,'12:00')                    
                        ->where('horario_medicos.horario','!=' ,'12:45')                                                                                                               
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get(); 
            }  
        }        

        if($turnos != null)
            return $turnos;
        else {
            $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.activo', 1)                        
                        ->orderby('horario_medicos.horario')
                        ->get();
            return $turnos;
        } 
    }

    function checkFechaAgregada($medico_id, $consultorio_id, $fecha) {
        $fechaId = -1;
        $fecha = DB::table('fechas_agregadas')
                        ->where('fechas_agregadas.medico',$medico_id)
                        ->where('fechas_agregadas.consultorio', $consultorio_id)
                        ->where('fechas_agregadas.fecha', $fecha)
                        ->where('fechas_agregadas.activo', 1)                        
                        ->first();

        if($fecha != null)
            $fechaId = $fecha->id;
        return $fechaId;
    }

    /**
     * Filtra los horarios semanales por vigencia (valido_desde/valido_hasta).
     * Si la tabla no tiene las columnas, no filtra.
     */
    protected function filtrarVigenciaTurnos($turnos, $fechaSolicitada)
    {
        if ($turnos === null) {
            return $turnos;
        }
        if (!Schema::hasColumn('horario_medicos', 'valido_desde')) {
            return $turnos;
        }

        $fechaNorm = str_replace('/', '-', (string) $fechaSolicitada);
        $turnosCol = ($turnos instanceof \Illuminate\Support\Collection) ? $turnos : collect($turnos);

        return $turnosCol->filter(function ($t) use ($fechaNorm) {
            $desde = $t->valido_desde ?? null;
            $hasta = $t->valido_hasta ?? null;

            if (!empty($desde) && $desde > $fechaNorm) {
                return false;
            }
            if (!empty($hasta) && $hasta < $fechaNorm) {
                return false;
            }
            return true;
        })->values();
    }

    public function createJson($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada, $tipoTurno) {        
        if($tipoTurno == 4){
            $turnos = DB::table('horario_medico_videollamadas')
                        ->where('horario_medico_videollamadas.medico',$medico_id)
                        ->where('horario_medico_videollamadas.consultorio', $consultorio_id)
                        ->where('horario_medico_videollamadas.dia', $diaSeleccionado)
                        ->where('horario_medico_videollamadas.activo', 1)                        
                        ->orderBy('horario_medico_videollamadas.horario')
                        ->get();
        } else {
            // Desactivado: antes había lógica especial por médico.
            // Ahora la disponibilidad se calcula en base a datos (horario_medicos + vigencia).
            if(false){
                if($medico_id == 1)
                    $turnos = $this->checkTurnoLibreEspecialFlor($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);
                if($medico_id == 2)
                    $turnos = $this->checkTurnoLibreEspecialLucasGili($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);                
                if($medico_id == 8)
                    $turnos = $this->checkTurnoLibreEspecialMarina($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);
                if($medico_id == 12)
                    $turnos = $this->checkTurnoLibreEspecialEricaPacheco($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);
                if($medico_id == 11)
                    $turnos = $this->checkTurnoLibreEspecialCeciCorti($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);
                if($medico_id == 13)
                    $turnos = $this->checkTurnoLibreEspecialLucasSosa($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada, $tipoTurno);
                if($medico_id == 14)
                    $turnos = $this->checkTurnoLibreEspecialPatriciaSosa($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);
                if($medico_id == 15)
                    $turnos = $this->checkTurnoLibreEspecialAmilcarSosa($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);
                if($medico_id == 18)
                    $turnos = $this->checkTurnoLibreEspecialCeleste($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);
                if($medico_id == 19)
                    $turnos = $this->checkTurnoLibreEspecialMagali($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);
                if($medico_id == 23)
                    $turnos = $this->checkTurnoLibreEspecialFonseca($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);
                if($medico_id == 24)
                    $turnos = $this->checkTurnoLibreEspecialPabloPrado($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);
                if($medico_id == 26)
                    $turnos = $this->checkTurnoLibreEspecialSole($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);
                if($medico_id == 29)
                    $turnos = $this->checkTurnoLibreEspecialEli($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);
                if($medico_id == 30)
                    $turnos = $this->checkTurnoLibreEspecialAnto($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);                
                if($medico_id == 31)
                    $turnos = $this->checkTurnoLibreEspecialNataliaFerrari($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);                
                if($medico_id == 38)
                    $turnos = $this->checkTurnoLibreEspecialMonica($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);                
            } else {
                $turnos = DB::table('horario_medicos')
                            ->where('horario_medicos.medico',$medico_id)
                            ->where('horario_medicos.consultorio', $consultorio_id)
                            ->where('horario_medicos.dia', $diaSeleccionado)
                            ->where('horario_medicos.tipo_turno', $tipoTurno)
                            ->where('horario_medicos.activo', 1)                        
                            ->orderBy('horario_medicos.horario')
                            ->get();
                }
        }

        // Vigencia del horario semanal (no aplica a videollamada).
        if ($tipoTurno != 4) {
            $turnos = $this->filtrarVigenciaTurnos($turnos, $fechaSolicitada);
        }

        $fechaId = $this->checkFechaAgregada($medico_id, $consultorio_id, $fechaSolicitada);
        if($fechaId != -1) {            
            $turnos = DB::table('horarios_medicos_agregados')
                        ->where('horarios_medicos_agregados.fecha_agregada_id',$fechaId)
                        ->where('horarios_medicos_agregados.medico',$medico_id)
                        ->where('horarios_medicos_agregados.consultorio', $consultorio_id)
                        ->where('horarios_medicos_agregados.dia', $diaSeleccionado)
                        ->where('horarios_medicos_agregados.activo', 1)                        
                        ->orderBy('horarios_medicos_agregados.horario')
                        ->get();        
        }

        if($medico_id == 13) {
            $turnosRegistrados = DB::table('turno_registrados')
                        ->where('turno_registrados.medico',$medico_id)
                        ->where('turno_registrados.consultorio', $consultorio_id)
                        ->where('turno_registrados.dia', $diaSeleccionado)
                        ->where('turno_registrados.fechaTurno', $fechaSolicitada)                        
                        ->whereIn('turno_registrados.activo', [1, 2])                        
                        ->get();            
        } else {
            $turnosRegistrados = DB::table('turno_registrados')
                        ->where('turno_registrados.medico',$medico_id)
                        ->where('turno_registrados.consultorio', $consultorio_id)
                        ->where('turno_registrados.dia', $diaSeleccionado)
                        ->where('turno_registrados.fechaTurno', $fechaSolicitada)
                        ->where('turno_registrados.tipo_turno', $tipoTurno)
                        ->whereIn('turno_registrados.activo', [1, 2])                        
                        ->get();        
        }

        $intentService = new \App\Services\MercadoPago\TurnoPagoIntentService();
        $horariosPagoPendiente = $intentService->getHorariosBloqueadosPorPagoPendiente(
            $medico_id,
            $consultorio_id,
            $diaSeleccionado,
            $fechaSolicitada,
            $tipoTurno
        );
        foreach ($horariosPagoPendiente as $horarioPendiente) {
            $yaListado = false;
            foreach ($turnosRegistrados as $tr) {
                if ($tr->horario === $horarioPendiente) {
                    $yaListado = true;
                    break;
                }
            }
            if (!$yaListado) {
                $turnosRegistrados->push((object) ['horario' => $horarioPendiente]);
            }
        }
        
        $json = array();
        $data = array();
        $contador = 0;
        $encontre = 0;
        foreach($turnos as $turno){                                    
            while (($encontre < 1) && ($contador<count($turnosRegistrados))){            
                if(strcmp ($turno->horario , $turnosRegistrados[$contador]->horario ) == 0){
                    $encontre=1;
                    $json['horario'] = $turno->horario;
                    $json['libre'] = 0;                    
                    $data[] = $json;
                                                                                                
                } else {                                                            
                    $encontre = 0;                    
                }
                $contador++;
            }
            if($encontre == 0){
                $json['horario'] = $turno->horario;
                $json['libre'] = 1;                    
                $data[] = $json;
            }            
            $encontre=0;
            $contador=0;
        }
        return json_encode($data);
        
    }

     public function createJsonTurnosDobles($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada, $tipoTurno){
        $turnos = DB::table('horario_medicos')                                                                                                        
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.tipo_turno', $tipoTurno)
                        ->where('horario_medicos.activo', 1)
                        ->orderBy('horario_medicos.horario', 'asc')                                        
                        ->get();

        // Vigencia del horario semanal (fijos por fecha).
        $turnos = $this->filtrarVigenciaTurnos($turnos, $fechaSolicitada);

        $fechaId = $this->checkFechaAgregada($medico_id, $consultorio_id, $fechaSolicitada);
        if($fechaId != -1) {            
            $turnos = DB::table('horarios_medicos_agregados')
                        ->where('horarios_medicos_agregados.fecha_agregada_id',$fechaId)
                        ->where('horarios_medicos_agregados.medico',$medico_id)
                        ->where('horarios_medicos_agregados.consultorio', $consultorio_id)
                        ->where('horarios_medicos_agregados.dia', $diaSeleccionado)
                        ->where('horarios_medicos_agregados.activo', 1)                        
                        ->orderBy('horarios_medicos_agregados.horario')
                        ->get();        
        }

        $turnosRegistrados = DB::table('turno_registrados')                                                                                                        
                        ->where('turno_registrados.medico',$medico_id)
                        ->where('turno_registrados.consultorio', $consultorio_id)
                        ->where('turno_registrados.dia', $diaSeleccionado)
                        ->where('turno_registrados.fechaTurno', $fechaSolicitada)
                        ->where('turno_registrados.tipo_turno', $tipoTurno)
                        ->whereIn('turno_registrados.activo', [1, 2])
                        ->orderBy('turno_registrados.horario', 'asc')                                                                    
                        ->get();

        $intentService = new \App\Services\MercadoPago\TurnoPagoIntentService();
        $horariosPagoPendiente = $intentService->getHorariosBloqueadosPorPagoPendiente(
            $medico_id,
            $consultorio_id,
            $diaSeleccionado,
            $fechaSolicitada,
            $tipoTurno
        );
        foreach ($horariosPagoPendiente as $horarioPendiente) {
            $yaListado = false;
            foreach ($turnosRegistrados as $tr) {
                if ($tr->horario === $horarioPendiente) {
                    $yaListado = true;
                    break;
                }
            }
            if (!$yaListado) {
                $turnosRegistrados->push((object) ['horario' => $horarioPendiente]);
            }
        }

        $json = array();
        $data = [];
        $i=0;
        while($i<count($turnos)){
            $libre=0;
            //echo $i.' horario '.$turnos[$i]->horario.'<br>';
            $turnoActualLibre = $this->estaLibre($turnos[$i],$turnosRegistrados);
            //echo $turnos[$i]->horario.' - '.$turnoActualLibre.'<br>';            
                  
            if(($turnos[$i]->doble==0) && ($turnoActualLibre == 0)){
                $j=$i+1;
                if($j<count($turnos)){
                    $turnosContiguos = 1;//$this->esTurnoContiguo($turnos[$i]->horario,$turnos[$j]->horario);
                    if($turnosContiguos==1){
                        $turnoSiguienteLibre = $this->estaLibre($turnos[$j],$turnosRegistrados);
                  //      echo $turnoSiguienteLibre.'<br>';                    
                        if($turnoSiguienteLibre == 0){
                            $json['horario'] = $turnos[$i]->horario;
                            $json['horario2'] = $turnos[$j]->horario;
                            $json['libre'] = 1;  //1 quiere decir que esta libre                    
                            $data[] = $json;                            
                            $libre=1;
                        }
                    }
                }
                }
                if($libre==0){
                    $json['horario'] = $turnos[$i]->horario;
                    $json['horario2'] = $turnos[$i]->horario;
                    $json['libre'] = 0;  //1 quiere decir que esta libre                    
                    $data[] = $json;    
                }
                $i++;
        }            

        return json_encode($data);
    }


    function estaLibre($turno, $turnosRegistrados){
        $encontre = 0;
        $contador = 0;
        //if($turno->doble==1){$encontre=1;}
        while (($encontre < 1) && ($contador<count($turnosRegistrados))){            
            if(strcmp ($turno->horario , $turnosRegistrados[$contador]->horario ) == 0){
                $encontre = 1;
            }
            $contador++;
        }
        return $encontre;
    }

    function actualizarTipoTurno(Request $request) {
        $turno_id = $request->turno_id;

        $turno = TurnoRegistrado::find($turno_id);
        $turno->tipo_turno = $request->tipoTurno;
        $turno->save();
         
         return response()->json(array('turno'=>$turno));
    }

    // no se esta usando.
    public function turnoRegistrado2(Request $request){
                     
        $turno = turnoRegistrado::find($request->turno_id);  
        
        $horario= $turno->horario;
        $fechaSolicitada = $turno->fechaTurno;        
          
          return view('turnos.turno_registrado')
            ->with('fechaSolicitada',$fechaSolicitada)
            ->with('horario',$horario);            
    }


    public function turnoRegistrado($t){
                     
        $turno = turnoRegistrado::find($t);  
        
        $horario= $turno->horario;
        $fechaSolicitada = $turno->fechaTurno;

          return view('turnos.turno_registrado')
            ->with('fechaSolicitada',$fechaSolicitada)
            ->with('horario',$horario);            
    } 

    public function validarTurnoLibre($medico_id, $consultorio_id, $diaSeleccionado, $horario,$fechaSolicitada){
         $checkTurno = DB::table('turno_registrados')                                                                     
                        ->where('turno_registrados.medico',$medico_id)
                        ->where('turno_registrados.consultorio', $consultorio_id)
                        ->where('turno_registrados.dia', $diaSeleccionado)
                        ->where('turno_registrados.horario', $horario)
                        ->where('turno_registrados.fechaTurno', $fechaSolicitada)
                        ->whereIn('turno_registrados.activo', [1, 2])                                                                    
                        ->get();

        if ($checkTurno->count() > 0) {
            return $checkTurno;
        }

        $intentService = new \App\Services\MercadoPago\TurnoPagoIntentService();
        if ($intentService->slotTienePagoPendiente($medico_id, $consultorio_id, $diaSeleccionado, $horario, $fechaSolicitada)) {
            return collect([(object) ['id' => 0, 'pending_mp' => true]]);
        }

        return $checkTurno;
    }

    public function validarTurnoLibreVideollamada($medico_id, $consultorio_id, $diaSeleccionado, $horario,$fechaSolicitada){
         $checkTurno = DB::table('turno_registrado_videollamadas')
                        ->where('turno_registrado_videollamadas.medico',$medico_id)
                        ->where('turno_registrado_videollamadas.consultorio', $consultorio_id)
                        ->where('turno_registrado_videollamadas.dia', $diaSeleccionado)
                        ->where('turno_registrado_videollamadas.horario', $horario)
                        ->where('turno_registrado_videollamadas.fechaTurno', $fechaSolicitada)
                        ->where('turno_registrado_videollamadas.activo', 1)
                        ->get();    
        return $checkTurno;
    } 

    public function registrarTurnoPrimerControl(Request $request){
        $paciente_id = $request->paciente_id;
        $medico_id = $request->get('medico_id');
        $consultorio_id = $request->get('consultorio');
        //$dia = $request->get('dia');
        $horario1 = $request->get('horario1');
        $horario2= $request->get('horario2');
        $fechaTurno_aux = explode('/',$request->get('fechaTurno'));        
        $fechaTurno = $fechaTurno_aux[2].'-'.$fechaTurno_aux[1].'-'.$fechaTurno_aux[0];              
        $tipoTurno = $request->tipoTurno;
        $dia = $this->getDiaSeleccionado2($fechaTurno);
        $espFlujoRaw = $request->input('especialidad_nombre_flujo');
        $especialidadNombreGuardar = ($espFlujoRaw !== null && trim((string) $espFlujoRaw) !== '') ? trim((string) $espFlujoRaw) : null;

        if($request->get('primerControl')==0)
            $primerControl = 'NO';
        else   
            $primerControl = 'SI';

        if ($tipoTurno != 4) {
            $intentService = new \App\Services\MercadoPago\TurnoPagoIntentService();
            $intentService->expireStaleIntents();
            if ($intentService->medicoRequiresPayment($medico_id, 0, (int) $paciente_id, $fechaTurno)) {
                return response()->json(array(
                    'turnoRegistrado' => '6',
                    'message' => 'Debe completar el pago online para reservar este turno.',
                ));
            }
        }

        $moduloPrimerControlDobleMed = $this->moduloActivo($medico_id, 3);
        $horarioParMostrarDos = ($moduloPrimerControlDobleMed === 1) ? $horario2 : null;
        if (!$this->horarioEsVisibleMostrarDosPaciente(
            $medico_id,
            $consultorio_id,
            $dia,
            $fechaTurno,
            $tipoTurno,
            (int) $request->get('primerControl'),
            $horario1,
            $horarioParMostrarDos,
            0
        )) {
            return response()->json(array('turnoRegistrado'=>'0'));
        }

        // confirmo que no haya una persona en ese mismo horario.
        $checkTurno1 = $this->validarTurnoLibre($medico_id, $consultorio_id, $dia, $horario1, $fechaTurno);
        $checkTurno2 = $this->validarTurnoLibre($medico_id, $consultorio_id, $dia, $horario2, $fechaTurno);
        // verifico que esa persona no tenga un turno para ese dia.
        $validarMismoDia = $this-> validarTurnoMismoDia($paciente_id , $medico_id, $consultorio_id, $fechaTurno); 

        // si es true quiere decir que el paciente ya tiene un turno para ese dia.
        if($validarMismoDia){
             return response()->json(array('turnoRegistrado'=>'2'));
        } else {        
            if(($checkTurno1->count()>0) || ($checkTurno2->count()>0)){
                 return response()->json(array('turnoRegistrado'=>'0'));            
            } else {            
                $turnoRegistrado = $this->registrarNuevoTurno($paciente_id, $medico_id, $consultorio_id, $dia, $horario1, $fechaTurno, 'SI', 0, $tipoTurno, $especialidadNombreGuardar);

                $moduloPrimerControlDoble = $this->moduloActivo($medico_id, 3);
                if($moduloPrimerControlDoble == 1) {
                    $turnoRegistrado2 = $this->registrarNuevoTurno($paciente_id, $medico_id, $consultorio_id, $dia, $horario2, $fechaTurno, 'SI', 0, $tipoTurno, $especialidadNombreGuardar);    
                }
                                        
                //return response()->json(array('turnoRegistrado'=>'1','horario'=>$horario1,'turno_id'=>$turnoRegistrado->id));
                $medico = Medico::find($medico_id);
                $paciente = Paciente::find($paciente_id);
                $consultorio = Consultorio::find($consultorio_id);
                
                // Intentar agregar evento automáticamente a Google Calendar si tiene OAuth
                $calendarEventAdded = false;
                $googleCalendarEventId = null;
                if ($paciente && $paciente->google_calendar_access_token) {
                    $googleCalendarEventId = $this->addEventToGoogleCalendar($paciente, $turnoRegistrado, $medico, $consultorio);
                    $calendarEventAdded = ($googleCalendarEventId !== null && $googleCalendarEventId !== false && $googleCalendarEventId !== 'no event_id');
                } else {
                    // Si no tiene OAuth, marcar como 'no event_id'
                    $googleCalendarEventId = 'no event_id';
                }
                
                // Guardar el event_id en la base de datos (o "no event_id" si no se creó)
                if ($googleCalendarEventId !== null && $googleCalendarEventId !== false && $googleCalendarEventId !== 'no event_id') {
                    $turnoRegistrado->google_calendar_event_id = $googleCalendarEventId;
                } else {
                    $turnoRegistrado->google_calendar_event_id = 'no event_id';
                }
                $turnoRegistrado->save();
                
                // Generar URLs y contenido para calendario (SIEMPRE como fallback)
                // Esto permite abrir el calendario directamente mientras Google verifica OAuth
                // Incluso si OAuth está configurado, generamos las URLs por si falla
                $googleCalendarUrls = null;
                $icsContent = null;
                
                // Solo generar URLs si OAuth no funcionó (o no está disponible)
                // Mientras Google verifica OAuth, siempre generamos las URLs para abrir manualmente
                if (!$calendarEventAdded) {
                    try {
                        // Primero intentar con Google Calendar (funciona en todos los dispositivos)
                        $googleCalendarUrls = \App\Helpers\CalendarHelper::generateTurnoCalendarLink(
                            $turnoRegistrado, 
                            $medico, 
                            $consultorio
                        );
                    } catch (\Exception $e) {
                        \Log::error('Error generando URL de Google Calendar: ' . $e->getMessage());
                        $googleCalendarUrls = null;
                    }
                    
                    // También generar .ics como fallback
                    try {
                        $icsContent = \App\Helpers\CalendarHelper::generateTurnoICS($turnoRegistrado, $medico, $consultorio, $paciente);
                    } catch (\Exception $e) {
                        \Log::error('Error generando contenido .ics: ' . $e->getMessage());
                        $icsContent = null;
                    }
                }
                
                return response()->json(array(
                    'consultorio'=>$consultorio,
                    'paciente'=>$paciente,
                    'medico'=>$medico,
                    'datosTurno'=>$turnoRegistrado,
                    'turnoRegistrado'=>'1',
                    'horario'=>$horario1,
                    'turno_id'=>$turnoRegistrado->id, 
                    'esVideollamada'=>0,
                    'calendar_event_added' => $calendarEventAdded,
                    'google_calendar_reminder_url' => ($googleCalendarUrls && isset($googleCalendarUrls['reminder'])) ? $googleCalendarUrls['reminder'] : null, // URL del recordatorio (día anterior)
                    'google_calendar_turno_url' => ($googleCalendarUrls && isset($googleCalendarUrls['turno'])) ? $googleCalendarUrls['turno'] : null, // URL del turno
                    'ics_content' => $icsContent // Contenido del .ics como fallback
                ));
            }                    
        }        
    }

    // Verifico que una misma persona no pueda sacar otro turno el mismo dia.
    // return true si ya tiene un turno ese dia.
    public function validarTurnoMismoDia($paciente_id , $medico_id, $consultorio_id, $fechaTurno){
        if($paciente_id == 14043) // paciente prenatal, no debo validarlo
            return false;
         $checkTurno = DB::table('turno_registrados')                                          
                ->where('turno_registrados.medico',$medico_id)
                ->where('turno_registrados.consultorio', $consultorio_id)
                ->where('turno_registrados.paciente', $paciente_id)
                ->where('turno_registrados.fechaTurno', $fechaTurno)
                ->where('turno_registrados.activo', 1)
                ->get();
         if($checkTurno->count()>0)
             return true;
         else 
            return false;    
    }

    public function registrarNuevoTurno($paciente_id, $medico_id, $consultorio, $diaSeleccionado, $horario, $nuevaFecha, $primerControl, $sobreturno, $tipoTurno, $especialidadNombre = null){        
        $turnoRegistrado = new turnoRegistrado; 
        $turnoRegistrado->paciente = $paciente_id;
        $turnoRegistrado->medico = $medico_id;
        $turnoRegistrado->consultorio = $consultorio;
        $turnoRegistrado->dia = $diaSeleccionado;
        $turnoRegistrado->horario = $horario;       
        $turnoRegistrado->fechaTurno = $nuevaFecha;
        $turnoRegistrado->asistio = 0;
        $turnoRegistrado->sobreturno = $sobreturno;
        $turnoRegistrado->primerControl = $primerControl;
        $turnoRegistrado->caja = 0;
        $turnoRegistrado->comentario = '';
        $turnoRegistrado->tipo_turno = $tipoTurno;
        $turnoRegistrado->especialidad = $especialidadNombre;
        $turnoRegistrado->cancelado_por = '';
        $turnoRegistrado->otorgado_por = 'Paciente';
        $turnoRegistrado->msj_enviado = 0;
        $turnoRegistrado->activo = 1;
        $turnoRegistrado->google_calendar_event_id = '';
        $turnoRegistrado->save();

        $this->vincularMedicoPaciente($medico_id, $paciente_id);
        $this->vincularSecretariaPaciente($paciente_id, $consultorio);

        return $turnoRegistrado;
    }

    function sendNotifications() {
        $result = app('onesignal')->sendToUser(
            '2d03d89e-ffd6-4321-9636-433e8a7bfc27',
            'Tienes un nuevo mensaje',
            null//route('homes')
        );

        print_r($result);    
        
    }

    /**
     * Método de prueba para enviar notificación FCM - COMENTADO
     * Esta funcionalidad fue removida porque no funciona correctamente en iOS.
     * Se implementará una solución alternativa en el futuro.
     */
    /*
    function testFcmNotification($paciente_id) {
        $paciente = Paciente::find($paciente_id);
        
        if (!$paciente) {
            return response()->json(['error' => 'Paciente no encontrado'], 404);
        }
        
        if (!$paciente->fcm_token) {
            return response()->json(['error' => 'El paciente no tiene token FCM registrado'], 400);
        }
        
        $firebase = app('firebase');
        
        // Enviar notificación de prueba
        $result = $firebase->sendToToken(
            $paciente->fcm_token,
            'Notificación de Prueba',
            'Esta es una notificación de prueba de Firebase Cloud Messaging',
            ['test' => 'true']
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Notificación enviada',
            'result' => $result
        ]);
    }
    */

    public function vincularSecretariaPaciente($paciente_id, $consultorio_id){
        $check = DB::table('paciente_secretarias')
                    ->where('paciente_secretarias.paciente',$paciente_id)
                    ->where('paciente_secretarias.consultorio',$consultorio_id)
                    ->where('paciente_secretarias.activo', 1)
                    ->first();
        if($check == null){
            $paciente_sec = new pacienteSecretaria;
            $paciente_sec->paciente = $paciente_id;
            $paciente_sec->consultorio = $consultorio_id;
            $paciente_sec->activo = 1;
            $paciente_sec->save();
        }
    }

    function vincularMedicoPaciente($medico_id, $paciente_id){
        $check = DB::table('medico_pacientes')                                                                     
                ->where('medico_pacientes.medico',$medico_id)
                ->where('medico_pacientes.paciente', $paciente_id)                
                ->first();
        if($check == null){
            $medicoPaciente = new MedicoPaciente;
            $medicoPaciente->medico = $medico_id;
            $medicoPaciente->paciente = $paciente_id;
            $medicoPaciente->bloqueado = 0;
            $medicoPaciente->save();
        }
    }

    public function registrarTurno(Request $request)
    {        
        $paciente_id = $request->paciente_id;
        $medico_id = $request->get('medico_id');
        $consultorio_id = $request->get('consultorio');
        //$dia = $request->get('dia');

        $horario = $request->get('horario');
        $fechaTurno_aux = explode('/',$request->get('fechaTurno'));        
        $fechaTurno = $fechaTurno_aux[2].'-'.$fechaTurno_aux[1].'-'.$fechaTurno_aux[0];              
        $esVideollamada = $request->esVideollamada;
        $tipoTurno = $request->tipoTurno;
        $dia = $this->getDiaSeleccionado2($fechaTurno);
        $espFlujoRawPc = $request->input('especialidad_nombre_flujo');
        $especialidadNombreGuardar = ($espFlujoRawPc !== null && trim((string) $espFlujoRawPc) !== '') ? trim((string) $espFlujoRawPc) : null;

        if($request->get('primerControl') == 0)
            $primerControl = 'NO';
        else   
            $primerControl = 'SI';
        if($esVideollamada == 0 && $tipoTurno != 4) {
            $intentService = new \App\Services\MercadoPago\TurnoPagoIntentService();
            $intentService->expireStaleIntents();
            if ($intentService->medicoRequiresPayment($medico_id, 0, (int) $paciente_id, $fechaTurno)) {
                return response()->json(array(
                    'turnoRegistrado' => '6',
                    'message' => 'Debe completar el pago online para reservar este turno.',
                ));
            }

            if (!$this->horarioEsVisibleMostrarDosPaciente(
                $medico_id,
                $consultorio_id,
                $dia,
                $fechaTurno,
                $tipoTurno,
                (int) $request->get('primerControl'),
                $horario,
                null,
                0
            )) {
                return response()->json(array('turnoRegistrado'=>'0'));
            }
            // confirmo que no haya una persona en ese mismo horario
            $checkTurno = $this->validarTurnoLibre($medico_id, $consultorio_id, $dia, $horario, $fechaTurno);
            // confirmo que la misma persona no tenga 2 turno con el mismo medico el mismo dia.
            $checkTurno2 = DB::select('select * from turno_registrados where paciente = ? and medico = ? and consultorio = ? and dia = ? and fechaTurno = ? and activo=1',
                [$paciente_id , $medico_id, $consultorio_id, $dia, $fechaTurno]);
            // confirmo que no tenga turno con un PEG
            /*if($tipoTurno != 0){
                $checkTurnoPeg = DB::select('select * from turno_registrado_videollamadas where paciente = ? and medico = ? and consultorio = ? and horario = ? and fechaTurno = ? and activo=1',
                [$paciente_id , $medico_id, $consultorio_id, $horario, $fechaTurno]);
                if($checkTurnoPeg)
                    return response()->json(array('turnoRegistrado'=>'4'));                
            } */

            if($consultorio_id == 6) { // patricia sosa
                $vts = $this->validarTurnosPosteriores($paciente_id, $fechaTurno, $medico_id, $consultorio_id);
                if($vts > 0)
                    return response()->json(array('turnoRegistrado'=>'5'));                
            } 

            if($checkTurno2 && $tipoTurno == 1){        
                // confirmo que la misma persona no tenga 2 turno con el mismo medico el mismo dia.
                return response()->json(array('turnoRegistrado'=>'2'));                
            } else {
                if($checkTurno->count()>0){
                    // El turno fue reservado por otra persona.
                     return response()->json(array('turnoRegistrado'=>'0'));            
                } else {            
                    $registrarTurno = new turnoRegistrado;
                    $registrarTurno->paciente = $paciente_id;
                    $registrarTurno->medico = $medico_id;        
                    $registrarTurno->consultorio = $consultorio_id;        
                    $registrarTurno->dia = $dia;
                    $registrarTurno->horario = $horario;       
                    $registrarTurno->fechaTurno = $fechaTurno;    
                    $registrarTurno->asistio = 0;
                    $registrarTurno->sobreturno = 0;
                    $registrarTurno->primerControl = $primerControl;
                    $registrarTurno->caja = 0;
                    $registrarTurno->comentario = '';
                    $registrarTurno->tipo_turno = $tipoTurno;
                    $registrarTurno->especialidad = $especialidadNombreGuardar;
                    $registrarTurno->cancelado_por = '';
                    $registrarTurno->otorgado_por = 'Paciente';
                    $registrarTurno->msj_enviado = 0;
                    $registrarTurno->activo = 1;
                    $registrarTurno->google_calendar_event_id = '';
                    $registrarTurno->save();
                    $this->vincularMedicoPaciente($medico_id, $paciente_id);
                    $this->vincularSecretariaPaciente($paciente_id, $consultorio_id);
                    
                    $medico = Medico::find($medico_id);
                    $paciente = Paciente::find($paciente_id);
                    $consultorio = Consultorio::find($consultorio_id);
                    
                    // Intentar agregar evento automáticamente a Google Calendar si tiene OAuth
                    $calendarEventAdded = false;
                    $googleCalendarEventId = null;
                    if ($paciente && $paciente->google_calendar_access_token) {
                        $googleCalendarEventId = $this->addEventToGoogleCalendar($paciente, $registrarTurno, $medico, $consultorio);
                        $calendarEventAdded = ($googleCalendarEventId !== null && $googleCalendarEventId !== false && $googleCalendarEventId !== 'no event_id');
                    } else {
                        // Si no tiene OAuth, marcar como 'no event_id'
                        $googleCalendarEventId = 'no event_id';
                    }
                    
                    // Guardar el event_id en la base de datos (o "no event_id" si no se creó)
                    if ($googleCalendarEventId !== null && $googleCalendarEventId !== false && $googleCalendarEventId !== 'no event_id') {
                        $registrarTurno->google_calendar_event_id = $googleCalendarEventId;
                    } else {
                        $registrarTurno->google_calendar_event_id = 'no event_id';
                    }
                    $registrarTurno->save();
                    
                    // Generar URLs y contenido para calendario (siempre como fallback, incluso si OAuth falló)
                    // Esto permite abrir el calendario directamente mientras Google verifica OAuth
                    $googleCalendarUrls = null;
                    $icsContent = null;
                    if (!$calendarEventAdded) {
                        try {
                            // Primero intentar con Google Calendar (funciona en todos los dispositivos)
                            $googleCalendarUrls = \App\Helpers\CalendarHelper::generateTurnoCalendarLink(
                                $registrarTurno, 
                                $medico, 
                                $consultorio
                            );
                        } catch (\Exception $e) {
                            \Log::error('Error generando URL de Google Calendar: ' . $e->getMessage());
                            $googleCalendarUrls = null;
                        }
                        
                        // También generar .ics como fallback
                        try {
                            $icsContent = \App\Helpers\CalendarHelper::generateTurnoICS($registrarTurno, $medico, $consultorio, $paciente);
                        } catch (\Exception $e) {
                            \Log::error('Error generando contenido .ics: ' . $e->getMessage());
                            $icsContent = null;
                        }
                    }
                    
                    return response()->json(array(
                        'consultorio'=>$consultorio,
                        'paciente'=>$paciente,
                        'medico'=>$medico,
                        'datosTurno'=>$registrarTurno,
                        'turnoRegistrado'=>'1',
                        'horario'=>$horario,
                        'turno_id'=>$registrarTurno->id,
                        'esVideollamada'=>$esVideollamada,
                        'calendar_event_added' => $calendarEventAdded,
                        'google_calendar_reminder_url' => ($googleCalendarUrls && isset($googleCalendarUrls['reminder']) && !empty($googleCalendarUrls['reminder'])) ? $googleCalendarUrls['reminder'] : null, // URL del recordatorio (día anterior)
                        'google_calendar_turno_url' => ($googleCalendarUrls && isset($googleCalendarUrls['turno'])) ? $googleCalendarUrls['turno'] : null, // URL del turno
                        'ics_content' => $icsContent // Contenido del .ics como fallback
                    ));
                }       
            }   
        } else {
            if (!$this->horarioEsVisibleMostrarDosPaciente(
                $medico_id,
                $consultorio_id,
                $dia,
                $fechaTurno,
                $tipoTurno,
                (int) $request->get('primerControl'),
                $horario,
                null,
                1
            )) {
                return response()->json(array('turnoRegistrado'=>'0'));
            }
            // confirmo que no haya una persona en ese mismo horario
            $checkTurno = $this->validarTurnoLibreVideollamada($medico_id, $consultorio_id, $dia, $horario, $fechaTurno);
            // confirmo que la misma persona no tenga 2 turno con el mismo medico el mismo dia.
            $checkTurno2 = DB::select('select * from turno_registrado_videollamadas where paciente = ? and medico = ? and consultorio = ? and dia = ? and fechaTurno = ? and activo=1',
                [$paciente_id , $medico_id, $consultorio_id, $dia, $fechaTurno]);
            // si es un peg, debo validar que no tenga turno con otro estudio
            if($tipoTurno != 0){
                $checkTurnoOtroEstudio = DB::select('select * from turno_registrados where paciente = ? and medico = ? and consultorio = ? and horario = ? and fechaTurno = ? and activo=1', [$paciente_id , $medico_id, $consultorio_id, $horario, $fechaTurno]);
                if($checkTurnoOtroEstudio){
                    return response()->json(array('turnoRegistrado'=>'3'));                    
                }
            }
            //return response()->json(array('turnoRegistrado'=>'3', 'test'=>$checkTurnoOtroEstudio));

            if($checkTurno2){         
                // confirmo que la misma persona no tenga 2 turno con el mismo medico el mismo dia.
                return response()->json(array('turnoRegistrado'=>'2'));                
            }else{
                if($checkTurno->count()>0){
                    // El turno fue reservado por otra persona.
                     return response()->json(array('turnoRegistrado'=>'0'));            
                } else {            
                    $registrarTurno = new turnoRegistradoVideollamada;
                    $registrarTurno->paciente = $paciente_id;
                    $registrarTurno->medico = $medico_id;        
                    $registrarTurno->consultorio = $consultorio_id;        
                    $registrarTurno->dia = $dia;
                    $registrarTurno->horario = $horario;       
                    $registrarTurno->fechaTurno = $fechaTurno;    
                    $registrarTurno->asistio = 0;
                    $registrarTurno->sobreturno = 0;
                    $registrarTurno->primerControl = $primerControl;                    
                    $registrarTurno->comentario = '';
                    $registrarTurno->disponible = 0;
                    $registrarTurno->disponible_medico = 0;
                    $registrarTurno->pago = 0;
                    $registrarTurno->pago_ticket = '';
                    $registrarTurno->cargado = 0;       
                    $registrarTurno->activo = 1;       
                    $registrarTurno->save();
                        
                    return response()->json(array('turnoRegistrado'=>'1','horario'=>$horario,'turno_id'=>$registrarTurno->id, 'esVideollamada'=>$esVideollamada));
                }       
            }
        }
    }
    // validarTurnosPosteriores($paciente_id, $fechaTurno, $medico_id, $consultorio_id)
    function validarTurnosPosteriores($paciente_id, $fechaTurno, $medico_id, $consultorio_id) {
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $hoy = date('Y-m-d');
    
        $cantidad = DB::table('turno_registrados')
            ->where('turno_registrados.paciente', $paciente_id)
            ->where('turno_registrados.medico', $medico_id)
            ->where('turno_registrados.consultorio', $consultorio_id)
            ->where('turno_registrados.fechaTurno', '>=', $hoy)
            ->where('turno_registrados.activo', 1)
            ->count();
    
        return $cantidad;
    }

    function validarTurnoUnaSemana($paciente_id, $fechaTurno, $medico_id, $consultorio_id) {
        $dia = $this->getDiaSeleccionado2($fechaTurno);
        if($dia == 1) {
            $fechaDesde = $fechaTurno;
            $fechaHasta = date('Y-m-d', strtotime('+5 day' , strtotime ( $fechaTurno )));
        }
        if($dia == 2) {
            $fechaDesde = date('Y-m-d', strtotime('-1 day' , strtotime ( $fechaTurno )));
            $fechaHasta = date('Y-m-d', strtotime('+4 day' , strtotime ( $fechaTurno )));
        }
        if($dia == 3) {
            $fechaDesde = date('Y-m-d', strtotime('-2 day' , strtotime ( $fechaTurno )));
            $fechaHasta = date('Y-m-d', strtotime('+3 day' , strtotime ( $fechaTurno )));
        }
        if($dia == 4) {
            $fechaDesde = date('Y-m-d', strtotime('-3 day' , strtotime ( $fechaTurno )));
            $fechaHasta = date('Y-m-d', strtotime('+2 day' , strtotime ( $fechaTurno )));
        }
        if($dia == 5) {
            $fechaDesde = date('Y-m-d', strtotime('-4 day' , strtotime ( $fechaTurno )));
            $fechaHasta = date('Y-m-d', strtotime('+1 day' , strtotime ( $fechaTurno )));
        }        
        
        $cantidadTurnosAux = DB::table('turno_registrados')                    
                            ->where('turno_registrados.paciente', $paciente_id)
                            ->where('turno_registrados.medico', $medico_id)
                            ->where('turno_registrados.consultorio', $consultorio_id)
                            ->where('turno_registrados.fechaTurno', '>=', $fechaDesde)
                            ->where('turno_registrados.fechaTurno', '<=', $fechaHasta)
                            ->where('turno_registrados.activo', 1)                    
                            ->get()
                            ->count();
        return $cantidadTurnosAux;
    }

    // me devuelve la cantidad de turnos que tiene el paciente en el mes correspondiente a fechaTurno.
    public function cantidadTurnosPacienteMes($medico_id, $consultorio_id, $paciente_id, $fechaTurno){
        $mesAux = explode('-',$fechaTurno);        
        $mes = $mesAux[1];
        $anio = $mesAux[0];                          
        $cantidadTurnosAux = DB::table('turno_registrados')                    
                            ->where('turno_registrados.paciente', $paciente_id)
                            ->where('turno_registrados.medico', $medico_id)
                            ->where('turno_registrados.consultorio', $consultorio_id)
                            ->whereMonth('turno_registrados.fechaTurno', $mes)
                            ->whereYear('turno_registrados.fechaTurno', $anio)
                            ->where('turno_registrados.activo', 1)                    
                            ->get()
                            ->count();
        return $cantidadTurnosAux;
    }

    public function confirmarTurno(Request $request){
        $turno_id = $request->get('turno_id');        
        //$horario= $request->horario;
        $turnoRegistrado = turnoRegistrado::find($turno_id);
        $coment = $turnoRegistrado->comentario;
        $turnoRegistrado->comentario = $coment.' - Confirmado por link whatsapp';
        $turnoRegistrado->save();
        
        $medico = Medico::find($turnoRegistrado->medico);

        $fecha = $this->convertirFechaMostrar($turnoRegistrado->fechaTurno);
        return view('turnos.confirmar_turno')->with('fecha',$fecha)->with('medico',$medico)->with('turnoRegistrado',$turnoRegistrado);
        
        //return response()->json(array('horario'=>$horario));
    }

    /**
     * Confirmación por enlace GET (misma lógica que POST confirmar_turno), análoga a cancelaturno/{id}.
     */
    public function confirmaTurno($turno_id)
    {
        return $this->confirmarTurno(new Request([], ['turno_id' => $turno_id]));
    }

    public function cancelarTurno(Request $request) {
        $turno_id = $request->get('turno_id');        
        $esVideollamada = $request->get('esVideollamada');  
        if($esVideollamada == 0) {
            $turno = turnoRegistrado::find($turno_id);
            // Eliminar evento de Google Calendar si existe
            if ($turno) {
                $this->deleteEventFromGoogleCalendar($turno);
            }
        } else {
            $turno = turnoRegistradoVideollamada::find($turno_id);
        }
        $turno->activo = 0;
        $turno->save();
        return response()->json(array('turno'=>$turno));
    }
   
    public function cancelarTurnoDni(Request $request)
    {           
        $mensaje='';
        $paciente_get=DB::table('pacientes')                    
                    ->where('pacientes.dni',$request->get('dni_paciente'))
                    ->where('pacientes.activo', 1)                    
                    ->first();
        if(!$paciente_get){
            $mensaje = 'El DNI ingresado no se corresponde con un paciente.';
        } else {            
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $dia= date("Y-m-d");        
            $turnosAux = DB::table('turno_registrados')
                        ->join('medicos','medicos.id','=','turno_registrados.medico')
                        ->join('pacientes','pacientes.id','=','turno_registrados.paciente')                    
                        ->select('turno_registrados.id as trid','pacientes.nombre as pnombre','pacientes.apellido as papellido','medicos.apellido','medicos.nombre','turno_registrados.fechaTurno','turno_registrados.horario', 'turno_registrados.primerControl')
                        ->where('pacientes.id',$paciente_get->id)
                        ->where('turno_registrados.activo',1)
                        ->where('turno_registrados.fechaTurno','>=',$dia)
                        ->orderBy('turno_registrados.fechaTurno','asc')
                        ->orderBy('turno_registrados.horario','asc')    
                        ->distinct()
                        ->get();

            $turnosAuxVideollamada = DB::table('turno_registrado_videollamadas')
                        ->join('medicos','medicos.id','=','turno_registrado_videollamadas.medico')
                        ->join('pacientes','pacientes.id','=','turno_registrado_videollamadas.paciente')                    
                        ->select('turno_registrado_videollamadas.id as trid','pacientes.nombre as pnombre','pacientes.apellido as papellido','medicos.apellido','medicos.nombre','turno_registrado_videollamadas.fechaTurno','turno_registrado_videollamadas.horario', 'turno_registrado_videollamadas.primerControl','turno_registrado_videollamadas.pago','medicos.especialidad')
                        ->where('pacientes.id',$paciente_get->id)
                        ->where('turno_registrado_videollamadas.activo',1)
                        ->where('medicos.especialidad', '!=', 2)
                        ->where('turno_registrado_videollamadas.fechaTurno','>=',$dia)
                        ->orderBy('turno_registrado_videollamadas.fechaTurno','asc')
                        ->orderBy('turno_registrado_videollamadas.horario','asc')    
                        ->distinct()
                        ->get();

            $turnosAuxPeg = DB::table('turno_registrado_videollamadas')
                        ->join('medicos','medicos.id','=','turno_registrado_videollamadas.medico')
                        ->join('pacientes','pacientes.id','=','turno_registrado_videollamadas.paciente')                    
                        ->select('turno_registrado_videollamadas.id as trid','pacientes.nombre as pnombre','pacientes.apellido as papellido','medicos.apellido','medicos.nombre','turno_registrado_videollamadas.fechaTurno','turno_registrado_videollamadas.horario', 'turno_registrado_videollamadas.primerControl','turno_registrado_videollamadas.pago','medicos.especialidad')
                        ->where('pacientes.id',$paciente_get->id)
                        ->where('turno_registrado_videollamadas.activo',1)
                        ->where('medicos.especialidad', 2)
                        ->where('turno_registrado_videollamadas.fechaTurno','>=',$dia)
                        ->orderBy('turno_registrado_videollamadas.fechaTurno','asc')
                        ->orderBy('turno_registrado_videollamadas.horario','asc')    
                        ->distinct()
                        ->get();

                $turnos = $this-> filtrarTurnos($turnosAux);
                $someArray = json_decode($turnos, true);                
    //            return $someArray;
                if((!$turnosAux)&&(!$turnosAuxVideollamada) && (!$turnosAuxPeg)){                
                    $mensaje =  'No hay turnos registrados para el DNI ingresado.';
                }
                $videollamada = $this->existeVideollamada(1);
                return view('turnos.cancelar_turno')
                ->with('turnosRegistrados',$someArray)
                ->with('turnosRegistradosVideollamadas',$turnosAuxVideollamada)
                ->with('turnosRegistradosPeg',$turnosAuxPeg)
                ->with('dni_paciente',$paciente_get->dni)
                ->with('mensaje',$mensaje)
                ->with('videollamada',$videollamada);
        }
        $videollamada = $this->existeVideollamada(1);
        return view('turnos.cancelar_turno')
        ->with('dni_paciente',$request->get('dni_paciente'))
        ->with('mensaje',$mensaje)
        ->with('videollamada',$videollamada);
    }

    // Esta funcion me filtra los turnos dobles, para mostrar solo el primero.
    public function filtrarTurnos($turnos){
        $data = array();
        $json = array();
        $i=0;
        $position = -1;
        while($i<count($turnos)){
            if((strcmp($turnos[$i]->primerControl, 'SI') == 0) && ($position == -1)){
                $position = $i+1;
            }
            if($i == $position){
                $position = -1;
            } else {
                $json = $turnos[$i];
                $data[] = $json;
            }
            $i++;
         }
         return json_encode($data);
    }

     public function cancelarTurnoPacienteVideollamada(Request $request){        
        $mensaje='';
        $paciente_get=DB::table('pacientes')                    
                    ->where('pacientes.dni',$request->get('dni_paciente'))                    
                    ->first();

        $turno_id = $request->get('turno_id');        
        $turno = turnoRegistradoVideollamada::find($turno_id);
        $turno->comentario = 'Cancelado por paciente';
        $turno->activo = 0;
        $turno->save();

        $dni_paciente=$request->get('dni_paciente');
        //id_tr nombre apellido medico fechaTurno horario
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $dia= date("Y-m-d");        
        $turnosAux = DB::table('turno_registrado_videollamadas')
                    ->join('medicos','medicos.id','=','turno_registrado_videollamadas.medico')
                    ->join('pacientes','pacientes.id','=','turno_registrado_videollamadas.paciente')                    
                    ->select('turno_registrado_videollamadas.id as trid','pacientes.nombre as pnombre','pacientes.apellido as papellido','medicos.apellido','medicos.nombre','turno_registrado_videollamadas.fechaTurno','turno_registrado_videollamadas.horario','turno_registrado_videollamadas.primerControl')
                    ->where('pacientes.id',$paciente_get->id)                    
                    ->where('turno_registrado_videollamadas.activo',1)
                    ->where('turno_registrado_videollamadas.fechaTurno','>=',$dia)
                    ->orderBy('turno_registrado_videollamadas.fechaTurno','asc')
                    ->orderBy('turno_registrado_videollamadas.horario','asc')
                    ->distinct()
                    ->get();    
            $turnos = $this-> filtrarTurnos($turnosAux);
            $someArray = json_decode($turnos, true);
                            
            if(!$turnosAux){                
                $mensaje =  'No hay turnos registrados para el DNI ingresado.';
            }
            
            return response()->json(array('turnosRegistrados'=>$someArray,'dni_paciente'=>$paciente_get->dni,'mensaje'=>$mensaje));
    }

    public function cancelarTurnoPaciente(Request $request){        
        $mensaje='';
        $paciente_get=DB::table('pacientes')                    
                    ->where('pacientes.dni',$request->get('dni_paciente'))                    
                    ->first();

        $turno_id = $request->get('turno_id');        
        $turno = turnoRegistrado::find($turno_id);
        
        // Eliminar evento de Google Calendar si existe
        if ($turno) {
            $this->deleteEventFromGoogleCalendar($turno);
        }
        
        $turno->comentario = 'Cancelado por paciente';
        $turno->activo = 0;
        $turno->save();

        $reembolsoResult = (new TurnoPagoIntentService())->tryAutoRefundOnPatientCancel($turno);

        if(strcmp($turno->primerControl, 'SI') == 0){
            $cancelarTurnoDobleAux = DB::table('turno_registrados')
                                    ->where('turno_registrados.fechaTurno', $turno->fechaTurno)
                                    ->where('turno_registrados.paciente', $turno->paciente)
                                    ->where('turno_registrados.medico', $turno->medico)
                                    ->where('turno_registrados.consultorio', $turno->consultorio)
                                    ->where('turno_registrados.primerControl', 'SI')
                                    ->where('turno_registrados.activo', 1)
                                    ->first();
            
            if($cancelarTurnoDobleAux!=null){
                $cancelarTurnoDoble = turnoRegistrado::find($cancelarTurnoDobleAux->id);
                // Eliminar evento de Google Calendar del turno doble si existe
                $this->deleteEventFromGoogleCalendar($cancelarTurnoDoble);
                $cancelarTurnoDoble->comentario = 'Cancelado por paciente';
                $cancelarTurnoDoble->activo = 0;
                $cancelarTurnoDoble->save();
            }
        }

        $dni_paciente=$request->get('dni_paciente');
        //id_tr nombre apellido medico fechaTurno horario
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $dia= date("Y-m-d");        
        $turnosAux = DB::table('turno_registrados')
                    ->join('medicos','medicos.id','=','turno_registrados.medico')
                    ->join('pacientes','pacientes.id','=','turno_registrados.paciente')                    
                    ->select('turno_registrados.id as trid','pacientes.nombre as pnombre','pacientes.apellido as papellido','medicos.apellido','medicos.nombre','turno_registrados.fechaTurno','turno_registrados.horario','turno_registrados.primerControl')
                    ->where('pacientes.id',$paciente_get->id)
                    ->where('turno_registrados.activo',1)
                    ->where('turno_registrados.fechaTurno','>=',$dia)
                    ->orderBy('turno_registrados.fechaTurno','asc')
                    ->orderBy('turno_registrados.horario','asc')
                    ->distinct()
                    ->get();    
            $turnos = $this-> filtrarTurnos($turnosAux);
            $someArray = json_decode($turnos, true);
                            
            if(!$turnosAux){                
                $mensaje =  'No hay turnos registrados para el DNI ingresado.';
            }

            $response = [
                'turnosRegistrados' => $someArray,
                'dni_paciente' => $paciente_get->dni,
                'mensaje' => $mensaje,
            ];
            if (!empty($reembolsoResult['message'])) {
                $response['reembolso_mensaje'] = $reembolsoResult['message'];
            }

            return response()->json($response);
    }

    public function existeVideollamada($medico_id){
        $videollamada = DB::table('videollamadas')                                                                
                    ->where('videollamadas.medico',$medico_id)                                                      
                    ->where('videollamadas.activo', 1)
                    ->first();
        if($videollamada){
            $findVideollamada = Videollamada::find($videollamada->id);
            return $findVideollamada;
        }
        return null;
    }

    public function cancelarTurnoAction(Request $request)
    {   
        $mensaje='';
        $paciente_get=DB::table('pacientes')                    
                    ->where('pacientes.dni',$request->get('dni_paciente'))                    
                    ->first();

        $turno_id = $request->get('turno_id');        
        $turno = turnoRegistrado::find($turno_id);
        
        // Eliminar evento de Google Calendar si existe
        if ($turno) {
            $this->deleteEventFromGoogleCalendar($turno);
        }
        
        $turno->activo = 0;
        $turno->save();

        $dni_paciente=$request->get('dni_paciente');
        //id_tr nombre apellido medico fechaTurno horario
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $dia= date("Y-m-d");        
        $turnosAux = DB::table('turno_registrados')
                    ->join('medicos','medicos.id','=','turno_registrados.medico')
                    ->join('pacientes','pacientes.id','=','turno_registrados.paciente')                    
                    ->select('turno_registrados.id as trid','pacientes.nombre as pnombre','pacientes.apellido as papellido','medicos.apellido','medicos.nombre','turno_registrados.fechaTurno','turno_registrados.horario')
                    ->where('pacientes.id',$paciente_get->id)
                    ->where('turno_registrados.activo',1)
                    ->where('turno_registrados.fechaTurno','>=',$dia)
                    ->distinct()
                    ->get();            
            if(!$turnosAux){                
                $mensaje =  'No hay turnos registrados para el DNI ingresado.';
            }
            return view('turnos.cancelar_turno')
            ->with('turnosRegistrados',$turnosAux)
            ->with('dni_paciente',$paciente_get->dni)
            ->with('mensaje',$mensaje);
        }

    // $dia-> a partir de este dia empiezo a buscar turno libre 2021-11-29
    function turnoLibreMasCercano($medico, $primerControl, $esVideollamada, $dia){
        // date_default_timezone_set('America/Argentina/Buenos_Aires');
        // $dia= date("Y-m-d");        
        $encontre = 0;                

        if($esVideollamada == 0){
            // 3 corresponde a Primer Control Doble
            $moduloPrimerControlDoble = $this->moduloActivo($medico, 3);
            if($moduloPrimerControlDoble == 0)
                $primerControl = 0;
        }

        while($encontre == 0){
            if($this->esFeriado($dia) == 0) // devuelve 1 si es feriado, 0 caso contrario
                $hayTurnoLibre = $this->checkTurnoLibre($medico, $dia, $primerControl, $esVideollamada);
            else
               $hayTurnoLibre = 0; 
            if($hayTurnoLibre == 0){ // es decir no hay turno libre, avanzo de dia.            
                $siguienteDia = $dia;              
                $siguienteDia = date('Y-m-d', strtotime('+1 day' , strtotime ( $siguienteDia )));            
                $dia = $siguienteDia;
            } else {
                $encontre = 1;
            }
        }
        return $dia;
    }


    //retorna 1 si es feriado 0 en caso de que no lo sea.
    function esFeriado($fecha){
        $feriados = DB::table('feriados')                                                                                           
                        ->where('feriados.fecha',$fecha)
                        ->first();
        if($feriados != null)
            return 1;
        else
            return 0;     
    }

        // retorna 1 si hay al menos 1 turno libre, sino retorna 0.
    function checkTurnoLibre($medico_id, $fecha, $primerControl, $esVideollamada){        
        $medico = medico::find($medico_id);            
        $consultorio = DB::table('consultorios')                                                                          
                        ->where('consultorios.id',$medico->consultorio)
                        ->first();
       
        $diaSeleccionado = $this->getDiaSeleccionado2($fecha);

        if($esVideollamada == 1){
            $turnos = DB::table('horario_medico_videollamadas')
                        ->where('horario_medico_videollamadas.medico',$medico->id)
                        ->where('horario_medico_videollamadas.consultorio', $consultorio->id)
                        ->where('horario_medico_videollamadas.dia', $diaSeleccionado)
                        ->where('horario_medico_videollamadas.activo', 1)                        
                        ->get();
            $turnosRegistrados = DB::table('turno_registrado_videollamadas')                                
                        ->where('turno_registrado_videollamadas.medico',$medico->id)
                        ->where('turno_registrado_videollamadas.consultorio', $consultorio->id)
                        ->where('turno_registrado_videollamadas.dia', $diaSeleccionado)
                        ->where('turno_registrado_videollamadas.fechaTurno', $fecha)
                        ->where('turno_registrado_videollamadas.activo', 1)                        
                        ->get();
        } else {
            if($medico->id == 1 || $medico->id == 5 || $medico->id == 11 || $medico->id == 12 || $medico->id == 13 || $medico->id == 14 || $medico->id == 15 || $medico->id == 18 || $medico->id == 23 || $medico->id == 31 || $medico->id == 38){
                if($medico->id == 1)
                    $turnos = $this->checkTurnoLibreEspecialFlor($medico->id, $consultorio->id, $diaSeleccionado, $fecha);
                if($medico->id == 5)
                    $turnos = $this->checkTurnoLibreEspecialLuciaDiomedi($medico->id, $consultorio->id, $diaSeleccionado, $fecha);
                if($medico->id == 12)
                    $turnos = $this->checkTurnoLibreEspecialEricaPacheco($medico->id, $consultorio->id, $diaSeleccionado, $fecha);
                if($medico->id == 11)
                    $turnos = $this->checkTurnoLibreEspecialCeciCorti($medico->id, $consultorio->id, $diaSeleccionado, $fecha);
                if($medico->id == 13)
                    $turnos = $this->checkTurnoLibreEspecialLucasSosa($medico->id, $consultorio->id, $diaSeleccionado, $fecha);
                if($medico->id == 14)
                    $turnos = $this->checkTurnoLibreEspecialPatriciaSosa($medico->id, $consultorio->id, $diaSeleccionado, $fecha);
                if($medico->id == 15)
                    $turnos = $this->checkTurnoLibreEspecialAmilcarSosa($medico->id, $consultorio->id, $diaSeleccionado, $fecha);
                if($medico->id == 18)
                    $turnos = $this->checkTurnoLibreEspecialCeleste($medico->id, $consultorio->id, $diaSeleccionado, $fecha);
                if($medico->id == 23)
                    $turnos = $this->checkTurnoLibreEspecialFonseca($medico->id, $consultorio->id, $diaSeleccionado, $fecha);
                if($medico->id == 31)
                    $turnos = $this->checkTurnoLibreEspecialNataliaFerrari($medico->id, $consultorio->id, $diaSeleccionado, $fecha);
                if($medico->id == 38)
                    $turnos = $this->checkTurnoLibreEspecialMonica($medico->id, $consultorio->id, $diaSeleccionado, $fecha);
            } else {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico->id)
                        ->where('horario_medicos.consultorio', $consultorio->id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.activo', 1)                        
                        ->get();
            }      

            // nuevo agregar fecha
            $fechaId = $this->checkFechaAgregada($medico_id, $consultorio->id, $fecha);
            if($fechaId != -1 && $turnos->count() == 0) {            
                $turnos = DB::table('horarios_medicos_agregados')
                            ->where('horarios_medicos_agregados.fecha_agregada_id',$fechaId)
                            ->where('horarios_medicos_agregados.medico',$medico_id)
                            ->where('horarios_medicos_agregados.consultorio', $consultorio->id)
                            ->where('horarios_medicos_agregados.dia', $diaSeleccionado)
                            ->where('horarios_medicos_agregados.activo', 1)                        
                            ->orderBy('horarios_medicos_agregados.horario')
                            ->get();        
            }  

            $turnosRegistrados = DB::table('turno_registrados')                                
                        ->where('turno_registrados.medico',$medico->id)
                        ->where('turno_registrados.consultorio', $consultorio->id)
                        ->where('turno_registrados.dia', $diaSeleccionado)
                        ->where('turno_registrados.fechaTurno', $fecha)
                        ->where('turno_registrados.activo', 1)                        
                        ->get();        
        }
        $i=0;
        $libre=0;        
        if($primerControl == 0){
            while($i<count($turnos)&&($libre==0)){                
                $turnoActualLibre = $this->estaLibre2($turnos[$i]->horario,$turnosRegistrados);                            
                if($turnoActualLibre==0){
                    $libre = 1;
                }
                $i++;                            
            }
        } else {
            // en caso de ser primer control $medico_id, $dia, $consultorio_id, $fechaTurno
            if($this->controlarCantidadPrimerControl($medico_id, $diaSeleccionado, $consultorio->id, $fecha)){  
                while($i<count($turnos)&&($libre==0)){
                //$libre=0;            
                    $turnoActualLibre = $this->estaLibre2($turnos[$i]->horario, $turnosRegistrados);                    
                          
                    if(($turnos[$i]->doble==0) && ($turnoActualLibre == 0)){
                        $j=$i+1;
                        if($j<count($turnos)){
                            $turnosContiguos = 1;//$this->esTurnoContiguo($turnos[$i]->horario,$turnos[$j]->horario);
                            if($turnosContiguos==1){
                                $turnoSiguienteLibre = $this->estaLibre2($turnos[$j]->horario, $turnosRegistrados);
                          //      echo $turnoSiguienteLibre.'<br>';                    
                                if($turnoSiguienteLibre == 0){                            
                                    $libre=1;
                                }
                            }
                        }                                    
                    }        
                    $i++;
                }
            }
        }
    
        return $libre;
    }

    


    //retorna 0 si el $turno esta libre 
  function estaLibre2($horario, $turnosRegistrados){
        $encontre = 0;
        $contador = 0;
        //if($turno->doble==1){$encontre=1;}
        while (($encontre == 0) && ($contador<count($turnosRegistrados))){            
            if(strcmp ($horario , $turnosRegistrados[$contador]->horario ) == 0){
                $encontre = 1;
            }
            $contador++;
        }
        return $encontre;
    }

    //$fecha = date("Y-m-d"); 
    public function getDiaSeleccionado2($fecha){
        $date = new DateTime($fecha);              
        $diaLetras = date_format($date, 'l');

        $diaSeleccionado=0;
        switch ($diaLetras) {
        case 'Monday':
            $diaSeleccionado=1;
            break;
        case 'Tuesday':
            $diaSeleccionado=2;
            break;
        case 'Wednesday':
            $diaSeleccionado=3;
            break;
        case 'Thursday':
            $diaSeleccionado=4;
            break;
        case 'Friday':
            $diaSeleccionado=5;
            break;
        case 'Saturday':
            $diaSeleccionado=6;
            break;
         case 'Sunday':
            $diaSeleccionado=7;
            break;
        }
        return $diaSeleccionado;
    }

    public function adminFeriados(){
        $today = getdate();
        $anioActual = $today['year'];
        $anioAux = $today['year'].'-01-01';
        $anioProximo = $today['year']+1;
        $anioAux2 = $anioProximo.'-01-01';

        $feriados1 = DB::table('feriados')
                         ->where('feriados.fecha','>=',$anioAux)
                         ->where('feriados.fecha','<',$anioAux2)
                         //->where('feriados.activo', 1)
                         ->get();    
                 
        $feriados2 = DB::table('feriados')
                         ->where('feriados.fecha','>=',$anioAux2)
                         //->where('feriados.activo', 1)
                         ->get();    

        return view('turnos_admin.admin_feriados')
                    ->with('feriados1',$feriados1)
                    ->with('anioActual',$anioActual)
                    ->with('feriados2',$feriados2)
                    ->with('anioProximo',$anioProximo);
    }

    public function altaFeriados(Request $request){
        $descripcion = $request->descripcion;
        $anio = $request->fecha_anio;
        $mes = $request->fecha_mes;
        $dia = $request->fecha_dia;
        $nuevoFeriado = new Feriado;
        $nuevoFeriado->fecha = $anio.'/'.$mes.'/'.$dia;
        $nuevoFeriado->descripcion = $descripcion;
        $nuevoFeriado->save();

        $today = getdate();
        $anioActual = $today['year'];
        $anioAux = $today['year'].'-01-01';
        $anioProximo = $today['year']+1;
        $anioAux2 = $anioProximo.'-01-01';

        $feriados1 = DB::table('feriados')
                         ->where('feriados.fecha','>=',$anioAux)
                         ->where('feriados.fecha','<',$anioAux2)
                         //->where('feriados.activo', 1)
                         ->get();    
                 
        $feriados2 = DB::table('feriados')
                         ->where('feriados.fecha','>=',$anioAux2)
                         //->where('feriados.activo', 1)
                         ->get();    

        return view('turnos_admin.admin_feriados')
                    ->with('feriados1',$feriados1)
                    ->with('anioActual',$anioActual)
                    ->with('feriados2',$feriados2)
                    ->with('anioProximo',$anioProximo);
    }

    public function eliminarFechaFeriado(Request $request){
        $fecha = feriado::find($request->fecha_id);
        $fecha->delete();

        $today = getdate();
        $anioActual = $today['year'];
        $anioAux = $today['year'].'-01-01';
        $anioProximo = $today['year']+1;
        $anioAux2 = $anioProximo.'-01-01';

        $feriados1 = DB::table('feriados')
                         ->where('feriados.fecha','>=',$anioAux)
                         ->where('feriados.fecha','<',$anioAux2)
                         //->where('feriados.activo', 1)
                         ->get();    
                 
        $feriados2 = DB::table('feriados')
                         ->where('feriados.fecha','>=',$anioAux2)
                         //->where('feriados.activo', 1)
                         ->get();    

        return response()->json(array('feriados1'=>$feriados1, 'feriados2'=>$feriados2));
    }

    public function asistirVideollamada(Request $request){        
        $paciente = DB::table('pacientes')
                         ->where('pacientes.dni', $request->dni_paciente)
                         ->where('pacientes.activo', 1)                         
                         ->first();    
        $turnoRegistrado = DB::table('turno_registrado_videollamadas')
                         ->where('turno_registrado_videollamadas.id', $request->turno_id)
                         ->where('turno_registrado_videollamadas.activo', 1)                         
                         ->first();        
        $medico = medico::find($turnoRegistrado->medico);
        $horario = $turnoRegistrado->horario;
        $fecha_aux = explode('-',$turnoRegistrado->fechaTurno);
        $fecha = $fecha_aux[2].'-'.$fecha_aux[1].'-'.$fecha_aux[0];
        $videollamada = DB::table('videollamadas')
                         ->where('videollamadas.medico', $medico->id)
                         ->where('videollamadas.activo', 1)                         
                         ->first();

        $tieneObraSocialCompleta = 0;
        if(strcmp($paciente->obra_social, 'PARTICULAR') == 0){
            $tieneObraSocialCompleta = 0;
        } else {
            if(strcmp($paciente->obra_social_foto, '') != 0) {
                $tieneObraSocialCompleta = 1;
            }
        }
        
        $moduloMercadoPago = 0;
        if($this->moduloActivo($medico->id, 7) == 1)  
            $moduloMercadoPago = 1;

        $medicoTrabajaConObraSocial = $this->medicoTrabajaConObraSocial($medico, $paciente);

        $this->enviarMailConsentimiento($paciente);

        return view('turnos.videollamadas')
                    ->with('turnoRegistrado',$turnoRegistrado)
                    ->with('tieneObraSocialCompleta', $tieneObraSocialCompleta)
                    ->with('paciente',$paciente)
                    ->with('medico',$medico)
                    ->with('videollamada',$videollamada)
                    ->with('moduloMercadoPago',$moduloMercadoPago)
                    ->with('medicoTrabajaConObraSocial',$medicoTrabajaConObraSocial)
                    ->with('fechaTurno',$fecha)
                    ->with('payment',null)
                    ->with('horario',$horario);
    }

    function enviarMailConsentimiento($paciente){
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $fecha = date("d-m-Y");                 
        $turnoRegistrado = DB::table('turno_registrado_videollamadas')
                         ->where('turno_registrado_videollamadas.paciente', $paciente->id)
                         ->where('turno_registrado_videollamadas.activo', 1)                         
                         ->get();
        if($turnoRegistrado->count() == 1){
            if($paciente->mail != null){
                $data = array('fecha'=>$fecha);
                Mail::to($paciente->mail)->queue(new EnviarConsentimientoTelemedicina($data));
            }
        } 
    }

    public function medicoTrabajaConObraSocial($medico, $paciente){
        $obrasSocialesMedico = DB::table('obra_social_medicos')
                                ->join('obra_socials', 'obra_socials.id', '=', 'obra_social_medicos.obra_social')                          
                                ->where('obra_social_medicos.medico', $medico->id)
                                ->where('obra_socials.nombre', $paciente->obra_social)
                                ->where('obra_social_medicos.activo', 1)
                                ->get();
        if($obrasSocialesMedico->count()>0)
            return 1;
        else
            return 0;
    }

    public function actualizarEstadoPacienteVideollamada(Request $request){
        $turno_id = $request->turno_id;
        $estado = $request->estado;

        $turno = turnoRegistradoVideollamada::find($turno_id);
        $turno->disponible = $estado;
        $turno->save();

        return response()->json(array('response'=>1, 'turno'=>$turno)); 
    }

    public function checkEstadoMedicoVideollamada(Request $request){
        $turno = turnoRegistradoVideollamada::find($request->turno_id);
        return response()->json(array('response'=>1, 'turno'=>$turno));    
    }

    /**
     * Descargar archivo .ics para recordatorio (día previo)
     */
    public function downloadReminderCalendar($turno_id)
    {
        $turno = TurnoRegistrado::find($turno_id);
        if (!$turno) {
            abort(404);
        }
        
        $paciente = Paciente::find($turno->paciente);
        $medico = Medico::find($turno->medico);
        $consultorio = Consultorio::find($turno->consultorio);
        
        if (!$paciente || !$medico || !$consultorio) {
            abort(404);
        }
        
        // Fecha del recordatorio (día previo)
        $fechaRecordatorio = date('Y-m-d', strtotime($turno->fechaTurno . ' -1 day'));
        
        $title = "Recordatorio: Turno con Dr. {$medico->apellido}";
        $description = "Recordatorio: Tienes un turno mañana a las {$turno->horario} con el Dr. {$medico->apellido}, {$medico->nombre}";
        $description .= "\n\nFecha del turno: " . date('d/m/Y', strtotime($turno->fechaTurno));
        $description .= "\nHorario: {$turno->horario}";
        $description .= "\nConsultorio: {$consultorio->direccion}";
        if (isset($consultorio->telefono) && !empty($consultorio->telefono)) {
            $description .= "\nTeléfono: {$consultorio->telefono}";
        }
        
        // COMENTADO TEMPORALMENTE - Error: Class 'App\Helpers\CalendarHelper' not found
        // TODO: Regenerar autoloader en producción con: composer dump-autoload --optimize
        /*
        $ics = \App\Helpers\CalendarHelper::generateICSFile(
            $title,
            $description,
            $consultorio->direccion,
            $fechaRecordatorio,
            '09:00'
        );
        
        return response($ics)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="recordatorio-turno-' . $turno_id . '.ics"');
        */
        // Retornar error temporal mientras se soluciona
        return redirect()->back()->with('error', 'Servicio de calendario temporalmente no disponible.');
        
        return response($ics)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="recordatorio-turno-' . $turno_id . '.ics"');
    }
    
    /**
     * Descargar archivo .ics para el turno mismo
     * Se abre directamente en el calendario nativo del dispositivo
     */
    public function downloadTurnoCalendar($turno_id)
    {
        $turno = TurnoRegistrado::find($turno_id);
        if (!$turno) {
            abort(404);
        }
        
        $medico = Medico::find($turno->medico);
        $consultorio = Consultorio::find($turno->consultorio);
        $paciente = Paciente::find($turno->paciente);
        
        if (!$medico || !$consultorio) {
            abort(404);
        }
        
        // Generar archivo .ics con recordatorio del día previo
        $ics = \App\Helpers\CalendarHelper::generateTurnoICS($turno, $medico, $consultorio, $paciente);
        
        return response($ics, 200)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'inline; filename="turno-' . $turno_id . '.ics"')
            ->header('Content-Transfer-Encoding', 'binary')
            ->header('Cache-Control', 'no-cache, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('X-Content-Type-Options', 'nosniff');
    }

    /**
     * Eliminar evento de Google Calendar cuando se cancela un turno
     */
    private function deleteEventFromGoogleCalendar($turno)
    {
        try {
            // Si no tiene event_id o es "no event_id", no hay nada que eliminar
            if (!$turno->google_calendar_event_id || $turno->google_calendar_event_id === 'no event_id') {
                return false;
            }
            
            // Obtener el paciente
            $paciente = Paciente::find($turno->paciente);
            if (!$paciente || !$paciente->google_calendar_access_token) {
                \Log::info("No se puede eliminar evento de Google Calendar: Paciente sin token OAuth para turno {$turno->id}");
                return false;
            }
            
            $calendarService = app(GoogleCalendarService::class);
            
            // Verificar si el token es válido
            $isValid = $calendarService->isTokenValid(
                $paciente->google_calendar_access_token,
                $paciente->google_calendar_token_expires_at
            );
            
            // Si el token expiró, intentar refrescarlo
            if (!$isValid && $paciente->google_calendar_refresh_token) {
                $newToken = $calendarService->refreshAccessToken($paciente->google_calendar_refresh_token);
                
                if ($newToken && !isset($newToken['error'])) {
                    $paciente->google_calendar_access_token = $newToken['access_token'];
                    if (isset($newToken['refresh_token'])) {
                        $paciente->google_calendar_refresh_token = $newToken['refresh_token'];
                    }
                    $expiresIn = isset($newToken['expires_in']) ? $newToken['expires_in'] : 3600;
                    $paciente->google_calendar_token_expires_at = date('Y-m-d H:i:s', time() + $expiresIn);
                    $paciente->save();
                } else {
                    // Token no se pudo refrescar
                    \Log::warning("No se pudo refrescar token para eliminar evento de Google Calendar del turno {$turno->id}");
                    return false;
                }
            }
            
            // Configurar tokens en el servicio
            $calendarService->setAccessToken(
                $paciente->google_calendar_access_token,
                $paciente->google_calendar_refresh_token,
                $paciente->google_calendar_token_expires_at
            );
            
            // Eliminar el evento
            $deleted = $calendarService->deleteEvent($turno->google_calendar_event_id);
            
            if ($deleted) {
                \Log::info("Evento de Google Calendar eliminado exitosamente para turno {$turno->id} (event_id: {$turno->google_calendar_event_id})");
                // Limpiar el event_id del turno
                $turno->google_calendar_event_id = null;
                $turno->save();
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            \Log::error("Error eliminando evento de Google Calendar para turno {$turno->id}: " . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Agregar evento a Google Calendar usando OAuth
     */
    private function addEventToGoogleCalendar($paciente, $turno, $medico, $consultorio)
    {
        try {
            // TEMPORAL: Mientras Google verifica OAuth, siempre retornar 'no event_id' para que se abra el calendario manualmente
            // TODO: Remover este return cuando Google haya verificado OAuth
            return 'no event_id';
            
            $calendarService = app(GoogleCalendarService::class);
            
            // Verificar si el token es válido
            $isValid = $calendarService->isTokenValid(
                $paciente->google_calendar_access_token,
                $paciente->google_calendar_token_expires_at
            );
            
            // Si el token expiró, intentar refrescarlo
            if (!$isValid && $paciente->google_calendar_refresh_token) {
                $newToken = $calendarService->refreshAccessToken($paciente->google_calendar_refresh_token);
                
                if ($newToken && !isset($newToken['error'])) {
                    $paciente->google_calendar_access_token = $newToken['access_token'];
                    if (isset($newToken['refresh_token'])) {
                        $paciente->google_calendar_refresh_token = $newToken['refresh_token'];
                    }
                    $expiresIn = isset($newToken['expires_in']) ? $newToken['expires_in'] : 3600;
                    $paciente->google_calendar_token_expires_at = date('Y-m-d H:i:s', time() + $expiresIn);
                    $paciente->save();
                } else {
                    // Token no se pudo refrescar, limpiar
                    $paciente->google_calendar_access_token = null;
                    $paciente->google_calendar_refresh_token = null;
                    $paciente->google_calendar_token_expires_at = null;
                    $paciente->save();
                    return null;
                }
            }
            
            // Configurar tokens en el servicio
            $calendarService->setAccessToken(
                $paciente->google_calendar_access_token,
                $paciente->google_calendar_refresh_token,
                $paciente->google_calendar_token_expires_at
            );
            
            // Preparar datos del evento
            $title = "Turno con Dr. {$medico->apellido}";
            $description = "Turno médico con el Dr. {$medico->apellido}, {$medico->nombre}";
            $description .= "\nConsultorio: {$consultorio->direccion}";
            if (isset($consultorio->telefono) && !empty($consultorio->telefono)) {
                $description .= "\nTeléfono: {$consultorio->telefono}";
            }
            
            $location = $consultorio->direccion;
            
            // Fecha del recordatorio (día previo)
            $reminderDate = date('Y-m-d', strtotime($turno->fechaTurno . ' -1 day'));
            $reminderTime = '09:00'; // Hora del recordatorio
            
            // Crear solo el recordatorio del día anterior (no el evento del turno)
            $eventId = $calendarService->createReminderEvent(
                $reminderDate,
                $reminderTime,
                $title,
                $description,
                $location,
                $turno->fechaTurno,
                $turno->horario
            );
            
            // Retornar el event_id si se creó exitosamente, null si falló
            return $eventId;
        } catch (\Exception $e) {
            \Log::error('Error agregando evento a Google Calendar: ' . $e->getMessage());
            return null;
        }
    }

    public function enviarRecordatorioMail(){
     // $dataMock = array('nombre'=>'Rodrigo','medico'=>'MedicoTest','horario'=>'18:00','direccion'=>'Av La Plata','telefono'=>'454545');
     // Mail::to('banegasrodrigo89@gmail.com')->send(new SendMailable($dataMock));
       
    // Probado funciona bien  
      $today = getdate();
      $myDate = $today['year'].'-'.$today['mon'].'-'.$today['mday'];
      //$myDate = '2019-11-11';
      $date = date('Y/m/d', strtotime('+1 day' , strtotime ( $myDate )));
      
      $pacientes=DB::table('turno_registrados')
                    ->join('pacientes','pacientes.id','=','turno_registrados.paciente')                    
                    ->select('pacientes.id as paciente_id','pacientes.nombre','pacientes.apellido','pacientes.telefono','pacientes.mail','pacientes.fcm_token','turno_registrados.id as turno_id','turno_registrados.medico','turno_registrados.consultorio','turno_registrados.dia','turno_registrados.horario','turno_registrados.fechaTurno')
                    ->where('turno_registrados.fechaTurno',$date)    
                     ->where('turno_registrados.activo', 1)                  
                    ->get();    
      
      // COMENTADO: Firebase FCM - Esta funcionalidad fue removida porque no funciona correctamente en iOS
      // $firebase = app('firebase');
      
      foreach($pacientes as $paciente) {
            // Enviar email si tiene mail
            if($paciente->mail != null) {
               $medico = DB::table('medicos')->where('medicos.id', $paciente->medico)->first();   
               $medicoNombre = $medico->apellido.', '.$medico->nombre;
               $consultorio = DB::table('consultorios')->where('consultorios.id', $paciente->consultorio)->first();   
               $data = array('nombre'=>$paciente->nombre,'medico'=>$medicoNombre,'horario'=>$paciente->horario,'direccion'=>$consultorio->direccion,'telefono'=>$consultorio->telefono);               
               Mail::to($paciente->mail)->queue(new SendMailable($data));
            }
            
            // COMENTADO: Envío de notificaciones push FCM - Removido porque no funciona en iOS
            /*
            if($paciente->fcm_token != null) {
                $medico = DB::table('medicos')->where('medicos.id', $paciente->medico)->first();
                $medicoNombre = $medico->apellido.', '.$medico->nombre;
                
                // Formatear fecha
                $fechaFormateada = date('d/m/Y', strtotime($paciente->fechaTurno));
                
                $turnoData = [
                    'turno_id' => $paciente->turno_id,
                    'fecha' => $fechaFormateada,
                    'horario' => $paciente->horario,
                    'medico' => $medicoNombre
                ];
                
                // Enviar notificación push
                $firebase->sendTurnoReminder($paciente->fcm_token, $turnoData);
            }
            */
        } 
    }

    function getUsersInformation(){
        $users = Medico::all();
        
        $data = array();
        foreach($users as $user) {
           $json['user_id'] = $user->id;                       
           $json['nombre'] = $user->nombre;
           $json['apellido'] = $user->apellido;
           $json['especialidad_id'] = $user->especialidad;
           $json['consultorio_id'] = $user->consultorio;
           $json['telefono'] = $user->telefono;
           $json['mail'] = $user->mail; 
           $json['castigo_automatico'] = $user->castigo_automatico;
           $json['foto'] = 'images/medicos/'.$user->foto;            
           $json['perfil'] = $user->perfil; 
           $json['sexo'] = $user->sexo; 
           $json['activo'] = $user->activo; 
           $data[] = $json;
        }

        return response()->json(array('datosMedicos'=>$data));
    }

    function setMensajeEnviado(Request $request){
        $turnoId = $request->turno_id;
        $value = $request->value;

        $turno = TurnoRegistrado::find($turnoId);
        $turno->msj_enviado = $value;
        $turno->save();

        return response()->json($turno, 200);
    }

    function generarListadoSms() {
      $json = array();  
      $today = getdate();
      $myDate = $today['year'].'-'.$today['mon'].'-'.$today['mday'];
      //$date = '2019-12-11';
      $date = date('Y-m-d', strtotime('+1 day' , strtotime ( $myDate )));
      
      $pacientes=DB::table('turno_registrados')
                    ->join('pacientes','pacientes.id','=','turno_registrados.paciente')                    
                    ->select('turno_registrados.id','pacientes.nombre','pacientes.apellido','pacientes.telefono','pacientes.mail','turno_registrados.medico','turno_registrados.consultorio','turno_registrados.dia','turno_registrados.horario','turno_registrados.fechaTurno','turno_registrados.primerControl', 'turno_registrados.msj_enviado', 'turno_registrados.tipo_turno')
                    ->where('turno_registrados.fechaTurno',$date)
                    ->where('turno_registrados.paciente','!=','84')
                    ->where('turno_registrados.activo', 1)
                    ->where('pacientes.activo', 1)  
                    ->orderBy('turno_registrados.horario')                
                    ->get();
      
      $data = array();
      $primerControl = 0;

      $diaSeleccionado = $this->getDiaSeleccionado2($date);
      foreach($pacientes as $paciente) {
            if($this->moduloActivo($paciente->medico, 3) == 0)  // el 3 corresponde al modulo de turnos dobles
                $primerControl = 0;

            if($primerControl == 0) { 
                if($paciente->telefono != null) {
                   $medico = DB::table('medicos')
                                        ->where('medicos.id', $paciente->medico)
                                        ->where('medicos.activo', 1)
                                        ->first();
                   if($medico!=null) {   
                       $medicoNombre = $medico->apellido.', '.$medico->nombre;
                       $consultorio = DB::table('consultorios')
                                            ->where('consultorios.id', $paciente->consultorio)
                                            ->where('consultorios.activo', 1)
                                            ->first();                                              
                       $json['turno_registrado_id'] = $paciente->id;
                       $json['medico_id'] = $medico->id;                       
                       $json['medico'] = $medicoNombre;
                       $json['nombre'] = $paciente->nombre;
                       $json['fecha'] = $paciente->fechaTurno;
                       $json['dia'] = $paciente->dia;
                       $json['horario'] = $paciente->horario;
                       $json['direccion'] = $consultorio->direccion;
                       $json['telefono_consultorio'] = $consultorio->telefono; 
                       $json['telefono_paciente'] = $paciente->telefono;
                       $json['enviarSms'] = true;
                       $json['msj_enviado'] = $paciente->msj_enviado;

                       if($medico->id == 36) {
                            if($diaSeleccionado == 1) {
                                $json['direccion'] = 'Fournier 927 - Punta Alta';                                 
                            } else {
                                $json['direccion'] = 'Urquiza 562 - Punta Alta';                                                
                            }
                        } 

                       if($medico->id == 12) {
                            if($diaSeleccionado == 3) {
                                $json['direccion'] = 'Blandengues 505'; 
                                $json['telefono_consultorio'] = "2914717327";        
                            } else {
                                $json['direccion'] = 'Garibaldi 44';
                                $json['telefono_consultorio'] = "4814538";                         
                            }
                        }  
                        if($medico->id == 24) {                            
                            if($diaSeleccionado == 1 || $diaSeleccionado == 5) {
                                $json['direccion'] = 'Luiggi 463';
                                $json['telefono_consultorio'] = "2914717327";                         
                            }
                            if($diaSeleccionado == 3) {
                                $json['direccion'] = 'Blandengues 505';
                                $json['telefono_consultorio'] = "2914717327";                         
                            }                            
                        }
                        if($medico->id == 43) {                            
                            if($diaSeleccionado == 1 || $diaSeleccionado == 3 || $diaSeleccionado == 5) {
                                $json['direccion'] = 'Luiggi 463';
                                $json['telefono_consultorio'] = "2914236530";                         
                            }
                            if($diaSeleccionado == 2) {
                                $json['direccion'] = 'Gimnasio EFI · Jorge Walsh 31';
                                $json['telefono_consultorio'] = "2914236530";                         
                            }
                            if($diaSeleccionado == 4) {
                                $horarioTarde = ['15:00', '15:30', '16:00', '16:30', '17:00', '17:30', '18:00', '18:30'];
                                if($paciente->horario && in_array($paciente->horario, $horarioTarde)) {                                    
                                    $json['direccion'] = 'Gimnasio EFI · Jorge Walsh 31';
                                    $json['telefono_consultorio'] = "2914236530";                         
                                } else {
                                    $json['direccion'] = 'Luiggi 463';
                                    $json['telefono_consultorio'] = "2914236530";                         
                                }                                
                            }                            
                        } 
                        if($medico->id == 25) {                            
                            $json['direccion'] = 'Blandengues 505'; 
                            $json['telefono_consultorio'] = "2914717327";                                                    
                        } 
                       $json['sexo'] = $medico->sexo; 
                       $json['primerControl'] = $paciente->primerControl; 

                       if($medico->id == 1) {
                            if($date < '2025-02-01'){
                               $json['direccion'] = 'Unimed. 12 de Octubre 53, piso 9.'; 
                               $json['telefono_consultorio'] = "4540001";                                                    
                            } else {
                               $json['direccion'] = 'Viamonte 276'; 
                               $json['telefono_consultorio'] = "2915134858";                                                    
                            }
                        }
                        if($medico->id == 2) {
                            if($paciente->tipo_turno == 24){
                               $json['direccion'] = 'Equipo Dubie. Florida 700'; 
                               $json['telefono_consultorio'] = "2914165011";                                                    
                            } else {
                               $json['direccion'] = 'Garibaldi 44'; 
                               $json['telefono_consultorio'] = "4814538";                                                    
                            }
                        }
                       
                        if($this->estaEnListaNegra($paciente->telefono) == false) {
                            $data[] = $json;                                                     
                        }                                           
                       //$data = array('nombre'=>$paciente->nombre,'medico'=>$medicoNombre,'horario'=>$paciente->horario,'direccion'=>$consultorio->direccion,'telefono'=>$consultorio->telefono);               
                       //Mail::to($paciente->mail)->queue(new SendMailable($data));
                    }
                }              
            }
            if((strcmp($paciente->primerControl, 'SI') == 0) && ($primerControl == 0)) { 
                $primerControl = 1;
            } else {
                $primerControl = 0;
            } 
        }
        //$this->enviarRecordatorioMail();
    //return json_encode($data);
    return response()->json(array('datos'=>$data));
    }

    function estaEnListaNegra($numero) {
        $ln = DB::table('lista_negras')
                        ->where('lista_negras.numero', $numero)
                        ->first();
        if($ln != null) {
            return true;
        } 
        return false;
    }                           

    function generarListadoDiaTobb($fecha, $medico) {
        $listadoPacientes = DB::table('turno_registrados')
                            ->join('pacientes','pacientes.id','=','turno_registrados.paciente')                    
                            ->select('turno_registrados.horario','turno_registrados.primerControl','turno_registrados.sobreturno', 'pacientes.*')
                            ->where('turno_registrados.medico', $medico)                    
                            ->where('turno_registrados.fechaTurno', $fecha)                    
                            ->where('turno_registrados.activo', 1)  
                            ->orderBy('turno_registrados.horario')
                            ->get();    

        return $listadoPacientes;
    }   
    
    function getPacienteTobb($dni){
         $paciente = DB::table('pacientes')                            
                            ->where('pacientes.dni', $dni)                                                
                            ->where('pacientes.activo', 1)                              
                            ->get();
        return $paciente;
    }

    function checkErrorFecha($fechaSelect){
        echo 'Fecha desde compara cuando fue creado el turno registrado, comparo de ese dia en adelante <br>';
      $pacientes = DB::table('turno_registrados')
                    ->join('pacientes','pacientes.id','=','turno_registrados.paciente')                    
                    ->join('medicos','medicos.id','=','turno_registrados.medico')
                    ->select('pacientes.nombre','pacientes.apellido','pacientes.telefono','pacientes.mail','turno_registrados.medico','turno_registrados.consultorio','turno_registrados.dia','turno_registrados.horario','turno_registrados.fechaTurno','medicos.apellido as mapellido','pacientes.dni','turno_registrados.created_at as trca')
                    ->where('turno_registrados.created_at','>=', $fechaSelect)                    
                    ->where('turno_registrados.activo', 1)  
                    ->orderBy('turno_registrados.created_at','desc')                  
                    ->get();    
        echo 'Cantidad de turnos: '.$pacientes->count().'<br>';
        echo 'Fecha desde: '.$fechaSelect.'<br>';  
        $errores = 0;
        foreach($pacientes as $paciente) {
            $fecha_aux = explode('-',$paciente->fechaTurno);                    
            if((strlen($fecha_aux[0]) != 4)||(strlen($fecha_aux[1]) != 2)|| (strlen($fecha_aux[2]) != 2)){    
                $errores++;}                             
        }
        echo 'ERRORES: '.$errores.'<br><br>';        
        echo 'Turno Registrado Dia - - -  - FechaTurno - -  Horario - - - DNI - - - - - Medico <br>';
        foreach($pacientes as $paciente) {
            $fecha_aux = explode('-',$paciente->fechaTurno);                    
            if((strlen($fecha_aux[0]) != 4)||(strlen($fecha_aux[1]) != 2)|| (strlen($fecha_aux[2]) != 2)){
                echo 'ERROR ---> '. $paciente->fechaTurno.'<br>';     
                $errores++;
            } else {
                echo $paciente->trca.'. . . . . '.$paciente->fechaTurno.' . . . '.$paciente->horario.' . . .   '.$paciente->dni.' . . .  '.$paciente->mapellido.'<br>';          
            }                             
        }   
    }


/*SELECT count(paciente) as pc,fechaTurno, paciente, horario FROM turno_registrados where paciente!=392 and activo=1 and  primerControl='NO' and sobreturno=0 
        group by fechaTurno,paciente,medico  
ORDER BY `pc`  DESC*/
    public function checkTimeout(){
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $hoy = date("Y-m-d");         
        $query = DB::table('turno_registrados')
                        ->select(array('fechaTurno','paciente', DB::raw('COUNT(paciente) as countPac')))
                        ->where('paciente','!=','392') // 84 es cancelado para web y 392 para local            
                        ->where('activo',1)
                        ->where('sobreturno',0)
                        ->where('primerControl','NO')
                        ->where('fechaTurno', '>=', $hoy)
                        ->groupBy('fechaTurno')
                        ->groupBy('paciente')
                        ->groupBy('medico')
                        ->having('countPac', '>' , 1)
                        ->orderby('countPac','ASC')
                        ->get();
        echo $query;
    }

    function adminExtras(){
        return view('turnos_admin.admin_extra');      
    }

    function agregarMedicoPaciente($medico_id, $paciente_id){
        $medicoPacienteAux = DB::table('medico_pacientes')                            
                            ->where('medico_pacientes.medico', $medico_id)                                                
                            ->where('medico_pacientes.paciente', $paciente_id)                              
                            ->first();
        if($medicoPacienteAux == null){
            $medicoPaciente = new MedicoPaciente;
            $medicoPaciente->medico = $medico_id;
            $medicoPaciente->paciente = $paciente_id;
            $medicoPaciente->bloqueado = 0;
            $medicoPaciente->save();
        }
    }

    function getTurnoId(Request $request){
        $turnoId = $request->turnoId;
        $turno = TurnoRegistrado::find($turnoId);

        return response()->json(array('turno'=>$turno));
    }

    // me devuelve los turnos del dia, activos y desactivos
    function getTurnosDiaActivoDesactivo($fecha){
        $turnos = DB::table('turno_registrados')
                        ->join('medicos', 'medicos.id', 'turno_registrados.medico')
                        ->join('pacientes', 'pacientes.id', 'turno_registrados.paciente')
                        ->select('turno_registrados.activo', 'turno_registrados.horario','turno_registrados.comentario', 'pacientes.dni', 'pacientes.nombre as nombrep', 'pacientes.apellido as apellidop', 'medicos.nombre as nombrem', 'medicos.apellido as apellidom')
                        //->where('turno_registrados.paciente','!=', 84)                                                           
                        ->where('turno_registrados.fechaTurno', $fecha)
                        ->orderby('turno_registrados.medico')
                        ->get();
         
         echo 'Activo -- Horario -- Comentario -- DNI -- Nombre Apellido Paciente -- Nombre Apellido Medico <br><br>';

         echo 'Fecha: '.$fecha.'<br>';
         foreach($turnos as $turno) {
            echo $turno->activo.' -- '.$turno->horario.' -- '.$turno->comentario.' -- '.$turno->dni.' -- '.$turno->nombrep.', '.$turno->apellidop.'  -- '.$turno->nombrem.', '.$turno->apellidom.'<br>';
         }         
    }

    function getTurnoRegistradoId(Request $request){
        $turnoRegistradoId = $request->turnoRegistradoId;
        $turno = DB::table('turno_registrados')
                            ->where('turno_registrados.id', $turnoRegistradoId)                
                            ->first();
        return response()->json(array('turno'=>$turno));
    }

    function ejecutarExtras2(Request $request){
        $valorExtra = $request->valor_extra_2;
        $medicos = DB::table('medicos')                                
                        ->get();
        
        foreach($medicos as $ob){
                $newOs = new ObraSocialMedico;
                $newOs->medico = $ob->id;
                $newOs->obra_social = $valorExtra;
                $newOs->activo = 1;
                $newOs->importe = 1;
                $newOs->save();
        }
    }   

    function getObraSocialDiferencial(Request $request) {
        $medico_id = $request->medico_id;
        $texto = $request->texto;

        $obraSocialParticular = DB::table('obra_social_medicos')
            ->select('obra_socials.nombre', 'obra_social_medicos.importe')
            ->join('obra_socials', 'obra_socials.id', '=', 'obra_social_medicos.obra_social')
            ->where('obra_social_medicos.medico', $medico_id)            
            ->where('obra_socials.nombre', 'PARTICULAR')
            ->where('obra_social_medicos.activo', 1)
            ->first();

            $particular = $obraSocialParticular->importe;

            $query_os = DB::table('obra_social_medicos')
                ->select('obra_socials.nombre', 'obra_social_medicos.importe')
                ->join('obra_socials', 'obra_socials.id', '=', 'obra_social_medicos.obra_social')
                ->where('obra_social_medicos.medico', $medico_id)
                ->where('obra_socials.activo', 1)
                ->where('obra_social_medicos.activo', 1);

            if (strlen($texto) > 0) {
                $query_os->where('obra_socials.nombre', 'like', "%{$texto}%");
            }

            $obraSocial = $query_os->get()->map(function ($item) use ($particular) {
                if ($item->importe == 0) {
                    $item->importe = $particular;
                }
                return $item;
            });

        return response()->json(array('obraSocial'=>$obraSocial, 'obraSocialParticular'=>$obraSocialParticular));        
    }

    function ejecutarExtras(Request $request) {
        // con esto voy a cargar la tabla MedicoPaciente que es nueva.
        $valorExtra = $request->valor_extra;
        /*$pacientesMedico = DB::table('turno_registrados')
                            ->where('turno_registrados.paciente','!=', 84)                                   
                          ->distinct('turno_registrados.paciente')
                          ->orderby('turno_registrados.medico')
                        ->get();
        foreach ($pacientesMedico as $pm) {
            $this->agregarMedicoPaciente($pm->medico, $pm->paciente);
        }*/

        // con esto voy a cargarle a todos los medicos todas las obras sociales. dado que a partir de ahora cada cual podra
        // elegir con cual trabajar.
        //DB::table('obra_social_medicos')->delete();                 
        $query_os = DB::table('obra_socials')                        
                        ->orderby('obra_socials.nombre')                        
                        ->get();
        $medico = Medico::find($valorExtra);        
        
        foreach($query_os as $ob){
                $newOs = new ObraSocialMedico;
                $newOs->medico = $medico->id;
                $newOs->obra_social = $ob->id;
                $newOs->importe = 1;
                $newOs->activo = 1;
                $newOs->save();
        }
    
        // esto estaba de antes no debo ejecutarlo.
        /*$query = DB::table('pacientes')
                        ->select('pacientes.obra_social')
                        ->distinct()
                        ->orderby('pacientes.obra_social')
                        ->get();
        $query_os = DB::table('obra_socials')                        
                        ->select('obra_socials.nombre')                    
                        ->orderby('obra_socials.nombre')                        
                        ->get();
        echo $query->count().'<br>';
        echo $query_os->count().'<br>';
        foreach ($query as $valor){            
            echo $valor->obra_social.'<br>';
        }
        echo '<br>';
        foreach ($query_os as $valor){            
            echo $valor->nombre.'<br>';
        }
        */
                                        
    }

    function cancelaTurno($turno_id) {
        $turnoRegistrado = TurnoRegistrado::find($turno_id);
        if (!$turnoRegistrado) {
            abort(404);
        }

        // Eliminar evento de Google Calendar si existe
        $this->deleteEventFromGoogleCalendar($turnoRegistrado);

        $turnoRegistrado->activo = 0;
        $turnoRegistrado->cancelado_por = 'link whatsapp';
        $turnoRegistrado->save();

        $reembolsoResult = (new TurnoPagoIntentService())->tryAutoRefundOnPatientCancel($turnoRegistrado);

        $medico = Medico::find($turnoRegistrado->medico);

        $fecha = $this->convertirFechaMostrar($turnoRegistrado->fechaTurno);
        return view('turnos.cancela_turno')
            ->with('fecha', $fecha)
            ->with('medico', $medico)
            ->with('turnoRegistrado', $turnoRegistrado)
            ->with('reembolso_mensaje', $reembolsoResult['message'] ?? null);
    }

    function listaNegra() {
        return view('turnos_admin.admin_lista_negra');
    }

    function registrarNumeroListaNegra(Request $request) {
        $numero = $request->numero;
        $ln = new ListaNegra;
        $ln->numero = $numero;
        $ln->save();

        return response()->json(array('numero'=>$numero));        
    }

    function enviarMailConfirmacion(Request $request) {
        $turnoId = $request->turno_id;
        $turno = TurnoRegistrado::find($turnoId);
        $paciente = Paciente::find($turno->paciente);
        $medico = Medico::find($turno->medico);           
        $medicoNombre = $medico->apellido.', '.$medico->nombre;
        $consultorio = Consultorio::find($turno->consultorio);        

        $telefono = $consultorio->telefono;        
        $direccion = $consultorio->direccion;

        $diaSeleccionado = $turno->dia;
        if($medico->id == 36) {
            if($diaSeleccionado == 1) {
                $direccion = 'Fournier 927 - Punta Alta';                                 
            } else {
                $direccion = 'Urquiza 562 - Punta Alta';                                                
            }
        } 

       if($medico->id == 12) {
            if($diaSeleccionado == 3) {
                $direccion = 'Blandengues 505'; 
                $telefono = "2914717327";        
            } else {
                $direccion = 'Garibaldi 44';
                $telefono = "4814538";                         
            }
        }  
        if($medico->id == 24) {                            
            if($diaSeleccionado == 1 || $diaSeleccionado == 5) {
                $direccion = 'Luiggi 463';
                $telefono = "2914717327";                         
            }
            if($diaSeleccionado == 3) {
                $direccion = 'Blandengues 505';
                $telefono = "2914717327";                         
            }                            
        } 
        if($medico->id == 25) {                            
            $direccion = 'Blandengues 505'; 
            $telefono = "2914717327";                                                    
        }        
       if($medico->id == 1) {
            if($turno->fechaTurno < '2025-02-01'){
                $direccion = 'Unimed. 12 de Octubre 53, piso 9.'; 
                $telefono = "4540001";                                                    
            } else {
               $direccion = 'Viamonte 276'; 
               $telefono = "2915134858";                                                    
            }
        }
        if($medico->id == 2) {
            if($turno->tipo_turno == 24) {
               $direccion = 'Equipo Dubie. Florida 700'; 
               $telefono = "2914165011";                                                    
            } else {
               $direccion = 'Garibaldi 44'; 
               $telefono = "4814538";                                                    
            }
        }
                
        $fechaMostrar = $this->convertirFechaMostrar($turno->fechaTurno);

        $data = array('fecha'=>$fechaMostrar, 'nombre'=>$paciente->nombre, 'horario'=>$turno->horario, 'medico'=>$medicoNombre, 'direccion'=>$direccion, 'telefono'=>$telefono);

        Mail::to($paciente->mail)->queue(new EnviarConfirmacionTurno($data));
        
        return response()->json(array('turnoId'=>$turnoId));        
    }

    public function getFechasDisponibles(Request $request)
    {
        $medico_id = $request->get('medico');
        $tipoTurno = $request->get('tipoTurno');
        $esVideollamada = $request->get('esVideollamada', 0);
        $consultorio_id = $request->get('consultorio');
        $primerControl = $request->get('primer_control', 0);
        $fechaDesde = $request->get('fecha_desde');
        $fechaHasta = $request->get('fecha_hasta');

        if (!$fechaDesde || !$fechaHasta) {
            return response()->json(array('fechas' => []));
        }

        // Convertir fechas de formato DD/MM/YYYY a YYYY-MM-DD si es necesario
        if (strpos($fechaDesde, '/') !== false) {
            $fechaAux = explode('/', $fechaDesde);
            $fechaDesde = $fechaAux[2] . '-' . $fechaAux[1] . '-' . $fechaAux[0];
        }
        if (strpos($fechaHasta, '/') !== false) {
            $fechaAux = explode('/', $fechaHasta);
            $fechaHasta = $fechaAux[2] . '-' . $fechaAux[1] . '-' . $fechaAux[0];
        }

        $medico = DB::table('medicos')->find($medico_id);
        if (!$medico) {
            return response()->json(array('fechas' => []));
        }

        if (!$consultorio_id) {
            $consultorio_id = $medico->consultorio;
        }

        $fechasDisponibles = array();
        $fechasSinTurnos = array();
        
        // Obtener días habilitados del médico
        $diaSeleccionado = $this->getDiaSeleccionado2($fechaDesde);
        
        $fechaActual = new DateTime($fechaDesde);

        while ($fechaActual->format('Y-m-d') <= $fechaHasta) {
            $fechaStr = $fechaActual->format('Y-m-d');
            
            // Verificar si es feriado
            if ($this->esFeriado($fechaStr) == 1) {
                $fechaActual->modify('+1 day');
                continue;
            }
            
            // Obtener el día de la semana de esta fecha (1=Lunes, 7=Domingo)
            $diaSemana = $this->getDiaSeleccionado2($fechaStr);
            
            // Verificar si el médico atiende este día de la semana
            $medicoAtiende = false;
            if ($esVideollamada == 1) {
                $horarioExiste = DB::table('horario_medico_videollamadas')
                    ->where('medico', $medico_id)
                    ->where('consultorio', $consultorio_id)
                    ->where('dia', $diaSemana)
                    ->where('activo', 1)
                    ->exists();
                $medicoAtiende = $horarioExiste;
            } else {
                $horarioExiste = DB::table('horario_medicos')
                    ->where('medico', $medico_id)
                    ->where('consultorio', $consultorio_id)
                    ->where('dia', $diaSemana)
                    ->where('tipo_turno', $tipoTurno)
                    ->where('activo', 1)
                    ->exists();
                $medicoAtiende = $horarioExiste;
            }
            
            // Si el médico atiende este día, verificar si hay turnos
            if ($medicoAtiende) {
                // Verificar si hay turnos libres para esta fecha (usando misma lógica que en createJson)
                $hayTurnoLibre = $this->checkTurnoLibreTipoTurno($medico_id, $fechaStr, $primerControl, $esVideollamada, $tipoTurno, $consultorio_id);
                
                // Formatear fecha como DD/MM/YYYY para el datepicker
                $fechaFormateada = $this->convertirFechaMostrar($fechaStr);
                
                if ($hayTurnoLibre == 1) {
                    $fechasDisponibles[] = $fechaFormateada;
                } else {
                    // Es un día seleccionable pero sin turnos disponibles
                    $fechasSinTurnos[] = $fechaFormateada;
                }
            }

            $fechaActual->modify('+1 day');
        }

        return response()->json(array(
            'fechas' => $fechasDisponibles,
            'fechasSinTurnos' => $fechasSinTurnos
        ));
    }
}
