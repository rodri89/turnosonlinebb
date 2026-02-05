<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\SecretariaConsultorio;
use App\User;
use App\Paciente;
use App\Medico;
use App\Secretaria;
use App\TurnoRegistrado;
use App\TurnoRegistradoVideollamada;
use App\PacienteSecretaria;
use DateTime;
use \stdClass;
use Mail;
use App\Mail\ActivarPacienteMailable;
use Datatables;
use App\MedicoPaciente;

class SecretariaController extends Controller
{
    
    public function __construct()
    {    	
        $this->middleware('auth');
    }

    public function activarPaciente(Request $request){
    	$paciente = paciente::find($request->get('paciente'));
    	$paciente->activo = 1;
    	$paciente->save();

    	$pacienteSec = pacienteSecretaria::find($request->get('pacienteSecretariaId'));
    	$pacienteSec->activo = 1;
    	$pacienteSec->save();

		$usuario_actual = \Auth::user(); //obtengo los datos del usuario en sesion
		$usuario_id = $usuario_actual->id;		

		$secretaria = DB::table('secretarias')->where('user_id', $usuario_id)->get();		
		$consultorio = DB::table('secretaria_consultorios')->where('secretaria_id', $secretaria[0]->id)->get();						
		$pacientes = DB::table('paciente_secretarias')						
					->join('pacientes','pacientes.id','=','paciente_secretarias.paciente')					
					->select('pacientes.id','pacientes.nombre','pacientes.apellido','pacientes.telefono','pacientes.dni','paciente_secretarias.id as pac_id', 'pacientes.mail')
					->where('paciente_secretarias.consultorio',$consultorio[0]->consultorio_id)
					->where('paciente_secretarias.activo',0)
					->where('pacientes.activo',2)
					->get();

		if($paciente->mail != null){
            $data = array('nombre'=>$paciente->nombre,'apellido'=>$paciente->apellido);
            Mail::to($paciente->mail)->queue(new ActivarPacienteMailable($data));
        }

    	return response()->json(array('pacientes'=>$pacientes));
    }

    public function adminPacientes(){
    	$usuario_actual = \Auth::user(); //obtengo los datos del usuario en sesion
		$usuario_id = $usuario_actual->id;		

		$secretaria = DB::table('secretarias')->where('user_id', $usuario_id)->get();		
		$consultorio = DB::table('secretaria_consultorios')->where('secretaria_id', $secretaria[0]->id)->get();						
		$pacientes = DB::table('paciente_secretarias')						
					->join('pacientes','pacientes.id','=','paciente_secretarias.paciente')					
					->select('pacientes.id','pacientes.nombre','pacientes.apellido','pacientes.telefono','pacientes.dni','paciente_secretarias.id as pac_id', 'pacientes.mail')
					->where('paciente_secretarias.consultorio',$consultorio[0]->consultorio_id)
					->where('paciente_secretarias.activo',0)
					->where('pacientes.activo',2)
					->get();

    	return View('turnos_admin_secretaria.alta_paciente')
    	->with('pacientes',$pacientes); 	
    }

    public function AltaSecretariaForm(Request $request)
    {
		$secretarias= DB::table('secretarias')->get();
		$consultorios= DB::table('consultorios')->where('consultorios.activo', 1)->get();

    	return View('turnos_admin.admin_secretaria')
    	->with('secretarias',$secretarias)
    	->with('consultorios',$consultorios);         
    }
    
    public function vincularSecretariaConsultorio(Request $request)
    {
    	
		$consultorio_aux = explode('-',$request->get('consultorio'));
		$secretaria_aux = explode('-',$request->get('secretaria'));

		$sec_consul = new secretariaConsultorio;
		$sec_consul->consultorio_id = $consultorio_aux[0];
		$sec_consul->secretaria_id = $secretaria_aux[0];    
		$sec_consul->activo = 1;    
    	$sec_consul->save();


    	$secretarias= DB::table('secretarias')->get();
		$consultorios= DB::table('consultorios')->where('consultorios.activo', 1)->get();

    	return View('turnos_admin.admin_secretaria')
    	->with('secretarias',$secretarias)
    	->with('consultorios',$consultorios);   
    }

    public function seleccionarConsultorio(Request $request){
    	$usuario_actual = \Auth::user(); //obtengo los datos del usuario en sesion
		$usuario_id = $usuario_actual->id;
		$option = $request->option;
		$paciente_dni = $request->paciente_dni;
		$consultorios = DB::table('secretaria_consultorios')
						->join('secretarias','secretarias.id','=','secretaria_consultorios.secretaria_id')
	                    ->join('consultorios','consultorios.id','=','secretaria_consultorios.consultorio_id')                    
	                    ->select('consultorios.id','consultorios.direccion')
	                    ->where('secretarias.user_id', $usuario_id)
	                  	->where('consultorios.activo', 1)
						->where('secretaria_consultorios.activo', 1)
	                    ->get();
	    $aux_cont = $consultorios->count();
	    if($aux_cont>1){	                    
			return view('turnos_admin_secretaria/seleccionar_consultorio')
			->with('consultorios',$consultorios)
			->with('option', $option)
			->with('mensaje','');
    	} else {
    		$consultorio = $consultorios->first()->id;
    		$medicos = DB::table('medicos')						                   	                    
	                    ->where('medicos.consultorio', $consultorio)
	                    ->where('medicos.activo', 1)
	                    ->get();	
    		return view('turnos_admin_secretaria/seleccionar_medico')
			   ->with('consultorio',$consultorio)
			   ->with('option', $option)
			   ->with('paciente_dni', $paciente_dni)
			   ->with('medicos',$medicos);
    	}

	}

	public function showMedicosConsultorio(Request $request){
		$option = $request->option;
		$consultorio = $request->get('consultorio');
		$medicos = DB::table('medicos')						                   	                    
	                    ->where('medicos.consultorio', $consultorio)
	                    ->where('medicos.activo', 1)	                                      
	                    ->get();
		return view('turnos_admin_secretaria/seleccionar_medico')
			   ->with('consultorio',$consultorio)
			   ->with('option', $option)
			   ->with('medicos',$medicos);
	}

	public function obtenerMedicoPerfil($medicos){
		
	}

	public function adminSobreturnos(Request $request){		
		date_default_timezone_set('America/Argentina/Buenos_Aires');
        $dia = date("d/m/Y");		
        $dia_aux = date("Y/m/d");
		//$dia='10/07/2019';
		$paciente_dni = $request->paciente_dni;
		$medico_id = $request->get('medico_id');
		$medico = medico::find($request->get('medico_id'));
		$consultorio = $request->get('consultorio');
		$turnosPaciente = DB::table('turno_registrados')						                              
						->join('pacientes','pacientes.id','=','turno_registrados.paciente')
						->select('turno_registrados.id as trid','pacientes.nombre as nombrep','pacientes.apellido as apellidop','turno_registrados.horario', 'turno_registrados.asistio','pacientes.dni','turno_registrados.sobreturno', 'turno_registrados.tipo_turno', 'turno_registrados.comentario')
	                    ->where('turno_registrados.medico',$medico_id)
	                   	->where('turno_registrados.consultorio', $consultorio)
	                   	->where('turno_registrados.fechaTurno', $dia_aux)
	                   	->where('turno_registrados.activo', 1)
	                   	->distinct()
	                   	->orderByRaw('turno_registrados.horario')
	                    ->get();
		$contador = $turnosPaciente->count();	

		$dias_deshabilitados = $this->getDiasDeshabilitados($medico_id,$consultorio, 0);
		$cantSobreturnos = $this->getCantidadSobreturnos($medico_id, $consultorio, $dia_aux);
		$obrasSociales = DB::table('obra_socials')                                                                
	                    ->where('obra_socials.activo', 1)                                                      
	                    ->orderBy('obra_socials.nombre')
	                    ->get();
		$esFeriado = $this->esFeriado($dia_aux);
		$moduloAfiliadoObligatorio = $this->moduloActivo($medico->id, 8);

		return view('turnos_admin_secretaria.admin_sobreturnos')
				->with('turnosPaciente',$turnosPaciente)
				->with('tipo_estudio', 0)				
				->with('obraSociales',$obrasSociales)
				->with('medico',$medico)
				->with('consultorio',$consultorio)
				->with('contador',$contador)
				->with('paciente_dni',$paciente_dni)
				->with('dias_deshabilitados',$dias_deshabilitados)
				->with('moduloAfiliadoObligatorio',$moduloAfiliadoObligatorio)
				->with('cantSobreturnos',$cantSobreturnos)
				->with('esFeriado',$esFeriado)
				->with('dia',$dia);		
	}

	public function getCantidadSobreturnos($medico, $consultorio, $fecha){
        $turnosPaciente = DB::table('turno_registrados')                                                      
                        ->where('turno_registrados.medico',$medico)
                        ->where('turno_registrados.consultorio', $consultorio)
                        ->where('turno_registrados.fechaTurno', $fecha)
                        ->where('turno_registrados.sobreturno', 1)
                        ->where('turno_registrados.activo', 1)
                        ->distinct()
                        ->get();
        return $turnosPaciente->count();
    }

    public function getCantidadSobreturnosPeg($medico, $consultorio, $fecha){
        $turnosPaciente = DB::table('turno_registrado_videollamadas')                                                      
                        ->where('turno_registrado_videollamadas.medico',$medico)                        
                        ->where('turno_registrado_videollamadas.fechaTurno', $fecha)
                        ->where('turno_registrado_videollamadas.sobreturno', 1)
                        ->where('turno_registrado_videollamadas.activo', 1)
                        ->distinct()
                        ->get();
        return $turnosPaciente->count();
    }

    function secretariaAsignarTurnosPeg(Request $request){  
    	$tipo_estudio = $request->tipo_estudio;  
    	date_default_timezone_set('America/Argentina/Buenos_Aires');
    	$fechaSeleccionada = date("d/m/Y");

		$medico_id = $request->get('medico_id');
		$medico = medico::find($request->get('medico_id'));
		$consultorio = $request->get('consultorio');
		$dias_deshabilitados = $this->getDiasDeshabilitados($medico_id, $consultorio, 1);
		$paciente_dni = $request->paciente_dni;

		$fechaAux = explode('/',$fechaSeleccionada);
		$dia = $fechaAux[0];
		$mes = $fechaAux[1];
		$anio = $fechaAux[2];
		$nuevaFecha = $anio.'-'.$mes.'-'.$dia;
		$date = new DateTime($nuevaFecha);		
		
		$diaLetras = date_format($date, 'l');
		
		$dia=$nuevaFecha;						 
		$dia = date("d/m/Y", strtotime ( '-0 hour' , strtotime ($dia) )); //formato 07/03/2019
						
		$diaSeleccionado = $this->getDiaSeleccionado($diaLetras);
		
		//$dia = '14/08/2019';
		if($tipo_estudio == 1){		
 			$data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $nuevaFecha, 0);
 			$tipo_estudio = 0;
        } else {
        	$tipo_estudio = 1;
        	$data = $this-> createJsonPeg($medico_id, $consultorio, $diaSeleccionado, $nuevaFecha, 1);
        }

    	$someArray = json_decode($data, true);

    	$fechaLibreDisponible = $this->turnoLibreMasCercano($request->get('medico_id'), 0, 1);
		$fechaLibreDisponible_aux = explode('-',$fechaLibreDisponible);
		$fechaLibreDisponible = $fechaLibreDisponible_aux[2].'/'.$fechaLibreDisponible_aux[1].'/'.$fechaLibreDisponible_aux[0];

		   // Modulo id: 3 | corresponde a Primer Control Doble
        $moduloPrimerControlDoble = $this->moduloActivo($medico->id, 3);
        $esFeriado = $this->esFeriado($nuevaFecha);
        $obrasSociales = DB::table('obra_socials')                                                                
                        ->where('obra_socials.activo', 1)
                        ->orderBy('obra_socials.nombre')                                                      
                        ->get();

