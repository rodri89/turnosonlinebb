<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Videollamada;
use App\TurnoRegistradoVideollamada;
use DateTime;

class VideollamadaController extends Controller
{

	public function __construct()
    {
        $this->middleware('auth');
    }
    
    function medicoVideollamadas(Request $request){
        $medico = $this->getMedico();    
        $videollamada = $this->existeVideollamada($medico->id); 
        
        $diasAtencion = $this->diasAtencion($medico->id, $medico->consultorio, 1);
        $dias_deshabilitados = $this->diasDeshabilitados($diasAtencion);        
        date_default_timezone_set('America/Argentina/Buenos_Aires');        
        $fecha_a = date("Y-m-d");        
        $fecha = date("d/m/Y");
        $diaSeleccionado = $this->getDiaSeleccionado($fecha_a);
        //$turnosRegistradosVideollamadas = $this->getTurnosDia($fecha);
        $data = $this->createJsonVideollamada($medico->id, $medico->consultorio, $diaSeleccionado, $fecha_a);
        $turnosRegistradosVideollamadas = json_decode($data, true);
        $this->removerMedicoDisponibleTodos($medico->id, $medico->consultorio);
        $isMedicoDisponible = $this->isMedicoDisponible($medico->id, $medico->consultorio);
        return View('turnos_admin_medico.admin_videollamadas')
                    ->with('medico',$medico)
                    ->with('dias_deshabilitados',$dias_deshabilitados)
                    ->with('fecha',$fecha)
                    ->with('isMedicoDisponible',$isMedicoDisponible)
                    ->with('turnosRegistradosVideollamadas',$turnosRegistradosVideollamadas)
                    ->with('videollamada',$videollamada);                    
    }   

    function convertirFechaParaMostrar($fecha_a){
    	$fecha_aux = explode('/',$fecha_a);
    	$fecha = $fecha_aux[2].'/'.$fecha_aux[1].'/'.$fecha_aux[0];
    	return $fecha;	
    } 

    function convertirFechaParaBuscar($fecha_a){
    	$fecha_aux = explode('/',$fecha_a);
    	$fecha = $fecha_aux[2].'-'.$fecha_aux[1].'-'.$fecha_aux[0];
    	return $fecha;		
    }

    function getTurnosDia($fecha){    	
        $usuario_actual=\Auth::user();        
        $medico = DB::table('medicos')                                                                
                ->where('medicos.user_id',$usuario_actual->id)                                                      
                ->first();
        $turnos = $this->getTurnosPaciente($medico->id, $medico->consultorio, $fecha);
        return $turnos;
    }

    public function getTurnosPaciente($medico_id, $consultorio, $fecha){
        $turnosPaciente = DB::table('turno_registrado_videollamadas')                                                      
                        ->join('pacientes','pacientes.id','=','turno_registrado_videollamadas.paciente')
                        ->select('turno_registrado_videollamadas.id as trid','pacientes.nombre as nombrep','pacientes.apellido as apellidop','turno_registrado_videollamadas.horario', 'turno_registrado_videollamadas.asistio','pacientes.dni','turno_registrado_videollamadas.sobreturno','turno_registrado_videollamadas.primerControl','pacientes.telefono','turno_registrado_videollamadas.comentario','turno_registrado_videollamadas.disponible','turno_registrado_videollamadas.fechaTurno')
                        ->where('turno_registrado_videollamadas.medico',$medico_id)
                        ->where('turno_registrado_videollamadas.consultorio', $consultorio)
                        ->where('turno_registrado_videollamadas.fechaTurno', $fecha)
                        ->where('turno_registrado_videollamadas.activo', 1)
                        ->distinct()
                        ->orderByRaw('turno_registrado_videollamadas.horario')
                        ->get();            
        return $turnosPaciente;
    }

