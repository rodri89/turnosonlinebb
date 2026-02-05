<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Post;
use Illuminate\Support\Facades\DB;
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
use App\Feriado;
use App\Paciente;
use App\HorarioMedicoDH;
use App\MedicoPrimerControl;
use App\Videollamada;
use App\ObraSocialMedico;
use App\MedicoPaciente;
use App\User;
use App\PacienteSecretaria;
//use App\especialidad;

class TurnoController extends Controller
{
    
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
    function checkTurnoLibreTipoTurno($medico_id, $fecha, $primerControl, $esVideollamada, $tipoTurno){        
        $medico = medico::find($medico_id);            
        $consultorio = DB::table('consultorios')                                                                                           
                        ->where('consultorios.id',$medico->consultorio)
                        ->first();
       
        $diaSeleccionado = $this->getDiaSeleccionado2($fecha);
        
        $turnos = DB::table('horario_medicos')
                ->where('horario_medicos.medico',$medico->id)
                ->where('horario_medicos.consultorio', $consultorio->id)
                ->where('horario_medicos.dia', $diaSeleccionado)
                ->where('horario_medicos.tipo_turno', $tipoTurno)
                ->where('horario_medicos.activo', 1)                        
                ->get();        

        $turnosRegistrados = DB::table('turno_registrados')                                
                    ->where('turno_registrados.medico',$medico->id)
                    ->where('turno_registrados.consultorio', $consultorio->id)
                    ->where('turno_registrados.dia', $diaSeleccionado)
                    ->where('turno_registrados.fechaTurno', $fecha)
                    //->where('turno_registrados.tipo_turno', $tipoTurno)
                    ->where('turno_registrados.activo', 1)                        
                    ->get();        
        
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
            // en caso de ser primer control
            if($this->controlarCantidadPrimerControl($medico, $fecha)){  
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


    function seleccionarDiaTurnoOnline($request) {
        $opcion = $request->tipo_turno;
        $medico = medico::find($request->medico_id);        
        $consultorio = DB::select('select * from consultorios where id = ? and activo=1', [$request->consultorio_id]);        
        $paciente = paciente::find($request->paciente_id);
        $primerControl = $request->primerControl;
        $moduloRecetas = $request->moduloRecetas;
        $tipoTurno = 0;
        
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

        return view('turnos.seleccionar_dia')
                        ->with('esVideollamada', $esVideollamada)
                        ->with('tipoTurno', $opcion)
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
                        ->with('dias_deshabilitados',$dias_deshabilitados);
    }

    public function selectDia(Request $request)
    {   
        if($request->tipo_turno == 22) {
            return $this->seleccionarDiaTurnoOnline($request);
        }    
        $tipoTurno = $request->tipoTurno;
        $esVideollamada = $request->get('esVideollamada');
        $primerControl = $request->get('primer_control'); 	            
        $paciente = DB::table('pacientes')->find($request->paciente_id);
        $dni_paciente = $request->get('dni_paciente');
    	$medico = DB::select('select * from medicos where id = ? and activo=1', [$request->get('medico_id')]);
    	$consultorio = DB::select('select * from consultorios where id = ? and activo=1', [$medico[0]->consultorio]);
    	
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
            $dias_habilitados = $this->diasHabilitados($medico[0]->id, $consultorio[0]->id, 0, $tipoTurno);            
            $diasAtencion = $this->diasAtencion($medico[0]->id, $consultorio[0]->id, 0 , $tipoTurno);
            $dias_deshabilitados = $this->diasDeshabilitados($diasAtencion);
        } else {
            $esVideollamada = 1;
            $fechaLibreDisponible_aux = $this->turnoLibreMasCercano($medico[0]->id, $primerControl, 1);
            $fecha_aux = explode('-',$fechaLibreDisponible_aux);                    
            $fechaLibreDisponible1 = $fecha_aux[2].'/'.$fecha_aux[1].'/'.$fecha_aux[0];                                      
            $dias_habilitados = $this->diasHabilitados($medico[0]->id, $consultorio[0]->id, 1, $tipoTurno);            
            $diasAtencion = $this->diasAtencion($medico[0]->id, $consultorio[0]->id, 1, $tipoTurno);
            $dias_deshabilitados = $this->diasDeshabilitados($diasAtencion);   
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

        $fechaLibreDisponible22= $fechaLibreDisponible1Aux[2].'-'.$fechaLibreDisponible1Aux[1].'-'.$fechaLibreDisponible1Aux[0];                          
        $diferenciaDias = $this->getDiferenciaDiasFechas($fechaLibreDisponible22, $ventanaTiempoTope);
        
    	return view('turnos.seleccionar_dia')
        ->with('esVideollamada',$esVideollamada)
        ->with('tipoTurno',$tipoTurno)
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
        ->with('dias_deshabilitados',$dias_deshabilitados);    
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

    public function diasHabilitados($medico_id, $consultorio_id, $esVideollamada, $tipoTurno) {
        if($esVideollamada == 1){
        $diasAtencionAux = DB::select('select DISTINCT(dia) from horario_medico_videollamadas where consultorio= ? and medico = ? and activo=1 order by dia',    
            [$consultorio_id , $medico_id]);
        } else {
            $diasAtencionAux = DB::select('select DISTINCT(dia) from horario_medicos where consultorio= ? and medico = ? and tipo_turno = ? and activo=1 order by dia',    
            [$consultorio_id , $medico_id, $tipoTurno]);
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
        $fechasAgregadas = DB::table('fechas_agregadas')
                        ->select('dia')
                        ->where('fechas_agregadas.medico',$medico_id)
                        ->where('fechas_agregadas.consultorio', $consultorio_id)
                        ->where('fechas_agregadas.fecha', '>',$dateHoy)                        
                        ->where('fechas_agregadas.activo', 1)                        
                        ->distinct()
                        ->get();

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
    public function diasAtencion($medico_id, $consultorio_id, $esVideollamada, $tipoTurno) {
        if($esVideollamada == 1 || $tipoTurno == 4){
            $diasAtencionAux = DB::select('select DISTINCT(dia) from horario_medico_videollamadas where consultorio= ? and medico = ? and activo=1 order by dia',    
            [$consultorio_id , $medico_id]);
        } else {
            $diasAtencionAux = DB::select('select DISTINCT(dia) from horario_medicos where consultorio= ? and medico = ? and tipo_turno = ? and activo=1 order by dia',    
            [$consultorio_id , $medico_id, $tipoTurno]);
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
        $fechasAgregadas = DB::table('fechas_agregadas')
                        ->select('dia')
                        ->where('fechas_agregadas.medico',$medico_id)
                        ->where('fechas_agregadas.consultorio', $consultorio_id)
                        ->where('fechas_agregadas.fecha', '>',$dateHoy)                        
                        ->where('fechas_agregadas.activo', 1)                        
                        ->distinct()
                        ->get();

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
        }           

        $turnosLibres=0;        
        if($cantidadPrimerControlPermitido){
            $someArray = json_decode($data, true);
            foreach($someArray as $v){
                if($v["libre"] == 1)
                    $turnosLibres=1;
            }
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
        ->with('dias_deshabilitados',$dias_deshabilitados);    
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

        $turnos = DB::select('select * from horario_medicos where medico = ? and consultorio = ? and dia = ? and tipo_turno = ? and activo=1',
            [$medico_id , $consultorio_id, $diaSeleccionado, $tipoTurno]);

        $turnosRegistrados = DB::select('select * from turno_registrados where medico = ? and consultorio = ? and dia = ?  and fechaTurno = ? and  tipo_turno = ? and activo=1',
            [$medico_id, $consultorio_id, $diaSeleccionado, $nuevaFecha, $tipoTurno]);
        // En caso de ser 1 es su primer control. Tengo que mostrar turnos dobles sino de a un turno.
        
        // Modulo id: 3 | corresponde a Primer Control Doble
        $moduloPrimerControlDoble = $this->moduloActivo($medico->id, 3);
        if (($primerControl == 0)||($moduloPrimerControlDoble == 0)){
            //turnos comunes cada media hora.
            $data = $this-> createJson($medico_id, $consultorio_id, $diaSeleccionado, $nuevaFecha, $tipoTurno);
            $cantidadPrimerControlPermitido = true;
        } else {
            //turnos especiales cada 1 hora.
            $cantidadPrimerControlPermitido = $this->controlarCantidadPrimerControl($medico_id, $diaSeleccionado, $consultorio_id, $nuevaFecha);
            if($cantidadPrimerControlPermitido)
                $data = $this-> createJsonTurnosDobles($medico_id, $consultorio_id, $diaSeleccionado, $nuevaFecha);
        }   

        $turnosLibres=0;        
        if($cantidadPrimerControlPermitido) {
            $someArray = json_decode($data, true);
            foreach($someArray as $v){
                if($v["libre"] == 1)
                    $turnosLibres=1;
            }
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


        return response()->json(array('esVideollamada'=>$esVideollamada, 'tipoTurno'=>$tipoTurno, 'primerControl'=>$primerControl, 'turnos'=>$someArray, 'medico'=>$medico, 'obraSocialCargada'=>$obraSocialCargada, 'dia'=>$diaSeleccionado, 'fechaSolicitada'=>$nuevaFecha, 'hayTurnoLibre'=>$turnosLibres, 'paciente'=>$paciente, 'moduloPrimerControlDoble'=>$moduloPrimerControlDoble, 'validarFeriado'=>$validarFeriado, 'cantTurnosMes'=>$cantTurnosMes, 'consultorio'=>$consultorio, 'dias_deshabilitados'=>$dias_deshabilitados, 'cantidadPrimerControlPermitido'=>$cantidadPrimerControlPermitido));    
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
                $data = $this-> createJsonTurnosDobles($medico_id, $consultorio_id, $diaSeleccionado, $nuevaFecha);
        }   

        $turnosLibres=0;        
        if($cantidadPrimerControlPermitido){
            $someArray = json_decode($data, true);
            foreach($someArray as $v){
                if($v["libre"] == 1)
                    $turnosLibres=1;
            }
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
        ->with('dias_deshabilitados',$dias_deshabilitados);    
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
        foreach($someArray as $v){
            if($v["libre"] == 1)
                $turnosLibres=1;
        }
        
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
        ->with('dias_deshabilitados',$dias_deshabilitados);    
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
        // Antes de enero de 15 a 19 hs va los miercoles
        // despues de enero de 16 a 20 hs  va los miercoles
        $turnos = null;
        $mostrarDespuesDeFecha = '2025-01-01';
        $mostrarAntesDeFecha = '2025-03-01';
        if($fechaSeleccionada >= $mostrarDespuesDeFecha && $fechaSeleccionada <= $mostrarAntesDeFecha) {            
            if($diaSeleccionado == 3 ) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario', '!=', '15:00')
                        ->where('horario_medicos.horario', '!=', '15:30')                        
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
                        ->where('horario_medicos.horario', '!=', '19:00')
                        ->where('horario_medicos.horario', '!=', '19:30')                        
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

    function checkTurnoLibreEspecialMarina($medico_id, $consultorio_id, $diaSeleccionado, $fechaSeleccionada){        
        $turnos = null;                
        if($fechaSeleccionada >= '2025-04-01') {
            // mostrar 9.30 10.00 10.30 11.00 11.30
            if($diaSeleccionado == 2) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)                        
                        ->where('horario_medicos.horario','!=' ,'15:30')                        
                        ->where('horario_medicos.horario','!=' ,'16:10')
                        ->where('horario_medicos.horario','!=' ,'16:50')
                        ->where('horario_medicos.horario','!=' ,'17:30')
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
                        ->where('horario_medicos.horario','!=' ,'09:00')                       
                        ->where('horario_medicos.horario','!=' ,'09:30')                        
                        ->where('horario_medicos.horario','!=' ,'10:00')
                        ->where('horario_medicos.horario','!=' ,'10:30')
                        ->where('horario_medicos.horario','!=' ,'11:00')
                        ->where('horario_medicos.horario','!=' ,'11:30')                        
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
                        ->where('horario_medicos.tipo_turno', $tipoTurno)
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
                        ->where('horario_medicos.tipo_turno', $tipoTurno)
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
                        ->where('horario_medicos.tipo_turno', $tipoTurno)
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
                        ->where('horario_medicos.tipo_turno', $tipoTurno)
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
                        ->where('horario_medicos.tipo_turno', $tipoTurno)
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
                        ->where('horario_medicos.tipo_turno', $tipoTurno)
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
                        ->where('horario_medicos.horario', '!=', '18:30')                                                
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
        $turnos = DB::table('horario_medicos')
                    ->where('horario_medicos.medico',$medico_id)
                    ->where('horario_medicos.consultorio', $consultorio_id)
                    ->where('horario_medicos.dia', $diaSeleccionado)
                    ->where('horario_medicos.activo', 1)                        
                    ->orderby('horario_medicos.horario')
                    ->get();
        return $turnos;        
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

    public function createJson($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada, $tipoTurno){        
        if($tipoTurno == 4){
            $turnos = DB::table('horario_medico_videollamadas')
                        ->where('horario_medico_videollamadas.medico',$medico_id)
                        ->where('horario_medico_videollamadas.consultorio', $consultorio_id)
                        ->where('horario_medico_videollamadas.dia', $diaSeleccionado)
                        ->where('horario_medico_videollamadas.activo', 1)                        
                        ->orderBy('horario_medico_videollamadas.horario')
                        ->get();
        } else {
            if($medico_id == 1 || $medico_id == 6 || $medico_id == 8 || $medico_id == 11 || $medico_id == 12 || $medico_id == 13 || $medico_id == 14 || $medico_id == 15 || $medico_id == 18  || $medico_id == 19 || $medico_id == 24 || $medico_id == 26){
                if($medico_id == 1)
                    $turnos = $this->checkTurnoLibreEspecialFlor($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);
                if($medico_id == 6)
                    $turnos = $this->checkTurnoLibreEspecialGuilleGariboldi($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);
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
                if($medico_id == 24)
                    $turnos = $this->checkTurnoLibreEspecialPabloPrado($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);
                if($medico_id == 26)
                    $turnos = $this->checkTurnoLibreEspecialSole($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);
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
                        ->where('turno_registrados.activo', 1)                        
                        ->get();        
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

     public function createJsonTurnosDobles($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada){
        $turnos = DB::table('horario_medicos')                                                                                                        
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.activo', 1)
                        ->orderBy('horario_medicos.horario', 'asc')                                        
                        ->get();

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
                        ->where('turno_registrados.activo', 1)
                        ->orderBy('turno_registrados.horario', 'asc')                                                                    
                        ->get();

        $json = array();        
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

    public function confirmarTurno(Request $request){
        $horario= $request->horario;
         
         return response()->json(array('horario'=>$horario));
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
                        ->where('turno_registrados.activo', 1)                                                                    
                        ->get();    
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
        $dia = $request->get('dia');
        $horario1 = $request->get('horario1');
        $horario2= $request->get('horario2');
        $fechaTurno_aux = explode('/',$request->get('fechaTurno'));        
        $fechaTurno = $fechaTurno_aux[2].'-'.$fechaTurno_aux[1].'-'.$fechaTurno_aux[0];              

        if($request->get('primerControl')==0)
            $primerControl = 'NO';
        else   
            $primerControl = 'SI';

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
                $turnoRegistrado = $this->registrarNuevoTurno($paciente_id, $medico_id, $consultorio_id, $dia, $horario1, $fechaTurno, 'SI', 0);
                $turnoRegistrado2 = $this->registrarNuevoTurno($paciente_id, $medico_id, $consultorio_id, $dia, $horario2, $fechaTurno, 'SI', 0);
                            
                return response()->json(array('turnoRegistrado'=>'1','horario'=>$horario1,'turno_id'=>$turnoRegistrado->id));
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

    public function registrarNuevoTurno($paciente_id, $medico_id, $consultorio, $diaSeleccionado, $horario, $nuevaFecha, $primerControl, $sobreturno){        
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
        $turnoRegistrado->tipo_turno = 0;
        $turnoRegistrado->cancelado_por = '';
        $turnoRegistrado->otorgado_por = 'Paciente';
        $turnoRegistrado->msj_enviado = 0;
        $turnoRegistrado->activo = 1;
        $turnoRegistrado->save();

        $this->vincularMedicoPaciente($medico_id, $paciente_id);
        $this->vincularSecretariaPaciente($paciente_id, $consultorio);

        return $turnoRegistrado;
    }

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

        if($request->get('primerControl') == 0)
            $primerControl = 'NO';
        else   
            $primerControl = 'SI';
        if($esVideollamada == 0 && $tipoTurno != 4) {            
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

            if($medico_id == 14) { // patricia sosa
                $vts = $this->validarTurnoUnaSemana($paciente_id, $fechaTurno, $medico_id, $consultorio_id);
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
                    $registrarTurno->cancelado_por = '';
                    $registrarTurno->otorgado_por = 'Paciente';
                    $registrarTurno->msj_enviado = 0;
                    $registrarTurno->activo = 1;       
                    $registrarTurno->save();
                    $this->vincularMedicoPaciente($medico_id, $paciente_id);
                    $this->vincularSecretariaPaciente($paciente_id, $consultorio_id);
                    
                    $medico = Medico::find($medico_id);
                    $paciente = Paciente::find($paciente_id);
                    $consultorio = Consultorio::find($consultorio_id);
                    return response()->json(array('consultorio'=>$consultorio,'paciente'=>$paciente,'medico'=>$medico,'datosTurno'=>$registrarTurno,'turnoRegistrado'=>'1','horario'=>$horario,'turno_id'=>$registrarTurno->id, 'esVideollamada'=>$esVideollamada));
                }       
            }   
        } else {
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

    public function cancelarTurno(Request $request){
        $turno_id = $request->get('turno_id');        
        $esVideollamada = $request->get('esVideollamada');  
        if($esVideollamada == 0) {
            $turno = turnoRegistrado::find($turno_id);
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
        $turno->comentario = 'Cancelado por paciente';
        $turno->activo = 0;
        $turno->save();

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
            

            return response()->json(array('turnosRegistrados'=>$someArray,'dni_paciente'=>$paciente_get->dni,'mensaje'=>$mensaje));
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
            if($medico->id == 1 || $medico->id == 5 || $medico->id == 11 || $medico->id == 12 || $medico->id == 13 || $medico->id == 14 || $medico->id == 15 || $medico->id == 18){
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
                    ->select('pacientes.nombre','pacientes.apellido','pacientes.telefono','pacientes.mail','turno_registrados.medico','turno_registrados.consultorio','turno_registrados.dia','turno_registrados.horario','turno_registrados.fechaTurno')
                    ->where('turno_registrados.fechaTurno',$date)    
                     ->where('turno_registrados.activo', 1)                  
                    ->get();    
      foreach($pacientes as $paciente) {
            if($paciente->mail != null) {
               $medico = DB::table('medicos')->where('medicos.id', $paciente->medico)->first();   
               $medicoNombre = $medico->apellido.', '.$medico->nombre;
               $consultorio = DB::table('consultorios')->where('consultorios.id', $paciente->consultorio)->first();   
               $data = array('nombre'=>$paciente->nombre,'medico'=>$medicoNombre,'horario'=>$paciente->horario,'direccion'=>$consultorio->direccion,'telefono'=>$consultorio->telefono);               
               Mail::to($paciente->mail)->queue(new SendMailable($data));
            }   
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
                    ->select('turno_registrados.id','pacientes.nombre','pacientes.apellido','pacientes.telefono','pacientes.mail','turno_registrados.medico','turno_registrados.consultorio','turno_registrados.dia','turno_registrados.horario','turno_registrados.fechaTurno','turno_registrados.primerControl', 'turno_registrados.msj_enviado')
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
                            if($diaSeleccionado == 1) {
                                $json['direccion'] = 'Luiggi 463';
                                $json['telefono_consultorio'] = "2914717327";                         
                            }
                            if($diaSeleccionado == 3) {
                                $json['direccion'] = 'Blandengues 505';
                                $json['telefono_consultorio'] = "2914717327";                         
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
                       
                       $data[] = $json;                                                
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

    function generarListadoDiaTobb($fecha, $medico) {
        /* $listadoPacientes = DB::table('turno_registrados')
                            ->join('pacientes','pacientes.id','=','turno_registrados.paciente')                    
                            ->select('turno_registrados.horario','turno_registrados.primerControl','turno_registrados.sobreturno', 'pacientes.*')
                            ->where('turno_registrados.medico', $medico)                    
                            ->where('turno_registrados.fechaTurno', $fecha)                    
                            ->where('turno_registrados.activo', 1)  
                            ->orderBy('turno_registrados.horario')
                            ->get(); */

        $listadoPacientes = DB::table('turno_registrados')
                            ->join('pacientes','pacientes.id','=','turno_registrados.paciente')                    
                            ->select('turno_registrados.horario','turno_registrados.primerControl','turno_registrados.sobreturno', 'pacientes.*')
                            ->where('turno_registrados.medico', 10000)                                                                            
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
                $newOs->save();
        }
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
        $turnoRegistrado->activo = 0;
        $turnoRegistrado->save();

        $medico = Medico::find($turnoRegistrado->medico);

        $fecha = $this->convertirFechaMostrar($turnoRegistrado->fechaTurno);
        return view('turnos.cancela_turno')->with('fecha',$fecha)->with('medico',$medico)->with('turnoRegistrado',$turnoRegistrado);
    }

}