			return view('turnos_admin_secretaria.asignar_turnos_peg')				
        		->with('turnos',$someArray)
        		->with('tipo_estudio', $tipo_estudio)
				->with('medico',$medico)
				->with('obraSociales',$obrasSociales)
				->with('consultorio',$consultorio)
				->with('fechaLibreDisponible',$fechaLibreDisponible)
				->with('primerControl','NO')		
				->with('dias_deshabilitados',$dias_deshabilitados)
				->with('paciente_dni',$paciente_dni)
				->with('moduloPrimerControlDoble',$moduloPrimerControlDoble)
				->with('esFeriado',$esFeriado)
				->with('dia',$dia);	
    }

    function secretariaAsignarSobreturnosPeg(Request $request){  
    	$tipo_estudio = $request->tipo_estudio;  
    	date_default_timezone_set('America/Argentina/Buenos_Aires');
        $dia = date("d/m/Y");		
        $dia_aux = date("Y/m/d");
        $dia_aux_turno = date("Y-m-d");
		//$dia='10/07/2019';
		$paciente_dni = $request->paciente_dni;
		$medico_id = $request->get('medico_id');
		$medico = medico::find($request->get('medico_id'));
		$consultorio = $request->get('consultorio');
		$turnosPaciente = DB::table('turno_registrado_videollamadas')						                              
						->join('pacientes','pacientes.id','=','turno_registrado_videollamadas.paciente')
						->select('turno_registrado_videollamadas.id as trid','pacientes.nombre as nombrep','pacientes.apellido as apellidop','turno_registrado_videollamadas.horario', 'turno_registrado_videollamadas.asistio','pacientes.dni','turno_registrado_videollamadas.sobreturno', 'turno_registrado_videollamadas.comentario')
	                    ->where('turno_registrado_videollamadas.medico',$medico_id)
	                   	->where('turno_registrado_videollamadas.consultorio', $consultorio)
	                   	->where('turno_registrado_videollamadas.fechaTurno', $dia_aux_turno)
	                   	->where('turno_registrado_videollamadas.activo', 1)
	                   	->distinct()
	                   	->orderByRaw('turno_registrado_videollamadas.horario')
	                    ->get();
		$contador = $turnosPaciente->count();	

		$dias_deshabilitados = $this->getDiasDeshabilitados($medico_id,$consultorio, 1);
		$cantSobreturnos = $this->getCantidadSobreturnosPeg($medico_id, $consultorio, $dia_aux);
		
		$obrasSociales = DB::table('obra_socials')                                                                
	                    ->where('obra_socials.activo', 1)                                                      
	                    ->orderBy('obra_socials.nombre')
	                    ->get();
		$esFeriado = $this->esFeriado($dia_aux);
		return view('turnos_admin_secretaria.admin_sobreturnos_peg')
				->with('turnosPaciente',$turnosPaciente)
				->with('tipo_estudio', 1)
				->with('obraSociales',$obrasSociales)
				->with('medico',$medico)
				->with('consultorio',$consultorio)
				->with('contador',$contador)
				->with('paciente_dni',$paciente_dni)
				->with('dias_deshabilitados',$dias_deshabilitados)
				->with('cantSobreturnos',$cantSobreturnos)
				->with('esFeriado',$esFeriado)
				->with('dia',$dia);
    }
	
	public function secretariaAsignarTurnos(Request $request){
		date_default_timezone_set('America/Argentina/Buenos_Aires');
    	$fechaSeleccionada = date("d/m/Y");

		$medico_id = $request->get('medico_id');
		$medico = medico::find($request->get('medico_id'));
		$consultorio = $request->get('consultorio');
		$dias_deshabilitados = $this->getDiasDeshabilitados($medico_id,$consultorio, 0);
		$paciente_dni = $request->paciente_dni;

		$fechaAux = explode('/',$fechaSeleccionada);
		$dia = $fechaAux[0];
		$mes = $fechaAux[1];
		$anio = $fechaAux[2];
		$nuevaFecha = $anio.'-'.$mes.'-'.$dia;
		$date = new DateTime($nuevaFecha);		
		
		$diaLetras = date_format($date, 'l');
		
		$dia=$nuevaFecha;						 
		$dia = date("d/m/Y", strtotime ( '-0 hour' , strtotime ($dia) )); //formato 07/03/2019
						
		$diaSeleccionado = $this->getDiaSeleccionado($diaLetras);
		
		$turnosAux = DB::select('select * from horario_medicos where medico = ? and consultorio = ? and dia = ? and activo=1',
			[$medico_id , $consultorio, $diaSeleccionado]);
		
		//$dia = '14/08/2019';
				
 		$data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $nuevaFecha, 0);
        
    	$someArray = json_decode($data, true);

    	$fechaLibreDisponible = $this->turnoLibreMasCercano($request->get('medico_id'), 0, 0);
		$fechaLibreDisponible_aux = explode('-',$fechaLibreDisponible);
		$fechaLibreDisponible = $fechaLibreDisponible_aux[2].'/'.$fechaLibreDisponible_aux[1].'/'.$fechaLibreDisponible_aux[0];

		   // Modulo id: 3 | corresponde a Primer Control Doble
        $moduloPrimerControlDoble = $this->moduloActivo($medico->id, 3);
        $moduloAfiliadoObligatorio = $this->moduloActivo($medico->id, 8);
        $esFeriado = $this->esFeriado($nuevaFecha);
        $obrasSociales = DB::table('obra_socials')                                                                
                        ->where('obra_socials.activo', 1)
                        ->orderBy('obra_socials.nombre')                                                      
                        ->get();

			return view('turnos_admin_secretaria.secretaria_asignar_turnos')				
        		->with('turnos',$someArray)
				->with('tipo_estudio', 0)
				->with('medico',$medico)
				->with('obraSociales',$obrasSociales)
				->with('consultorio',$consultorio)
				->with('fechaLibreDisponible',$fechaLibreDisponible)
				->with('primerControl','NO')		
				->with('dias_deshabilitados',$dias_deshabilitados)
				->with('paciente_dni',$paciente_dni)
				->with('moduloPrimerControlDoble',$moduloPrimerControlDoble)
				->with('moduloAfiliadoObligatorio',$moduloAfiliadoObligatorio)
				->with('esFeriado',$esFeriado)
				->with('dia',$dia);	
	}

	public function actualizarAsignarTurnos(Request $request){
		$tipo_estudio = $request->tipo_estudio;
    	$fechaSeleccionada = $request->fecha;		//14/08/2019
        $medico_id = $request->medico_id;
		$consultorio = $request->consultorio;
		$primerControl = $request->primerControl;

		$fechaAux = explode('/',$fechaSeleccionada);
		$dia = $fechaAux[0];
		$mes = $fechaAux[1];
		$anio = $fechaAux[2];
		$nuevaFecha=$anio.'-'.$mes.'-'.$dia;
		$date = new DateTime($nuevaFecha);		
		
		$diaLetras = date_format($date, 'l');
				
		//$dia = date("d/m/Y", strtotime ( '-3 hour' , strtotime ($date) )); //formato 07/03/2019
						
		$diaSeleccionado = $this->getDiaSeleccionado($diaLetras);	
		
		// es peg
		if($tipo_estudio == 1){			
 		   $data = $this-> createJsonPeg($medico_id, $consultorio, $diaSeleccionado, $nuevaFecha, 0); 		   
 		   $fechaLibreDisponible = $this->turnoLibreMasCercano($request->get('medico_id'), 1, 1);
 		   $fechaLibreDisponible = $this->convertirFechaParaMostrar($fechaLibreDisponible);	        
		} else {
			if($primerControl==0){
	 		   $data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $nuevaFecha, 0); 		   
	 		   $fechaLibreDisponible = $this->turnoLibreMasCercano($request->get('medico_id'), 0, 0);
	 		   $fechaLibreDisponible = $this->convertirFechaParaMostrar($fechaLibreDisponible);
	        } else {
	            //turnos especiales cada 1 hora.         		   
	            $data = $this-> createJsonTurnosDobles($medico_id, $consultorio, $diaSeleccionado, $nuevaFecha);
	            $fechaLibreDisponible = $this->turnoLibreMasCercano($request->get('medico_id'), 1, 0);
	            $fechaLibreDisponible = $this->convertirFechaParaMostrar($fechaLibreDisponible);
	    	}
    	}
    	$someArray = json_decode($data, true);    	
    	$esFeriado = $this->esFeriado($nuevaFecha);

        return response()->json(array('turnosPaciente'=>$someArray,'medico_id'=>$medico_id,'consultorio'=>$consultorio,'dia'=>$dia, 'primerControl'=>$primerControl, 'fechaLibreMasCercana'=>$fechaLibreDisponible, 'esFeriado'=>$esFeriado));
	}

	// Me devuelve el feriado, debo validar si es distinto de null.
    public function esFeriado($fecha){
        $validarFeriado = DB::table('feriados')                                                             
                        ->where('feriados.fecha', '=', $fecha)                        
                        ->first();        
        return $validarFeriado;
    }

    function asignarTurnoCancelarPeg(Request $request) {
    	$fechaSeleccionada = $request->fecha;       //14/08/2019
        $medico_id = $request->medico_id;
        $consultorio = $request->consultorio;
        $primerControl = $request->primerControl;
        $horario = $request->horario;
        $canceladoPor = $request->canceladoPor;

        $secretariaCancel = $this->getSecretaria(\Auth::user()->id);
        if($secretariaCancel != null){
        	$canceladoPor = $secretariaCancel->apellido.', '.$secretariaCancel->nombre;
        }
 		
 		$fechaAux = explode('/',$fechaSeleccionada);
        $dia = $fechaAux[0];
        $mes = $fechaAux[1];
        $anio = $fechaAux[2];
        $nuevaFecha = $anio.'-'.$mes.'-'.$dia;
        
        $turnoCancelar = DB::table('turno_registrado_videollamadas')						                   	                    	
	                    ->where('turno_registrado_videollamadas.medico', $medico_id)
	                    ->where('turno_registrado_videollamadas.fechaTurno', $nuevaFecha)
	                    ->where('turno_registrado_videollamadas.horario', $horario)
	                    ->where('turno_registrado_videollamadas.activo', 1)	                                      
	                    ->first();
	     if($turnoCancelar!=null){
			$t = TurnoRegistradoVideollamada::find($turnoCancelar->id);
	     	$t->activo = 0;
	     	$t->comentario = $turnoCancelar->comentario.' - Cancelado por: '.$canceladoPor;
	     	$t->save();
	     }

        $date = new DateTime($nuevaFecha);      
        
        $diaLetras = date_format($date, 'l');
                
        //$dia = date("d/m/Y", strtotime ( '-3 hour' , strtotime ($date) )); //formato 07/03/2019
                        
        $diaSeleccionado = $this->getDiaSeleccionado($diaLetras);       

        if($primerControl==0){
           $data = $this-> createJsonPeg($medico_id, $consultorio, $diaSeleccionado, $nuevaFecha, 1);
           $fechaLibreDisponible = $this->turnoLibreMasCercano($request->get('medico_id'), 0, 0);
           $fechaLibreDisponible = $this->convertirFechaParaMostrar($fechaLibreDisponible);
        } 
        $someArray = json_decode($data, true);      
        return response()->json(array('turnosPaciente'=>$someArray,'medico_id'=>$medico_id,'consultorio'=>$consultorio,'dia'=>$dia, 'primerControl'=>$primerControl, 'fechaLibreMasCercana'=>$fechaLibreDisponible));
    }

	public function asignarTurnoCancelar(Request $request){
        $fechaSeleccionada = $request->fecha;       //14/08/2019
        $medico_id = $request->medico_id;
        $consultorio = $request->consultorio;
        $primerControl = $request->primerControl;
        $horario = $request->horario;
        $canceladoPor = $request->canceladoPor;

        $secretariaCancel = $this->getSecretaria(\Auth::user()->id);
        if($secretariaCancel != null){
        	$canceladoPor = $secretariaCancel->apellido.', '.$secretariaCancel->nombre;
        }
 		
 		$fechaAux = explode('/',$fechaSeleccionada);
        $dia = $fechaAux[0];
        $mes = $fechaAux[1];
        $anio = $fechaAux[2];
        $nuevaFecha = $anio.'-'.$mes.'-'.$dia;
        
        $turnoCancelar = DB::table('turno_registrados')						                   	                    
	                    ->where('turno_registrados.consultorio', $consultorio)
	                    ->where('turno_registrados.medico', $medico_id)
	                    ->where('turno_registrados.fechaTurno', $nuevaFecha)
	                    ->where('turno_registrados.horario', $horario)
	                    ->where('turno_registrados.activo', 1)	                                      
	                    ->first();
	     if($turnoCancelar!=null){
			$t = turnoRegistrado::find($turnoCancelar->id);
	     	$t->activo = 0;
	     	$t->comentario = $turnoCancelar->comentario.' - Cancelado por: '.$canceladoPor;
	     	$t->save();
	     }

        $date = new DateTime($nuevaFecha);      
        
        $diaLetras = date_format($date, 'l');
                
        //$dia = date("d/m/Y", strtotime ( '-3 hour' , strtotime ($date) )); //formato 07/03/2019
                        
        $diaSeleccionado = $this->getDiaSeleccionado($diaLetras);

        $turnosAux = DB::select('select * from horario_medicos where medico = ? and consultorio = ? and dia = ? and activo=1',
            [$medico_id , $consultorio, $diaSeleccionado]); 

        if($primerControl==0){
           $data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $nuevaFecha, 0);         
           $fechaLibreDisponible = $this->turnoLibreMasCercano($request->get('medico_id'), 0, 0);
           $fechaLibreDisponible = $this->convertirFechaParaMostrar($fechaLibreDisponible);
        } else {
            //turnos especiales cada 1 hora.                   
            $data = $this-> createJsonTurnosDobles($medico_id, $consultorio, $diaSeleccionado, $nuevaFecha);
            $fechaLibreDisponible = $this->turnoLibreMasCercano($request->get('medico_id'), 1, 0);
            $fechaLibreDisponible = $this->convertirFechaParaMostrar($fechaLibreDisponible);
        }
        $someArray = json_decode($data, true);      
        return response()->json(array('turnosPaciente'=>$someArray,'medico_id'=>$medico_id,'consultorio'=>$consultorio,'dia'=>$dia, 'primerControl'=>$primerControl, 'fechaLibreMasCercana'=>$fechaLibreDisponible));
    }


    // recibo fecha con formato 2020-01-01  Y-m-d
    // y retorno 01/01/2020 d/m/Y
    function convertirFechaParaMostrar($fecha){
        if(strpos($fecha, '-') === false){
            $fechaAux = explode('/',$fecha);
        } else {
            $fechaAux = explode('-',$fecha);
        }
        $dia = $fechaAux[2];
        $mes = $fechaAux[1];
        $anio = $fechaAux[0];
        $nuevaFecha=$dia.'/'.$mes.'/'.$anio;        
        return $nuevaFecha;
    }

    public function createJsonPeg($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada, $nombreAbreviado){
    	$fechaSolicitada = str_replace("/", "-", $fechaSolicitada);
        $turnos = DB::table('horario_medico_videollamadas')
                        ->where('horario_medico_videollamadas.medico',$medico_id)
                        ->where('horario_medico_videollamadas.consultorio', $consultorio_id)
                        ->where('horario_medico_videollamadas.dia', $diaSeleccionado)
                        ->where('horario_medico_videollamadas.activo', 1)                        
                        ->orderby('horario_medico_videollamadas.horario')
                        ->get();

        $turnosRegistrados = DB::table('turno_registrado_videollamadas')                       
        				->join('pacientes','pacientes.id','=','turno_registrado_videollamadas.paciente')
        				->select('turno_registrado_videollamadas.id', 'turno_registrado_videollamadas.horario', 'pacientes.dni', 'pacientes.apellido', 'pacientes.nombre', 'turno_registrado_videollamadas.comentario')
                        ->where('turno_registrado_videollamadas.medico',$medico_id)
                        ->where('turno_registrado_videollamadas.consultorio', $consultorio_id)
                        ->where('turno_registrado_videollamadas.dia', $diaSeleccionado)
                        ->where('turno_registrado_videollamadas.fechaTurno', $fechaSolicitada)
                        ->where('turno_registrado_videollamadas.activo', 1)                        
                        ->get();

        $json = array();
        $data = array();
        $contador=0;
        $encontre=0;

        $esFeriado = $this->esFeriado($fechaSolicitada);
        if($esFeriado != null){
            foreach($turnos as $turno){                                    
                $json['horario'] = $turno->horario;
                $json['dni'] = "";
                $json['nombre'] = "Feriado";                   
                $json['libre'] = 1;
                $json['comentario'] = "";                    
                $data[] = $json;
            }
            return json_encode($data);  
        }

        foreach($turnos as $turno){                                    
            while (($encontre < 1) && ($contador<count($turnosRegistrados))){            
                if(strcmp ($turno->horario , $turnosRegistrados[$contador]->horario ) == 0){

                    $encontre=1;                    
                   	$json['horario'] = $turno->horario;
    				$json['dni'] = $turnosRegistrados[$contador]->dni;
    				if(strcmp ($turnosRegistrados[$contador]->dni , 99999 ) == 0){    				
						$json['nombre'] = "Cancelado";
					} else{
	    				if($nombreAbreviado == 1){
	    					$json['nombre'] = $turnosRegistrados[$contador]->apellido.', '.substr($turnosRegistrados[$contador]->nombre,0,1).'.';
	    				} else{
	    					$json['nombre'] = $turnosRegistrados[$contador]->apellido.', '.$turnosRegistrados[$contador]->nombre;
	    				}
    				}
    				$json['trid'] = $turnosRegistrados[$contador]->id;
                    $json['tipo_turno'] = $medico_id;
                    $json['comentario'] = $turnosRegistrados[$contador]->comentario;    				
    				$json['libre'] = 1;    				
                    $data[] = $json;
                                                                                                
                } else {                                                            
                    $encontre=0;                    
                }
                $contador++;
            }
            if($encontre == 0){
            	$json['trid'] = 0;
                $json['horario'] = $turno->horario;
        		$json['dni'] = "";
        		$json['nombre'] = "";
        		$json['tipo_turno'] = "";
        		$json['comentario'] = ""; 	        		
                $json['libre'] = 0;                   
                $data[] = $json;
            }            
            $encontre=0;
            $contador=0;
        }
        return json_encode($data);
        
    }

    // lunes a viernes a partir de las 9 cada 20 min 7 pacientes... 
    function checkTurnoLibreEspecialAmilcarSosa($medico_id, $consultorio_id, $diaSeleccionado, $fechaSeleccionada){
        $turnos = null;
        $mostrarDespuesDeFecha = '2021-07-01';
        if($fechaSeleccionada > $mostrarDespuesDeFecha){
            // A partir de julio mostrar lo siguiente
            // Lunes y viernes            
            if($diaSeleccionado == 1 || $diaSeleccionado == 5){
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario', '!=', '08:30')
                        ->where('horario_medicos.horario', '!=', '09:30')
                        ->where('horario_medicos.horario', '!=', '10:30')                        
                        ->where('horario_medicos.horario', '!=', '11:30')                        
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }
        } else {
            // Antes de julio
            // Lunes            
            if($diaSeleccionado == 1 || $diaSeleccionado == 5){
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario', '!=', '09:20')
                        ->where('horario_medicos.horario', '!=', '09:40')
                        ->where('horario_medicos.horario', '!=', '10:20')
                        ->where('horario_medicos.horario', '!=', '10:40')                        
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
        $mostrarDespuesDeFecha = '2021-09-01';
        $mostrarAntesDeFecha = '2021-10-01';
        if($fechaSeleccionada >= $mostrarDespuesDeFecha && $fechaSeleccionada < $mostrarAntesDeFecha){
            if($diaSeleccionado == 4){
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario', '21:00')                        
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
        } else {
            if($diaSeleccionado == 5){
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario', '21:00')                        
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
        }
        return $turnos;
    }

    // Los turnos del martes, los paso al lunes desde las 15 hs. Cada 40 min. Ultimo 18:20 hs
    // Los del Jueves en Garibaldi 44 dejar 2. Uno a las 15:30  y otro 16:10 hs. Solo esos dos
    function checkTurnoLibreEspecialEricaPacheco($medico_id, $consultorio_id, $diaSeleccionado, $fechaSeleccionada){
        $turnos = null;
        $mostrarDespuesDeFecha = '2021-07-01';
        if($fechaSeleccionada >= $mostrarDespuesDeFecha){
            // A partir de julio mostrar lo siguiente
            // Lunes y viernes            
            if($diaSeleccionado == 1){
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)                        
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }
            if($diaSeleccionado == 2){
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)                                                
                        ->where('horario_medicos.horario', '!=', '16:40')
                        ->where('horario_medicos.horario', '!=', '17:20')
                        ->where('horario_medicos.horario', '!=', '18:00')
                        ->where('horario_medicos.horario', '!=', '18:40')
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }
            if($diaSeleccionado == 4){
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario', '!=', '16:00')                        
                        ->where('horario_medicos.horario', '!=', '16:30')                        
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }
        } else {
            // Antes de julio
            // Lunes            
            if($diaSeleccionado == 1){
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)                                                
                        ->where('horario_medicos.horario', '!=', '15:40')
                        ->where('horario_medicos.horario', '!=', '16:20')
                        ->where('horario_medicos.horario', '!=', '17:00')
                        ->where('horario_medicos.horario', '!=', '17:40')
                        ->where('horario_medicos.horario', '!=', '18:20')
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }
            if($diaSeleccionado == 2){
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)                                                
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }
            if($diaSeleccionado == 4){
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)                                             
                        ->where('horario_medicos.horario', '!=', '16:10')                        
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

    function checkTurnoLibreEspecialLucasSosa($medico_id, $consultorio_id, $diaSeleccionado, $fechaSeleccionada){
        $turnos = null;
        $mostrarDespuesDeFecha = '2021-08-01';
        $fechaSeleccionada = str_replace("/", "-", $fechaSeleccionada);
        if($fechaSeleccionada > $mostrarDespuesDeFecha){
            // A partir de agosto mostrar lo siguiente
            // Lunes ()
            if($diaSeleccionado == 1){
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
            if($diaSeleccionado == 2){
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario', '!=', '15:30')
                        ->where('horario_medicos.horario', '!=', '16:30')
                        ->where('horario_medicos.horario', '!=', '17:30')
                        ->where('horario_medicos.horario', '!=', '18:30')
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }
            if($diaSeleccionado == 3 || $diaSeleccionado == 5){
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario', '!=', '09:30')
                        ->where('horario_medicos.horario', '!=', '10:30')
                        ->where('horario_medicos.horario', '!=', '11:30')                        
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }
        } else {
            // Antes de agosto
            // Lunes
            if($diaSeleccionado == 1){
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario', '!=', '16:20')
                        ->where('horario_medicos.horario', '!=', '16:40')
                        ->where('horario_medicos.horario', '!=', '17:20')
                        ->where('horario_medicos.horario', '!=', '17:40')
                        ->where('horario_medicos.horario', '!=', '18:20')                        
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }
            if($diaSeleccionado == 2){
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario', '!=', '15:40')
                        ->where('horario_medicos.horario', '!=', '16:20')
                        ->where('horario_medicos.horario', '!=', '16:40')
                        ->where('horario_medicos.horario', '!=', '17:20')
                        ->where('horario_medicos.horario', '!=', '17:40')
                        ->where('horario_medicos.horario', '!=', '18:20')                        
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get();
            }
            if($diaSeleccionado == 3 || $diaSeleccionado == 5){
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.horario', '!=', '09:20')
                        ->where('horario_medicos.horario', '!=', '09:40')
                        ->where('horario_medicos.horario', '!=', '10:20')
                        ->where('horario_medicos.horario', '!=', '10:40')
                        ->where('horario_medicos.horario', '!=', '11:20')
                        ->where('horario_medicos.horario', '!=', '11:40')
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

	//$nombreAbreviado = 0 retorno apellido, nombre 
	//$nombreAbreviado = 1 retorno apellido, n 
	public function createJson($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada, $nombreAbreviado){
        if($medico_id == 8 || $medico_id == 13 || $medico_id == 12 || $medico_id == 15) {
        	if($medico_id == 8)
	            $turnos = $this->checkTurnoLibreEspecialMarina($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);
		if($medico_id == 12)
	            $turnos = $this->checkTurnoLibreEspecialEricaPacheco($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);
        	if($medico_id == 13)
	            $turnos = $this->checkTurnoLibreEspecialLucasSosa($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);
	        if($medico_id == 15)
	            $turnos = $this->checkTurnoLibreEspecialAmilcarSosa($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);
        } else {
        	$turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.activo', 1)                        
                        ->orderby('horario_medicos.horario')
                        ->get();
        }
        $turnosRegistrados = DB::table('turno_registrados')                       
        				->join('pacientes','pacientes.id','=','turno_registrados.paciente')    
        				->select('turno_registrados.id', 'turno_registrados.horario', 'pacientes.dni', 'pacientes.apellido', 'pacientes.nombre', 'turno_registrados.tipo_turno', 'turno_registrados.comentario')
                        ->where('turno_registrados.medico',$medico_id)
                        ->where('turno_registrados.consultorio', $consultorio_id)
                        ->where('turno_registrados.dia', $diaSeleccionado)
                        ->where('turno_registrados.fechaTurno', $fechaSolicitada)
                        ->where('turno_registrados.activo', 1)                        
                        ->get();

        $json = array();
        $data = array();
        $contador=0;
        $encontre=0;

        $esFeriado = $this->esFeriado($fechaSolicitada);
        if($esFeriado != null){
            foreach($turnos as $turno){                                    
                $json['horario'] = $turno->horario;
                $json['dni'] = "";
                $json['nombre'] = "Feriado";                   
                $json['libre'] = 1;  
                $json['comentario'] = "";                 
                $data[] = $json;
            }
            return json_encode($data);  
        }

        foreach($turnos as $turno){                                    
            while (($encontre < 1) && ($contador<count($turnosRegistrados))){            
                if(strcmp ($turno->horario , $turnosRegistrados[$contador]->horario ) == 0){

                    $encontre=1;                    
                   	$json['horario'] = $turno->horario;
    				$json['dni'] = $turnosRegistrados[$contador]->dni;
    				if(strcmp ($turnosRegistrados[$contador]->dni , 99999 ) == 0){    				
						$json['nombre'] = "Cancelado";
					} else{
	    				if($nombreAbreviado == 1){
	    					$json['nombre'] = $turnosRegistrados[$contador]->apellido.', '.substr($turnosRegistrados[$contador]->nombre,0,1).'.';
	    				} else{
	    					$json['nombre'] = $turnosRegistrados[$contador]->apellido.', '.$turnosRegistrados[$contador]->nombre;
	    				}
    				}
    				$tipoTurno = 'Consulta y ECG';
    				if($turnosRegistrados[$contador]->tipo_turno == 2)
    					$tipoTurno = 'Ecocardiograma Doppler Color';
    				if($turnosRegistrados[$contador]->tipo_turno == 3)
    					$tipoTurno = 'Ecodoppler de vasos de cuello';
    				$json['trid'] = $turnosRegistrados[$contador]->id;
                    $json['tipo_turno'] = $tipoTurno;
                    $json['comentario'] = $turnosRegistrados[$contador]->comentario;
    				$json['libre'] = 1;    				
                    $data[] = $json;
                                                                                                
                } else {                                                            
                    $encontre=0;                    
                }
                $contador++;
            }
            if($encontre == 0){
            	$json['trid'] = 0;
                $json['horario'] = $turno->horario;
        		$json['dni'] = "";
        		$json['nombre'] = "";
        		$json['tipo_turno'] = "";
        		$json['comentario'] = "";	        		
                $json['libre'] = 0;                   
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

        $turnosRegistrados = DB::table('turno_registrados')
						->join('pacientes','pacientes.id','=','turno_registrados.paciente')
                        ->where('turno_registrados.medico',$medico_id)
                        ->where('turno_registrados.consultorio', $consultorio_id)
                        ->where('turno_registrados.dia', $diaSeleccionado)
                        ->where('turno_registrados.fechaTurno', $fechaSolicitada)
                        ->where('turno_registrados.activo', 1)
                        ->orderBy('turno_registrados.horario', 'asc')                                                                    
                        ->get();

        $json = array(); 
        $data = array();       
        $i=0;
        while($i<count($turnos)){
        	
            if($turnos[$i]->doble==0){
            	
            	$turnoActualLibre = $this->estaLibre($turnos[$i], $turnosRegistrados);

	            if($turnoActualLibre == 0){
	                $j=$i+1;
	                if($j<count($turnos)){
                        $turnoSiguienteLibre = $this->estaLibre($turnos[$j],$turnosRegistrados);
                  //      echo $turnoSiguienteLibre.'<br>';                    
                        if($turnoSiguienteLibre == 0){
                        	$json['horario'] = $turnos[$j-1]->horario;
                        	$json['horario2'] = $turnos[$j]->horario;
    						$json['dni'] = ""; 
    						$json['nombre'] = "";
	        				$json['libre'] = 0;
	        				$data[] = $json;                                                         
                        }                     
	                }
	            }
	        }	        
        	  

			$i++;
        }            

        return json_encode($data);
    }

    function getInfo($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada,$horario){
    	$turnosRegistrados = DB::table('turno_registrados')
						->join('pacientes','pacientes.id','=','turno_registrados.paciente')
                        ->where('turno_registrados.medico',$medico_id)
                        ->where('turno_registrados.consultorio', $consultorio_id)
                        ->where('turno_registrados.dia', $diaSeleccionado)
                        ->where('turno_registrados.fechaTurno', $fechaSolicitada)
                        ->where('turno_registrados.horario', $horario)
                        ->where('turno_registrados.activo', 1)                        
                        ->first();
           return $turnosRegistrados;
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

	public function getPaciente($paciente_id){
		$paciente = DB::table('pacientes')												                              			
	                    ->where('pacientes.id',$paciente_id)	                   	
	                    ->first();			
	    return $paciente;
	}

	public function checkTurnoRegistrado($dia, $medico, $consultorio, $horario, $fechaTurno){
		$turnoPaciente = DB::table('turno_registrados')	
	                    ->where('turno_registrados.medico',$medico)
	                   	->where('turno_registrados.consultorio', $consultorio)
	                   	->where('turno_registrados.dia', $dia)
	                   	->where('turno_registrados.fechaTurno', $fechaTurno)
	                   	->where('turno_registrados.horario', $horario)
	                   	->where('turno_registrados.activo', 1)	                   		                   	
	                    ->get();			
        $contador = $turnoPaciente->count();
        if( $contador > 0)
        	return $turnoPaciente->first()->paciente;
        else
        	return -1;
	}

	public function turnosAsignadosDia(Request $request){
		//$today= getdate();
		date_default_timezone_set('America/Argentina/Buenos_Aires');
        $dia= date("d/m/Y");
		$dia_aux= date("Y/m/d");
	
		$medico_id = $request->get('medico_id');
		$medico = medico::find($request->get('medico_id'));
		$consultorio = $request->get('consultorio');
		$turnosPaciente = $this->getTurnosPaciente($medico_id, $consultorio, $dia_aux);
		
		$contador = $turnosPaciente->count();		

		if($medico->especialidad == 2){
			$dias_deshabilitados = $this->getDiasDeshabilitados($medico_id,$consultorio, 1);
		} else {
			$dias_deshabilitados = $this->getDiasDeshabilitados($medico_id,$consultorio, 0);
		}
		// Modulo id: 2 | corresponde a Caja | Comentario
        $moduloCajaComentario = $this->moduloActivo($medico->id, 2);

		return view('turnos_admin_secretaria/listado_pacientes')
				->with('turnosPaciente',$turnosPaciente)
				->with('medico',$medico)
				->with('moduloCajaComentario',$moduloCajaComentario)
				->with('consultorio',$consultorio)
				->with('contador',$contador)
				->with('dias_deshabilitados',$dias_deshabilitados)
				->with('dia',$dia);
	}
	
	public function registrarTurnoAgendaSemanalPeg($request){
		$tipoTurno = $request->tipo_turno;			
		$paciente_dni = $request->get('paciente');
		$medico_id = $request->get('medico_id');
		$medico = medico::find($request->get('medico_id'));
		$consultorio = $request->get('consultorio');
		$dias_deshabilitados = $this->getDiasDeshabilitados($medico_id,$consultorio, 0);				
        $fechaTurno = $request->get('fechaTurno');
        $horario = $request->get('horario');
        $diaSeleccionado = $this-> getDiaSeleccionado2($fechaTurno);
        $fechaInput = $request->get('fechaInput'); //05/12/2019 d/m/Y
        $dia_aux = explode('/',$fechaInput);        
        $fechaInput = $dia_aux[2].'/'.$dia_aux[1].'/'.$dia_aux[0];

    	$paciente = DB::table('pacientes')												                              			
                    ->where('pacientes.dni', $paciente_dni)
                    ->where('pacientes.activo', 1)                   
                    ->first();

        
        if($paciente->dni == 99999){
        	$turnoRegistrado = $this->registrarTurnoVideollamada($paciente->id,$medico_id,$consultorio,$diaSeleccionado,$horario,$fechaTurno, 'NO', 0, $tipoTurno);
					$data = array();
		 		   	$data = $this-> createJsonPeg($medico_id, $consultorio, $diaSeleccionado, $fechaTurno, 0); 		   
		 		   	$someArray = json_decode($data, true);    	

		 		   	$turnoRegistradoResponse = 1;	        	
        } else {
			$turnoLibre = $this->turnoPegEstaLibre($medico_id, $fechaTurno, $horario);
			if($turnoLibre){
				$otrosTurnos = $this->validarTurnoMismoDiaHorario($paciente->id , $medico_id, $fechaTurno, $horario);
				// true si ya tiene turno con otro estudio
				if($otrosTurnos){
					$turnoRegistradoResponse = 3;
				} else {
					$turnoRegistrado = $this->registrarTurnoVideollamada($paciente->id,$medico_id,$consultorio,$diaSeleccionado,$horario,$fechaTurno, 'NO', 0, $tipoTurno);
					$data = array();
		 		   	$data = $this-> createJsonPeg($medico_id, $consultorio, $diaSeleccionado, $fechaTurno, 0); 		   
		 		   	$someArray = json_decode($data, true);    	

		 		   	$turnoRegistradoResponse = 1;	        	
				} 
			} else {
				$turnoRegistradoResponse = 0;
			}		
		}

		$cincodias = $this -> obtener5Dias($fechaInput, $medico, 1);		
		 
		$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[0]);
		//$test = $medico_id.' '.$consultorio.' '.$diaSeleccionado.' '.$cincodias[0];
		$data = $this-> createJsonPeg($medico_id, $consultorio, $diaSeleccionado, $cincodias[0], 1);
		$someArray1 = json_decode($data, true);    
		$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[1]);
		$data = $this-> createJsonPeg($medico_id, $consultorio, $diaSeleccionado, $cincodias[1], 1);
		$someArray2 = json_decode($data, true);    
		$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[2]);
		$data = $this-> createJsonPeg($medico_id, $consultorio, $diaSeleccionado, $cincodias[2], 1);
		$someArray3 = json_decode($data, true);    
		$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[3]);
		$data = $this-> createJsonPeg($medico_id, $consultorio, $diaSeleccionado, $cincodias[3], 1);
		$someArray4 = json_decode($data, true);    
		$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[4]);
		$data = $this-> createJsonPeg($medico_id, $consultorio, $diaSeleccionado, $cincodias[4], 1);
		$someArray5 = json_decode($data, true);    
		
		$cincodias = $this->transformarFechas($cincodias);
		return response()->json(array('cincodias'=>$cincodias,'turnos1'=>$someArray1,'turnos2'=>$someArray2,'turnos3'=>$someArray3,'turnos4'=>$someArray4,'turnos5'=>$someArray5, 'turnoRegistrado'=>$turnoRegistradoResponse));
	}

	public function registrarTurnoAgendaSemanal(Request $request){	
		$tipoTurno = $request->tipo_turno;			
		$paciente_dni = $request->get('paciente');
		$medico_id = $request->get('medico_id');
		$medico = medico::find($request->get('medico_id'));
		$consultorio = $request->get('consultorio');
		$dias_deshabilitados = $this->getDiasDeshabilitados($medico_id,$consultorio, 0);				
        $fechaTurno = $request->get('fechaTurno');
        $horario = $request->get('horario');
        $diaSeleccionado = $this-> getDiaSeleccionado2($fechaTurno);
        $fechaInput = $request->get('fechaInput'); //05/12/2019 d/m/Y
        $dia_aux = explode('/',$fechaInput);        
        $fechaInput = $dia_aux[2].'/'.$dia_aux[1].'/'.$dia_aux[0];

    	$paciente = DB::table('pacientes')												                              			
                    ->where('pacientes.dni', $paciente_dni)
                    ->where('pacientes.activo', 1)                   
                    ->first();

        if($tipoTurno == 4){
			return $this->registrarTurnoAgendaSemanalPeg($request);
		}

        $turnoLibre = $this->consultarTurno($medico_id, $consultorio, $diaSeleccionado, $fechaTurno, $horario);	
        
      	if($medico->especialidad == 2){
      		$validarTurnoMismoDia = $this->validarTurnoMismoDiaPEG($paciente->id , $medico_id, $fechaTurno, $horario);
	    	if($validarTurnoMismoDia){
	    		return response()->json(array('turnoRegistrado'=>3));	
	    	}
      	}

        if($turnoLibre->count() == 0){

			$turnoRegistrado = $this->registrarTurno($paciente->id, $medico_id, $consultorio, $diaSeleccionado, $horario, $fechaTurno, 'NO', 0, $tipoTurno);

			$cincodias = $this -> obtener5Dias($fechaInput, $medico, 0);		
		
			$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[0]);
			$data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $cincodias[0], 1);
			$someArray1 = json_decode($data, true);    
			$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[1]);
			$data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $cincodias[1], 1);
			$someArray2 = json_decode($data, true);    
			$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[2]);
			$data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $cincodias[2], 1);
			$someArray3 = json_decode($data, true);    
			$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[3]);
			$data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $cincodias[3], 1);
			$someArray4 = json_decode($data, true);    
			$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[4]);
			$data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $cincodias[4], 1);
			$someArray5 = json_decode($data, true);    
			
			$cincodias = $this->transformarFechas($cincodias);
			return response()->json(array('cincodias'=>$cincodias,'turnos1'=>$someArray1,'turnos2'=>$someArray2,'turnos3'=>$someArray3,'turnos4'=>$someArray4,'turnos5'=>$someArray5, 'turnoRegistrado'=>1));
        } else {
			return response()->json(array('turnoRegistrado'=>0));
        }
	}

	public function actualizarListadoVerSemanaPeg(Request $request){
		$medico_id = $request->get('medico_id');
		$medico = medico::find($request->get('medico_id'));
		$consultorio = $request->get('consultorio');
		$dias_deshabilitados = $this->getDiasDeshabilitados($medico_id,$consultorio, 1);				
        $fechaSeleccionada = $request->get('fechaSeleccionada'); // 03/12/2019 d/m/Y
		$dia_aux = explode('/',$fechaSeleccionada);        
        $fechaSeleccionada = $dia_aux[2].'/'.$dia_aux[1].'/'.$dia_aux[0];

		$cincodias = $this -> obtener5Dias($fechaSeleccionada, $medico, 1);		
		 
		$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[0]);
		//$test = $medico_id.' '.$consultorio.' '.$diaSeleccionado.' '.$cincodias[0];
		$data = $this-> createJsonPeg($medico_id, $consultorio, $diaSeleccionado, $cincodias[0], 1);
		$someArray1 = json_decode($data, true);    
		$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[1]);
		$data = $this-> createJsonPeg($medico_id, $consultorio, $diaSeleccionado, $cincodias[1], 1);
		$someArray2 = json_decode($data, true);    
		$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[2]);
		$data = $this-> createJsonPeg($medico_id, $consultorio, $diaSeleccionado, $cincodias[2], 1);
		$someArray3 = json_decode($data, true);    
		$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[3]);
		$data = $this-> createJsonPeg($medico_id, $consultorio, $diaSeleccionado, $cincodias[3], 1);
		$someArray4 = json_decode($data, true);    
		$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[4]);
		$data = $this-> createJsonPeg($medico_id, $consultorio, $diaSeleccionado, $cincodias[4], 1);
		$someArray5 = json_decode($data, true);    
		
		$cincodias = $this->transformarFechas($cincodias);
		return response()->json(array('cincodias'=>$cincodias,'turnos1'=>$someArray1,'turnos2'=>$someArray2,'turnos3'=>$someArray3,'turnos4'=>$someArray4,'turnos5'=>$someArray5, 'dias_deshabilitados'=>$dias_deshabilitados));
	}

	public function actualizarListadoVerSemana(Request $request){
    	$medico_id = $request->get('medico_id');
		$medico = medico::find($request->get('medico_id'));
		$consultorio = $request->get('consultorio');
		$dias_deshabilitados = $this->getDiasDeshabilitados($medico_id,$consultorio, 0);				
        $fechaSeleccionada = $request->get('fechaSeleccionada'); // 03/12/2019 d/m/Y
		$dia_aux = explode('/',$fechaSeleccionada);        
        $fechaSeleccionada = $dia_aux[2].'/'.$dia_aux[1].'/'.$dia_aux[0];

		$cincodias = $this -> obtener5Dias($fechaSeleccionada, $medico, 0);		
		
		$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[0]);
		$data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $cincodias[0], 1);
		$someArray1 = json_decode($data, true);    
		$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[1]);
		$data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $cincodias[1], 1);
		$someArray2 = json_decode($data, true);    
		$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[2]);
		$data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $cincodias[2], 1);
		$someArray3 = json_decode($data, true);    
		$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[3]);
		$data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $cincodias[3], 1);
		$someArray4 = json_decode($data, true);    
		$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[4]);
		$data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $cincodias[4], 1);
		$someArray5 = json_decode($data, true);    
		
		$cincodias = $this->transformarFechas($cincodias);
		return response()->json(array('cincodias'=>$cincodias,'turnos1'=>$someArray1,'turnos2'=>$someArray2,'turnos3'=>$someArray3,'turnos4'=>$someArray4,'turnos5'=>$someArray5, 'dias_deshabilitados'=>$dias_deshabilitados));
    }

    public function bloquarDiaAgendaSemanalPeg($request){
    	$paciente_dni = $request->get('paciente');
		$medico_id = $request->get('medico_id');
		$medico = medico::find($request->get('medico_id'));
		$consultorio = $request->get('consultorio');
		$dias_deshabilitados = $this->getDiasDeshabilitados($medico_id,$consultorio, 1);				
        $fechaTurno = $request->get('fechaTurno');
        
        $horario = $request->get('horario');
        $diaSeleccionado = $this-> getDiaSeleccionado2($fechaTurno);
        
        $dia_aux = explode('/', $request->get('fechaInput'));        
        $fechaInput = $dia_aux[2].'-'.$dia_aux[1].'-'.$dia_aux[0];

    	$paciente = DB::table('pacientes')												                              			
                    ->where('pacientes.dni', $paciente_dni)
                    ->where('pacientes.activo', 1)                   
                    ->first();

        $pacienteCancelado = DB::table('pacientes')												                              			
					        ->where('pacientes.dni', 99999)
					        ->where('pacientes.activo', 1)                   
					        ->first();

        $turnoLibre = DB::table('turno_registrado_videollamadas')												
                    ->where('turno_registrado_videollamadas.medico',$medico_id)
                    ->where('turno_registrado_videollamadas.fechaTurno',$fechaTurno) 
                    ->where('turno_registrado_videollamadas.paciente', '!=', $pacienteCancelado->id)                   
                    ->where('turno_registrado_videollamadas.activo', 1)
                    ->get();
   		
   		if($turnoLibre->count() == 0){  

   			$turnosCancelados = $this->cancelarTurnosDiaCompletoVideollamadas($medico_id, $consultorio, $fechaTurno, $diaSeleccionado, $paciente);  	
   			
			$cincodias = $this -> obtener5Dias($fechaInput, $medico, 1);

			$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[0]);
			$data = $this-> createJsonPeg($medico_id, $consultorio, $diaSeleccionado, $cincodias[0], 1);
			$someArray1 = json_decode($data, true);    
			$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[1]);
			$data = $this-> createJsonPeg($medico_id, $consultorio, $diaSeleccionado, $cincodias[1], 1);
			$someArray2 = json_decode($data, true);    
			$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[2]);
			$data = $this-> createJsonPeg($medico_id, $consultorio, $diaSeleccionado, $cincodias[2], 1);
			$someArray3 = json_decode($data, true);    
			$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[3]);
			$data = $this-> createJsonPeg($medico_id, $consultorio, $diaSeleccionado, $cincodias[3], 1);
			$someArray4 = json_decode($data, true);    
			$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[4]);
			$data = $this-> createJsonPeg($medico_id, $consultorio, $diaSeleccionado, $cincodias[4], 1);
			$someArray5 = json_decode($data, true);    
						
			$cincodias = $this->transformarFechas($cincodias);			
			return response()->json(array('cincodias'=>$cincodias,'turnos1'=>$someArray1,'turnos2'=>$someArray2,'turnos3'=>$someArray3,'turnos4'=>$someArray4,'turnos5'=>$someArray5, 'turnoCancelado'=>1));
        } else {
			return response()->json(array('turnoCancelado'=>0));
        }
    }

    public function bloquarDiaAgendaSemanal(Request $request){
    	$tipo_turno = $request->tipo_turno;
    	if($tipo_turno == 4){
    		return $this->bloquarDiaAgendaSemanalPeg($request);
    	}
 		$paciente_dni = $request->get('paciente');
		$medico_id = $request->get('medico_id');
		$medico = medico::find($request->get('medico_id'));
		$consultorio = $request->get('consultorio');
		$dias_deshabilitados = $this->getDiasDeshabilitados($medico_id,$consultorio, 0);				
        $fechaTurno = $request->get('fechaTurno');
        
        $horario = $request->get('horario');
        $diaSeleccionado = $this-> getDiaSeleccionado2($fechaTurno);
        
        $dia_aux = explode('/', $request->get('fechaInput'));        
        $fechaInput = $dia_aux[2].'-'.$dia_aux[1].'-'.$dia_aux[0];

    	$paciente = DB::table('pacientes')												                              			
                    ->where('pacientes.dni', $paciente_dni)
                    ->where('pacientes.activo', 1)                   
                    ->first();
        $pacienteCancelado = DB::table('pacientes')												                              			
                    ->where('pacientes.dni', 99999)
                    ->where('pacientes.activo', 1)                   
                    ->first();

        $turnoLibre = DB::table('turno_registrados')												                              		
                    ->where('turno_registrados.medico',$medico_id)                   
                    ->where('turno_registrados.consultorio',$consultorio)                                     
                    ->where('turno_registrados.fechaTurno',$fechaTurno)
                    ->where('turno_registrados.dia',$diaSeleccionado)
                    ->where('turno_registrados.paciente','!=', $pacienteCancelado->id)
                    ->where('turno_registrados.activo', 1)
                    ->get();
   		
   		if($turnoLibre->count() == 0){  

   			$turnosCancelados = $this->cancelarTurnosDiaCompleto($medico_id, $consultorio, $fechaTurno, $diaSeleccionado, $paciente);   		
   			
			$cincodias = $this -> obtener5Dias($fechaInput, $medico, 0);

			$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[0]);
			$data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $cincodias[0], 1);
			$someArray1 = json_decode($data, true);    
			$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[1]);
			$data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $cincodias[1], 1);
			$someArray2 = json_decode($data, true);    
			$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[2]);
			$data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $cincodias[2], 1);
			$someArray3 = json_decode($data, true);    
			$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[3]);
			$data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $cincodias[3], 1);
			$someArray4 = json_decode($data, true);    
			$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[4]);
			$data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $cincodias[4], 1);
			$someArray5 = json_decode($data, true);    
						
			$cincodias = $this->transformarFechas($cincodias);			
			return response()->json(array('cincodias'=>$cincodias,'turnos1'=>$someArray1,'turnos2'=>$someArray2,'turnos3'=>$someArray3,'turnos4'=>$someArray4,'turnos5'=>$someArray5, 'turnoCancelado'=>1));
        } else {
			return response()->json(array('turnoCancelado'=>0));
        }
    }

    public function cancelarTurnosDiaCompleto($medico_id, $consultorio, $fechaTurno, $diaSeleccionado, $paciente){    	
    	$horariosMedico = DB::table('horario_medicos')	    												                              
            ->where('horario_medicos.medico', $medico_id)
            ->where('horario_medicos.dia', $diaSeleccionado)
            ->where('horario_medicos.consultorio', $consultorio)
            ->where('horario_medicos.activo', 1)
            ->get();

    	foreach ($horariosMedico as $turno){
			$turnoRegistrado = $this->registrarTurno($paciente->id, $medico_id, $consultorio, $diaSeleccionado, $turno->horario, $fechaTurno, 'NO', 0, 0);    		                  		
		}
		return $horariosMedico;
    }

    public function cancelarTurnosDiaCompletoVideollamadas($medico_id, $consultorio, $fechaTurno, $diaSeleccionado, $paciente){    	
    	$horariosMedico = DB::table('horario_medico_videollamadas')	    												
            ->where('horario_medico_videollamadas.medico', $medico_id)
            ->where('horario_medico_videollamadas.dia', $diaSeleccionado)
            ->where('horario_medico_videollamadas.consultorio', $consultorio)
            ->where('horario_medico_videollamadas.activo', 1)
            ->get();

    	foreach ($horariosMedico as $turno){    		
			$turnoRegistrado = $this->registrarTurnoVideollamada($paciente->id, $medico_id, $consultorio, $diaSeleccionado, $turno->horario, $fechaTurno, 'NO', 0, 4);    		                  		
		}
		return $horariosMedico;
    }

    public function getSecretaria($id){
    	$secretarias = DB::table('secretarias')	    										                           	            
            ->where('secretarias.user_id', $id)
            ->first();
    	return $secretarias;
    }

    public function borrarTurnoAgendaSemanalPeg($request){
    	$medico_id = $request->get('medico_id');
		$medico = medico::find($request->get('medico_id'));
		$consultorio = $request->get('consultorio');
		$dias_deshabilitados = $this->getDiasDeshabilitados($medico_id,$consultorio, 1);				
        
        $fechaTurno = $request->get('fechaTurno');
        
        $horario = $request->get('horario');
        $diaSeleccionado = $this-> getDiaSeleccionado2($fechaTurno);
       // $fechaInput = $request->get('fechaInput');
        $dia_aux = explode('/', $request->get('fechaInput'));        
        $fechaInput = $dia_aux[2].'-'.$dia_aux[1].'-'.$dia_aux[0];
		
		$turnoRegistrado = DB::table('turno_registrado_videollamadas')	    												
	        ->where('turno_registrado_videollamadas.medico',$medico_id)	        	       
			->where('turno_registrado_videollamadas.fechaTurno',$fechaTurno)
			->where('turno_registrado_videollamadas.horario',$horario)
			->where('turno_registrado_videollamadas.activo', 1)
	        ->first();	
		
		$secretariaCancel = $this->getSecretaria(\Auth::user()->id);

	    $miTurnoRegistrado = turnoRegistradoVideollamada::find($turnoRegistrado->id);
	    $miTurnoRegistrado->activo = 0;
	    $miTurnoRegistrado->comentario = 'Cancelado por: '.$secretariaCancel->apellido.', '.$secretariaCancel->nombre;
	    $miTurnoRegistrado->save();
	    //$miTurnoRegistrado->delete();
	        
        $cincodias = $this -> obtener5Dias($fechaInput, $medico, 1);

		$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[0]);
		$data = $this-> createJsonPeg($medico_id, $consultorio, $diaSeleccionado, $cincodias[0], 1);
		$someArray1 = json_decode($data, true);    
		$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[1]);
		$data = $this-> createJsonPeg($medico_id, $consultorio, $diaSeleccionado, $cincodias[1], 1);
		$someArray2 = json_decode($data, true);    
		$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[2]);
		$data = $this-> createJsonPeg($medico_id, $consultorio, $diaSeleccionado, $cincodias[2], 1);
		$someArray3 = json_decode($data, true);    
		$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[3]);
		$data = $this-> createJsonPeg($medico_id, $consultorio, $diaSeleccionado, $cincodias[3], 1);
		$someArray4 = json_decode($data, true);    
		$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[4]);
		$data = $this-> createJsonPeg($medico_id, $consultorio, $diaSeleccionado, $cincodias[4], 1);
		$someArray5 = json_decode($data, true);    
		
		$cincodias = $this->transformarFechas($cincodias);				
		return response()->json(array('cincodias'=>$cincodias,'turnos1'=>$someArray1,'turnos2'=>$someArray2,'turnos3'=>$someArray3,'turnos4'=>$someArray4,'turnos5'=>$someArray5, 'texto'=>'1'));
    }

    public function borrarTurnoAgendaSemanal(Request $request){    			       
       	// 2019/12/19 fechaTurno
       	$tipo_turno = $request->tipo_turno;
       	if($tipo_turno == 4) {
       		return $this->borrarTurnoAgendaSemanalPeg($request);
       	} else {
       	$medico_id = $request->get('medico_id');
		$medico = medico::find($request->get('medico_id'));
		$consultorio = $request->get('consultorio');
		$dias_deshabilitados = $this->getDiasDeshabilitados($medico_id,$consultorio, 0);				
        
        $fechaTurno = $request->get('fechaTurno');
        
        $horario = $request->get('horario');
        $diaSeleccionado = $this-> getDiaSeleccionado2($fechaTurno);
       // $fechaInput = $request->get('fechaInput');
        $dia_aux = explode('/', $request->get('fechaInput'));        
        $fechaInput = $dia_aux[2].'-'.$dia_aux[1].'-'.$dia_aux[0];
		
		$turnoRegistrado = DB::table('turno_registrados')	    												                              	
	        ->where('turno_registrados.medico',$medico_id)
	        ->where('turno_registrados.dia',$diaSeleccionado)
	        ->where('turno_registrados.consultorio',$consultorio)
			->where('turno_registrados.fechaTurno',$fechaTurno)
			->where('turno_registrados.horario',$horario)
			->where('turno_registrados.activo', 1)
	        ->first();	
		
		$secretariaCancel = $this->getSecretaria(\Auth::user()->id);

	    $miTurnoRegistrado = turnoRegistrado::find($turnoRegistrado->id);
	    $miTurnoRegistrado->activo = 0;
	    $miTurnoRegistrado->comentario = 'Cancelado por: '.$secretariaCancel->apellido.', '.$secretariaCancel->nombre;
	    $miTurnoRegistrado->save();
	    //$miTurnoRegistrado->delete();
	        
        $cincodias = $this -> obtener5Dias($fechaInput, $medico, 0);

		$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[0]);
		$data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $cincodias[0], 1);
		$someArray1 = json_decode($data, true);    
		$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[1]);
		$data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $cincodias[1], 1);
		$someArray2 = json_decode($data, true);    
		$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[2]);
		$data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $cincodias[2], 1);
		$someArray3 = json_decode($data, true);    
		$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[3]);
		$data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $cincodias[3], 1);
		$someArray4 = json_decode($data, true);    
		$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[4]);
		$data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $cincodias[4], 1);
		$someArray5 = json_decode($data, true);    
		
		$cincodias = $this->transformarFechas($cincodias);				
		return response()->json(array('cincodias'=>$cincodias,'turnos1'=>$someArray1,'turnos2'=>$someArray2,'turnos3'=>$someArray3,'turnos4'=>$someArray4,'turnos5'=>$someArray5, 'texto'=>'1'));
      	}
    }

	public function mostrarSemana(Request $request){
		$medico_id = $request->get('medico_id');
		$medico = medico::find($request->get('medico_id'));
		$consultorio = $request->get('consultorio');
		$dias_deshabilitados = $this->getDiasDeshabilitados($medico_id, $consultorio, 0);		
		date_default_timezone_set('America/Argentina/Buenos_Aires');
        $fechaSeleccionada = date("Y/m/d");
		
		$cincodias = $this -> obtener5Dias($fechaSeleccionada, $medico, 0);

		$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[0]);
		$data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $cincodias[0], 1);
		$someArray1 = json_decode($data, true);    
		$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[1]);
		$data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $cincodias[1], 1);
		$someArray2 = json_decode($data, true);    
		$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[2]);
		$data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $cincodias[2], 1);
		$someArray3 = json_decode($data, true);    
		$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[3]);
		$data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $cincodias[3], 1);
		$someArray4 = json_decode($data, true);    
		$diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[4]);
		$data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $cincodias[4], 1);
		$someArray5 = json_decode($data, true);    

		$fechaLibreDisponible = $this->turnoLibreMasCercano($request->get('medico_id'), 0, 0);
	
		$dia_aux = explode('/',$cincodias[0]);        
        $dia = $dia_aux[2].'/'.$dia_aux[1].'/'.$dia_aux[0];

        $dia_aux = explode('-',$fechaLibreDisponible);        
        $fechaLibreDisponible = $dia_aux[2].'/'.$dia_aux[1].'/'.$dia_aux[0];

        $cincodias = $this->transformarFechas($cincodias);
		if($request->option == 6){
			return view('turnos_admin_secretaria/bloquear_turnos')
			->with('dias_deshabilitados', $dias_deshabilitados)
			->with('consultorio', $consultorio)
			->with('dia', $dia)
			->with('turnos1', $someArray1)
			->with('turnos2', $someArray2)
			->with('turnos3', $someArray3)
			->with('turnos4', $someArray4)
			->with('turnos5', $someArray5)
			->with('cincoDias', $cincodias)
			->with('medico', $medico);
		} else {
			$moduloAfiliadoObligatorio = $this->moduloActivo($medico->id, 8);
            $obrasSociales = DB::table('obra_socials')   
                        ->join('obra_social_medicos', 'obra_social_medicos.obra_social', 'obra_socials.id')
                        ->where('obra_social_medicos.medico', $medico->id)
                        ->where('obra_socials.activo', 1)     
                        ->orderBy('obra_socials.nombre')                                                 
                        ->get();

			return view('turnos_admin_secretaria/ver_semana')
			->with('dias_deshabilitados', $dias_deshabilitados)
			->with('consultorio', $consultorio)
			->with('dia', $dia)
			->with('fechaLibreDisponible', $fechaLibreDisponible)
			->with('turnos1', $someArray1)
			->with('turnos2', $someArray2)
			->with('turnos3', $someArray3)
			->with('turnos4', $someArray4)
			->with('turnos5', $someArray5)
			->with('cincoDias', $cincodias)
			->with('moduloAfiliadoObligatorio', $moduloAfiliadoObligatorio)
			->with('obraSociales', $obrasSociales)
			->with('medico', $medico);
		}
	}

	public function obtener5Dias($fechaPrimerDia, $medico, $esVideollamada){
		if($esVideollamada == 1){
			$diasMedico = DB::table('horario_medico_videollamadas')						                     						
						->select('horario_medico_videollamadas.dia')
	                   	->where('horario_medico_videollamadas.medico', $medico->id)
	                   	->where('horario_medico_videollamadas.activo', 1)	                   		          	                    
	                    ->distinct('horario_medico_videollamadas.dia')
	                    ->get();    // 2 y 4
		} else {
			// me guardo que dias atiende el medico
			$diasMedico = DB::table('horario_medicos')						                     						
						->select('horario_medicos.dia')
	                   	->where('horario_medicos.medico', $medico->id)
	                   	->where('horario_medicos.activo', 1)	                   		          	                    
	                    ->distinct('horario_medicos.dia')
	                    ->get();    // 2 y 4
		}

		//$fechaPrimerDia = "26/09/2019";
		$diaAux = $this-> getDiaSeleccionado2($fechaPrimerDia);
		
		$data = array();						
		$json = array();						
		$cont = 1;
		while($cont <6){
			$encontre = 0;
			$cont_aux = 0;
			while ($cont_aux < sizeof($diasMedico)&&($encontre==0)){
				if($diaAux == $diasMedico[$cont_aux]->dia){
					//$json['fecha'.$cont] = $fechaPrimerDia;
					$cont++; $encontre = 1;
					//$data[] = $json;
					$data[] = $fechaPrimerDia;
				}
				$cont_aux++;
			}		
			//$dia_aux = explode('/',$fechaPrimerDia);        
	        $siguienteDia = $fechaPrimerDia;//$dia_aux[2].'-'.$dia_aux[1].'-'.$dia_aux[0];              
	        $siguienteDia = date('Y/m/d', strtotime('+1 day' , strtotime ( $siguienteDia )));            
	        $fechaPrimerDia = $siguienteDia;
	        $diaAux = $this-> getDiaSeleccionado2($fechaPrimerDia);
    	}
    	return $data;
	    //return json_encode($data);
	}

	public function consultarPaciente(Request $request){		
		$dni_paciente = $request->dni_paciente;		
		$paciente = DB::table('pacientes')						                     						
	                   	->where('pacientes.dni', $dni_paciente)	                   		                          	
	                    ->where('pacientes.activo', 1)
	                    ->first();			
        				
        return response()->json(array('paciente'=>$paciente));
	}

	public function nuevoPaciente(){
		$usuario_actual=\Auth::user();        
        $secretaria = DB::table('secretarias')                                                                
                ->where('secretarias.user_id', $usuario_actual->id)                                                      
                ->first();
        $secretariaConsultorio = DB::table('secretaria_consultorios')         
                ->where('secretaria_consultorios.secretaria_id', $secretaria->id)
                ->where('secretaria_consultorios.activo', 1)
                ->first();
                
        $consultorio = $secretariaConsultorio->consultorio_id;
        $obrasSociales = DB::table('obra_socials')                                                                
                        ->where('obra_socials.activo', 1)
                        ->orderBy('obra_socials.nombre')                                                      
                        ->get();

        $medico = DB::table('medicos')
					->where('medicos.consultorio', $consultorio)
					->where('activo', 1)
					->first();
		$moduloAfiliadoObligatorio = $this->moduloActivo($medico->id, 8);

		return view('turnos_admin_secretaria/nuevo_paciente')
					->with('obraSociales',$obrasSociales)
					->with('moduloAfiliadoObligatorio',$moduloAfiliadoObligatorio)					
					->with('consultorio',$consultorio);
	}

	function modalBuscarPacientesSecretariaList(){
		$usuario_actual = \Auth::user(); //obtengo los datos del usuario en sesion
		$usuario_id = $usuario_actual->id;		
		$secretaria = DB::table('secretarias')->where('user_id', $usuario_id)->first();	
		$secretaria_consultorios = DB::table('secretaria_consultorios')
									->where('secretaria_id', $secretaria->id)
									->where('activo', 1)->first();
		$consultorio_id = $secretaria_consultorios->consultorio_id;

        $users = DB::table('pacientes')                                
                                ->join('paciente_secretarias','paciente_secretarias.paciente','=','pacientes.id')            
                                ->select('pacientes.*')                                
                                ->where('paciente_secretarias.consultorio',$consultorio_id)   
                                ->where('pacientes.dni','!=', 99999)                         
                                ->where('pacientes.activo', 1) 
                                ->where('paciente_secretarias.activo', 1)                                 
                                ->orderby('pacientes.apellido');

        return datatables()->of($users)
                           ->addIndexColumn()
                           ->addColumn('action', function($row){                                   
                               $val = $row->dni;
                               $paciente_id = $row->id;                            
                               $btn = "<button onclick=seleccionarPaciente($val) class='rodri_button_aceptar_si'>></button>";                               
                               return $btn;
                            })
                            ->rawColumns(['action'])
                            ->make(true);
	}

	public function usersList(){
		$usuario_actual = \Auth::user(); //obtengo los datos del usuario en sesion
		$usuario_id = $usuario_actual->id;		
		$secretaria = DB::table('secretarias')->where('user_id', $usuario_id)->first();	
		$secretaria_consultorios = DB::table('secretaria_consultorios')
									->where('secretaria_id', $secretaria->id)
									->where('activo', 1)->first();
		$consultorio_id = $secretaria_consultorios->consultorio_id;

        $users = DB::table('pacientes')                                
                                ->join('paciente_secretarias','paciente_secretarias.paciente','=','pacientes.id')            
                                ->select('pacientes.*')                                
                                ->where('paciente_secretarias.consultorio',$consultorio_id)   
                                ->where('pacientes.dni','!=', 99999)                         
                                ->where('pacientes.activo', 1) 
                                ->where('paciente_secretarias.activo', 1)                                 
                                ->orderby('pacientes.apellido');   
		 
		return datatables()->of($users)
    					   ->addIndexColumn()
    					   ->addColumn('action', function($row){        					   	   
    					   	   $val = $row->dni;
	                           $btn = "<button onclick=darTurno($val) class='rodri_button_aceptar_si'>...</button>";
 		                       return $btn;
		                    })
		                    ->rawColumns(['action'])
		            		->make(true);
	}



	public function listadoPacientes(){
		$usuario_actual = \Auth::user(); //obtengo los datos del usuario en sesion
		$usuario_id = $usuario_actual->id;		
		$secretaria = DB::table('secretarias')->where('user_id', $usuario_id)->first();	
		$secretaria_consultorios = DB::table('secretaria_consultorios')
									->where('secretaria_id', $secretaria->id)
									->where('activo', 1)->first();
		$consultorio = $secretaria_consultorios->consultorio_id;

		$pacientes=DB::table('pacientes')->where('pacientes.dni','!=',99999)->where('activo', 1)->get();
		return view('turnos_admin_secretaria/actualizar_paciente_listado')
		->with('pacientes',$pacientes)
		->with('consultorio',$consultorio);
	}	

	public function actualizarPaciente(Request $request){
		$usuario_actual = \Auth::user(); //obtengo los datos del usuario en sesion
		$usuario_id = $usuario_actual->id;		
		$secretaria = DB::table('secretarias')->where('user_id', $usuario_id)->first();	
		$secretaria_consultorios = DB::table('secretaria_consultorios')
									->where('secretaria_id', $secretaria->id)
									->where('activo', 1)->first();
		$consultorio = $secretaria_consultorios->consultorio_id;

		$paciente = DB::table('pacientes')->where('pacientes.dni', $request->paciente_dni)->where('activo', 1)->first();
		$obrasSociales = DB::table('obra_socials')                                                                
                        ->where('obra_socials.activo', 1)
                        ->orderBy('obra_socials.nombre')                                                      
                        ->get();

        $medico = DB::table('medicos')
					->where('medicos.consultorio', $secretaria_consultorios->consultorio_id)
					->where('activo', 1)
					->first();

        $moduloAfiliadoObligatorio = $this->moduloActivo($medico->id, 8);        
		return view('turnos_admin_secretaria/actualizar_paciente')
		->with('paciente',$paciente)
		->with('obraSociales',$obrasSociales)
		->with('moduloAfiliadoObligatorio',$moduloAfiliadoObligatorio)
		->with('consultorio',$consultorio);
	}	

	
	public function actualizarListadoPacientes(Request $request){        		
		$dia = $request->fecha;
		$dia_aux = explode('/', $dia);        
        $nuevaFecha = $dia_aux[2].'-'.$dia_aux[1].'-'.$dia_aux[0];
        $tipoEstudio = $request->tipo_estudio;

        $medico_id = $request->medico_id;
		$consultorio = $request->consultorio;
		
		//$turnosPaciente = $this->getTurnosPaciente($medico_id, $consultorio, $nuevaFecha);		
		if($tipoEstudio == 0){
			$turnosPaciente = DB::table('turno_registrados')						                              
						->join('pacientes','pacientes.id','=','turno_registrados.paciente')
						->select('turno_registrados.id as trid','pacientes.nombre as nombrep','pacientes.apellido as apellidop','turno_registrados.horario', 'turno_registrados.asistio','pacientes.dni','turno_registrados.sobreturno','turno_registrados.primerControl','pacientes.telefono','turno_registrados.caja','turno_registrados.comentario', 'turno_registrados.tipo_turno', 'pacientes.obra_social')
	                    ->where('turno_registrados.medico',$medico_id)
	                   	->where('turno_registrados.consultorio', $consultorio)
	                   	->where('turno_registrados.fechaTurno', $nuevaFecha)
	                   	->where('turno_registrados.activo', 1)
	                   	->distinct()
	                   	->orderByRaw('turno_registrados.horario')
	                    ->get();
	    	$cantSobreturnos = $this->getCantidadSobreturnos($medico_id, $consultorio, $nuevaFecha); 
		} else {
			if($tipoEstudio == 2){
				$turnosPaciente = $this->getTurnosPaciente($medico_id, $consultorio, $nuevaFecha);
				$cantSobreturnos = 0;                              
			} else {
				$turnosPaciente = DB::table('turno_registrado_videollamadas')						                              
							->join('pacientes','pacientes.id','=','turno_registrado_videollamadas.paciente')
							->select('turno_registrado_videollamadas.id as trid','pacientes.nombre as nombrep','pacientes.apellido as apellidop','turno_registrado_videollamadas.horario', 'turno_registrado_videollamadas.asistio','pacientes.dni','turno_registrado_videollamadas.sobreturno','turno_registrado_videollamadas.primerControl','pacientes.telefono','turno_registrado_videollamadas.pago','turno_registrado_videollamadas.comentario', 'pacientes.obra_social')
		                    ->where('turno_registrado_videollamadas.medico',$medico_id)
		                   	->where('turno_registrado_videollamadas.consultorio', $consultorio)
		                   	->where('turno_registrado_videollamadas.fechaTurno', $nuevaFecha)
		                   	->where('turno_registrado_videollamadas.activo', 1)
		                   	->distinct()
		                   	->orderByRaw('turno_registrado_videollamadas.horario')
		                    ->get();
		        $cantSobreturnos = $this->getCantidadSobreturnosPeg($medico_id, $consultorio, $nuevaFecha); 
			}
		}
		
        
        $contador = $turnosPaciente->count();
        
        $medico = DB::table('medicos')                    
                    ->where('medicos.id', $medico_id)
                    ->where('medicos.activo', 1)                    
                    ->first();

           // Modulo id: 2 | corresponde a Caja | Comentario
        $moduloCajaComentario = $this->moduloActivo($medico->id, 2);
        $esFeriado = $this->esFeriado($nuevaFecha);

        return response()->json(array('turnosPaciente'=>$turnosPaciente,'medico'=>$medico,'consultorio'=>$consultorio,'dia'=>$dia, 'contador'=>$contador,'cantSobreturnos'=>$cantSobreturnos,'moduloCajaComentario'=>$moduloCajaComentario,'esFeriado'=>$esFeriado));
    }

    public function registrarAsistencia(Request $request){
    	$asistio = $request->asistio;
    	$turnoRegistradoId = $request->get('tregistradoId');
    	$turno = turnoRegistrado::find($turnoRegistradoId);
    	$turno->asistio=$asistio;
    	$turno->save();    

    	$dia = $request->fecha;		
        $dia_aux = explode('/', $dia);        
        $nuevaFecha = $dia_aux[2].'-'.$dia_aux[1].'-'.$dia_aux[0];
        $medico_id = $request->medico_id;
		$consultorio = $request->consultorio;
		$turnosPaciente = $this->getTurnosPaciente($medico_id, $consultorio, $nuevaFecha);		
        $contador = $turnosPaciente->count();

        $medico = DB::table('medicos')                    
                    ->where('medicos.id', $medico_id)
                    ->where('medicos.activo', 1)                    
                    ->first();

           // Modulo id: 2 | corresponde a Caja | Comentario
        $moduloCajaComentario = $this->moduloActivo($medico->id, 2);

        return response()->json(array('turnosPaciente'=>$turnosPaciente,'medico'=>$medico,'consultorio'=>$consultorio,'dia'=>$dia,'contador'=>$contador, 'moduloCajaComentario'=>$moduloCajaComentario));
    }

    public function registrarAsistenciaPeg(Request $request){
    	$asistio = $request->asistio;
    	$turnoRegistradoId = $request->get('tregistradoId');
    	$turno = TurnoRegistradoVideollamada::find($turnoRegistradoId);
    	$turno->asistio=$asistio;
    	$turno->save();    

    	$dia = $request->fecha;		
        $dia_aux = explode('/', $dia);        
        $nuevaFecha = $dia_aux[2].'-'.$dia_aux[1].'-'.$dia_aux[0];
        $medico_id = $request->medico_id;
		$consultorio = $request->consultorio;
		$turnosPaciente = $this->getTurnosPaciente($medico_id, $consultorio, $nuevaFecha);		
        $contador = $turnosPaciente->count();

        $medico = DB::table('medicos')                    
                    ->where('medicos.id', $medico_id)
                    ->where('medicos.activo', 1)                    
                    ->first();

           // Modulo id: 2 | corresponde a Caja | Comentario
        $moduloCajaComentario = $this->moduloActivo($medico->id, 2);

        return response()->json(array('turnosPaciente'=>$turnosPaciente,'medico'=>$medico,'consultorio'=>$consultorio,'dia'=>$dia,'contador'=>$contador, 'moduloCajaComentario'=>$moduloCajaComentario));
    }

	public function registrarPaciente($request){
            $dni = $request->dni_paciente;      
            $paciente_get=DB::table('pacientes')                    
                        ->where('pacientes.dni',$dni)                    
                        ->first();
                    
            if($paciente_get == null) {                    
                $paciente = new Paciente;
                $paciente->fcm_token = '';
            } else {
                $paciente = Paciente::find($paciente_get->id);
            }
            if($request->nombre != null && strcmp ($request->nombre , '' ) != 0){
                $paciente->nombre = $request->nombre;
            } else {
                $paciente->nombre = '';
            }

            if($request->apellido != null && strcmp ($request->apellido , '' ) != 0){
                $paciente->apellido = $request->apellido;
            } else {
                $paciente->apellido = '';
            }

            if($request->fecha_nacimiento != null && strcmp ($request->fecha_nacimiento , '' ) != 0){
                $paciente->fecha_nacimiento = $request->fecha_nacimiento;
            } else {
                $paciente->fecha_nacimiento = '1000-01-01 00:00:00';
            }
            
            if($request->dni_paciente != null && strcmp ($request->dni_paciente , '' ) != 0){
                $paciente->dni = $request->dni_paciente;
            } 

            if($request->telefono != null && strcmp ($request->telefono , '' ) != 0){
                $paciente->telefono = $request->telefono;
            } else {
                $paciente->telefono = '';
            }

            $paciente->terminos_condiciones = 0;         
            $paciente->fecha_castigo = '2000-01-01 00:00:00';        
            
            if($request->mail != null && strcmp ($request->mail , '' ) != 0){
                $paciente->mail = $request->mail;
            } else {
                $paciente->mail = '';
            }

            if($request->obra_social != null && strcmp ($request->obra_social , '' ) != 0){
                $paciente->obra_social = $request->obra_social;
            } else {
                $paciente->obra_social = '';
            }

            if($request->obra_social != null && strcmp ($request->obra_social , '' ) != 0){
                $paciente->obra_social = $request->obra_social;
            } else {
                $paciente->obra_social = '';
            }

            if($request->numero_afiliado != null && strcmp ($request->numero_afiliado , '' ) != 0){
                $paciente->numero_afiliado = $request->numero_afiliado;
            } else {
                $paciente->numero_afiliado = '';
            }

            if($request->obra_social_plan != null && strcmp ($request->obra_social_plan , '' ) != 0){
                $paciente->obra_social_plan = $request->obra_social_plan;
            } else {
                $paciente->obra_social_plan = '';
            }
                       
            $paciente->obra_social_foto = ""; 
            $paciente->afiliado_obligatorio = $request->afiliado_obligatorio; 
            $paciente->activo = 1;
            $paciente->save();             
                        
            $medico = medico::find($request->medico_id);
            $check = DB::table('paciente_secretarias')
                        ->where('paciente_secretarias.paciente',$paciente->id)
                        ->where('paciente_secretarias.consultorio',$medico->consultorio)
                        ->where('paciente_secretarias.activo', 1)
                        ->first();
            if($check == null){
                $paciente_sec = new pacienteSecretaria;
                $paciente_sec->paciente = $paciente->id;
                $paciente_sec->consultorio = $medico->consultorio;
                $paciente_sec->activo = 1;
                $paciente_sec->save();
            }

        return $paciente;
    }

    public function registrarTurno($paciente_id, $medico_id, $consultorio, $diaSeleccionado, $horario, $nuevaFecha, $primerControl, $sobreturno, $tipoTurno){
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
		$turnoRegistrado->tipo_turno = $tipoTurno;
		$turnoRegistrado->cancelado_por = '';
        $turnoRegistrado->comentario = '';
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

        public function secretariaRegistrarTurnoPacienteAgendaSemanal(Request $request){
        $tipoTurno = $request->tipo_turno;                                  
        $paciente_dni = $request->get('paciente');
        $medico_id = $request->get('medico_id');
        $medico = medico::find($request->get('medico_id'));
        $consultorio = $request->get('consultorio');
        $dias_deshabilitados = $this->getDiasDeshabilitados($medico_id,$consultorio, 0);               
        $fechaTurno = $request->get('fechaTurno');
        $horario = $request->get('horario');
        $diaSeleccionado = $this-> getDiaSeleccionado2($fechaTurno);
        
        $dia_aux = explode('/', $request->get('fechaInput'));        
        $fechaInput = $dia_aux[2].'/'.$dia_aux[1].'/'.$dia_aux[0];

        $paciente = $this->registrarPaciente($request);

        if($tipoTurno == 4){
            return $this->registrarTurnoAgendaSemanalPeg($request);
        }

        $turnoLibre = $this->consultarTurno($medico_id, $consultorio, $diaSeleccionado, $fechaTurno, $horario);     
        
        if($turnoLibre->count() == 0){
            $turnoRegistrado = $this->registrarTurno($paciente->id, $medico_id, $consultorio, $diaSeleccionado, $horario, $fechaTurno, 'NO', 0, $tipoTurno);                     

            $cincodias = $this -> obtener5Dias($fechaInput, $medico, 0);       
        
            $diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[0]);
            $data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $cincodias[0], 1);
            $someArray1 = json_decode($data, true);    
            $diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[1]);
            $data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $cincodias[1], 1);
            $someArray2 = json_decode($data, true);    
            $diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[2]);
            $data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $cincodias[2], 1);
            $someArray3 = json_decode($data, true);    
            $diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[3]);
            $data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $cincodias[3], 1);
            $someArray4 = json_decode($data, true);    
            $diaSeleccionado = $this-> getDiaSeleccionado2($cincodias[4]);
            $data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $cincodias[4], 1);
            $someArray5 = json_decode($data, true);    
            
            $cincodias = $this->transformarFechas($cincodias);

            return response()->json(array('cincodias'=>$cincodias,'turnos1'=>$someArray1,'turnos2'=>$someArray2,'turnos3'=>$someArray3,'turnos4'=>$someArray4,'turnos5'=>$someArray5, 'turnoRegistrado'=>1));
        } else {
            return response()->json(array('turnoRegistrado'=>0));
        }
    }


    public function registrarTurnoVideollamada($paciente_id, $medico_id, $consultorio, $diaSeleccionado, $horario, $nuevaFecha, $primerControl, $sobreturno, $tipoTurno){
    	$turnoRegistradoVideollamada = new TurnoRegistradoVideollamada; 
		$turnoRegistradoVideollamada->paciente = $paciente_id;
		$turnoRegistradoVideollamada->medico = $medico_id;
		$turnoRegistradoVideollamada->consultorio = $consultorio;
		$turnoRegistradoVideollamada->dia = $diaSeleccionado;
		$turnoRegistradoVideollamada->horario = $horario;   	
		$turnoRegistradoVideollamada->fechaTurno = $nuevaFecha;
		$turnoRegistradoVideollamada->asistio = 0;
		$turnoRegistradoVideollamada->sobreturno = $sobreturno;
		$turnoRegistradoVideollamada->primerControl = $primerControl;
		$turnoRegistradoVideollamada->comentario = '';
		$turnoRegistradoVideollamada->disponible = 0;
		$turnoRegistradoVideollamada->disponible_medico = 0;
		$turnoRegistradoVideollamada->pago = 0;		
		$turnoRegistradoVideollamada->pago_ticket = '';
		$turnoRegistradoVideollamada->cargado = 0;		
		$turnoRegistradoVideollamada->activo = 1;
		$turnoRegistradoVideollamada->save();

		return $turnoRegistradoVideollamada;
    }

    public function consultarTurno($medico_id, $consultorio, $diaSeleccionado, $nuevaFecha, $horario){
    		$turno = DB::table('turno_registrados')						                              								
	                    ->where('turno_registrados.medico',$medico_id)
	                   	->where('turno_registrados.consultorio', $consultorio)
	                   	->where('turno_registrados.dia', $diaSeleccionado)
	                   	->where('turno_registrados.fechaTurno', $nuevaFecha)
	                   	->where('turno_registrados.horario', $horario)	 
	                   	->where('turno_registrados.activo', 1)	 
	                    ->get();

	        return $turno;
    }

    // True en caso de que este libre, false en caso de que el turno este ocupado
    public function turnoPegEstaLibre($medico_id, $nuevaFecha, $horario){
    		$checkTurno = DB::table('turno_registrado_videollamadas')						                              				
	                    ->where('turno_registrado_videollamadas.medico',$medico_id)	                   		                   
	                   	->where('turno_registrado_videollamadas.fechaTurno', $nuevaFecha)
	                   	->where('turno_registrado_videollamadas.horario', $horario)	 
	                   	->where('turno_registrado_videollamadas.activo', 1)	 
	                    ->get();

		    if($checkTurno->count()>0)
	             return false;
	         else 
	            return true; 	        
    }

      // Verifico que una misma persona no pueda sacar otro turno el mismo dia.
    // return true si ya tiene un turno ese dia.
    public function validarTurnoMismoDia($paciente_id , $medico_id, $consultorio_id, $fechaTurno){
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

        // Verifico que una misma persona no pueda sacar otro turno el mismo dia.
    // return true si ya tiene un turno ese dia.
    public function validarTurnoMismoDiaHorario($paciente_id , $medico_id, $fechaTurno, $horario){
         $checkTurno = DB::table('turno_registrados')                                                                     
                ->where('turno_registrados.medico',$medico_id)
                ->where('turno_registrados.horario', $horario)
                ->where('turno_registrados.paciente', $paciente_id)
                ->where('turno_registrados.fechaTurno', $fechaTurno)
                ->where('turno_registrados.activo', 1)                                                                    
                ->get();
         if($checkTurno->count()>0)
             return true;
         else 
            return false;    
    }

    // return true si ya tiene un turno ese dia en ese horario.
    public function validarTurnoMismoDiaPEG($paciente_id , $medico_id, $fechaTurno, $horario){
         $checkTurno = DB::table('turno_registrado_videollamadas')                                                                     
                ->where('turno_registrado_videollamadas.medico',$medico_id)
                ->where('turno_registrado_videollamadas.horario', $horario)
                ->where('turno_registrado_videollamadas.paciente', $paciente_id)
                ->where('turno_registrado_videollamadas.fechaTurno', $fechaTurno)
                ->where('turno_registrado_videollamadas.activo', 1)                                                                    
                ->get();
         if($checkTurno->count()>0 && $paciente_id != 84)
             return true;
         else 
            return false;    
    }

    public function registrarAsignarTurnoDoble(Request $request){
    	$tipoTurno = $request->tipo_turno;
    	$fechaTurno = $request->fechaTurno;    	
		$fechaAux = explode('/',$fechaTurno);
		$dia = $fechaAux[0];
		$mes = $fechaAux[1];
		$anio = $fechaAux[2];
		$nuevaFecha=$anio.'-'.$mes.'-'.$dia;
		$date = new DateTime($nuevaFecha);		
		//return date_format($date, 'g:ia \o\n l jS F Y');	 //12:00am on Friday 7th June 2019	
		$diaLetras = date_format($date, 'l');

		$diaSeleccionado = $this->getDiaSeleccionado($diaLetras);
		
		$paciente_id = $request->paciente_id;
		$medico_id = $request->medico_id;
		$consultorio = $request->consultorio;			
		$horario = $request->horario;
		$horario2 = $request->horario2;
		$primerControl = $request->primerControl;

		$turnosPaciente = $this->consultarTurno($medico_id, $consultorio, $diaSeleccionado, $nuevaFecha, $horario);
	    $contador = $turnosPaciente->count();
	   
		$turnosPaciente2 = $this->consultarTurno($medico_id, $consultorio, $diaSeleccionado, $nuevaFecha, $horario2);	
	    $contador2 = $turnosPaciente2->count();

	    $validarTurnoMismoDia = $this->validarTurnoMismoDia($paciente_id , $medico_id, $consultorio, $nuevaFecha);
    	if($validarTurnoMismoDia){
    		return response()->json(array('turnoRegistrado'=>2));	
    	}

	    $validarCantidadPrimerControl = $this->controlarCantidadPrimerControl($medico_id, $diaSeleccionado, $consultorio, $nuevaFecha);
	    if(!$validarCantidadPrimerControl){
	    	return response()->json(array('turnoRegistrado'=>3));
	    }
		if(($contador==0) && ($contador2==0)){
			$turnoRegistrado = $this->registrarTurno($paciente_id,$medico_id,$consultorio,$diaSeleccionado,$horario,$nuevaFecha,'SI', 0, $tipoTurno);

			$turnoRegistrado2 = $this->registrarTurno($paciente_id,$medico_id,$consultorio,$diaSeleccionado,$horario2,$nuevaFecha,'SI', 0, $tipoTurno);

			$turnosAux = DB::select('select * from horario_medicos where medico = ? and consultorio = ? and dia = ? and activo=1',
			[$medico_id , $consultorio, $diaSeleccionado]);			
			
			$data = array();
			
            //turnos especiales cada 1 hora.         		   
        	$data = $this-> createJsonTurnosDobles($medico_id, $consultorio, $diaSeleccionado, $nuevaFecha);				
    		
    		$someArray = json_decode($data, true);    

        	return response()->json(array('turnosPaciente'=>$someArray,'medico_id'=>$medico_id,'consultorio'=>$consultorio,'dia'=>$dia, 'primerControl'=>$primerControl,'turnoRegistrado'=>1));
	    } else {
	    	return response()->json(array('turnoRegistrado'=>0));
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
    }

    function registrarTurnoPeg($request){
    	$fechaTurno = $request->fechaTurno;    	
		$fechaAux = explode('/',$fechaTurno);
		$dia = $fechaAux[0];
		$mes = $fechaAux[1];
		$anio = $fechaAux[2];
		$nuevaFecha=$anio.'-'.$mes.'-'.$dia;
		$date = new DateTime($nuevaFecha);		
		//return date_format($date, 'g:ia \o\n l jS F Y');	 //12:00am on Friday 7th June 2019	
		$diaLetras = date_format($date, 'l');

		$diaSeleccionado = $this->getDiaSeleccionado($diaLetras);
		
		$paciente_id = $request->paciente_id;
		$medico_id = $request->medico_id;
		$consultorio = $request->consultorio;			
		$horario = $request->horario;
		$primerControl = $request->primerControl;
		$primerContorlTexto = 'NO';
		$tipo_turno = 4;

		$medico = medico::find($request->get('medico_id'));
		$turnoLibre = $this->turnoPegEstaLibre($medico_id, $nuevaFecha, $horario);
		
		if($turnoLibre){
			$otrosTurnos = $this->validarTurnoMismoDiaHorario($paciente_id , $medico_id, $nuevaFecha, $horario);
			// true si ya tiene turno con otro estudio
			if($otrosTurnos){
				return response()->json(array('turnoRegistrado'=>3));				
			} else {
				$turnoRegistrado = $this->registrarTurnoVideollamada($paciente_id,$medico_id,$consultorio,$diaSeleccionado,$horario,$nuevaFecha, $primerContorlTexto, 0, $tipo_turno);
				$data = array();
	 		   	$data = $this-> createJsonPeg($medico_id, $consultorio, $diaSeleccionado, $nuevaFecha, 0); 		   
	 		   	$someArray = json_decode($data, true);    	

	        	return response()->json(array('turnosPaciente'=>$someArray,'medico_id'=>$medico_id,'consultorio'=>$consultorio,'dia'=>$dia, 'primerControl'=>$primerControl,'turnoRegistrado'=>1));	
			} 
		} else {
			return response()->json(array('turnoRegistrado'=>0));
		}			
    }

	public function registrarAsignarTurno(Request $request){		
    	$tipo_turno = $request->tipo_turno;
    	if($tipo_turno == 4){
    		return $this->registrarTurnoPeg($request);
    	}
    	$fechaTurno = $request->fechaTurno;    	
		$fechaAux = explode('/',$fechaTurno);
		$dia = $fechaAux[0];
		$mes = $fechaAux[1];
		$anio = $fechaAux[2];
		$nuevaFecha=$anio.'-'.$mes.'-'.$dia;
		$date = new DateTime($nuevaFecha);		
		//return date_format($date, 'g:ia \o\n l jS F Y');	 //12:00am on Friday 7th June 2019	
		$diaLetras = date_format($date, 'l');

		$diaSeleccionado = $this->getDiaSeleccionado($diaLetras);
		
		$paciente_id = $request->paciente_id;
		$medico_id = $request->medico_id;
		$consultorio = $request->consultorio;			
		$horario = $request->horario;
		$primerControl = $request->primerControl;

		$medico = medico::find($request->get('medico_id'));
		   // Modulo id: 3 | corresponde a Primer Control Doble
		$moduloPrimerControlDoble = $this->moduloActivo($medico->id, 3);
		
        if($moduloPrimerControlDoble == 1){ 
            if($primerControl == 1)
                $primerContorlTexto = 'SI';
            else
                $primerContorlTexto = 'NO';
        } else {
            $primerContorlTexto = 'NO';
        }

		// consulto si hay algun turno reservado en ese dia y horario.
		$turnosPaciente = $this->consultarTurno($medico_id, $consultorio, $diaSeleccionado, $nuevaFecha, $horario);
	    // si contador = 0 el turno esta libre para ser reservado.
	    $contador = $turnosPaciente->count();

	    if($medico->especialidad == 2){
	    	$validarTurnoMismoDia = $this->validarTurnoMismoDiaPEG($paciente_id , $medico_id, $nuevaFecha, $horario);
	    	if($validarTurnoMismoDia){
	    		return response()->json(array('turnoRegistrado'=>3));	
	    	}
	    } else {
		    $validarTurnoMismoDia = $this->validarTurnoMismoDia($paciente_id , $medico_id, $consultorio, $nuevaFecha);
	    	if($validarTurnoMismoDia){
	    		return response()->json(array('turnoRegistrado'=>2));	
	    	}
    	}

        if ($contador == 0){
        	// Registro un nuevo turno y lo devuelvo.
        	$turnoRegistrado = $this->registrarTurno($paciente_id,$medico_id,$consultorio,$diaSeleccionado,$horario,$nuevaFecha, $primerContorlTexto, 0, $tipo_turno);
			
			$data = array();
 		   	$data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $nuevaFecha, 0); 		   
 		   	$someArray = json_decode($data, true);    	

        	return response()->json(array('turnosPaciente'=>$someArray,'medico_id'=>$medico_id,'consultorio'=>$consultorio,'dia'=>$dia, 'primerControl'=>$primerControl,'turnoRegistrado'=>1));

    	} else {   	
     	   return response()->json(array('turnoRegistrado'=>0));
    	}
	}

	public function registrarSobreturnoPeg($request){
		$fechaTurno = $request->fechaTurno;    	
		$fechaAux = explode('/',$fechaTurno);
		$dia = $fechaAux[0];
		$mes = $fechaAux[1];
		$anio = $fechaAux[2];
		$nuevaFecha=$anio.'-'.$mes.'-'.$dia;
		$date = new DateTime($nuevaFecha);		
		//return date_format($date, 'g:ia \o\n l jS F Y');	 //12:00am on Friday 7th June 2019	
		$diaLetras = date_format($date, 'l');

		$diaSeleccionado = $this->getDiaSeleccionado($diaLetras);
		
		$paciente_id = $request->paciente_id;
		$medico_id = $request->medico_id;
		$consultorio = $request->consultorio;			
		$horario = $request->horario;
		//turnoPegEstaLibre($medico_id, $nuevaFecha, $horario)
		$turnosPaciente_aux = $this->turnoPegEstaLibre($medico_id, $nuevaFecha, $horario);
       // $contador = $turnosPaciente_aux->count();

        if ($turnosPaciente_aux){
        	$turnoRegistrado = $this->registrarTurnoVideollamada($paciente_id, $medico_id, $consultorio, $diaSeleccionado, $horario, $nuevaFecha, 'NO', 1, 4);

			$turnosPaciente = DB::table('turno_registrado_videollamadas')						                              
						->join('pacientes','pacientes.id','=','turno_registrado_videollamadas.paciente')
						->select('turno_registrado_videollamadas.id as trid','pacientes.nombre as nombrep','pacientes.apellido as apellidop','turno_registrado_videollamadas.horario', 'turno_registrado_videollamadas.asistio','pacientes.dni','turno_registrado_videollamadas.sobreturno', 'turno_registrado_videollamadas.comentario')
	                    ->where('turno_registrado_videollamadas.medico',$medico_id)
	                   	->where('turno_registrado_videollamadas.consultorio', $consultorio)
	                   	->where('turno_registrado_videollamadas.fechaTurno', $nuevaFecha)
	                   	->where('turno_registrado_videollamadas.activo', 1)
	                   	->distinct()
	                   	->orderByRaw('turno_registrado_videollamadas.horario')
	                    ->get();			
        	$contador = $turnosPaciente->count();
        	$cantSobreturnos = $this->getCantidadSobreturnosPeg($medico_id, $consultorio, $nuevaFecha);
        	return response()->json(array('turnosPaciente'=>$turnosPaciente,'medico_id'=>$medico_id,'consultorio'=>$consultorio,'dia'=>$dia, 'contador'=>$contador, 'turnoRegistrado'=>1,'cantSobreturnos'=>$cantSobreturnos));
	
		} else {   	
     	   return response()->json(array('turnoRegistrado'=>0));
    	}
	}

    public function registrarSobreturno(Request $request){
    	$tipoTurno = $request->tipo_turno;
    	if($tipoTurno == 4){
    		return $this->registrarSobreturnoPeg($request);
    	}
    	$fechaTurno = $request->fechaTurno;    	
		$fechaAux = explode('/',$fechaTurno);
		$dia = $fechaAux[0];
		$mes = $fechaAux[1];
		$anio = $fechaAux[2];
		$nuevaFecha=$anio.'-'.$mes.'-'.$dia;
		$date = new DateTime($nuevaFecha);		
		//return date_format($date, 'g:ia \o\n l jS F Y');	 //12:00am on Friday 7th June 2019	
		$diaLetras = date_format($date, 'l');

		$diaSeleccionado = $this->getDiaSeleccionado($diaLetras);
		
		$paciente_id = $request->paciente_id;
		$medico_id = $request->medico_id;
		$consultorio = $request->consultorio;			
		$horario = $request->horario;

		$turnosPaciente_aux = $this->consultarTurno($medico_id, $consultorio, $diaSeleccionado, $nuevaFecha, $horario);
        $contador = $turnosPaciente_aux->count();

        if ($contador == 0){
        	$turnoRegistrado = $this->registrarTurno($paciente_id, $medico_id, $consultorio, $diaSeleccionado, $horario, $nuevaFecha, 'NO', 1, $tipoTurno);

			$turnosPaciente = DB::table('turno_registrados')						                              
						->join('pacientes','pacientes.id','=','turno_registrados.paciente')
						->select('turno_registrados.id as trid','pacientes.nombre as nombrep','pacientes.apellido as apellidop','turno_registrados.horario', 'turno_registrados.asistio','pacientes.dni','turno_registrados.sobreturno', 'turno_registrados.tipo_turno', 'turno_registrados.comentario')
	                    ->where('turno_registrados.medico',$medico_id)
	                   	->where('turno_registrados.consultorio', $consultorio)
	                   	->where('turno_registrados.fechaTurno', $nuevaFecha)
	                   	->where('turno_registrados.activo', 1)
	                   	->distinct()
	                   	->orderByRaw('turno_registrados.horario')
	                    ->get();			
        	$contador = $turnosPaciente->count();
        	$cantSobreturnos = $this->getCantidadSobreturnos($medico_id, $consultorio, $nuevaFecha);
        	return response()->json(array('turnosPaciente'=>$turnosPaciente,'medico_id'=>$medico_id,'consultorio'=>$consultorio,'dia'=>$dia, 'contador'=>$contador, 'turnoRegistrado'=>1,'cantSobreturnos'=>$cantSobreturnos));
	
		} else {   	
     	   return response()->json(array('turnoRegistrado'=>0));
    	}
    }


    public function getDiaSeleccionado($diaLetras){
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

    function getTurnosPacientePeg($medico_id, $consultorio, $dia){
    	$fecha = str_replace("/", "-", $dia);

    	$turnosPacientePeg = DB::table('turno_registrado_videollamadas')						                              
						->join('pacientes','pacientes.id','=','turno_registrado_videollamadas.paciente')
						->select('turno_registrado_videollamadas.id as trid','pacientes.nombre as nombrep','pacientes.apellido as apellidop','turno_registrado_videollamadas.horario', 'turno_registrado_videollamadas.asistio','pacientes.dni','turno_registrado_videollamadas.sobreturno','turno_registrado_videollamadas.primerControl','pacientes.telefono','turno_registrado_videollamadas.pago as caja','turno_registrado_videollamadas.comentario','turno_registrado_videollamadas.medico as tipo_turno')
	                    ->where('turno_registrado_videollamadas.medico',$medico_id)
	                   	->where('turno_registrado_videollamadas.consultorio', $consultorio)
	                   	->where('turno_registrado_videollamadas.fechaTurno', $fecha)
	                   	->where('turno_registrado_videollamadas.activo', 1)
	                   	->distinct()
	                   	->orderByRaw('turno_registrado_videollamadas.horario')
	                    ->get();

	    return $turnosPacientePeg;
    }

    public function getTurnosPaciente($medico_id, $consultorio, $dia){
    	$turnosPaciente = DB::table('turno_registrados')						                              
						->join('pacientes','pacientes.id','=','turno_registrados.paciente')
						->select('turno_registrados.id as trid','pacientes.nombre as nombrep','pacientes.apellido as apellidop','turno_registrados.horario', 'turno_registrados.asistio','pacientes.dni','turno_registrados.sobreturno','turno_registrados.primerControl','pacientes.telefono','turno_registrados.caja','turno_registrados.comentario', 'turno_registrados.tipo_turno', 'pacientes.obra_social')
	                    ->where('turno_registrados.medico',$medico_id)
	                   	->where('turno_registrados.consultorio', $consultorio)
	                   	->where('turno_registrados.fechaTurno', $dia)
	                   	->where('turno_registrados.activo', 1)
	                   	->distinct()
	                   	->orderByRaw('turno_registrados.horario')
	                    ->get();
	    
	    $medico = Medico::find($medico_id);	    
	    if($medico->especialidad == 2){
	    	$fecha = str_replace("/", "-", $dia);
	    	$turnosPacientePeg = DB::table('turno_registrado_videollamadas')						                              
						->join('pacientes','pacientes.id','=','turno_registrado_videollamadas.paciente')
						->select('turno_registrado_videollamadas.id as trid','pacientes.nombre as nombrep','pacientes.apellido as apellidop','turno_registrado_videollamadas.horario', 'turno_registrado_videollamadas.asistio','pacientes.dni','turno_registrado_videollamadas.sobreturno','turno_registrado_videollamadas.primerControl','pacientes.telefono','turno_registrado_videollamadas.pago as caja','turno_registrado_videollamadas.comentario','turno_registrado_videollamadas.medico as tipo_turno', 'pacientes.obra_social')
	                    ->where('turno_registrado_videollamadas.medico',$medico_id)
	                   	->where('turno_registrado_videollamadas.consultorio', $consultorio)
	                   	->where('turno_registrado_videollamadas.fechaTurno', $fecha)
	                   	->where('turno_registrado_videollamadas.activo', 1)
	                   	->distinct()
	                   	->orderByRaw('turno_registrado_videollamadas.horario')
	                    ->get();	
	        
	        $_turnosPaciente = $turnosPaciente->merge($turnosPacientePeg);
            //$_turnosPaciente = $_turnosPaciente->unique('id');               
           // return $_turnosPaciente;
            $turnosPaciente = $_turnosPaciente;
	    }	

	    return $turnosPaciente;
    }




    public function getDiasDeshabilitados($medico_id, $consultorio, $videollamada){
    	//$medico = DB::select('select * from medicos where id = ? and activo=1', [$medico_id]);
    	//$consultorio = DB::select('select * from consultorios where id = ? and activo=1', [$consultorio]);
    	if($videollamada == 0){
    		$diasAtencionAux = DB::select('select DISTINCT(dia) from horario_medicos where consultorio= ? and medico = ? and activo=1', 
    		[$consultorio , $medico_id]);
		} else {
			$diasAtencionAux = DB::select('select DISTINCT(dia) from horario_medico_videollamadas where consultorio= ? and medico = ? and activo=1', 
    		[$consultorio , $medico_id]);
		}

    	$diasAtencion=array();

    	foreach ($diasAtencionAux as $valor){
    		if($valor->dia ==1){
    			array_push($diasAtencion, 'Lunes');    				                
            } 
    		if($valor->dia ==2){
    			array_push($diasAtencion, 'Martes');
            } 
    		if($valor->dia ==3){
    			array_push($diasAtencion, 'Miercoles');
            } 
    		if($valor->dia ==4){
    			array_push($diasAtencion, 'Jueves');
            } 
    		if($valor->dia ==5){
    			array_push($diasAtencion, 'Viernes');
            } 
    		if($valor->dia ==6){
    			array_push($diasAtencion, 'Sabado');
            } 
    		if($valor->dia ==7){
    			array_push($diasAtencion, 'Domingo');
            } 
    	}
        
        $dias_deshabilitados='';
        $sietedias=array('Domingo','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado');
        $diaauxcont=0;
        foreach($sietedias as $diaaux){           
            $encontreAux=0;
            for($cont=0;$cont<count($diasAtencion); $cont++){            
            if(strcmp ($diaaux, $diasAtencion[$cont]) == 0){
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



	public function generarTurnoHorario($diaLetras,$fechaSolicitada,$medico_id,$consultorio_id)
    {                         		
		$diaSeleccionado = $this->getDiaSeleccionado($diaLetras);
				
		$turnos = DB::select('select * from horario_medicos where medico = ? and consultorio = ? and dia = ? and activo=1',
			[$medico_id , $consultorio_id, $diaSeleccionado]);


        $turnosRegistrados = DB::select('select * from turno_registrados where medico = ? and consultorio = ? and dia = ?  and fechaTurno = ? and activo=1',
            [$medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada]);

        $arrayTurnos = array();        
        $contador=0;
        $encontre=0;
        //return count($turnosRegistrados);
        foreach($turnos as $turno){                                    
            while (($encontre < 1) && ($contador<count($turnosRegistrados))){            
                if(strcmp ($turno->horario , $turnosRegistrados[$contador]->horario ) == 0){
                    $encontre=1;
                                                                                                
                } else {                                                            
                    $encontre=0;                    
                }
                $contador++;
            }
            array_push($arrayTurnos,$encontre);                        
            $encontre=0;
            $contador=0;
        }
        
        $turnosLibres=0;
        foreach($arrayTurnos as $value){
            if($value==0)
                $turnosLibres=1;
        }

    	return $arrayTurnos;
    }

    //$fecha = 07/09/2019   dia mes año
    public function getDiaSeleccionado2($fecha){
        //$fecha_aux = explode('/',$fecha);        
        //$nuevaFecha = $fecha_aux[2].'-'.$fecha_aux[1].'-'.$fecha_aux[0];                  
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

    function turnoLibreMasCercano($medico, $primerControl, $esVideollamada){
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $dia= date("Y-m-d");        
        $encontre = 0;                
        //$hayTurnoLibre= $this->checkTurnoLibre($medico, $dia);
        while($encontre==0){
        	if($this->esFeriado($dia) == null) // no es feriado
                $hayTurnoLibre= $this->checkTurnoLibre($medico, $dia, $primerControl, $esVideollamada);
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
        	if($medico->id == 8 || $medico->id == 13 || $medico->id == 12 || $medico->id == 15) {
	        	if($medico->id == 8)
		            $turnos = $this->checkTurnoLibreEspecialMarina($medico->id, $consultorio->id, $diaSeleccionado, $fecha);
			if($medico->id == 12)
		            $turnos = $this->checkTurnoLibreEspecialEricaPacheco($medico->id, $consultorio->id, $diaSeleccionado, $fecha);
	        	if($medico->id == 13)
		            $turnos = $this->checkTurnoLibreEspecialLucasSosa($medico->id, $consultorio->id, $diaSeleccionado, $fecha);
		        if($medico->id == 15)
		            $turnos = $this->checkTurnoLibreEspecialAmilcarSosa($medico->id, $consultorio->id, $diaSeleccionado, $fecha);
        	} else {
	        	$turnos = DB::table('horario_medicos')
	                        ->where('horario_medicos.medico',$medico->id)
	                        ->where('horario_medicos.consultorio', $consultorio->id)
	                        ->where('horario_medicos.dia', $diaSeleccionado)
	                        ->where('horario_medicos.activo', 1)                        
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
            // en caso de ser primer control  
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

    public function updateCaja(Request $request){
        $caja = $request->caja;
        $turnoRegistradoId = $request->turnoRegistradoId;
        $turno = turnoRegistrado::find($turnoRegistradoId);
        $turno->caja = floatval($caja);
        $turno->save();    

        $dia = $request->fecha;
		$dia_aux = explode('/', $dia);        
        $nuevaFecha = $dia_aux[2].'-'.$dia_aux[1].'-'.$dia_aux[0];     
        $medico_id = $request->medico_id;
        $consultorio = $request->consultorio;
        $turnosPaciente = $this->getTurnosPaciente($medico_id, $consultorio, $nuevaFecha);     
        $contador = $turnosPaciente->count();

        $medico = DB::table('medicos')                                                                
                ->where('medicos.id', $medico_id)
                ->where('medicos.activo', 1)                                                      
                ->first();

           // Modulo id: 2 | corresponde a Caja | Comentario
        $moduloCajaComentario = $this->moduloActivo($medico->id, 2);

        return response()->json(array('turnosPaciente'=>$turnosPaciente,'medico'=>$medico,'consultorio'=>$consultorio,'dia'=>$dia,'contador'=>$contador, 'moduloCajaComentario'=>$moduloCajaComentario));
    }

    function updateComentarioPeg(Request $request){
        $comentario = $request->comentario;
        if($comentario == null)
            $comentario = '';
        $turnoRegistradoId = $request->turnoRegistradoId;
        $turno = TurnoRegistradoVideollamada::find($turnoRegistradoId);
        $turno->comentario = $comentario;
        $turno->save();            

        return response()->json(array('response'=> 1));
    }

    public function updateComentario(Request $request){
        $comentario = $request->comentario;
        if($comentario == null)
            $comentario = '';
        $turnoRegistradoId = $request->turnoRegistradoId;
        $turno = turnoRegistrado::find($turnoRegistradoId);
        $turno->comentario = $comentario;
        $turno->save();    

        $dia = $request->fecha;
        $dia_aux = explode('/', $dia);        
        $nuevaFecha = $dia_aux[2].'-'.$dia_aux[1].'-'.$dia_aux[0];        
        $medico_id = $request->medico_id;
        $consultorio = $request->consultorio;
        $turnosPaciente = $this->getTurnosPaciente($medico_id, $consultorio, $nuevaFecha);     
        $contador = $turnosPaciente->count();

        $medico = DB::table('medicos')                                                                
                ->where('medicos.id', $medico_id)
                ->where('medicos.activo', 1)                                                      
                ->first();
    
     // Modulo id: 2 | corresponde a Caja | Comentario
        $moduloCajaComentario = $this->moduloActivo($medico->id, 2);

        return response()->json(array('turnosPaciente'=>$turnosPaciente,'medico'=>$medico,'consultorio'=>$consultorio,'dia'=>$dia,'contador'=>$contador, 'moduloCajaComentario'=>$moduloCajaComentario));
    }

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

    public function transformarFechas($cincoDias){
    $data = array(); 
    for($i=0;$i<sizeof($cincoDias); $i++){            
        $data[] = $this->convertirFechaParaMostrar($cincoDias[$i]);
    }
    return $data;
	}

	function cancelarSobreturno(Request $request){
		$secretariaCancel = $this->getSecretaria(\Auth::user()->id);
		$tipo_estudio = $request->tipo_estudio;
		if($tipo_estudio == 0){
			$sobreturno = TurnoRegistrado::find($request->get('sobreturno_id'));
		} else {
			$sobreturno = TurnoRegistradoVideollamada::find($request->get('sobreturno_id'));
		}
		$sobreturno->comentario = 'Cancelado por: '.$secretariaCancel->apellido.', '.$secretariaCancel->nombre;
    	$sobreturno->activo = 0;
    	$sobreturno->save();	

    	$dia = $sobreturno->fechaTurno;
		     
        $nuevaFecha = $sobreturno->fechaTurno;

        $medico_id = $sobreturno->medico;
		$consultorio = $sobreturno->consultorio;
		if($tipo_estudio == 0){
			$turnosPaciente = $this->getTurnosPaciente($medico_id, $consultorio, $nuevaFecha);		
	        $cantSobreturnos = $this->getCantidadSobreturnos($medico_id, $consultorio, $nuevaFecha); 
    	} else {
    		$turnosPaciente = $this->getTurnosPacientePeg($medico_id, $consultorio, $nuevaFecha);		
	        $cantSobreturnos = $this->getCantidadSobreturnosPeg($medico_id, $consultorio, $nuevaFecha); 
    	}
        $contador = $turnosPaciente->count();
        
        $medico = Medico::find($medico_id);

           // Modulo id: 2 | corresponde a Caja | Comentario
        $moduloCajaComentario = $this->moduloActivo($medico->id, 2);

        return response()->json(array('turnosPaciente'=>$turnosPaciente,'medico'=>$medico,'consultorio'=>$consultorio,'dia'=>$dia, 'contador'=>$contador,'cantSobreturnos'=>$cantSobreturnos,'moduloCajaComentario'=>$moduloCajaComentario));
	}

	function listadoPacientesHistorial(Request $request){
		$secretaria = $this->getSecretaria(\Auth::user()->id);
		$consultorio = DB::table('secretaria_consultorios')                                                                
                                ->where('secretaria_consultorios.secretaria_id', $secretaria->id)
                                ->where('secretaria_consultorios.activo', 1) 
                                ->first();                                                                 
        return view('turnos_admin_secretaria.historial_paciente')->with('consultorio',$consultorio->consultorio_id);
    }

    function historialPacientesList(){
    	$secretaria = $this->getSecretaria(\Auth::user()->id);
    	$consultorio = DB::table('secretaria_consultorios')                                                                
                                ->where('secretaria_consultorios.secretaria_id', $secretaria->id)
                                ->where('secretaria_consultorios.activo', 1) 
                                ->first(); 
        // $consultorio_id = $this->getMedico()->consultorio;
        // $medico_id = $this->getMedico()->id;
        $users = DB::table('turno_registrados')                                
                                ->join('pacientes','pacientes.id','=','turno_registrados.paciente')                                
                                ->join('medicos','medicos.id','=','turno_registrados.medico')                                
                                ->select('turno_registrados.id','pacientes.nombre', 'pacientes.apellido','pacientes.dni','pacientes.telefono', 'medicos.apellido as apellido_m', 'medicos.nombre as nombre_m', 'turno_registrados.fechaTurno','turno_registrados.horario', 'turno_registrados.sobreturno','turno_registrados.asistio','turno_registrados.activo', 'turno_registrados.comentario')                                
                                ->where('turno_registrados.consultorio',$consultorio->consultorio_id)   
                                ->where('pacientes.dni','!=', 99999)                         
                                ->where('pacientes.activo', 1)                                                                
                                ->orderby('pacientes.apellido');        
        /*
        $users = DB::table('pacientes')                                
                                ->join('medico_pacientes','medico_pacientes.paciente','=','pacientes.id')
                                ->select('pacientes.*', 'medico_pacientes.bloqueado')                                
                                ->where('medico_pacientes.medico',$medico_id)   
                                ->where('pacientes.dni','!=', 99999)                         
                                ->where('pacientes.activo', 1)                                 
                                ->orderby('pacientes.apellido');        
		*/
        return datatables()->of($users)
                           ->addIndexColumn()
                           ->addColumn('action', function($row){                                   
                               $val = $row->dni;                                                                                      
                               $btn = "<button class='rodri_button_aceptar'>...</button>";
                               
                               return $btn;
                            })
                           ->addColumn('asistio_', function($row){ 
	                           	if($row->activo == 1){                                                                  
	                               if($row->asistio == 1)                                                                  
	                               	$aux = "<label>SI</label>";
	                               if($row->asistio == 2)                                                                  
	                               	$aux = "<label class='letrasrojo'>NO</label>";
	                               if($row->asistio == 0)                                                                  
	                               	$aux = "<label>-</label>";      
	                            } else {
	                            	$val = $row->id;
	                            	$aux = "<a type='button' onclick=verComentario($val)><u>Cancelado</u></a>";
	                            }                         
                               return $aux;
                            })
                           ->addColumn('sobreturno_', function($row){                                                                   
                               if($row->sobreturno == 1)                                                                                    
                               	$aux = "SI";
                               if($row->sobreturno == 0)                                                                                    
                               	$aux = "NO";
                                                            
                               return $aux;
                            })
                           ->addColumn('fechaTurno_', function($row){                                                                   
                               	$aux_fecha = $row->fechaTurno;
                                // $aux = explode('-', $aux_fecha);
                               	// $fecha = $aux[2].'/'.$aux[1].'/'.$aux[0];
                               return $aux_fecha; //$fecha;
                            })
                           ->addColumn('medico_', function($row){                                                                   
                               	$aux = $row->apellido_m.', '.$row->nombre_m;                                                            
                               return $aux;
                            })
                           
                            ->rawColumns(['medico_','fechaTurno_','sobreturno_','asistio_','action'])
                            ->make(true);
    }
	
}