    function guardarKeySecret(Request $request) {
        $medico_id = $request->medico_id;
        $key = $request->key;
        $secret = $request->secret;
        $consultorio = $request->consultorio;
        $perfil = $request->perfil;

        $existeVideollamada = $this->existeVideollamada($medico_id);

        if($existeVideollamada){
            $existeVideollamada->key = $key;
            $existeVideollamada->secret = $secret;
            $existeVideollamada->$perfil;
            $existeVideollamada->save();
            $videollamada = $existeVideollamada;
        } else {
            $videollamada = new Videollamada;
            $videollamada->medico = $medico_id;
            $videollamada->link = '';
            $videollamada->link_pago = '';
            $videollamada->key = $key;
            $videollamada->secret = $secret;
            $videollamada->consultorio = $consultorio;
            $videollamada->disponible = 0;
            $videollamada->perfil = 1;
            $videollamada->activo = 1;
            $videollamada->save();
        }
        return response()->json(array('response'=>1, 'videollamada'=>$videollamada));
    }

    function guardarLinkVideollamada(Request $request){
        $medico_id = $request->medico_id;
        $link = $request->link;
        $consultorio = $request->consultorio;

        $existeVideollamada = $this->existeVideollamada($medico_id);

        if($existeVideollamada){
        	$existeVideollamada->link = $link;
        	$existeVideollamada->save();
        	$videollamada = $existeVideollamada;
        } else {
	        $videollamada = new Videollamada;
	        $videollamada->medico = $medico_id;
	        $videollamada->link = $link;
            $videollamada->key = '';
            $videollamada->secret = '';
	        $videollamada->consultorio = $consultorio;
	        $videollamada->disponible = 0;
	        $videollamada->activo = 1;
	        $videollamada->save();
    	}
        return response()->json(array('response'=>1, 'videollamada'=>$videollamada));
    }

