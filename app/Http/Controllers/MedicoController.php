<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Especialidad;
use App\Consultorios;
use App\Videollamada;
use dateTimeZone;
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
use Image;
use Mail;
use App\Mail\ActivarPacienteMailable;
use App\MedicoPrimerControl;
use App\ModuloMedico;
use App\Feriado;
use App\ObraSocialMedico;
use App\MedicoPaciente;
use App\MedicoConfig;
use App\FechasAgregada;
use App\HorariosMedicosAgregado;
use App\Util\Util;

class MedicoController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

	public function adminMedico(Request $request)
    {        
		$usuario_actual=\Auth::user();
        $medico_id= $request->medico_id;        
		$consultorios = DB::table('consultorios')->get();

        $medico = DB::table('medicos')                                                                
                    ->where('medicos.id',$medico_id)                                                      
                    ->get();

        $especialidad= DB::table('especialidads')->where('especialidads.id',$medico[0]->especialidad)->first();
        $modulos = DB::table('modulos')->where('modulos.activo',1)->get();
        $json = array();
        $data = array();
        foreach ($modulos as $modulo){
            $moduloExiste = $this->moduloExiste($medico_id, $modulo->id);
            if($moduloExiste->count()>0)
                $json['activo'] = 1;
            else
                $json['activo'] = 0;
            $json['modulo'] = $modulo->id;
            $json['descripcion'] = $modulo->descripcion;
            $data[] = $json; 
        }
        $videollamada = null;
        $moduloMercadoPago = 0;
         if($this->moduloExiste($medico_id, 7)->count()>0){
            $moduloMercadoPago = 1;
            $videollamada = $this->existeVideollamada($medico_id);
        }

        $ventanaDias = DB::table('medico_configs')                                                                
                    ->where('medico_configs.medico',$medico_id)                                                      
                    ->where('medico_configs.modulo', 9) // 9 es el id del modulo ventana_dias
                    ->first();

    	return View('turnos_admin.alta_medico')
    	->with('especialidad',$especialidad)
        ->with('user',$usuario_actual)
        ->with('ventanaDias',$ventanaDias)
        ->with('medico',$medico[0])
        ->with('moduloMedicos',$data)
        ->with('videollamada',$videollamada)
        ->with('moduloMercadoPago',$moduloMercadoPago)
    	->with('consultorios',$consultorios);    	        
    }

    public function actualizarFoto(Request $request){
        
        $medico_id = $request->medico_id;

        if($request->hasfile('foto')){
            $image = $request->file('foto');
            $name  = $image->getClientOriginalName().time().'.'.$image->getClientOriginalExtension();
            $path = 'images/medicos/'.$name;        
            Image::make($image->getRealPath())->resize(300, 300)->save($path);
                        
        } else {
            $name = 'medico_sin_foto.png';
        }

        $medico = Medico::find($medico_id);
    
        $medico->foto = $name;
        $medico->save();

       $users = DB::table('users')                                                
                    ->join('medicos','medicos.user_id','=','users.id')
                    ->join('especialidads','medicos.especialidad','=','especialidads.id')
                    ->select('especialidads.nombre as especialidad','medicos.nombre as nombrem','medicos.apellido as apellidom','medicos.id as medico_id','medicos.consultorio','medicos.foto')
                    ->where('users.usuario_tipo',2)                                        
                    ->distinct()
                        ->get();
        
        return view('turnos_admin.admin_show_medicos')->with('users',$users);

    }

    public function actualizarMedico(Request $request){
        $medico_id = $request->medico_id;

        $medico = Medico::find($medico_id);
        $medico->nombre = $request->nombre;
        $medico->apellido = $request->apellido;
        $medico->mail = $request->mail;
        $medico->telefono = $request->telefono;
        $medico->sexo = $request->sexo;
        $medico->perfil = $request->get('perfil');
        $medico->castigo_automatico = $request->get('radio');
        $medico->save();

        $userPerfil = user::find($medico->user_id);
        $userPerfil->perfil = $request->get('perfil');
        $userPerfil->save();

       $users = DB::table('users')                                                
                    ->join('medicos','medicos.user_id','=','users.id')
                    ->join('especialidads','medicos.especialidad','=','especialidads.id')
                    ->select('especialidads.nombre as especialidad','medicos.nombre as nombrem','medicos.apellido as apellidom','medicos.id as medico_id','medicos.consultorio','medicos.foto')
                    ->where('users.usuario_tipo',2)                                        
                    ->distinct()
                        ->get();
        
        return view('turnos_admin.admin_show_medicos')->with('users',$users);
    }

    public function medicoActualizarPaciente(Request $request){
        $usuario_actual=\Auth::user();        
        $medico = DB::table('medicos')                                                                
                ->where('medicos.user_id',$usuario_actual->id)                                                      
                ->first();
        $consultorio = $medico->consultorio;
        $paciente_dni = $request->paciente_dni;
        $obrasSociales = DB::table('obra_socials')                                                                
                        ->where('obra_socials.activo', 1)                                                      
                        ->orderBy('obra_socials.nombre')
                        ->get();
        $moduloAfiliadoObligatorio = $this->moduloActivo($medico->id, 8);
        
        return view('turnos_admin_medico.medico_actualizar_paciente')
                    ->with('obraSociales',$obrasSociales)
                    ->with('medico',$medico)
                    ->with('moduloAfiliadoObligatorio',$moduloAfiliadoObligatorio)
                    ->with('paciente_dni',$paciente_dni)
                    ->with('consultorio',$consultorio);
    }

    public function store(Request $request)
    {                    
    	if($request->hasfile('foto')){
            $image = $request->file('foto');
            $name  = $image->getClientOriginalName().time().'.'.$image->getClientOriginalExtension();
            $path = 'images/medicos/'.$name;        
            Image::make($image->getRealPath())->resize(300, 300)->save($path);    		          
    	} else {
    		$name = 'medico_sin_foto.png';
    	}
				
		$consultorio_aux = explode('-',$request->get('consultorio'));        
        $especialidad_aux = explode('-',$request->get('especialidad'));
		
        $medico = new medico;
        $medico->nombre = $request->get('nombre');
        $medico->apellido = $request->get('apellido');
        $medico->especialidad = $especialidad_aux[0];
        $medico->consultorio = $consultorio_aux[0];
        $medico->telefono = $request->get('telefono');
        $medico->mail = $request->get('mail');
        $medico->sexo = $request->get('sexo');
        $medico->castigo_automatico = $request->get('radio');
        $medico->foto = $name;
        $medico->perfil = $request->get('perfil');
        $medico->activo = 1;
        $medico->user_id = $request->get('user_id');
        $medico->save();

        $userPerfil = user::find($request->get('user_id'));
        $userPerfil->perfil = $request->get('perfil');
        $userPerfil->save();
		
        $users = DB::table('users')                                                
                    ->join('medicos','medicos.user_id','=','users.id')
                    ->join('especialidads','medicos.especialidad','=','especialidads.id')
                    ->select('especialidads.nombre as especialidad','medicos.nombre as nombrem','medicos.apellido as apellidom','medicos.id as medico_id','medicos.consultorio','medicos.foto')
                    ->where('users.usuario_tipo',2)                                        
                    ->distinct()
                        ->get();
        
        return view('turnos_admin.admin_show_medicos')->with('users',$users);

    }

    public function adminShowMedicos()
    {           
        $users = DB::table('users')                                                
                        ->join('medicos','medicos.user_id','=','users.id')
                        ->join('especialidads','medicos.especialidad','=','especialidads.id')
                        ->select('especialidads.nombre as especialidad','medicos.nombre as nombrem','medicos.apellido as apellidom','medicos.id as medico_id','medicos.consultorio as consultorio','medicos.foto')                        
                        ->where('users.usuario_tipo',2)                              
                        ->distinct()          
                        ->get();
        
        return view('turnos_admin.admin_show_medicos')->with('users',$users);
    }

    public function showMedicos(Request $request)
    {    	    
    	$medicos = DB::select('select * from medicos where especialidad = ? and activo=1', [$request->get('especialidad_id')]);
        $dni_paciente = $request->get('dni_paciente');
    	
    	return view('turnos.seleccionar_medico')->with('medicos',$medicos)->with('dni_paciente',$dni_paciente);
  
    }

    public function showMedicosIndex() {
        $medicos = DB::select('select * from medicos where activo=1');
        return view('turnos.mostrar_medicos_index')->with('medicos',$medicos);
    }

    public function selectConsultorio(Request $request) {
        //$today= getdate();
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $dia= date("d/m/Y");
        $dia_aux= date("Y/m/d");

        $usuario_actual=\Auth::user();        
        $medico = DB::table('medicos')                                                                
                ->where('medicos.user_id',$usuario_actual->id)                                                      
                ->first();
        $medico_id = $medico->id;                

        $consultorio = DB::table('consultorios')                                                                
                ->where('id',$medico->consultorio)                                                      
                ->first();
        
        $turnosPaciente = $this->getTurnosPaciente($medico_id, $consultorio->id, $dia_aux);
        $contador = $turnosPaciente->count();       
        
        if($medico->especialidad == 2){
            $dias_habilitados = $this->diasHabilitados($medico_id, $consultorio->id, 0, 0);        
            $diasAtencion = $this->diasAtencion($medico_id, $consultorio->id, 0, 0);        
            $dias_deshabilitados= $this->diasDeshabilitados2($diasAtencion);
            //$dias_deshabilitados = $this->getDiasDeshabilitados($medico_id,$consultorio->id, 1);
        } else {
            $dias_habilitados = $this->diasHabilitados($medico_id, $consultorio->id, 0, 0);        
            $diasAtencion = $this->diasAtencion($medico_id, $consultorio->id, 0, 0);        
            $dias_deshabilitados= $this->diasDeshabilitados2($diasAtencion);
            //$dias_deshabilitados = $this->getDiasDeshabilitados($medico_id,$consultorio->id, 0);
        }
          // Modulo id: 2 | corresponde a Caja | Comentario
        $moduloCajaComentario = $this->moduloActivo($medico->id, 2);

        return view('turnos_admin_medico/listado_pacientes')
                ->with('turnosPaciente',$turnosPaciente)
                ->with('medico',$medico)
                ->with('consultorio',$consultorio)
                ->with('contador',$contador)
                ->with('moduloCajaComentario',$moduloCajaComentario)
                ->with('dias_deshabilitados',$dias_deshabilitados)
                ->with('dia',$dia);
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

/*    public function selectConsultorio(){
        $usuario_actual=\Auth::user();
        $id = $usuario_actual->id;

        $medico = DB::table('medicos')                                                                                                
                        ->where('medicos.user_id', $id)                                                      
                        ->first();
        if($medico->activo == 0){
            return view('turnos_admin_medico.user_desactivado')->with('medico',$medico);       
        }
        
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $dia= date("d/m/Y");

        $medico_id = $medico->id;
        $consultorio = $medico->consultorio;

        $turnosPaciente = $this->getTurnosPaciente($medico_id, $consultorio, $dia);
        $contador = $turnosPaciente->count();       

        $dias_deshabilitados = $this->getDiasDeshabilitados($medico_id,$consultorio);

        return view('turnos_admin_medico.listado_pacientes')
                ->with('turnosPaciente',$turnosPaciente)
                ->with('medico_id',$medico_id)
                ->with('consultorio',$consultorio)                
                ->with('dias_deshabilitados',$dias_deshabilitados)
                ->with('dia',$dia);       
    }
*/  

    

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

    public function getTurnosPaciente($medico_id, $consultorio, $dia) {
        $turnosPaciente = DB::table('turno_registrados')                                                      
                        ->join('pacientes','pacientes.id','=','turno_registrados.paciente')
                        ->select('turno_registrados.id as trid','pacientes.nombre as nombrep','pacientes.apellido as apellidop','turno_registrados.horario', 'turno_registrados.asistio','pacientes.dni','turno_registrados.sobreturno','turno_registrados.primerControl','pacientes.telefono','turno_registrados.caja','turno_registrados.comentario', 'turno_registrados.tipo_turno')
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
                        ->select('turno_registrado_videollamadas.id as trid','pacientes.nombre as nombrep','pacientes.apellido as apellidop','turno_registrado_videollamadas.horario', 'turno_registrado_videollamadas.asistio','pacientes.dni','turno_registrado_videollamadas.sobreturno','turno_registrado_videollamadas.primerControl','pacientes.telefono','turno_registrado_videollamadas.pago as caja','turno_registrado_videollamadas.comentario','turno_registrado_videollamadas.medico as tipo_turno')
                        ->where('turno_registrado_videollamadas.medico',$medico_id)
                        ->where('turno_registrado_videollamadas.consultorio', $consultorio)
                        ->where('turno_registrado_videollamadas.fechaTurno', $fecha)
                        ->where('turno_registrado_videollamadas.activo', 1)
                        ->distinct()
                        ->orderByRaw('turno_registrado_videollamadas.horario')
                        ->get();    
            
            $_turnosPaciente = $turnosPaciente->merge($turnosPacientePeg);            
            $turnosPaciente = $_turnosPaciente;
        }             
        return $turnosPaciente;
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

    public function diaAtencionDesdeHasta($medicoId, $dia, $esVideollamada, $tipoTurno){
        if($esVideollamada == 1 || $tipoTurno == 4){
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

    public function diasAtencion($medico_id, $consultorio_id, $esVideollamada, $tipoTurno) {
        if($esVideollamada == 1 || $tipoTurno == 4){
            $diasAtencionAux = DB::select('select DISTINCT(dia) from horario_medico_videollamadas where consultorio= ? and medico = ? and activo=1 order by dia',    
            [$consultorio_id , $medico_id]);
        } else {
            $diasAtencionAux = DB::select('select DISTINCT(dia) from horario_medicos where consultorio= ? and medico = ? and activo=1 order by dia',    
            [$consultorio_id , $medico_id]);
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

    public function diasHabilitados($medico_id, $consultorio_id, $esVideollamada, $tipoTurno) {
        if($esVideollamada == 1){
        $diasAtencionAux = DB::select('select DISTINCT(dia) from horario_medico_videollamadas where consultorio= ? and medico = ? and activo=1 order by dia',    
            [$consultorio_id , $medico_id]);
        } else {
            $diasAtencionAux = DB::select('select DISTINCT(dia) from horario_medicos where consultorio= ? and medico = ? and activo=1 order by dia',    
            [$consultorio_id , $medico_id]);
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

    public function diasDeshabilitados2($diasAtencion){
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

    public function getDiasDeshabilitados($medico_id,$consultorio, $videollamada){
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

    public function medicoActualizarListadoPacientes(Request $request){            
        $tipoEstudio = $request->tipo_estudio;
        $dia = $request->fecha;
        $dia_aux = explode('/', $dia);        
        $nuevaFecha = $dia_aux[2].'-'.$dia_aux[1].'-'.$dia_aux[0];

        $medico_id = $request->medico_id;
        $consultorio = $request->consultorio;

        if($tipoEstudio == 0){
            $turnosPaciente = DB::table('turno_registrados')                                                      
                        ->join('pacientes','pacientes.id','=','turno_registrados.paciente')
                        ->select('turno_registrados.id as trid','pacientes.nombre as nombrep','pacientes.apellido as apellidop','turno_registrados.horario', 'turno_registrados.asistio','pacientes.dni','turno_registrados.sobreturno','turno_registrados.primerControl','pacientes.telefono','turno_registrados.caja','turno_registrados.comentario', 'turno_registrados.tipo_turno')
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
                            ->select('turno_registrado_videollamadas.id as trid','pacientes.nombre as nombrep','pacientes.apellido as apellidop','turno_registrado_videollamadas.horario', 'turno_registrado_videollamadas.asistio','pacientes.dni','turno_registrado_videollamadas.sobreturno','turno_registrado_videollamadas.primerControl','pacientes.telefono','turno_registrado_videollamadas.pago','turno_registrado_videollamadas.comentario')
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

        //$turnosPaciente = $this->getTurnosPaciente($medico_id, $consultorio, $nuevaFecha); 
        //$cantSobreturnos = $this->getCantidadSobreturnos($medico_id, $consultorio, $nuevaFecha);    
        $contador = $turnosPaciente->count();
        
        $medico = DB::table('medicos')                                                                
                ->where('medicos.id', $medico_id)
                ->where('medicos.activo', 1)                                                      
                ->first();

           // Modulo id: 2 | corresponde a Caja | Comentario
        $moduloCajaComentario = $this->moduloActivo($medico->id, 2);
        $esFeriado = $this->esFeriado($nuevaFecha);

        return response()->json(array('turnosPaciente'=>$turnosPaciente,'medico'=>$medico,'consultorio'=>$consultorio,'dia'=>$dia, 'contador'=>$contador,'cantSobreturnos'=>$cantSobreturnos, 'moduloCajaComentario'=>$moduloCajaComentario,'esFeriado'=>$esFeriado));    
    }

    public function medicoAsignarTurnos(Request $request){
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $fechaSeleccionada = date("d/m/Y");
        $paciente_dni = $request->paciente_dni;
        $usuario_actual=\Auth::user();        
        $medico = DB::table('medicos')                                                                
                ->where('medicos.user_id',$usuario_actual->id)                                                      
                ->first();
        $medico_id = $medico->id;        

        //$medico = medico::find($request->get('medico_id'));
        $consultorio = $medico->consultorio;        
        
        $dias_habilitados = $this->diasHabilitados($medico_id, $consultorio, 0, 0);        
        $diasAtencion = $this->diasAtencion($medico_id, $consultorio, 0, 0);        
        $dias_deshabilitados= $this->diasDeshabilitados2($diasAtencion);
        //$dias_deshabilitados = $this->getDiasDeshabilitados($medico_id,$consultorio, 0);
        
        $fechaAux = explode('/',$fechaSeleccionada);
        $dia = $fechaAux[0];
        $mes = $fechaAux[1];
        $anio = $fechaAux[2];
        $nuevaFecha=$anio.'-'.$mes.'-'.$dia;
        $date = new DateTime($nuevaFecha);      
        
        $diaLetras = date_format($date, 'l');
        
        $dia=$nuevaFecha;                        
        $dia = date("d/m/Y", strtotime ( '-0 hour' , strtotime ($dia) )); //formato 07/03/2019
                        
        $diaSeleccionado = $this->getDiaSeleccionado($diaLetras);
        
        $turnosAux = DB::select('select * from horario_medicos where medico = ? and consultorio = ? and dia = ? and activo=1',
            [$medico_id , $consultorio, $diaSeleccionado]);
        
        //$dia = '14/08/2019';
                
        $data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $nuevaFecha, 0);
        // $data = $this->agregarSobreturnos($medico_id, $consultorio, $diaSeleccionado, $nuevaFecha, 0, $data);
        $someArray = json_decode($data, true);
        
        $fechaLibreDisponible = $this->turnoLibreMasCercano($medico_id, 0, 0);
        $fechaLibreDisponible = $this->convertirFechaParaMostrar($fechaLibreDisponible);

           // Modulo id: 3 | corresponde a Primer Control Doble
        $moduloPrimerControlDoble = $this->moduloActivo($medico->id, 3);
        $moduloAfiliadoObligatorio = $this->moduloActivo($medico->id, 8);

        $esFeriado = $this->esFeriado($nuevaFecha);
        
        $obrasSociales = DB::table('obra_socials')   
                        ->join('obra_social_medicos', 'obra_social_medicos.obra_social', 'obra_socials.id')
                        ->where('obra_social_medicos.medico', $medico->id)
                        ->where('obra_socials.activo', 1)     
                        ->orderBy('obra_socials.nombre')                                                 
                        ->get();   

        return view('turnos_admin_medico.medico_asignar_turnos')                
                ->with('turnos',$someArray)
                ->with('medico',$medico)
                ->with('tipo_estudio', 0)
                ->with('obraSociales',$obrasSociales)
                ->with('paciente_dni',$paciente_dni)
                ->with('consultorio',$consultorio)
                ->with('moduloPrimerControlDoble',$moduloPrimerControlDoble)
                ->with('moduloAfiliadoObligatorio',$moduloAfiliadoObligatorio)
                ->with('fechaLibreDisponible',$fechaLibreDisponible)
                ->with('primerControl','NO')        
                ->with('dias_deshabilitados',$dias_deshabilitados)
                ->with('esFeriado',$esFeriado)
                ->with('dia',$dia); 
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

    function agregarSobreturnos($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada, $nombreAbreviado, $data){
        $someArray = json_decode($data, true);      
        $json = array();

        $sobreturnosRegistrados = DB::table('turno_registrados')
                        ->select('turno_registrados.id', 'turno_registrados.horario', 'pacientes.dni', 'pacientes.apellido', 'pacientes.nombre', 'turno_registrados.tipo_turno', 'turno_registrados.comentario' )                       
                        ->join('pacientes','pacientes.id','=','turno_registrados.paciente')                                             
                        ->where('turno_registrados.medico',$medico_id)
                        ->where('turno_registrados.consultorio', $consultorio_id)
                        ->where('turno_registrados.dia', $diaSeleccionado)
                        ->where('turno_registrados.fechaTurno', $fechaSolicitada)
                        ->where('turno_registrados.sobreturno', 1)                        
                        ->where('turno_registrados.activo', 1)                        
                        ->get();

        foreach($sobreturnosRegistrados as $turno){
            $json['trid'] = $turno->id;
            $json['horario'] = $turno->horario;
            $json['dni'] = $turno->dni;

            if($nombreAbreviado == 1){
                $json['nombre'] = $turno->apellido.', '.substr($turno->nombre,0,1).'.';
            } else{
                $json['nombre'] = $turno->apellido.', '.$turno->nombre;
            }
            
            $json['tipo_turno'] = "0";
            $json['comentario'] = $turno->comentario;                   
            $json['libre'] = 1;                   
            $someArray[] = $json;
        }

        return json_encode($someArray);
    }

    // lunes a viernes a partir de las 9 cada 20 min 7 pacientes... 
    function checkTurnoLibreEspecialAmilcarSosa($medico_id, $consultorio_id, $diaSeleccionado, $fechaSeleccionada){
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

        function checkTurnoLibreEspecialMarina($medico_id, $consultorio_id, $diaSeleccionado, $fechaSeleccionada){        
        $turnos = null;                
        if($fechaSeleccionada >= '2025-07-01') {
            // mostrar 9.30 10.00 10.30 11.00 11.30
            if($diaSeleccionado == 2 || $diaSeleccionado == 4) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
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
                        ->where('horario_medicos.horario','!=' ,'12:00')                       
                        ->where('horario_medicos.horario','!=' ,'12:30')                        
                        ->where('horario_medicos.horario','!=' ,'13:00')
                        ->where('horario_medicos.horario','!=' ,'13:30')                                        
                        ->where('horario_medicos.activo', 1)         
                        ->orderby('horario_medicos.horario')               
                        ->get(); 
            }
            if($diaSeleccionado == 4) {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)    
                        ->where('horario_medicos.horario','!=' ,'11:30')                       
                        ->where('horario_medicos.horario','!=' ,'12:00')
                        ->where('horario_medicos.horario','!=' ,'12:30')                                                
                        ->where('horario_medicos.horario','!=' ,'13:30')                                        
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

    function checkTurnoLibreEspecialCeleste($medico_id, $consultorio_id, $diaSeleccionado, $fechaSeleccionada){
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

    function checkTurnoLibreEspecialLucasSosa($medico_id, $consultorio_id, $diaSeleccionado, $fechaSeleccionada){
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
                        ->where('horario_medicos.activo', 1)                        
                        ->orderby('horario_medicos.horario')
                        ->get();
            return $turnos;
        }
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


    function checkTurnoLibreEspecialFlor($medico_id, $consultorio_id, $diaSeleccionado, $fechaSeleccionada) {
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

//$nombreAbreviado = 0 retorno apellido, nombre 
    //$nombreAbreviado = 1 retorno apellido, n                                 // 2021/06/07
    public function createJson($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada, $nombreAbreviado){
        if($medico_id == 1 || $medico_id == 2 || $medico_id == 5 || $medico_id == 8 || $medico_id == 11 || $medico_id == 13 || $medico_id == 12 || $medico_id == 14 || $medico_id == 15 || $medico_id == 18 || $medico_id == 19 || $medico_id == 23 || $medico_id == 24 || $medico_id == 26 || $medico_id == 31) {
            $fechaSolicitada = str_replace('/','-', $fechaSolicitada);
            if($medico_id == 1)
                $turnos = $this->checkTurnoLibreEspecialFlor($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);
            if($medico_id == 2)
                $turnos = $this->checkTurnoLibreEspecialLucasGili($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);
            if($medico_id == 5)
                $turnos = $this->checkTurnoLibreEspecialLuciaDiomedi($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);
            if($medico_id == 8)
                $turnos = $this->checkTurnoLibreEspecialMarina($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);
            if($medico_id == 11)
                $turnos = $this->checkTurnoLibreEspecialCeciCorti($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);                
            if($medico_id == 12)
                $turnos = $this->checkTurnoLibreEspecialEricaPacheco($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);                
            if($medico_id == 13)
                $turnos = $this->checkTurnoLibreEspecialLucasSosa($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);
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
            if($medico_id == 31)
                $turnos = $this->checkTurnoLibreEspecialNataliaFerrari($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada);
            
        } else {
            $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico_id)
                        ->where('horario_medicos.consultorio', $consultorio_id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.activo', 1)                        
                        ->orderby('horario_medicos.horario')
                        ->get();
        }

        // nuevo agregar fecha
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
        // nuevo agregar fecha fin

        $turnosRegistrados = DB::table('turno_registrados')
                        ->select('turno_registrados.id', 'turno_registrados.horario', 'pacientes.dni', 'pacientes.apellido', 'pacientes.nombre', 'turno_registrados.tipo_turno', 'turno_registrados.comentario' )                       
                        ->join('pacientes','pacientes.id','=','turno_registrados.paciente')                                             
                        ->where('turno_registrados.medico',$medico_id)
                        ->where('turno_registrados.consultorio', $consultorio_id)
                        ->where('turno_registrados.dia', $diaSeleccionado)
                        ->where('turno_registrados.fechaTurno', $fechaSolicitada)
                        ->where('turno_registrados.activo', 1)                        
                        ->get();

        $json = array();
        $data = array();
        $contador = 0;
        $encontre = 0;

        $esFeriado = $this->esFeriado($fechaSolicitada);
        if($esFeriado != null){
            foreach($turnos as $turno){                                    
                $json['horario'] = $turno->horario;
                $json['dni'] = "";
                $json['nombre'] = "Feriado";                   
                $json['libre'] = 1;
                $json['comentario'] = "";       
                $json['tipo_turno'] = isset($turno->tipo_turno) ? $turno->tipo_turno : 1;
                $json['tipo_turno_text'] = Util::getTipoTurno(isset($turno->tipo_turno) ? $turno->tipo_turno : 1);                         
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
                    } else {
                        if($nombreAbreviado == 1){
                            $json['nombre'] = $turnosRegistrados[$contador]->apellido.', '.substr($turnosRegistrados[$contador]->nombre,0,1).'.';
                        } else{
                            $json['nombre'] = $turnosRegistrados[$contador]->apellido.', '.$turnosRegistrados[$contador]->nombre;
                        }
                    }
                    /*$tipoTurno = 'Consulta y ECG';
                    if($turnosRegistrados[$contador]->tipo_turno == 2)
                        $tipoTurno = 'Ecocardiograma Doppler Color';
                    if($turnosRegistrados[$contador]->tipo_turno == 3)
                        $tipoTurno = 'Ecodoppler de vasos de cuello'; */

                    $json['tipo_turno'] = isset($turnosRegistrados[$contador]->tipo_turno) ? $turnosRegistrados[$contador]->tipo_turno : 1;
                    $json['tipo_turno_text'] = Util::getTipoTurno(isset($turnosRegistrados[$contador]->tipo_turno) ? $turnosRegistrados[$contador]->tipo_turno : 1);                                  
                    $json['trid'] = $turnosRegistrados[$contador]->id;                    
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
                $json['tipo_turno'] = $turno->tipo_turno ?? 1;
                $json['tipo_turno_text'] = Util::getTipoTurno($turno->tipo_turno ?? 1);                                  
                $json['comentario'] = "";                   
                $json['libre'] = 0;                   
                $data[] = $json;
            }            
            $encontre=0;
            $contador=0;
        }
        return json_encode($data);  
    }

     public function createJsonTurnosDobles($medico_id, $consultorio_id, $diaSeleccionado, $fechaSolicitada) {
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

     function turnoLibreMasCercano($medico, $primerControl, $esVideollamada){
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $dia= date("Y-m-d");        
        $encontre = 0;                
        //$hayTurnoLibre= $this->checkTurnoLibre($medico, $dia);
        while($encontre==0){
            if($this->esFeriado($dia) == null) // si no es feriado
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
        $medico = DB::table('medicos')                                                                
                ->where('medicos.id',$medico_id)                                                      
                ->first();       

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
            if($medico->id == 1 || $medico->id == 6 || $medico->id == 8 || $medico->id == 11 || $medico->id == 13 || $medico->id == 12 || $medico->id == 14 || $medico->id == 15 || $medico->id == 18 || $medico->id == 23 || $medico->id == 31){
                if($medico->id == 1)
                    $turnos = $this->checkTurnoLibreEspecialFlor($medico->id, $consultorio->id, $diaSeleccionado, $fecha);     
                if($medico->id == 6)
                    $turnos = $this->checkTurnoLibreEspecialGuilleGariboldi($medico->id, $consultorio->id, $diaSeleccionado, $fecha);                
                if($medico->id == 8)
                    $turnos = $this->checkTurnoLibreEspecialMarina($medico->id, $consultorio->id, $diaSeleccionado, $fecha);                
                if($medico->id == 11)
                    $turnos = $this->checkTurnoLibreEspecialCeciCorti($medico->id, $consultorio->id, $diaSeleccionado, $fecha);
                if($medico->id == 12)
                    $turnos = $this->checkTurnoLibreEspecialEricaPacheco($medico->id, $consultorio->id, $diaSeleccionado, $fecha);
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
            // en caso de ser primer control  
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

    public function consultarPaciente(Request $request){        
        $dni_paciente = $request->dni_paciente;     
        $paciente = DB::table('pacientes')                                                                  
                        ->where('pacientes.dni',$dni_paciente)                                                      
                        ->first();          
                        
        return response()->json(array('paciente'=>$paciente));
    }

    public function actualizarAsignarTurnos(Request $request){
        $tipo_estudio = $request->tipo_estudio;
        $fechaSeleccionada = $request->fecha;       //14/08/2019
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
           $fechaLibreDisponible = $this->turnoLibreMasCercano($request->get('medico_id'), 0, 1);
           $fechaLibreDisponible = $this->convertirFechaParaMostrar($fechaLibreDisponible);                  
        } else {
            if($primerControl == 0){
               $data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $nuevaFecha, 0);    
               $fechaLibreDisponible = $this->turnoLibreMasCercano($request->get('medico_id'), 0, 0);
               $fechaLibreDisponible = $this->convertirFechaParaMostrar($fechaLibreDisponible);      
               //return response()->json(array('msj'=>'rodri true'));
            } else {
                //turnos especiales cada 1 hora.                   
                $data = $this-> createJsonTurnosDobles($medico_id, $consultorio, $diaSeleccionado, $nuevaFecha);
                $fechaLibreDisponible = $this->turnoLibreMasCercano($request->get('medico_id'), 1, 0);
                $fechaLibreDisponible = $this->convertirFechaParaMostrar($fechaLibreDisponible);
            }
        }
        $someArray = json_decode($data, true);      
        $esFeriado = $this->esFeriado($nuevaFecha);
        return response()->json(array('turnosPaciente'=>$someArray,'medico_id'=>$medico_id,'consultorio'=>$consultorio,'dia'=>$dia, 'primerControl'=>$primerControl, 'fechaLibreDisponible'=>$fechaLibreDisponible, 'esFeriado'=>$esFeriado));
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

            if($request->localidad != null && strcmp ($request->localidad , '' ) != 0){
                $paciente->localidad = $request->localidad;
            } else {
                $paciente->localidad = '';
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

            if($request->one_signal_id != null && strcmp ($request->one_signal_id , '' ) != 0){
                $paciente->one_signal_id = $request->one_signal_id;
            } else {
                $paciente->one_signal_id = '';
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

      public function registrarTurno($paciente_id, $medico_id, $consultorio, $diaSeleccionado, $horario, $nuevaFecha, $primerControl, $sobreturno, $tipoTurno){
        $usuario_actual=\Auth::user();                

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
        $turnoRegistrado->otorgado_por = $usuario_actual->email;
        $turnoRegistrado->msj_enviado = 0;
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
         if($paciente_id == 14043) // paciente prenatal, no debo validarlo
            return false;
         if($paciente_id == 84) // paciente cancelado, no debo validarlo
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
         if($checkTurno->count()>0)
             return true;
         else 
            return false;    
    }

    function medicoAsignarTurnosPeg(Request $request){
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

            return view('turnos_admin_medico.medico_asignar_turnos_peg')               
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

    public function registrarAsignarTurnoDoble(Request $request){
        $tipo_turno = $request->tipo_turno;
        $fechaTurno = $request->fechaTurno;     
        $fechaAux = explode('/',$fechaTurno);
        $dia = $fechaAux[0];
        $mes = $fechaAux[1];
        $anio = $fechaAux[2];
        $nuevaFecha=$anio.'-'.$mes.'-'.$dia;
        $date = new DateTime($nuevaFecha);      
        //return date_format($date, 'g:ia \o\n l jS F Y');   //12:00am on Friday 7th June 2019  
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
        
        if(($contador==0) && ($contador2==0)){
            $turnoRegistrado = $this->registrarTurno($paciente_id, $medico_id, $consultorio, $diaSeleccionado, $horario, $nuevaFecha, 'SI', 0, $tipo_turno);
        
            $turnoRegistrado2 = $this->registrarTurno($paciente_id, $medico_id, $consultorio, $diaSeleccionado, $horario2, $nuevaFecha, 'SI', 0, $tipo_turno);

            $turnosAux = DB::select('select * from horario_medicos where medico = ? and consultorio = ? and dia = ? and activo=1',
            [$medico_id , $consultorio, $diaSeleccionado]);         
            
            $data = array();
            
            //turnos especiales cada 1 hora.                   
            $data = $this-> createJsonTurnosDobles($medico_id, $consultorio, $diaSeleccionado, $nuevaFecha);                
            
            $someArray = json_decode($data, true);    

            return response()->json(array('turnosPaciente'=>$someArray,'medico_id'=>$medico_id,'consultorio'=>$consultorio,'dia'=>$dia,
                'primerControl'=>$primerControl,'turnoRegistrado'=>1));
        } else {    
           return response()->json(array('turnoRegistrado'=>0));
        }
    }

    function registrarTurnoPeg($request){
        $fechaTurno = $request->fechaTurno;     
        $fechaAux = explode('/',$fechaTurno);
        $dia = $fechaAux[0];
        $mes = $fechaAux[1];
        $anio = $fechaAux[2];
        $nuevaFecha=$anio.'-'.$mes.'-'.$dia;
        $date = new DateTime($nuevaFecha);      
        //return date_format($date, 'g:ia \o\n l jS F Y');   //12:00am on Friday 7th June 2019  
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
        //return date_format($date, 'g:ia \o\n l jS F Y');   //12:00am on Friday 7th June 2019  
        $diaLetras = date_format($date, 'l');

        $diaSeleccionado = $this->getDiaSeleccionado($diaLetras);
        
        $paciente_id = $request->paciente_id;
        $medico_id = $request->medico_id;
        $consultorio = $request->consultorio;           
        $horario = $request->horario;
        $horario2 = $request->horario2;
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

        $turnosPaciente = $this->consultarTurno($medico_id, $consultorio, $diaSeleccionado, $nuevaFecha, $horario);
            
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
            $turnoRegistrado = $this->registrarTurno($paciente_id, $medico_id, $consultorio, $diaSeleccionado, $horario, $nuevaFecha, $primerContorlTexto, 0, $tipo_turno);

            $turnosAux = DB::select('select * from horario_medicos where medico = ? and consultorio = ? and dia = ? and activo=1',
            [$medico_id , $consultorio, $diaSeleccionado]);         
            
            $data = array();
            
            $data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, $nuevaFecha, 0);            
            
            $someArray = json_decode($data, true);      
            return response()->json(array('turnosPaciente'=>$someArray,'medico_id'=>$medico_id,'consultorio'=>$consultorio,'dia'=>$dia, 'primerControl'=>$primerControl,'turnoRegistrado'=>1));

        } else {    
           return response()->json(array('turnoRegistrado'=>0));
        }
    }

    public function mostrarSemana(Request $request){
        $usuario_actual=\Auth::user();        
        $medico = DB::table('medicos')                                                                
                ->where('medicos.user_id',$usuario_actual->id)                                                      
                ->first();
        $medico_id = $medico->id;        
        $consultorio = $medico->consultorio;         
        
        $dias_habilitados = $this->diasHabilitados($medico_id, $consultorio, 0, 0);        
        $diasAtencion = $this->diasAtencion($medico_id, $consultorio, 0, 0);        
        $dias_deshabilitados= $this->diasDeshabilitados2($diasAtencion);
        //$dias_deshabilitados = $this->getDiasDeshabilitados($medico_id,$consultorio, 0);       
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $fechaSeleccionada = date("Y/m/d");
        
        // nueva logica
            
            $cincodiasTotal = $this -> obtener5Dias($fechaSeleccionada, $medico, 0);
            //return 'test 2';
            $cincodias = array();
            $someArrayTotal = array();
            $contSomeArray = 0;
            $cont = 0;        
            
            while($contSomeArray < 5 && $cont<sizeof($cincodiasTotal)) {
                
                $diaSeleccionado = $this-> getDiaSeleccionado2($cincodiasTotal[$cont]);
                $data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, str_replace("/", "-", $cincodiasTotal[$cont]), 1);
                $someArrayAux = json_decode($data, true);
                if (is_array($someArrayAux) && sizeof($someArrayAux) > 0) {

                    $cincodias[] = $cincodiasTotal[$cont];
                    $someArrayTotal[] = $someArrayAux;
                    $contSomeArray++;
                }
                $cont++;                
            }

            if (isset($someArrayTotal[0]) && sizeof($someArrayTotal[0]) > 0) 
                $someArray1 = $someArrayTotal[0];
            else
                $someArray1 = [];

            if (isset($someArrayTotal[1]) && sizeof($someArrayTotal[1]) > 0) 
                $someArray2 = $someArrayTotal[1];
            else
                $someArray2 = [];       

            if (isset($someArrayTotal[2]) && sizeof($someArrayTotal[2]) > 0) 
                $someArray3 = $someArrayTotal[2];
            else
                $someArray3 = [];       

            if (isset($someArrayTotal[3]) && sizeof($someArrayTotal[3]) > 0) 
                $someArray4 = $someArrayTotal[3];
            else
                $someArray4 = [];       

            if (isset($someArrayTotal[4]) && sizeof($someArrayTotal[4]) > 0) 
                $someArray5 = $someArrayTotal[4];
            else
                $someArray5 = [];                                       

            // nueva logica fin       

        $fechaLibreDisponible = $this->turnoLibreMasCercano($medico_id, 0, 0);
    
        $dia_aux = explode('/',$cincodias[0]);        
        $dia = $dia_aux[2].'/'.$dia_aux[1].'/'.$dia_aux[0];

        $dia_aux = explode('-',$fechaLibreDisponible);        
        $fechaLibreDisponible = $dia_aux[2].'/'.$dia_aux[1].'/'.$dia_aux[0];

        $cincodias = $this->transformarFechas($cincodias);
        if($request->option == 4) {
            
            return view('turnos_admin_medico/medico_bloquear_turnos')
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
            return view('turnos_admin_medico/medico_ver_semana')
            ->with('dias_deshabilitados', $dias_deshabilitados)
            ->with('consultorio', $consultorio)
            ->with('dia', $dia)
            ->with('fechaLibreDisponible', $fechaLibreDisponible)
            ->with('moduloAfiliadoObligatorio', $moduloAfiliadoObligatorio)
            ->with('turnos1', $someArray1)
            ->with('turnos2', $someArray2)
            ->with('turnos3', $someArray3)
            ->with('turnos4', $someArray4)
            ->with('turnos5', $someArray5)
            ->with('cincoDias', $cincodias)
            ->with('obraSociales', $obrasSociales)
            ->with('medico', $medico);
        }
    }

    public function transformarFechas($cincoDias){
        $data = array(); 
        for($i=0;$i<sizeof($cincoDias); $i++){            
            $data[] = $this->convertirFechaParaMostrar($cincoDias[$i]);
        }
        return $data;
    }

    function validarFechaValida($medico, $fecha, $dia) {        
        if($medico->id == 1) { // flor
            $cambioHorarioDesde = '2025-02-01';
            if($fecha >= $cambioHorarioDesde) {
                if($dia == 3){
                    return false;
                }
            } else {
                if($dia == 4){
                    return false;
                }
            }
        }
        return true;    
    }

    public function obtener5Dias($fechaPrimerDia, $medico, $esVideollamada)
{
    // Obtener los días en que el médico atiende
    if ($esVideollamada == 1) {
        $diasMedico = DB::table('horario_medico_videollamadas')
            ->where('horario_medico_videollamadas.medico', $medico->id)
            ->where('horario_medico_videollamadas.activo', 1)
            ->distinct()
            ->pluck('dia');
    } else {
        $diasMedicoU = DB::table('horario_medicos')
            ->select('horario_medicos.dia')
            ->where('horario_medicos.medico', $medico->id)
            ->where('horario_medicos.activo', 1)
            ->distinct()
            ->pluck('dia');

        // Obtener días agregados
        $diasAgregadosMedico = DB::table('horarios_medicos_agregados')
            ->join('fechas_agregadas', 'fechas_agregadas.id', '=', 'horarios_medicos_agregados.fecha_agregada_id')
            ->where('horarios_medicos_agregados.medico', $medico->id)
            ->where('horarios_medicos_agregados.consultorio', $medico->consultorio)
            ->where('horarios_medicos_agregados.activo', 1)
            ->where('fechas_agregadas.fecha', '>=', $fechaPrimerDia)
            ->orderBy('horarios_medicos_agregados.horario')
            ->distinct()
            ->pluck('horarios_medicos_agregados.dia');

        // Fusionar y eliminar duplicados
        $diasMedico = $diasMedicoU->merge($diasAgregadosMedico)->unique(); 
    }

    if ($diasMedico->isEmpty()) {
        return [];
    }

    $data = [];
    $cont = 0;
    $fecha = new DateTime($fechaPrimerDia);

    while ($cont < 5) {
        $diaActual = $this->getDiaSeleccionado2($fecha->format('Y-m-d')); 
        $fechaValida = $this->validarFechaValida($medico, $fecha->format('Y-m-d'), $diaActual);

        if ($diasMedico->contains($diaActual) && $fechaValida) {
            $data[] = $fecha->format('Y/m/d');
            $cont++;
        }

        $fecha->modify('+1 day');
    }

    return $data;
}


    public function medicoActualizarListadoVerSemanaPeg(Request $request){
        $medico_id = $request->get('medico_id');
        $medico = medico::find($request->get('medico_id'));
        $consultorio = $request->get('consultorio');
        $dias_deshabilitados = $this->getDiasDeshabilitados($medico_id,$consultorio, 1);               
        $fechaSeleccionada = $request->get('fechaSeleccionada'); // 03/12/2019 d/m/Y
        $dia_aux = explode('/',$fechaSeleccionada);        
        $fechaSeleccionada = $dia_aux[2].'/'.$dia_aux[1].'/'.$dia_aux[0];
        
        // nueva logica
        
            $cincodiasTotal = $this -> obtener5Dias($fechaSeleccionada, $medico, 0);
            $cincodias = array();
            $someArrayTotal = array();
            $contSomeArray = 0;
            $cont = 0;        
            
            while($contSomeArray < 5 && $cont<sizeof($cincodiasTotal)) {
                
                $diaSeleccionado = $this-> getDiaSeleccionado2($cincodiasTotal[$cont]);
                $data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, str_replace("/", "-", $cincodiasTotal[$cont]), 1);
                $someArrayAux = json_decode($data, true);
                if (is_array($someArrayAux) && count($someArrayAux) > 0) {                                
                    $cincodias[] = $cincodiasTotal[$cont];
                    $someArrayTotal[] = $someArrayAux;
                    $contSomeArray++;
                }
                $cont++;                
            }

            $someArray1 = $someArrayTotal[0] ?? [];
            $someArray2 = $someArrayTotal[1] ?? [];
            $someArray3 = $someArrayTotal[2] ?? [];
            $someArray4 = $someArrayTotal[3] ?? [];
            $someArray5 = $someArrayTotal[4] ?? [];

            // nueva logica fin    
        
        $cincodias = $this->transformarFechas($cincodias);

        return response()->json(array('cincodias'=>$cincodias,'turnos1'=>$someArray1,'turnos2'=>$someArray2,'turnos3'=>$someArray3,'turnos4'=>$someArray4,'turnos5'=>$someArray5));
    }

    public function actualizarListadoVerSemana(Request $request){
        $medico_id = $request->get('medico_id');
        $medico = medico::find($request->get('medico_id'));
        $consultorio = $request->get('consultorio');
        $dias_deshabilitados = $this->getDiasDeshabilitados($medico_id,$consultorio, 0);               
        $fechaSeleccionada = $request->get('fechaSeleccionada'); // 03/12/2019 d/m/Y
        $dia_aux = explode('/',$fechaSeleccionada);        
        $fechaSeleccionada = $dia_aux[2].'/'.$dia_aux[1].'/'.$dia_aux[0];
        
        // nueva logica
        
        $cincodiasTotal = $this -> obtener5Dias($fechaSeleccionada, $medico, 0);
        $cincodias = array();
        $someArrayTotal = array();
        $contSomeArray = 0;
        $cont = 0;        
        
        while($contSomeArray < 5 && $cont<sizeof($cincodiasTotal)) {
            
            $diaSeleccionado = $this->getDiaSeleccionado2($cincodiasTotal[$cont]);
            $data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, str_replace("/", "-", $cincodiasTotal[$cont]), 1);
            $someArrayAux = json_decode($data, true);
            if (is_array($someArrayAux) && count($someArrayAux) > 0) {
                $cincodias[] = $cincodiasTotal[$cont];
                $someArrayTotal[] = $someArrayAux;
                $contSomeArray++;
            }
            $cont++;                
        }

        if (isset($someArrayTotal[0])) {
            $someArray1 = $someArrayTotal[0];
        } else {
            $someArray1 = null; // o algún valor predeterminado
        }
        //$someArray1 = $someArrayTotal[0];
        if (isset($someArrayTotal[1])) {
            $someArray2 = $someArrayTotal[1];
        } else {
            $someArray2 = null; // o algún valor predeterminado
        }
        //$someArray2 = $someArrayTotal[1];
        if (isset($someArrayTotal[2])) {
            $someArray3 = $someArrayTotal[2];
        } else {
            $someArray3 = null; // o algún valor predeterminado
        }
        //$someArray3 = $someArrayTotal[2];
        if (isset($someArrayTotal[3])) {
            $someArray4 = $someArrayTotal[3];
        } else {
            $someArray4 = null; // o algún valor predeterminado
        }
        //$someArray4 = $someArrayTotal[3];
        if (isset($someArrayTotal[4])) {
            $someArray5 = $someArrayTotal[4];
        } else {
            $someArray5 = null; // o algún valor predeterminado
        }
        //$someArray5 = $someArrayTotal[4];

        // nueva logica fin   
        
        $cincodias = $this->transformarFechas($cincodias);

        return response()->json(array('cincodias'=>$cincodias,'turnos1'=>$someArray1,'turnos2'=>$someArray2,'turnos3'=>$someArray3,'turnos4'=>$someArray4,'turnos5'=>$someArray5));
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

        // nueva logica
        
            $cincodiasTotal = $this -> obtener5Dias($fechaSeleccionada, $medico, 0);
            $cincodias = array();
            $someArrayTotal = array();
            $contSomeArray = 0;
            $cont = 0;        
            
            while($contSomeArray < 5 && $cont<sizeof($cincodiasTotal)) {
                
                $diaSeleccionado = $this-> getDiaSeleccionado2($cincodiasTotal[$cont]);
                $data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, str_replace("/", "-", $cincodiasTotal[$cont]), 1);
                $someArrayAux = json_decode($data, true);
                if (is_array($someArrayAux) && count($someArrayAux) > 0) {                                
                    $cincodias[] = $cincodiasTotal[$cont];
                    $someArrayTotal[] = $someArrayAux;
                    $contSomeArray++;
                }
                $cont++;                
            }

            $someArray1 = $someArrayTotal[0] ?? [];
            $someArray2 = $someArrayTotal[1] ?? [];
            $someArray3 = $someArrayTotal[2] ?? [];
            $someArray4 = $someArrayTotal[3] ?? [];
            $someArray5 = $someArrayTotal[4] ?? [];


            // nueva logica fin       
        
        $cincodias = $this->transformarFechas($cincodias);
        return response()->json(array('cincodias'=>$cincodias,'turnos1'=>$someArray1,'turnos2'=>$someArray2,'turnos3'=>$someArray3,'turnos4'=>$someArray4,'turnos5'=>$someArray5, 'turnoRegistrado'=>$turnoRegistradoResponse));
    }

    public function medicoRegistrarTurnoPacienteAgendaSemanal(Request $request){
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

            // nueva logica
        
            $cincodiasTotal = $this -> obtener5Dias($fechaSeleccionada, $medico, 0);
            $cincodias = array();
            $someArrayTotal = array();
            $contSomeArray = 0;
            $cont = 0;        
            
            while($contSomeArray < 5 && $cont<sizeof($cincodiasTotal)) {
                
                $diaSeleccionado = $this-> getDiaSeleccionado2($cincodiasTotal[$cont]);
                $data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, str_replace("/", "-", $cincodiasTotal[$cont]), 1);
                $someArrayAux = json_decode($data, true);
                if (is_array($someArrayAux) && count($someArrayAux) > 0) {                                
                    $cincodias[] = $cincodiasTotal[$cont];
                    $someArrayTotal[] = $someArrayAux;
                    $contSomeArray++;
                }
                $cont++;                
            }

            $someArray1 = $someArrayTotal[0] ?? [];
            $someArray2 = $someArrayTotal[1] ?? [];
            $someArray3 = $someArrayTotal[2] ?? [];
            $someArray4 = $someArrayTotal[3] ?? [];
            $someArray5 = $someArrayTotal[4] ?? [];


            // nueva logica fin     
            
            $cincodias = $this->transformarFechas($cincodias);

            return response()->json(array('cincodias'=>$cincodias,'turnos1'=>$someArray1,'turnos2'=>$someArray2,'turnos3'=>$someArray3,'turnos4'=>$someArray4,'turnos5'=>$someArray5, 'turnoRegistrado'=>1));
        } else {
            return response()->json(array('turnoRegistrado'=>0));
        }
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
        
        $dia_aux = explode('/', $request->get('fechaInput'));        
        $fechaInput = $dia_aux[2].'/'.$dia_aux[1].'/'.$dia_aux[0];

        $paciente = DB::table('pacientes')                                                                                      
                    ->where('pacientes.dni',$paciente_dni)                   
                    ->first();

        if($tipoTurno == 4){
            return $this->registrarTurnoAgendaSemanalPeg($request);
        }

        $turnoLibre = $this->consultarTurno($medico_id, $consultorio, $diaSeleccionado, $fechaTurno, $horario);     
        
        if($turnoLibre->count() == 0){
            $turnoRegistrado = $this->registrarTurno($paciente->id, $medico_id, $consultorio, $diaSeleccionado, $horario, $fechaTurno, 'NO', 0, $tipoTurno);                     

            // nueva logica
        
            $cincodiasTotal = $this -> obtener5Dias($fechaInput, $medico, 0);
            $cincodias = array();
            $someArrayTotal = array();
            $contSomeArray = 0;
            $cont = 0;        
            
            while($contSomeArray < 5 && $cont<sizeof($cincodiasTotal)) {
                
                $diaSeleccionado = $this-> getDiaSeleccionado2($cincodiasTotal[$cont]);
                $data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, str_replace("/", "-", $cincodiasTotal[$cont]), 1);
                $someArrayAux = json_decode($data, true);
                if (is_array($someArrayAux) && count($someArrayAux) > 0) {                    
                    $cincodias[] = $cincodiasTotal[$cont];
                    $someArrayTotal[] = $someArrayAux;
                    $contSomeArray++;
                }
                $cont++;                
            }

            $someArray1 = $someArrayTotal[0] ?? [];
            $someArray2 = $someArrayTotal[1] ?? [];
            $someArray3 = $someArrayTotal[2] ?? [];
            $someArray4 = $someArrayTotal[3] ?? [];
            $someArray5 = $someArrayTotal[4] ?? [];

            // nueva logica fin      
            
            $cincodias = $this->transformarFechas($cincodias);

            return response()->json(array('cincodias'=>$cincodias,'turnos1'=>$someArray1,'turnos2'=>$someArray2,'turnos3'=>$someArray3,'turnos4'=>$someArray4,'turnos5'=>$someArray5, 'turnoRegistrado'=>1));
        } else {
            return response()->json(array('turnoRegistrado'=>0));
        }
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

        $miTurnoRegistrado = turnoRegistradoVideollamada::find($turnoRegistrado->id);
        $miTurnoRegistrado->activo = 0;
        $miTurnoRegistrado->comentario = 'Cancelado por: '.$medico->apellido.', '.$medico->nombre;
        $miTurnoRegistrado->save();
        //$miTurnoRegistrado->delete();
            
        // nueva logica
        
            $cincodiasTotal = $this -> obtener5Dias($fechaSeleccionada, $medico, 0);
            $cincodias = array();
            $someArrayTotal = array();
            $contSomeArray = 0;
            $cont = 0;        
            
            while($contSomeArray < 5 && $cont<sizeof($cincodiasTotal)) {
                
                $diaSeleccionado = $this-> getDiaSeleccionado2($cincodiasTotal[$cont]);
                $data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, str_replace("/", "-", $cincodiasTotal[$cont]), 1);
                $someArrayAux = json_decode($data, true);
                if (is_array($someArrayAux) && count($someArrayAux) > 0) {
                    $cincodias[] = $cincodiasTotal[$cont];
                    $someArrayTotal[] = $someArrayAux;
                    $contSomeArray++;
                }
                $cont++;                
            }

            $someArray1 = $someArrayTotal[0] ?? [];
            $someArray2 = $someArrayTotal[1] ?? [];
            $someArray3 = $someArrayTotal[2] ?? [];
            $someArray4 = $someArrayTotal[3] ?? [];
            $someArray5 = $someArrayTotal[4] ?? [];


            // nueva logica fin   
        
        $cincodias = $this->transformarFechas($cincodias);              
        return response()->json(array('cincodias'=>$cincodias,'turnos1'=>$someArray1,'turnos2'=>$someArray2,'turnos3'=>$someArray3,'turnos4'=>$someArray4,'turnos5'=>$someArray5, 'texto'=>'1'));
    }


    public function borrarTurnoAgendaSemanal(Request $request){                    
        $tipo_turno = $request->tipo_turno;
        if($tipo_turno == 4){
            return $this->borrarTurnoAgendaSemanalPeg($request);
        }
        $medico_id = $request->get('medico_id');
        $medico = medico::find($request->get('medico_id'));
        $consultorio = $request->get('consultorio');
        $dias_deshabilitados = $this->getDiasDeshabilitados($medico_id,$consultorio, 0);               
        $fechaTurno = $request->get('fechaTurno');
        $horario = $request->get('horario');
        $diaSeleccionado = $this-> getDiaSeleccionado2($fechaTurno);
        
        $dia_aux = explode('/', $request->get('fechaInput'));        
        $fechaInput = $dia_aux[2].'-'.$dia_aux[1].'-'.$dia_aux[0];
        
        $turnoRegistrado = DB::table('turno_registrados')                                                                   
            ->where('turno_registrados.medico',$medico_id)
            ->where('turno_registrados.dia',$diaSeleccionado)
            ->where('turno_registrados.consultorio',$consultorio)
            ->where('turno_registrados.fechaTurno',$fechaTurno)
            ->where('turno_registrados.horario',$horario)
            ->where('turno_registrados.activo',1)
            ->first();  

        $miTurnoRegistrado = turnoRegistrado::find($turnoRegistrado->id);
        $miTurnoRegistrado->activo = 0;
        $miTurnoRegistrado->comentario = 'Cancelado por :'.$this->getMedico()->apellido.', '.$this->getMedico()->nombre;
        $miTurnoRegistrado->save();
        //$miTurnoRegistrado->delete();
            
        // nueva logica
        
            $cincodiasTotal = $this -> obtener5Dias($fechaInput, $medico, 0);
            $cincodias = array();
            $someArrayTotal = array();
            $contSomeArray = 0;
            $cont = 0;        
            
            while($contSomeArray < 5 && $cont<sizeof($cincodiasTotal)) {
                
                $diaSeleccionado = $this-> getDiaSeleccionado2($cincodiasTotal[$cont]);
                $data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, str_replace("/", "-", $cincodiasTotal[$cont]), 1);
                $someArrayAux = json_decode($data, true);                
                if (is_array($someArrayAux) && count($someArrayAux) > 0) {
                    $cincodias[] = $cincodiasTotal[$cont];
                    $someArrayTotal[] = $someArrayAux;
                    $contSomeArray++;
                }
                $cont++;                
            }

            $someArray1 = $someArrayTotal[0] ?? [];
            $someArray2 = $someArrayTotal[1] ?? [];
            $someArray3 = $someArrayTotal[2] ?? [];
            $someArray4 = $someArrayTotal[3] ?? [];
            $someArray5 = $someArrayTotal[4] ?? [];


            // nueva logica fin   
        
        $cincodias = $this->transformarFechas($cincodias);

        return response()->json(array('cincodias'=>$cincodias,'turnos1'=>$someArray1,'turnos2'=>$someArray2,'turnos3'=>$someArray3,'turnos4'=>$someArray4,'turnos5'=>$someArray5, 'texto'=>'1'));
        
    }

    public function bloquarDiaAgendaSemanal(Request $request){

        $paciente_dni = $request->get('paciente');
        $medico_id = $request->get('medico_id');
        $medico = medico::find($request->get('medico_id'));
        $consultorio = $request->get('consultorio');
        $dias_deshabilitados = $this->getDiasDeshabilitados($medico_id, $consultorio, 0);               
        $fechaTurno = $request->get('fechaTurno');
        $horario = $request->get('horario');
        $diaSeleccionado = $this-> getDiaSeleccionado2($fechaTurno);
        $tipoTurno = $request->tipo_turno;

        $dia_aux = explode('/', $request->get('fechaInput'));        
        $fechaInput = $dia_aux[2].'-'.$dia_aux[1].'-'.$dia_aux[0];

        $paciente = DB::table('pacientes')                                                                                      
                    ->where('pacientes.dni',$paciente_dni)                   
                    ->first();

        $turnoLibre = DB::table('turno_registrados')                                                                         
                    ->where('turno_registrados.medico',$medico_id)                   
                    ->where('turno_registrados.consultorio',$consultorio)                                     
                    ->where('turno_registrados.fechaTurno',$fechaTurno)
                    ->where('turno_registrados.dia',$diaSeleccionado)
                    ->where('turno_registrados.activo',1)
                    ->get();
        
        if($turnoLibre->count() == 0){  

            $turnosCancelados = $this->cancelarTurnosDiaCompleto($medico_id, $consultorio, $fechaTurno, $diaSeleccionado, $paciente, $tipoTurno);
            
            // nueva logica
        
            $cincodiasTotal = $this -> obtener5Dias($fechaInput, $medico, 0);
            $cincodias = array();
            $someArrayTotal = array();
            $contSomeArray = 0;
            $cont = 0;        
            
            while($contSomeArray < 5 && $cont<sizeof($cincodiasTotal)) {
                
                $diaSeleccionado = $this-> getDiaSeleccionado2($cincodiasTotal[$cont]);
                $data = $this-> createJson($medico_id, $consultorio, $diaSeleccionado, str_replace("/", "-", $cincodiasTotal[$cont]), 1);
                $someArrayAux = json_decode($data, true);
                if (is_array($someArrayAux) && count($someArrayAux) > 0) {                             
                    $cincodias[] = $cincodiasTotal[$cont];
                    $someArrayTotal[] = $someArrayAux;
                    $contSomeArray++;
                }
                $cont++;                
            }

            $someArray1 = $someArrayTotal[0] ?? [];
            $someArray2 = $someArrayTotal[1] ?? [];
            $someArray3 = $someArrayTotal[2] ?? [];
            $someArray4 = $someArrayTotal[3] ?? [];
            $someArray5 = $someArrayTotal[4] ?? [];


            // nueva logica fin     
            
            $cincodias = $this->transformarFechas($cincodias);

            return response()->json(array('cincodias'=>$cincodias,'turnos1'=>$someArray1,'turnos2'=>$someArray2,'turnos3'=>$someArray3,'turnos4'=>$someArray4,'turnos5'=>$someArray5, 'turnoCancelado'=>1));
        } else {
            return response()->json(array('turnoCancelado'=>0));
        }
    }

    public function cancelarTurnosDiaCompleto($medico_id, $consultorio, $fechaTurno, $diaSeleccionado, $paciente, $tipoTurno){      
        $horariosMedico = DB::table('horario_medicos')                                                                                          
            ->where('horario_medicos.medico',$medico_id)
            ->where('horario_medicos.dia',$diaSeleccionado)
            ->where('horario_medicos.consultorio',$consultorio)     
            ->where('horario_medicos.activo', 1)                          
            ->get();

        foreach ($horariosMedico as $turno){
            $turnoRegistrado = $this->registrarTurno($paciente->id, $medico_id, $consultorio, $diaSeleccionado, $turno->horario, $fechaTurno, 'NO', 0, $tipoTurno);                     
        }

        return $horariosMedico;
    }

    function medicoAsignarsobreTurnosPeg(Request $request){
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $dia= date("d/m/Y");        
        $dia_aux = date("Y/m/d");
        $dia_aux_turno = date("Y-m-d");
        $paciente_dni = $request->paciente_dni;
        //$dia='10/07/2019';
        $usuario_actual=\Auth::user();        
        $medico = DB::table('medicos')                                                                
                ->where('medicos.user_id',$usuario_actual->id)                                                      
                ->first();
        $medico_id = $medico->id;            
        $consultorio = $medico->consultorio;        
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

        $dias_deshabilitados = $this->getDiasDeshabilitados($medico_id, $consultorio, 1);
        $cantSobreturnos = $this->getCantidadSobreturnosPeg($medico_id, $consultorio, $dia_aux);
        $esFeriado = $this->esFeriado($dia_aux);

        $obrasSociales = DB::table('obra_socials')                                                                
                        ->where('obra_socials.activo', 1)                                                      
                        ->orderBy('obra_socials.nombre')
                        ->get();    
        
        return view('turnos_admin_medico.medico_admin_sobreturnos_peg')
                ->with('turnosPaciente',$turnosPaciente)
                ->with('medico',$medico)
                ->with('tipo_estudio', 1)
                ->with('obraSociales',$obrasSociales)
                ->with('paciente_dni',$paciente_dni)
                ->with('consultorio',$consultorio)
                ->with('contador',$contador)
                ->with('dias_deshabilitados',$dias_deshabilitados)
                ->with('cantSobreturnos',$cantSobreturnos)
                ->with('esFeriado',$esFeriado)
                ->with('dia',$dia);     
    }

    public function adminSobreturnos(Request $request){     
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $dia= date("d/m/Y");        
        $dia_aux = date("Y/m/d");
        $paciente_dni = $request->paciente_dni;
        //$dia='10/07/2019';
        $usuario_actual=\Auth::user();        
        $medico = DB::table('medicos')                                                                
                ->where('medicos.user_id',$usuario_actual->id)                                                      
                ->first();
        $medico_id = $medico->id;            
        $consultorio = $medico->consultorio;        
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

        $dias_habilitados = $this->diasHabilitados($medico_id, $consultorio, 0, 0);        
        $diasAtencion = $this->diasAtencion($medico_id, $consultorio, 0, 0);        
        $dias_deshabilitados= $this->diasDeshabilitados2($diasAtencion);
        //$dias_deshabilitados = $this->getDiasDeshabilitados($medico_id, $consultorio, 0);
        $cantSobreturnos = $this->getCantidadSobreturnos($medico_id, $consultorio, $dia_aux);
        $esFeriado = $this->esFeriado($dia_aux);

        $obrasSociales = DB::table('obra_socials')                                                                
                        ->where('obra_socials.activo', 1)                                                      
                        ->orderBy('obra_socials.nombre')
                        ->get();    
        $moduloAfiliadoObligatorio = $this->moduloActivo($medico->id, 8);

        return view('turnos_admin_medico.medico_admin_sobreturnos')
                ->with('turnosPaciente',$turnosPaciente)
                ->with('medico',$medico)
                ->with('tipo_estudio', 0)
                ->with('obraSociales',$obrasSociales)
                ->with('paciente_dni',$paciente_dni)
                ->with('consultorio',$consultorio)
                ->with('moduloAfiliadoObligatorio',$moduloAfiliadoObligatorio)
                ->with('contador',$contador)
                ->with('dias_deshabilitados',$dias_deshabilitados)
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

    public function registrarSobreturnoPeg($request){
        $fechaTurno = $request->fechaTurno;     
        $fechaAux = explode('/',$fechaTurno);
        $dia = $fechaAux[0];
        $mes = $fechaAux[1];
        $anio = $fechaAux[2];
        $nuevaFecha=$anio.'-'.$mes.'-'.$dia;
        $date = new DateTime($nuevaFecha);      
        //return date_format($date, 'g:ia \o\n l jS F Y');   //12:00am on Friday 7th June 2019  
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
        //return date_format($date, 'g:ia \o\n l jS F Y');   //12:00am on Friday 7th June 2019  
        $diaLetras = date_format($date, 'l');

        $diaSeleccionado = $this->getDiaSeleccionado($diaLetras);
        
        $paciente_id = $request->paciente_id;
        $medico_id = $request->medico_id;
        $consultorio = $request->consultorio;           
        $horario = $request->horario;
        
        $turnosPaciente_aux = $this->consultarTurno($medico_id, $consultorio, $diaSeleccionado, $nuevaFecha, $horario);
        $contador = $turnosPaciente_aux->count();

        if ($contador == 0){
            $turnoRegistrado = $this->registrarTurno($paciente_id, $medico_id, $consultorio, $diaSeleccionado, $horario, $nuevaFecha, 'NO', 1 , $tipoTurno);

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

    public function nuevoPaciente(){
        $usuario_actual=\Auth::user();        
        $medico = DB::table('medicos')                                                                
                ->where('medicos.user_id',$usuario_actual->id)                                                      
                ->first();
        $consultorio = $medico->consultorio;
        $obrasSociales = DB::table('obra_socials')                                                                
                        ->where('obra_socials.activo', 1)
                        ->orderBy('obra_socials.nombre')                                                      
                        ->get();
        $moduloAfiliadoObligatorio = $this->moduloActivo($medico->id, 8);

        return view('turnos_admin_medico/medico_nuevo_paciente')
                        ->with('medico_id',$medico->id)
                        ->with('obraSociales',$obrasSociales)
                        ->with('medico',$medico)
                        ->with('moduloAfiliadoObligatorio',$moduloAfiliadoObligatorio)
                        ->with('consultorio',$consultorio);
    }

    public function adminPacientes(){
        $usuario_actual = \Auth::user(); //obtengo los datos del usuario en sesion
        $usuario_id = $usuario_actual->id;      

        $medico = DB::table('medicos')->where('medicos.user_id', $usuario_id)->first();               
        $pacientes = DB::table('paciente_secretarias')                      
                    ->join('pacientes','pacientes.id','=','paciente_secretarias.paciente')                  
                    ->select('pacientes.id','pacientes.nombre','pacientes.apellido','pacientes.telefono','pacientes.dni','paciente_secretarias.id as pac_id', 'pacientes.mail')
                    ->where('paciente_secretarias.consultorio',$medico->consultorio)
                    ->where('paciente_secretarias.activo',0)
                    ->where('pacientes.activo',2)
                    ->get();

        return View('turnos_admin_medico.medico_alta_paciente')
        ->with('pacientes',$pacientes)->with('medico',$medico);     
    }

    public function activarPaciente(Request $request) {
        $paciente = paciente::find($request->get('paciente'));
        $paciente->activo = 1;
        $paciente->save();

        $pacienteSec = pacienteSecretaria::find($request->get('pacienteSecretariaId'));
        $pacienteSec->activo = 1;
        $pacienteSec->save();        

        $usuario_actual = \Auth::user(); //obtengo los datos del usuario en sesion
        $usuario_id = $usuario_actual->id;      

        $medico = DB::table('medicos')->where('medicos.user_id', $usuario_id)->first();               
        $this->vincularMedicoPaciente($medico->id, $paciente->id);
        $this->vincularSecretariaPaciente($paciente->id, $medico->consultorio);

        $pacientes = DB::table('paciente_secretarias')                      
                    ->join('pacientes','pacientes.id','=','paciente_secretarias.paciente')                  
                    ->select('pacientes.id','pacientes.nombre','pacientes.apellido','pacientes.telefono','pacientes.dni','paciente_secretarias.id as pac_id', 'pacientes.mail')
                    ->where('paciente_secretarias.consultorio',$medico->consultorio)
                    ->where('paciente_secretarias.activo',0)
                    ->where('pacientes.activo',2)
                    ->get();

        if($paciente->mail != null){
            $data = array('nombre'=>$paciente->nombre,'apellido'=>$paciente->apellido);
            Mail::to($paciente->mail)->queue(new ActivarPacienteMailable($data));
        }

        return response()->json(array('pacientes'=>$pacientes));
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

        return response()->json(array('turnosPaciente'=>$turnosPaciente,'medico'=>$medico,'consultorio'=>$consultorio,'dia'=>$dia,'contador'=>$contador,'moduloCajaComentario'=>$moduloCajaComentario));
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
                ->where('medicos.id',$medico_id) 
                 ->where('medicos.activo', 1)                                                       
                ->first();

           // Modulo id: 2 | corresponde a Caja | Comentario
        $moduloCajaComentario = $this->moduloActivo($medico->id, 2);

        return response()->json(array('turnosPaciente'=>$turnosPaciente,'medico'=>$medico,'consultorio'=>$consultorio,'dia'=>$dia,'contador'=>$contador,'moduloCajaComentario'=>$moduloCajaComentario));
    }

    public function getMedico(){
        $usuario_actual=\Auth::user();        
        $medico = DB::table('medicos')                                                                
                ->where('medicos.user_id',$usuario_actual->id)                                                      
                ->first();
        return $medico;
    }

    public function configuracion(){
        $medico = $this->getMedico();
        $medico_id = $medico->id;        
        $videollamada = 0;
        $dias = DB::table('medico_primer_controls')                                                                
                ->where('medico_primer_controls.medico',$medico_id) 
                ->where('medico_primer_controls.activo', 1)                                                       
                ->get();
        
        $data = array();
        $json = array();
        foreach($dias as $dia){
            $json['id'] = $dia->id;
            $json['dia'] = $this->getDiaNombre($dia->dia);
            $json['cantidad'] = $dia->cantidadPrimerControl;
            $data[] = $json; 
        }                                            
        
        $moduloCantidadPrimerControl = 0;
        if(($this->moduloExiste($medico_id, 3))->count()>0)
            $moduloCantidadPrimerControl = 1;

        $moduloVideollamada = 0;
        if(($this->moduloExiste($medico_id, 6))->count()>0){
            $moduloVideollamada = 1;
            $videollamada = DB::table('videollamadas')                                                                
                            ->where('videollamadas.medico', $medico_id) 
                            ->where('videollamadas.activo', 1)                                                       
                            ->get();
        }   
        $medicoConfig = DB::table('medico_configs')                                                                
                            ->where('medico_configs.medico', $medico_id)                             
                            ->get();
        if($medicoConfig->count() == 0) { 
            $newMedicoConfig = new MedicoConfig;        
            $newMedicoConfig->medico = $medico_id;
            $newMedicoConfig->modulo = 9;
            $newMedicoConfig->valor_consulta = 0;                            
            $newMedicoConfig->valor_string = '+180d';
            $newMedicoConfig->valor_integer = 180;                        
            $newMedicoConfig->activo = 1;
            $newMedicoConfig->save();
        } else {
            $newMedicoConfig = $medicoConfig[0];
        }

        return View('turnos_admin_medico.medico_config')
                    ->with('actualizar', 0)
                    ->with('moduloCantidadPrimerControl', $moduloCantidadPrimerControl)
                    ->with('videollamada', $videollamada)
                    ->with('moduloVideollamada', $moduloVideollamada)
                    ->with('medicoConfig', $newMedicoConfig)
                    ->with('dias',$data);     
    }

    function actualizarValorConsulta(Request $request) {
        $medico_id = $request->medico_id;
        $nuevoValor = $request->valorConsulta;
        $medicoConfig = DB::table('medico_configs')                                                                
                            ->where('medico_configs.medico', $medico_id)                             
                            ->get();
        $mConfig = MedicoConfig::find($medicoConfig[0]->id);
        $mConfig->valor_consulta = $nuevoValor;
        $mConfig->save();

        return response()->json(array('mConfig'=>$mConfig));   
    }

    public function configPrimerControlActualizar(Request $request){

        $medico = $this->getMedico();
        $medico_id = $medico->id;        

        $dias = DB::table('medico_primer_controls')                                                                
                ->where('medico_primer_controls.medico',$medico_id) 
                ->where('medico_primer_controls.activo', 1)                                                       
                ->get();
        
        $data = array();
        $json = array();
        foreach($dias as $dia){
            $current = MedicoPrimerControl::find($dia->id);
            $id_aux = $dia->id;
            $current->cantidadPrimerControl = $request->$id_aux;
            $current->save();

            $json['id'] = $current->id;
            $json['dia'] = $this->getDiaNombre($current->dia);
            $json['cantidad'] = $current->cantidadPrimerControl;
            $data[] = $json;
        }                         
        //return $data;
        return View('turnos_admin_medico.medico_config')
                    ->with('actualizar', 1)
                    ->with('dias',$data);
    }

    public function getDiaNombre($diaNumero){
        $diaNombre = "";
        switch ($diaNumero) {
        case 1:
            $diaNombre='Lunes';
            break;
        case 2:
            $diaNombre='Martes';
            break;
        case 3:
            $diaNombre='Miercoles';
            break;
        case 4:
            $diaNombre='Jueves';
            break;
        case 5:
            $diaNombre='Viernes';
            break;
        case 6:
            $diaNombre='Sabado';
            break;
         case 7:
            $diaNombre='Domingo';
            break;
        }
        return $diaNombre;
    }

    public function guardarVentanaDias(Request $request){
        $medico_id = $request->medico_id;
        $valor_string = $request->valor_string;
        $valor_integer = $request->valor_integer;

        $ventanaDias = DB::table('medico_configs')                                                                
                    ->where('medico_configs.medico',$medico_id)
                    ->where('medico_configs.modulo', 9)                                                      
                    ->where('medico_configs.activo', 1)
                    ->first();

        if($ventanaDias == null) {
            $vd = new MedicoConfig;
            $vd->medico = $medico_id;
            $vd->modulo = 9;
            $vd->valor_consulta = 0;            
            $vd->activo = 1;
            
        } else {
            $vd = MedicoConfig::find($ventanaDias->id);
        }
        $vd->valor_string = $valor_string;
        $vd->valor_integer = $valor_integer;
        $vd->save();

        return response()->json(array('response'=>1));   
    }

    public function adminModuloMedico(Request $request){
        $medico_id = $request->medico_id;
        $consultorio_id = $request->consultorio_id;
        $activarPacienteCheck = $request->activarPaciente;  // modulo 1
        $cajaComentarioCheck = $request->cajaComentario;   // modulo 2
        $primerControlDobleCheck = $request->primerControlDoble; // modulo 3
        $soloUnTurnoCheck = $request->soloUnTurno; // modulo 4
        $recetasCheck = $request->recetas; // modulo 5
        $videollamadasCheck = $request->videollamadas; // modulo 6
        $mercadopagoCheck = $request->mercadopago; // modulo 7
        $afiliadoObligatorioCheck = $request->afiliadoObligatorio; // modulo 8
        $ventanaDiasCheck = $request->ventanaDias; // modulo 9
        $extraTurnoCheck = $request->extraTurno; // modulo 10
     
        $moduloActivarPacienteExiste = $this->moduloExiste($medico_id, 1);
        if($activarPacienteCheck == 1){    
            if($moduloActivarPacienteExiste->count()==0){ // quiere decir que NO existe
                $this->agregarModulo($medico_id, 1);
            }
        } else {
            if($moduloActivarPacienteExiste->count()>0){ // quiere decir que existe            
                $this->eliminarModulo($moduloActivarPacienteExiste[0]->id);
            }
        }

        $moduloCajaComentarioExiste = $this->moduloExiste($medico_id, 2);
        if($cajaComentarioCheck == 1){    
            if($moduloCajaComentarioExiste->count()==0) // quiere decir que NO existe
                $this->agregarModulo($medico_id, 2);
        } else {
            if($moduloCajaComentarioExiste->count()>0) // quiere decir que existe
                $this->eliminarModulo($moduloCajaComentarioExiste[0]->id);
        }

        $moduloPrimerControlDobleExiste = $this->moduloExiste($medico_id, 3);
        if($primerControlDobleCheck == 1){    
            if($moduloPrimerControlDobleExiste->count()==0) // quiere decir que NO existe
                $this->agregarModulo($medico_id, 3);
        } else {
            if($moduloPrimerControlDobleExiste->count()>0) // quiere decir que existe
                $this->eliminarModulo($moduloPrimerControlDobleExiste[0]->id);
        }

        $moduloSoloUnTurno = $this->moduloExiste($medico_id, 4);
        if($soloUnTurnoCheck == 1){    
            if($moduloSoloUnTurno->count()==0) // quiere decir que NO existe
                $this->agregarModulo($medico_id, 4);
        } else {
            if($moduloSoloUnTurno->count()>0) // quiere decir que existe
                $this->eliminarModulo($moduloSoloUnTurno[0]->id);
        }

        $moduloRecetas = $this->moduloExiste($medico_id, 5);
        if($recetasCheck == 1){    
            if($moduloRecetas->count()==0) // quiere decir que NO existe
                $this->agregarModulo($medico_id, 5);
        } else {
            if($moduloRecetas->count()>0) // quiere decir que existe
                $this->eliminarModulo($moduloRecetas[0]->id);
        }

        $moduloVideollamadas = $this->moduloExiste($medico_id, 6);
        if($videollamadasCheck == 1){    
            if($moduloVideollamadas->count()==0) // quiere decir que NO existe
                $this->agregarModulo($medico_id, 6);                
                $this->crearVideollamada($medico_id, $consultorio_id);
        } else {
            if($moduloVideollamadas->count()>0) // quiere decir que existe
                $this->eliminarModulo($moduloVideollamadas[0]->id);
        }

        $moduloMercadopago = $this->moduloExiste($medico_id, 7);
        if($mercadopagoCheck == 1){    
            if($moduloMercadopago->count()==0) // quiere decir que NO existe
                $this->agregarModulo($medico_id, 7);
        } else {
            if($moduloMercadopago->count()>0) // quiere decir que existe
                $this->eliminarModulo($moduloMercadopago[0]->id);
        }

        $moduloAfiliadoObligatorio = $this->moduloExiste($medico_id, 8);
        if($afiliadoObligatorioCheck == 1){    
            if($moduloAfiliadoObligatorio->count()==0) // quiere decir que NO existe
                $this->agregarModulo($medico_id, 8);
        } else {
            if($moduloAfiliadoObligatorio->count()>0) // quiere decir que existe
                $this->eliminarModulo($moduloAfiliadoObligatorio[0]->id);
        }

        $moduloVentanaDias = $this->moduloExiste($medico_id, 9);
        if($ventanaDiasCheck == 1){    
            if($moduloVentanaDias->count()==0) // quiere decir que NO existe
                $this->agregarModulo($medico_id, 9);
        } else {
            if($moduloVentanaDias->count()>0){ // quiere decir que existe
                $this->eliminarModulo($moduloVentanaDias[0]->id);
                $this->eliminarVentanaConfig($medico_id);
            }
        }

        $moduloExtraTurno = $this->moduloExiste($medico_id, 10);
        if($extraTurnoCheck == 1){    
            if($moduloExtraTurno->count()==0) // quiere decir que NO existe
                $this->agregarModulo($medico_id, 10);
        } else {
            if($moduloExtraTurno->count()>0) // quiere decir que existe
                $this->eliminarModulo($moduloExtraTurno[0]->id);
        }

        return response()->json(array('response'=>11));    
    }

    public function crearVideollamada($medico_id, $consultorio_id){
        if(!$this->existeVideollamada($medico_id)){
            $videollamada = new Videollamada;
            $videollamada->medico = $medico_id;
            $videollamada->link = '';
            $videollamada->link_pago = '';
            $videollamada->key = '';
            $videollamada->secret = '';
            $videollamada->consultorio = $consultorio_id;
            $videollamada->disponible = 0;
            $videollamada->importe = 1;
            $videollamada->perfil = 1;
            $videollamada->activo = 1;
            $videollamada->save();
        }
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

    public function moduloExiste($medico_id, $modulo_id){
        $moduloExiste = DB::table('modulo_medicos')                                                                
                ->where('modulo_medicos.medico', $medico_id) 
                ->where('modulo_medicos.modulo', $modulo_id)                                                       
                ->where('modulo_medicos.activo', 1)                                                       
                ->get();
        return $moduloExiste;
    }

    public function agregarModulo($medico_id, $modulo_id){
        $agregarModulo = new ModuloMedico;
        $agregarModulo->medico = $medico_id;
        $agregarModulo->modulo = $modulo_id;
        $agregarModulo->activo = 1;
        $agregarModulo->save();
    }

    public function eliminarModulo($id){
        $moduloMedico = ModuloMedico::find($id);        
        $moduloMedico->delete();        
    }

    public function eliminarVentanaConfig($medico_id){
        $aux = DB::table('medico_configs')                                                             
                        ->where('medico_configs.medico', $medico_id)
                        ->where('medico_configs.modulo', 9)
                        ->first();
        if($aux != null){
            $ventanaConfig = MedicoConfig::find($aux->id);
            $ventanaConfig->delete();
        }
    }

    // Me devuelve el feriado, debo validar si es distinto de null.
    public function esFeriado($fecha){
        $validarFeriado = DB::table('feriados')                                                             
                        ->where('feriados.fecha', '=', $fecha)                        
                        ->first();        
        return $validarFeriado;
    }

    public function modalBuscarPacientesList(){
        $consultorio_id = $this->getMedico()->consultorio;
        $medico_id = $this->getMedico()->id;

        $users = DB::table('pacientes')                                
                                ->join('medico_pacientes','medico_pacientes.paciente','=','pacientes.id')
                                ->select('pacientes.*', 'medico_pacientes.bloqueado')                                
                                ->where('medico_pacientes.medico',$medico_id)   
                                ->where('pacientes.dni','!=', 99999)                         
                                ->where('pacientes.activo', 1)                                 
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
        $consultorio_id = $this->getMedico()->consultorio;
        $medico_id = $this->getMedico()->id;
        /*$users = DB::table('pacientes')                                
                                ->join('paciente_secretarias','paciente_secretarias.paciente','=','pacientes.id')
                                ->select('pacientes.*')                                
                                ->where('paciente_secretarias.consultorio',$consultorio_id)   
                                ->where('pacientes.dni','!=', 99999)                         
                                ->where('pacientes.activo', 1) 
                                ->where('paciente_secretarias.activo', 1)                                 
                                ->orderby('pacientes.apellido');        
        */
        $users = DB::table('pacientes')                                
                                ->join('medico_pacientes','medico_pacientes.paciente','=','pacientes.id')
                                ->select('pacientes.*', 'medico_pacientes.bloqueado')                                
                                ->where('medico_pacientes.medico',$medico_id)   
                                ->where('pacientes.dni','!=', 99999)                         
                                ->where('pacientes.activo', 1)                                 
                                ->orderby('pacientes.apellido');        

        return datatables()->of($users)
                ->addIndexColumn()
                ->addColumn('dni', function($row){                                   
                    $val = $row->dni;                                
                    $btn = "<a onclick='verDatosPaciente($val)' style='text-decoration: underline; cursor: pointer;'>$val</a>";                               
                    return $btn;
                })
                ->addColumn('action', function($row){                                   
                    $val = $row->dni;
                    $paciente_id = $row->id;
                    if($row->bloqueado == 1){                                
                        $btn = "<button onclick='desbloquear($paciente_id)' class='rodri_button_aceptar'>Desbloquear</button>";
                    } else {
                        $btn = "<button onclick='darTurno($val)' class='rodri_button_aceptar_si'>...</button><button onclick='bloquearEliminar($paciente_id)' class='rodri_button_cancelar_no marginLeft10px'>...</button>";
                    }
                    return $btn;
                })
                ->rawColumns(['dni','action'])
                ->make(true);
    }

    public function validarPagosList($opcion){
        $medico = $this->getMedico();
        $consultorio_id = $medico->consultorio;        
        if($opcion == 1){
        $users = DB::table('turno_registrado_videollamadas')
                            ->join('pacientes','turno_registrado_videollamadas.paciente','=','pacientes.id')    
                            ->select('pacientes.*', 'turno_registrado_videollamadas.id as turno_id', 'turno_registrado_videollamadas.pago_ticket','turno_registrado_videollamadas.fechaTurno', 'turno_registrado_videollamadas.horario')
                            ->where('turno_registrado_videollamadas.medico', $medico->id)
                            ->where('pacientes.dni','!=',99999)
                            ->where('pacientes.activo', 1)
                            ->where('turno_registrado_videollamadas.activo', 1)                        
                            ->where('turno_registrado_videollamadas.pago', 0)                            
                            ->distinct()
                            ->orderBy('turno_registrado_videollamadas.fechaTurno', 'asc')
                            ->get();     
        } else {
            $users = DB::table('turno_registrado_videollamadas')
                            ->join('pacientes','turno_registrado_videollamadas.paciente','=','pacientes.id')    
                            ->select('pacientes.*', 'turno_registrado_videollamadas.id as turno_id', 'turno_registrado_videollamadas.pago_ticket','turno_registrado_videollamadas.fechaTurno', 'turno_registrado_videollamadas.horario')
                            ->where('turno_registrado_videollamadas.medico', $medico->id)
                            ->where('pacientes.dni','!=',99999)
                            ->where('pacientes.activo', 1)
                            ->where('turno_registrado_videollamadas.activo', 1)                                                    
                            ->distinct()
                            ->orderBy('turno_registrado_videollamadas.fechaTurno', 'asc')
                            ->get();     
        }

        return datatables()->of($users)
                           ->addIndexColumn()
                           ->addColumn('action', function($row){                                                                  
                               $val = $row->turno_id; 
                               $btn = "<button onclick=validarPago($val) class='rodri_button_aceptar'>Validar</button>";
                               return $btn;
                            })
                           ->addColumn('paciente', function($row){                                   
                               $nombre = $row->nombre;
                               $apellido = $row->apellido;                              
                               return $apellido.', '.$nombre;
                            })
                            ->addColumn('turno', function($row){                                                                  
                                $fechaAux = explode('-',$row->fechaTurno);
                                $fecha = $fechaAux[2].'/'.$fechaAux[1].'/'.substr($fechaAux[0], -2).' ('.$row->horario.')';
                               return $fecha;
                            })
                            ->rawColumns(['action'])
                            ->make(true);
    }

    public function listadoBuscarPacientes(){
        $usuario_actual=\Auth::user();        
        $medico = DB::table('medicos')                                                                
                ->where('medicos.user_id',$usuario_actual->id)                                                      
                ->first();
        $consultorio = $medico->consultorio;
        $pacientes=DB::table('pacientes')->where('pacientes.dni','!=',99999)->where('activo', 1)->get();

        return view('turnos_admin_medico.medico_buscar_pacientes')
                            ->with('medico',$medico)
                            ->with('pacientes',$pacientes)
                            ->with('consultorio',$consultorio);    
    }

    public function medicoAccionValidarPago(Request $request){        
        $turno = TurnoRegistradoVideollamada::find($request->turno_id);
        $turno->pago = 1;
        $turno->save();

        return response()->json(array('response'=> 1,'turno'=>$turno));
    }

    public function medicoValidarPagos(){
        $usuario_actual=\Auth::user();        
        $medico = DB::table('medicos')                                                                
                ->where('medicos.user_id',$usuario_actual->id)                                                      
                ->first();
        $consultorio = $medico->consultorio;
        $pacientes = DB::table('turno_registrado_videollamadas')
                            ->join('pacientes','turno_registrado_videollamadas.paciente','=','pacientes.id')    
                            ->where('turno_registrado_videollamadas.medico', $medico->id)
                            ->where('pacientes.dni','!=',99999)
                            ->where('pacientes.activo', 1)
                            ->where('turno_registrado_videollamadas.activo', 1)
                            ->get();

        return view('turnos_admin_medico.medico_validar_pagos')
                            ->with('medico',$medico)
                            ->with('pacientes',$pacientes)
                            ->with('consultorio',$consultorio);    
    }

    function medicoCancelarSobreturno(Request $request){
        $tipo_estudio = $request->tipo_estudio;
        if($tipo_estudio == 0){
            $sobreturno = TurnoRegistrado::find($request->get('sobreturno_id'));
        } else {
            $sobreturno = TurnoRegistradoVideollamada::find($request->get('sobreturno_id'));
        }
        //$sobreturno = turnoRegistrado::find($request->get('sobreturno_id'));
        $sobreturno->activo = 0;
        $sobreturno->comentario = 'Cancelado por :'.$this->getMedico()->apellido.', '.$this->getMedico()->nombre;
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
        //$turnosPaciente = $this->getTurnosPaciente($medico_id, $consultorio, $nuevaFecha);      
        //$cantSobreturnos = $this->getCantidadSobreturnos($medico_id, $consultorio, $nuevaFecha); 
        $contador = $turnosPaciente->count();
        
        $medico = DB::table('medicos')                    
                    ->where('medicos.id', $medico_id)
                    ->where('medicos.activo', 1)                    
                    ->first();

           // Modulo id: 2 | corresponde a Caja | Comentario
        $moduloCajaComentario = $this->moduloActivo($medico->id, 2);

        return response()->json(array('turnosPaciente'=>$turnosPaciente,'medico'=>$medico,'consultorio'=>$consultorio,'dia'=>$dia, 'contador'=>$contador,'cantSobreturnos'=>$cantSobreturnos,'moduloCajaComentario'=>$moduloCajaComentario));
    }

     function medicoCancelarAsignarTurnos(Request $request){
        $tipo_estudio = $request->tipo_estudio;
    
        $horario = $request->horario;
        $fechaSeleccionada = $request->fecha;       //14/08/2019
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
        
        // Quiere decir que es un PEG
        if($tipo_estudio == 1){
            $turnoCancelar = DB::table('turno_registrado_videollamadas')
                            ->where('turno_registrado_videollamadas.medico', $medico_id)
                            ->where('turno_registrado_videollamadas.fechaTurno', $nuevaFecha)
                            ->where('turno_registrado_videollamadas.horario', $horario)
                            ->where('turno_registrado_videollamadas.activo', 1)                                        
                            ->first();
            if($turnoCancelar != null){
                $t = turnoRegistradoVideollamada::find($turnoCancelar->id);
                $t->activo = 0;
                $t->comentario = $turnoCancelar->comentario.' - Cancelado por :'.$this->getMedico()->apellido.', '.$this->getMedico()->nombre;
                $t->save();
            }
            return response()->json(array('response'=> 1));
        }

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
            $t->comentario = $turnoCancelar->comentario.' - Cancelado por :'.$this->getMedico()->apellido.', '.$this->getMedico()->nombre;
            $t->save();
        }
 
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
        return response()->json(array('turnosPaciente'=>$someArray,'medico_id'=>$medico_id,'consultorio'=>$consultorio,'dia'=>$dia, 'primerControl'=>$primerControl, 'fechaLibreDisponible'=>$fechaLibreDisponible));
     }



     public function medicoObrasSocialesList() {
        $medico = $this->getMedico();
        $consultorio_id = $medico->consultorio;        
        
        $tabla = DB::table('obra_social_medicos')
                            ->join('obra_socials','obra_socials.id','=','obra_social_medicos.obra_social')                                
                            ->select('obra_social_medicos.id as osmid', 'obra_socials.nombre', 'obra_social_medicos.importe','obra_social_medicos.activo')
                            ->where('obra_social_medicos.medico', $medico->id)                            
                            ->where('obra_socials.activo', 1)                                                        
                            ->orderBy('obra_socials.nombre', 'asc')
                            ->get();             

        return datatables()->of($tabla)
                           ->addIndexColumn()
                           ->addColumn('action', function($row){                                                                  
                               $val = $row->osmid;
                               if($row->activo == 1){ 
                                $btn = "<button onclick=desactivarObraSocialMedico($val) class='rodri_button_aceptar'>Desactivar</button>";}
                               else{
                                $btn = "<button onclick=activarObraSocialMedico($val) class='rodri_button_aceptar'>Activar</button>";}
                               return $btn;
                            })
                           ->addColumn('estado', function($row){                                                                
                               if($row->activo == 1) {
                                    return 'Activo';
                               }
                               else {
                                    return 'Desactivado';
                               }
                            })                            
                           ->addColumn('importe', function($row){                               
                                $value = $row->importe;
                                $osmid = $row->osmid;
                                return '<input type="text" value="' . $value . '" onchange="updateImporte(this.value, ' . $osmid . ')">';
                            })                            
                            ->rawColumns(['importe', 'action'])
                            ->make(true);        
     }   

     function medicoObraSocialActualizarImporte(Request $request) {
        //$medico = $this->getMedico();
        $obraSocialId = $request->obra_social_id;
        $importe = $request->importe;
        
        $osm = ObraSocialMedico::find($obraSocialId);
        $osm->importe = $importe;
        $osm->save();
                                    
       return response()->json(array('response'=>1));
     }  

     public function medicoAdminObraSocial(Request $request){
        $medico = $this->getMedico();
        $obrasSocialesMedico = DB::table('obra_social_medicos')                                  
                        ->where('obra_social_medicos.medico', $medico->id)                                        
                        ->get();
        $activarObraSociales = 0;
        if($obrasSocialesMedico->count()>0)
            $activarObraSociales = 1;           
        return View('turnos_admin_medico.admin_obras_sociales')
                    ->with('activarObraSociales', $activarObraSociales);
     }

     public function medicoObraSocialEstado(Request $request){
        $osm = ObraSocialMedico::find($request->medicoObraSocialId);
        $osm->activo = $request->estado;
        $osm->save();
        return response()->json(array('response'=>1));
     } 

     public function activarObrasSociales(Request $request){        
        $medico = $this->getMedico();                
        $obrasSociales = DB::table('obra_socials')                                                                                     
                        ->where('obra_socials.activo', 1)
                        ->orderBy('obra_socials.nombre')                                        
                        ->get();   
        $obrasSocialesMedico = DB::table('obra_social_medicos')                                  
                        ->where('obra_social_medicos.medico', $medico->id)                                        
                        ->get();
        if($obrasSocialesMedico->count() == 0){
            foreach ($obrasSociales as $os){
                $osm = new ObraSocialMedico;            
                $osm->medico = $medico->id;
                $osm->obra_social = $os->id;
                $osm->importe = 0;
                $osm->activo = 1;
                $osm->save();
            }                                 
        }

        return View('turnos_admin_medico.admin_obras_sociales')
                    ->with('activarObraSociales', 1);
     }

     public function desactivarObrasSociales(Request $request){
        $medico = $this->getMedico();
        $obrasSocialesMedico = DB::table('obra_social_medicos')                                  
                        ->where('obra_social_medicos.medico', $medico->id)                                        
                        ->get();        
        foreach ($obrasSocialesMedico as $os){
            $osm = ObraSocialMedico::find($os->id);
            $osm->activo = 0;
            $osm->save();
        }                                 
       return response()->json(array('response'=>1));
     }

     function medicoDesbloquearPaciente(Request $request) {
         $paciente_id = $request->paciente_id;
        $medico_id = $this->getMedico()->id;

        $medicoPaciente_aux = DB::table('medico_pacientes')
                        ->where('medico_pacientes.medico', $medico_id)
                        ->where('medico_pacientes.paciente', $paciente_id)                                        
                        ->first();
        $response = 0;
        if($medicoPaciente_aux != null) {
            $medicoPaciente = MedicoPaciente::find($medicoPaciente_aux->id);
            $medicoPaciente->bloqueado = 0;
            $medicoPaciente->save();
            $response = 1;
        }

        return response()->json(array('response'=>$response));
     }

     function medicoBloquearPaciente(Request $request) {
        $paciente_id = $request->paciente_id;
        $medico_id = $this->getMedico()->id;

        $medicoPaciente_aux = DB::table('medico_pacientes')
                        ->where('medico_pacientes.medico', $medico_id)
                        ->where('medico_pacientes.paciente', $paciente_id)                                        
                        ->first();
        $response = 0;
        if($medicoPaciente_aux != null) {
            $medicoPaciente = MedicoPaciente::find($medicoPaciente_aux->id);
            $medicoPaciente->bloqueado = 1;
            $medicoPaciente->save();
            $response = 1;
        }

        return response()->json(array('response'=>$response));
     }

     function medicoEliminarPaciente(Request $request) {
        $paciente_id = $request->paciente_id;
        $medico_id = $this->getMedico()->id;

        $medicoPaciente_aux = DB::table('medico_pacientes')
                        ->where('medico_pacientes.medico', $medico_id)
                        ->where('medico_pacientes.paciente', $paciente_id)                                        
                        ->first();
        $response = 0;
        if($medicoPaciente_aux != null) {
            $medicoPaciente = MedicoPaciente::find($medicoPaciente_aux->id);
            $medicoPaciente->delete();
            $response = 1;
        }

        return response()->json(array('response'=>$response));

     }

     function checkRecetasPendientes(Request $request) {
        $medico_id = $this->getMedico()->id;

        $recetas_aux1 = DB::table('recetas')
                        ->where('recetas.medico', $medico_id)
                        ->where('recetas.estado', 1) // estado solicitada
                        ->where('recetas.activo', 1)                                        
                        ->get();

        $cantidadRecetasPendientes = $recetas_aux1->count();

        return response()->json(array('cantidadRecetasPendientes'=>$cantidadRecetasPendientes));
     }    

     function pacientesHistorialListado(Request $request){
        $consultorio = $this->getMedico()->consultorio;
                                                                     
        return view('turnos_admin_medico.paciente_historial')->with('consultorio',$consultorio);
     }

     function historialPacientesList(){
        $medico_id = $this->getMedico()->id;        
        // $consultorio_id = $this->getMedico()->consultorio;
        // $medico_id = $this->getMedico()->id;
        $users = DB::table('turno_registrados')                                
                                ->join('pacientes','pacientes.id','=','turno_registrados.paciente')                                
                                ->join('medicos','medicos.id','=','turno_registrados.medico')                                
                                ->select('turno_registrados.id','pacientes.nombre', 'pacientes.apellido','pacientes.dni','pacientes.telefono', 'medicos.apellido as apellido_m', 'medicos.nombre as nombre_m', 'turno_registrados.fechaTurno','turno_registrados.horario', 'turno_registrados.sobreturno','turno_registrados.asistio','turno_registrados.activo', 'turno_registrados.comentario', 'turno_registrados.otorgado_por')                                
                                ->where('turno_registrados.medico',$medico_id)   
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
                           ->addColumn('nombre_apellido', function($row){
                                $aux = $row->apellido.','.$row->nombre;                                              
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
                               return $aux_fecha;//$fecha;
                            })
                           
                           
                            ->rawColumns(['nombre_apellido','fechaTurno_','sobreturno_','asistio_','action'])
                            ->make(true);
    
     }

// NUEVO FECHAS AGREGADAS

     function adminHorarios(){
        //$medicos = $this->getMedico();
    
        $especialistas = DB::table('medicos')                                                
                        ->where('medicos.perfil', '!=',0)
                        ->where('medicos.activo', 1)                        
                        ->get();

        return view('turnos_admin.admin_horarios')
                    ->with('especialistas',$especialistas);
    }

    function existeFechaAgregada($fecha, $medico, $consultorio) {
        $fechaAgregada = DB::table('fechas_agregadas')                        
                        ->where('fechas_agregadas.fecha', $fecha)
                        ->where('fechas_agregadas.medico', $medico)                        
                        ->where('fechas_agregadas.consultorio', $consultorio)                        
                        ->where('fechas_agregadas.activo', 1)                        
                        ->first();
        return $fechaAgregada;        
    }

    function existeHorarioAgregada($fechaAgregadaId, $medico, $consultorio, $horario) {
        $horarioAgregada = DB::table('horarios_medicos_agregados')                        
                        ->where('horarios_medicos_agregados.fecha_agregada_id', $fechaAgregadaId)
                        ->where('horarios_medicos_agregados.medico', $medico)                        
                        ->where('horarios_medicos_agregados.consultorio', $consultorio)
                        ->where('horarios_medicos_agregados.horario', $horario)                        
                        ->where('horarios_medicos_agregados.activo', 1)                        
                        ->first();
        return $horarioAgregada;        
    }

    function registrarNuevoHorario(Request $request) {
        $medico_id = $request->medico_id;
        
        $medico = DB::table('medicos')                        
                        ->where('medicos.id', $medico_id)     
                        ->where('medicos.activo', 1)                        
                        ->first();
        $consultorio = $medico->consultorio;

        $fecha = $request->fecha;
        $horario = $request->horario;

        $fechaAux = explode('/', $fecha);
        $fechaBD = $fechaAux[2].'-'.$fechaAux[1].'-'.$fechaAux[0];

        $fechaAgregadaAux = $this->existeFechaAgregada($fechaBD, $medico_id, $consultorio);
        if($fechaAgregadaAux != null) {
            $fechaAgregada = FechasAgregada::find($fechaAgregadaAux->id);
        } else {
            $fechaAgregada = new FechasAgregada;
            $fechaAgregada->activo = 1;
            $fechaAgregada->fecha = $fechaBD;
            $fechaAgregada->medico = $medico_id;
            $fechaAgregada->consultorio = $consultorio;
            $fechaAgregada->dia = $this->getDiaSeleccionado2($fechaBD);
            $fechaAgregada->save();
        }

        $horarioAgregadaAux = $this->existeHorarioAgregada($fechaAgregada->id, $medico_id, $consultorio, $horario);
        if($horarioAgregadaAux != null) {
            $horarioAgregado = HorariosMedicosAgregado::find($horarioAgregadaAux->id);
        } else {
            $horarioAgregado = new HorariosMedicosAgregado;
            $horarioAgregado->activo = 1;
            $horarioAgregado->doble = 0;
            $horarioAgregado->medico = $medico_id;
            $horarioAgregado->consultorio = $consultorio;
            $horarioAgregado->horario = $horario;
            $horarioAgregado->fecha_agregada_id = $fechaAgregada->id;
            $horarioAgregado->dia = $this->getDiaSeleccionado2($fechaBD);
            $horarioAgregado->save();
        }

        $dateHoy = date("Y-m-d");
        $listadoFechas = DB::table('fechas_agregadas')                        
                        ->where('fechas_agregadas.fecha', '>=', $dateHoy)
                        ->where('fechas_agregadas.medico', $medico_id)                        
                        ->where('fechas_agregadas.consultorio', $consultorio)                        
                        ->where('fechas_agregadas.activo', 1)                        
                        ->get();

        $listadoHorarios = DB::table('horarios_medicos_agregados')                        
                        ->where('horarios_medicos_agregados.fecha_agregada_id', $fechaAgregada->id)
                        ->where('horarios_medicos_agregados.medico', $medico_id)                        
                        ->where('horarios_medicos_agregados.consultorio', $consultorio)                        
                        ->where('horarios_medicos_agregados.activo', 1)                        
                        ->get();

        return response()->json(array('fechaAgregada'=>$fechaAgregada,'horarioAgregado'=>$horarioAgregado,'listadoFechas'=>$listadoFechas, 'listadoHorarios'=>$listadoHorarios));

    }

    function actualizarListadoNuevoHorario(Request $request){
        $medico_id = $request->medico_id;
        $medico = DB::table('medicos')                        
                        ->where('medicos.id', $medico_id)     
                        ->where('medicos.activo', 1)                        
                        ->first();
        $consultorio = $medico->consultorio;

        $dateHoy = date("Y-m-d");
        $listadoFechas = DB::table('fechas_agregadas')                        
                        ->where('fechas_agregadas.fecha', '>=', $dateHoy)
                        ->where('fechas_agregadas.medico', $medico_id)                        
                        ->where('fechas_agregadas.consultorio', $consultorio)                        
                        ->where('fechas_agregadas.activo', 1)                        
                        ->get();

        $listadoHorarios = null;
        
        return response()->json(array('listadoFechas'=>$listadoFechas, 'listadoHorarios'=>$listadoHorarios));        
    }

    function actualizarListadoHorariosFecha(Request $request){
        $fechaId = $request->fechaId;

        $listadoHorarios = DB::table('horarios_medicos_agregados')                        
                        ->where('horarios_medicos_agregados.fecha_agregada_id', $fechaId)
                        ->where('horarios_medicos_agregados.activo', 1)                        
                        ->get();

        return response()->json(array('listadoHorarios'=>$listadoHorarios));
    }

    function actualizarListadoHorariosFechaEliminar(Request $request){
        $fechaId = $request->fecha;
        $horarioId = $request->horario;        

        if($fechaId != null) {
            $fechaAux = FechasAgregada::find($fechaId);
            $fechaAuxId = null;            
            $listadoHorarios = null;

            $listadoHorariosAux = DB::table('horarios_medicos_agregados')                        
                        ->where('horarios_medicos_agregados.fecha_agregada_id', $fechaAux->id)
                        ->where('horarios_medicos_agregados.activo', 1)                        
                        ->get();
            foreach ($listadoHorariosAux as $horario) {
                $horarioAux = HorariosMedicosAgregado::find($horario->id);        
                $horarioAux->delete();    
            }
            $fechaAux->delete();

        }
        if($horarioId != null) {
            $horarioAux = HorariosMedicosAgregado::find($horarioId);
            $fechaAuxId = $horarioAux->fecha_agregada_id;
            $horarioAux->delete();

            $listadoHorarios = DB::table('horarios_medicos_agregados')                        
                        ->where('horarios_medicos_agregados.fecha_agregada_id', $fechaAuxId)
                        ->where('horarios_medicos_agregados.activo', 1)                        
                        ->get();
        }

        return response()->json(array('fechaAuxId'=>$fechaAuxId, 'listadoHorarios'=>$listadoHorarios));

    }
}