    public function getMedico(){
        $usuario_actual=\Auth::user();        
        $medico = DB::table('medicos')                                                                
                ->where('medicos.user_id',$usuario_actual->id)                                                      
                ->first();
        return $medico;
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

    public function medicoActualizarListadoVideollamadas(Request $request){    	
    	$medico = $this->getMedico();    
    	$fecha = $this->convertirFechaParaBuscar($request->dia);
    	$diaSeleccionado = $this->getDiaSeleccionado($fecha);
    	//$turno = $this->getTurnosDia($fecha);
    	$data = $this->createJsonVideollamada($medico->id, $medico->consultorio, $diaSeleccionado, $fecha);
    	$turno = json_decode($data, true);
        $isMedicoDisponible = $this->isMedicoDisponible($medico->id, $medico->consultorio);
    	return response()->json(array('response'=>1, 'fecha'=>$fecha, 'turno'=>$turno, 'isMedicoDisponible'=>$isMedicoDisponible));
    }

     public function medicoCancelarTurnoVideollamadasId(Request $request){
        $turno_id = $request->turno_id;
        $medico = $this->getMedico();    
        $fecha = $this->convertirFechaParaBuscar($request->dia);        
        $diaSeleccionado = $this->getDiaSeleccionado($fecha);              
        $findVideollamada = turnoRegistradoVideollamada::find($turno_id);  
        $findVideollamada->comentario = "Cancelado por medico";
        $findVideollamada->activo = 0;
        $findVideollamada->save();
        //$turno = $this->getTurnosDia($fecha);
        $data = $this->createJsonVideollamada($medico->id, $medico->consultorio, $diaSeleccionado, $fecha);
        $turno = json_decode($data, true);
        return response()->json(array('response'=>1, 'fecha'=>$fecha, 'turno'=>$turno));    
    } 

    public function medicoCancelarTurnoVideollamadas(Request $request){
    	$horario = $request->horario;
        $medico = $this->getMedico();    
    	$fecha = $this->convertirFechaParaBuscar($request->dia);    	
    	$diaSeleccionado = $this->getDiaSeleccionado($fecha);              
    	$findVideollamada = $this->registrarTurno($this->getPacienteCancelado()->id, $medico->id, $medico->consultorio, $diaSeleccionado, $horario, $fecha, 0);
    	//$turno = $this->getTurnosDia($fecha);
    	$data = $this->createJsonVideollamada($medico->id, $medico->consultorio, $diaSeleccionado, $fecha);
    	$turno = json_decode($data, true);
    	return response()->json(array('response'=>1, 'fecha'=>$fecha, 'turno'=>$turno));	
    }

    public function medicoDescancelarTurnoVideollamadas(Request $request){
        $horario = $request->horario;
        $medico = $this->getMedico();    
        $fecha = $this->convertirFechaParaBuscar($request->dia);        
        $diaSeleccionado = $this->getDiaSeleccionado($fecha);              
        $turno_id = $request->turno_id;
        $findVideollamada = turnoRegistradoVideollamada::find($turno_id);   
        $findVideollamada->delete();
        
        $data = $this->createJsonVideollamada($medico->id, $medico->consultorio, $diaSeleccionado, $fecha);
        $turno = json_decode($data, true);
        return response()->json(array('response'=>1, 'fecha'=>$fecha, 'turno'=>$turno));    
    }

    function medicoEnviarLinkVideollamadas(Request $request){
        $horario = $request->horario;
        $medico = $this->getMedico();    
        $fecha = $this->convertirFechaParaBuscar($request->dia);        
        $diaSeleccionado = $this->getDiaSeleccionado($fecha);              
        $turno_id = $request->turno_id;
        $opcion = $request->opcion;

        //$this->removerMedicoDisponibleTodos($medico->id, $medico->consultorio);

        $findVideollamada = turnoRegistradoVideollamada::find($turno_id);   
        if($opcion == 1){ // enviar link
            $findVideollamada->disponible_medico = 1;
            if($findVideollamada->disponible == 1)
                $findVideollamada->comentario = "Conectados";
            else
                $findVideollamada->comentario = "Medico Conectado";
        } else {   // finalizar
            $findVideollamada->disponible_medico = 0;
            if($findVideollamada->disponible == 1)
                $findVideollamada->comentario = "Finalizado";
            else
                $findVideollamada->comentario = "Paciente no se conecto";
        }   
        $findVideollamada->save();            
        $data = $this->createJsonVideollamada($medico->id, $medico->consultorio, $diaSeleccionado, $fecha);
        $turno = json_decode($data, true);
        $isMedicoDisponible = $this->isMedicoDisponible($medico->id, $medico->consultorio);
        return response()->json(array('response'=>1, 'fecha'=>$fecha, 'turno'=>$turno, 'isMedicoDisponible'=>$isMedicoDisponible));    
    }

    function isMedicoDisponible($medico_id, $consultorio_id){
         $turnosRegistrados = DB::table('turno_registrado_videollamadas')                                                
                                ->where('turno_registrado_videollamadas.medico',$medico_id)
                                ->where('turno_registrado_videollamadas.consultorio', $consultorio_id)                        
                                ->where('turno_registrado_videollamadas.disponible_medico', 1)
                                ->where('turno_registrado_videollamadas.activo', 1)                        
                                ->get();
        if($turnosRegistrados->count()>0)
            return 1;
        return 0;                                
    }    

    function removerMedicoDisponibleTodos($medico_id, $consultorio_id){
         $turnosRegistrados = DB::table('turno_registrado_videollamadas')                                                
                                ->where('turno_registrado_videollamadas.medico',$medico_id)
                                ->where('turno_registrado_videollamadas.consultorio', $consultorio_id)                        
                                ->where('turno_registrado_videollamadas.disponible_medico', 1)
                                ->where('turno_registrado_videollamadas.activo', 1)                        
                                ->get();
        foreach ($turnosRegistrados as $tr){
            $findVideollamada = turnoRegistradoVideollamada::find($tr->id);
            $findVideollamada->disponible_medico = 0;
            $findVideollamada->save();
        }                                 
    }

    function getPacienteCancelado(){
    	 $paciente = DB::table('pacientes')        				
                        ->where('pacientes.dni', 99999)                        
                        ->where('pacientes.activo', 1)                        
                        ->first();
        return $paciente;     
    }

    function registrarTurno($paciente_id, $medico_id, $consultorio_id, $dia, $horario, $fechaTurno, $sobreturno){
    	$registrarTurno = new turnoRegistradoVideollamada;
                    $registrarTurno->paciente = $paciente_id;
                    $registrarTurno->medico = $medico_id;        
                    $registrarTurno->consultorio = $consultorio_id;        
                    $registrarTurno->dia = $dia;
                    $registrarTurno->horario = $horario;       
                    $registrarTurno->fechaTurno = $fechaTurno;    
                    $registrarTurno->asistio = 0;
                    $registrarTurno->sobreturno = $sobreturno;
                    $registrarTurno->primerControl = 'NO';                    
                    $registrarTurno->comentario = '';
                    $registrarTurno->disponible = 0;
                    $registrarTurno->disponible_medico = 0;
                    $registrarTurno->pago = 0;
                    $registrarTurno->pago_ticket = '';
                    $registrarTurno->cargado = 0;       
                    $registrarTurno->activo = 1;       
                    $registrarTurno->save();

        return $registrarTurno;
    }

    function actualizarConfigVideollamada(Request $request){
        $findVideollamada = Videollamada::find($request->videollamada_id);
        if($request->importe != null)
            $findVideollamada->importe = $request->importe;
        if($request->link != null)
            $findVideollamada->link = $request->link;
        $findVideollamada->perfil = $request->perfil;
        $findVideollamada->save();

        return response()->json(array('response'=>1, 'videollamada'=>$findVideollamada));
    }

    // $esVideollamada 1 quiere decir que si, 0 que no
    public function diasAtencion($medico_id, $consultorio_id, $esVideollamada) {
        if($esVideollamada == 1){
            $diasAtencionAux = DB::select('select DISTINCT(dia) from horario_medico_videollamadas where consultorio= ? and medico = ? and activo=1 order by dia',    
            [$consultorio_id , $medico_id]);
        } else {
            $diasAtencionAux = DB::select('select DISTINCT(dia) from horario_medicos where consultorio= ? and medico = ? and activo=1 order by dia',    
            [$consultorio_id , $medico_id]);
        }

        $diasAtencion = array();        
        foreach ($diasAtencionAux as $valor){
            if($valor->dia == 1){                
                array_push($diasAtencion, 'Lunes: ');                
            } 
            if($valor->dia == 2){                
                array_push($diasAtencion, 'Martes: ');                
            } 
            if($valor->dia == 3){                
                array_push($diasAtencion, 'Miercoles: ');                
            } 
            if($valor->dia == 4){                
                array_push($diasAtencion, 'Jueves: ');        
            } 
            if($valor->dia == 5){               
                array_push($diasAtencion, 'Viernes: ');                
            } 
            if($valor->dia == 6){
                array_push($diasAtencion, 'Sabado');
            } 
            if($valor->dia == 7){
                array_push($diasAtencion, 'Domingo');
            } 
        }    

        return $diasAtencion;    
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


    public function createJsonVideollamada($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada){
        $turnos = DB::table('horario_medico_videollamadas')        				                 
                        ->where('horario_medico_videollamadas.medico',$medico_id)
                        ->where('horario_medico_videollamadas.consultorio', $consultorio_id)
                        ->where('horario_medico_videollamadas.dia', $diaSeleccionado)
                        ->where('horario_medico_videollamadas.activo', 1)                        
                        ->orderBy('horario_medico_videollamadas.horario')                        
                        ->get();

        $turnosRegistrados = DB::table('turno_registrado_videollamadas')
        				->join('pacientes','pacientes.id','=','turno_registrado_videollamadas.paciente')
                        ->select('turno_registrado_videollamadas.id as trid','pacientes.apellido', 'pacientes.nombre', 'turno_registrado_videollamadas.disponible', 'turno_registrado_videollamadas.disponible_medico', 'turno_registrado_videollamadas.horario', 'pacientes.dni','turno_registrado_videollamadas.comentario')
                        ->where('turno_registrado_videollamadas.medico',$medico_id)
                        ->where('turno_registrado_videollamadas.consultorio', $consultorio_id)                        
                        ->where('turno_registrado_videollamadas.fechaTurno', $fechaSolicitada)
                        ->where('turno_registrado_videollamadas.activo', 1)                        
                        ->get();                                
        $json = array();
        $data = array();
        $contador=0;
        $encontre=0;
        foreach($turnos as $turno) {                                    
            while (($encontre < 1) && ($contador<count($turnosRegistrados))){            
                if(strcmp ($turno->horario , $turnosRegistrados[$contador]->horario ) == 0){
                    $encontre=1;                    
                    $json['trid'] = $turnosRegistrados[$contador]->trid;
                    $json['paciente'] = $turnosRegistrados[$contador]->apellido.', '. $turnosRegistrados[$contador]->nombre;
                	$json['disponible'] = $turnosRegistrados[$contador]->disponible;
                    $json['disponible_medico'] = $turnosRegistrados[$contador]->disponible_medico;
                    $json['comentario'] = $turnosRegistrados[$contador]->comentario;
                    $json['horario'] = $turno->horario;
                    $json['dni'] =  $turnosRegistrados[$contador]->dni;
                    $json['libre'] = 0;                    
                    $data[] = $json;
                                                                                                
                } else {                                                            
                    $encontre=0;                    
                }
                $contador++;
            }
            if($encontre == 0){
                $json['paciente'] = 'LIBRE';               
                $json['horario'] = $turno->horario;                
                $json['libre'] = 1;                    
                $data[] = $json;
            }            
            $encontre=0;
            $contador=0;
        }

        $turnosRegistrados2 = DB::table('turno_registrado_videollamadas')
                        ->join('pacientes','pacientes.id','=','turno_registrado_videollamadas.paciente')
                        ->select('turno_registrado_videollamadas.id as trid','pacientes.apellido', 'pacientes.nombre', 'turno_registrado_videollamadas.disponible', 'turno_registrado_videollamadas.disponible_medico', 'turno_registrado_videollamadas.horario', 'pacientes.dni','turno_registrado_videollamadas.comentario')
                        ->where('turno_registrado_videollamadas.sobreturno', 1)
                        ->where('turno_registrado_videollamadas.medico',$medico_id)
                        ->where('turno_registrado_videollamadas.consultorio', $consultorio_id)                        
                        ->where('turno_registrado_videollamadas.fechaTurno', $fechaSolicitada)
                        ->where('turno_registrado_videollamadas.activo', 1)                        
                        ->get();    
        foreach($turnosRegistrados2 as $turno) { 
            $json['trid'] = $turno->trid;
            $json['paciente'] = $turno->apellido.', '. $turno->nombre;
            $json['disponible'] = $turno->disponible;
            $json['disponible_medico'] = $turno->disponible_medico;
            $json['comentario'] = $turno->comentario;
            $json['horario'] = $turno->horario;
            $json['dni'] =  $turno->dni;
            $json['libre'] = 0;                    
            $data[] = $json;
        }
        return json_encode($data);
        
    }

    public function getDiaSeleccionado($fecha){        
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

    public function historialVideollamadaList(){
        $medico = $this->getMedico();

        $videollamadas = DB::table('turno_registrado_videollamadas')                     
                    ->join('pacientes','pacientes.id','=','turno_registrado_videollamadas.paciente')                                       
                    ->select('turno_registrado_videollamadas.id as trvid','turno_registrado_videollamadas.fechaTurno','turno_registrado_videollamadas.horario','pacientes.nombre','pacientes.apellido','pacientes.obra_social', 'pacientes.obra_social_foto','pacientes.numero_afiliado','pacientes.obra_social_plan','turno_registrado_videollamadas.cargado')
                    ->where('turno_registrado_videollamadas.medico',$medico->id)                            
                    ->where('turno_registrado_videollamadas.activo',1)
                    ->where('pacientes.activo',1)                    
                    ->orderby('turno_registrado_videollamadas.fechaTurno','asc');
         
        return datatables()->of($videollamadas)
                           ->addIndexColumn()
                           ->addColumn('fecha', function($row){                                                            
                               $fechaAux = explode('-',$row->fechaTurno);                               
                               $solicitud = $fechaAux[2].'/'.$fechaAux[1].'/'.$fechaAux[0];                        
                               return $solicitud;
                            })
                           ->addColumn('paciente', function($row){                                             
                               $paciente = $row->apellido.', '.$row->nombre;                        
                               return $paciente;
                            })                           
                           ->addColumn('action', function($row){                                   
                               $val = $row->trvid;
                               $btn = "<button onclick=actualizarCargado($val) class='rodri_button_aceptar'>Cargado</button>";
                               return $btn;
                            })
                           ->addColumn('foto', function($row){                                
                               $val = $row->obra_social_foto;                                
                               $btn = "";
                               if($row->obra_social_foto != null && strcmp($row->obra_social_foto, '') != 0){
                                $btn = "<button onclick=verCarnetObraSocial('$val') class='rodri_button_aceptar'>Ver</button>";
                               }
                                return $btn;
                            })
                           ->addColumn('cargado', function($row){                                
                               $val = $row->cargado;                                
                               if($val == 0)
                                return 'NO';
                               else
                                return 'SI';
                            })
                            ->rawColumns(['action','foto','fecha','paciente','cargado'])
                            ->make(true);        
    }

    public function historialVideollamadas(Request $request){        
        return View('turnos_admin_medico.historial_videollamadas');
    }

    public function medicoActualizarCargado(Request $request){
        $findTurno = TurnoRegistradoVideollamada::find($request->turno_id);
        $findTurno->cargado = $request->estado;
        $findTurno->save();

        return response()->json(array('response'=>1));        
    }

    public function turnoExiste($medico, $horario, $fecha){
        $turno = DB::table('turno_registrado_videollamadas')
                    ->where('turno_registrado_videollamadas.medico', $medico)
                    ->where('turno_registrado_videollamadas.horario', $horario)
                    ->where('turno_registrado_videollamadas.fechaTurno', $fecha)
                    ->where('turno_registrado_videollamadas.activo', 1)
                    ->first();
        if($turno!=null)
            return 1;
        else
            return 0;
    }

    public function agregarSobreturnoVideollamada(Request $request){
        $paciente = DB::table('pacientes')
                    ->where('pacientes.dni', $request->paciente_dni)
                    ->where('pacientes.activo', 1)
                    ->first();
       // $medico_id = $request->medico_id;
        $horario = $request->horario;            
                                            
        if($paciente == null){
             return response()->json(array('response'=>0));  
        } else {
            
            $medico = $this->getMedico();    
            $fecha = $this->convertirFechaParaBuscar($request->fecha);
            $diaSeleccionado = $this->getDiaSeleccionado($fecha);
            if($this->turnoExiste($medico->id, $horario, $fecha) == 1){
                return response()->json(array('response'=>2));  
            } else {
                $this->registrarTurno($paciente->id, $medico->id, $medico->consultorio, $diaSeleccionado, $horario, $fecha, 1);
                $data = $this->createJsonVideollamada($medico->id, $medico->consultorio, $diaSeleccionado, $fecha);
                $turno = json_decode($data, true);
                $isMedicoDisponible = $this->isMedicoDisponible($medico->id, $medico->consultorio);
                return response()->json(array('response'=>1, 'fecha'=>$fecha, 'turno'=>$turno, 'isMedicoDisponible'=>$isMedicoDisponible));
           }
        }
    }
}
