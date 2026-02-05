<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Paciente;
use App\Medico;
use App\Receta;
use datetime;
use Image;
use Storage;
use App\PacienteSecretaria;
use App\MedicoPaciente;

class PacienteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $medico_id = $request->get('medico_id');
        $medico = medico::find($medico_id);
        $consultorio = DB::table('consultorios')
                        ->where('consultorios.id', $medico->consultorio)
                        ->first();

        
        $obraSociales = DB::table('obra_socials')
                        ->join('obra_social_medicos', 'obra_social_medicos.obra_social', 'obra_socials.id')
                        ->where('obra_social_medicos.activo', 1)
                        ->where('obra_socials.activo', 1)
                        ->where('obra_social_medicos.medico', $medico_id)
                        ->orderBy('obra_socials.nombre')
                        ->get();

         // Modulo id: 1 | corresponde a ACtivar pacientes
        $moduloActivarPaciente = $this->moduloActivo($medico->id, 1);
        $moduloAfiliadoObligatorio = $this->moduloActivo($medico->id, 8);
        $especialidad = $medico->especialidad;

        if($moduloAfiliadoObligatorio == 0){
            return view('turnos.datos_paciente')
                ->with('medico',$medico)
                ->with('especialidad',$especialidad)
                ->with('obraSociales',$obraSociales)
                ->with('moduloActivarPaciente',$moduloActivarPaciente)
                ->with('moduloAfiliadoObligatorio',$moduloAfiliadoObligatorio)
                ->with('consultorio',$consultorio);    
        } else {
            return view('turnos.ingresar_datos_paciente')
                ->with('medico',$medico)
                ->with('especialidad',$especialidad)
                ->with('obraSociales',$obraSociales)
                ->with('moduloActivarPaciente',$moduloActivarPaciente)
                ->with('moduloAfiliadoObligatorio',$moduloAfiliadoObligatorio)
                ->with('consultorio',$consultorio);
        }
        
    }

    // 1 si el modulo esta activo
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    public function registrarPacientePendiente(Request $request){

        $paciente = new paciente;
        $paciente->fcm_token = '';
        $paciente->nombre = $request->get('nombre');
        $paciente->apellido = $request->get('apellido');
        $paciente->fecha_nacimiento = $request->get('fecha_nacimiento');
        $paciente->dni = $request->get('dni');
        $paciente->fecha_castigo = '2000-01-01 00:00:00'; 
        if($request->get('terminos_condiciones'))
            $paciente->terminos_condiciones = 1;
        else
            $paciente->terminos_condiciones = 0;
        if($request->get('telefono')== null)        
            $paciente->telefono = 0;
        else
            $paciente->telefono = $request->get('telefono');

        if($request->get('mail')== null)        
            $paciente->mail = '';
        else
            $paciente->mail = $request->get('mail');        
        if($request->get('obra_social')== null)        
            $paciente->obra_social = '';
        else
            $paciente->obra_social = $request->get('obra_social');

        if($request->get('numero_afiliado')== null)
            $paciente->numero_afiliado = '';
        else    
            $paciente->numero_afiliado = $request->get('numero_afiliado');
        
        if($request->get('plan')== null)
            $paciente->obra_social_plan = '';
        else
            $paciente->obra_social_plan = $request->get('plan');

        if($request->get('domicilio')== null)        
            $paciente->domicilio = 0;
        else
            $paciente->domicilio = $request->get('domicilio');

        if($request->get('localidad')== null)        
            $paciente->localidad = 0;
        else
            $paciente->localidad = $request->get('localidad');

        $paciente->obra_social_foto = "";
        $paciente->afiliado_obligatorio = 0;
        $paciente->activo = 2;  // 2 indica que esta pendiente 1 activo, 0 desactivado
    
        $paciente->save();  

        $pacienteSecretaria = new pacienteSecretaria;
        $pacienteSecretaria->paciente = $paciente->id;
        $pacienteSecretaria->consultorio = $request->get('consultorio');
        $pacienteSecretaria->activo = 0;
        $pacienteSecretaria->save();  
        
        return response()->json(array('paciente'=>$paciente));
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
        if($esVideollamada == 1 || $tipoTurno == 4) {
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

    public function crearPaciente($request){
        $paciente = new paciente;
        $paciente->fcm_token = '';
        $paciente->nombre = $request->get('nombre');
        $paciente->apellido = $request->get('apellido');
        $paciente->dni = $request->get('dni');
        $paciente->fecha_castigo = '2000-01-01 00:00:00';             
        if(strcmp ($request->get('terminos_condiciones'), 'on') == 0)           
            $paciente->terminos_condiciones = 1;
        else
            $paciente->terminos_condiciones = 0;

        if($request->get('fecha_nacimiento')== null)        
            $paciente->fecha_nacimiento = '1000-01-01 00:00:00';
        else
            $paciente->fecha_nacimiento = $request->get('fecha_nacimiento');

        if($request->get('telefono') == null)        
            $paciente->telefono = 0;
        else
            $paciente->telefono = $request->get('telefono'); 
        
        if($request->get('mail') == null)        
            $paciente->mail = '';
        else
            $paciente->mail = $request->get('mail');

        if(strcmp($request->get('obraSociales'),'N/A') == 0)        
            $paciente->obra_social = 'PARTICULAR';
        else
            $paciente->obra_social = $request->get('obraSociales');

        if($request->get('numero_afiliado')== null)
            $paciente->numero_afiliado = '';
        else    
            $paciente->numero_afiliado = $request->get('numero_afiliado');
        
        if($request->get('plan')== null)
            $paciente->obra_social_plan = '';
        else
            $paciente->obra_social_plan = $request->get('plan');
        
        if($request->get('domicilio')== null)
            $paciente->domicilio = '';
        else
            $paciente->domicilio = $request->get('domicilio');

        if($request->get('localidad')== null)
            $paciente->localidad = '';
        else
            $paciente->localidad = $request->get('localidad');

        if($request->hasfile('foto')){
            $pathName = $request->file('foto')->store('images/obra_social_carnet/');
            $name = collect(explode('/', $pathName))->last();
            $image = $request->file('foto');
            //$name  = $image->getClientOriginalName().time().'.'.$image->getClientOriginalExtension();
            $path = 'images/obra_social_carnet/'.$name;        
            Image::make($image->getRealPath())->resize(1980, 1920)->save($path);             
        } else {
            $name = '';
        }
        $paciente->obra_social_foto = $name;
        if($request->moduloAfiliadoObligatorio == 1){
            $paciente->afiliado_obligatorio = $request->radio1;
        } else {     
            $paciente->afiliado_obligatorio = 0;
        }
        $paciente->activo = 1;  // 2 indica que esta pendiente 1 activo, 0 desactivado
    
        $paciente->save();
        $this->crearPacienteConsultorio($paciente->id, $request->medico_id);
        return $paciente;
    }


    public function actualizarDatosPacienteStore($request, $paciente) {                
        if($paciente != null) {            
            if(($request->nombre != null) && (strcmp($request->nombre, $paciente->nombre) != 0)){
                $paciente->nombre = $request->nombre;              
            }
            if(($request->apellido != null) && (strcmp($request->apellido, $paciente->apellido) != 0)){
                $paciente->apellido = $request->apellido;               
            }
            if(($request->fecha_nacimiento != null) && (strcmp($request->fecha_nacimiento, $paciente->fecha_nacimiento) != 0)){
                $paciente->fecha_nacimiento = $request->fecha_nacimiento;               
            }    
             if(($request->telefono != null) && (strcmp($request->telefono, $paciente->telefono) != 0)){
                $paciente->telefono = $request->telefono;               
            }
             if(($request->mail != null) && (strcmp($request->mail, $paciente->mail) != 0)){
                $paciente->mail = $request->mail;               
            }

            /*
            if(($request->obraSociales != null) && strcmp($request->obraSociales,'N/A') != 0 && (strcmp($request->obraSociales, $paciente->obra_social) != 0)){
                $paciente->obra_social = $request->obraSociales;   
            } */

            /*if(strcmp($request->obraSociales,'N/A') == 0 && strcmp($paciente->obra_social, 'PARTICULAR') == 0) {
                $paciente->obra_social = 'PARTICULAR';
            } else {
                if(($request->obraSociales != null) && (strcmp($request->obraSociales, $paciente->obra_social) != 0)){
                  $paciente->obra_social = $request->obraSociales;   
                }    
            }  */        
                 
             if(($request->numero_afiliado != null) && (strcmp($request->numero_afiliado, $paciente->numero_afiliado) != 0)){
                $paciente->numero_afiliado = $request->numero_afiliado;                
            }
             if(($request->plan_obra_social != null) && (strcmp($request->plan_obra_social, $paciente->obra_social_plan) != 0)){
                $paciente->obra_social_plan = $request->plan_obra_social;                
            }
            if(($request->domicilio != null) && (strcmp($request->domicilio, $paciente->domicilio) != 0)){
                $paciente->domicilio = $request->domicilio;                
            }

            if(($request->localidad != null) && (strcmp($request->localidad, $paciente->localidad) != 0)){
                $paciente->localidad = $request->localidad;                
            }
            
            if($request->moduloAfiliadoObligatorio == 1){
                $paciente->afiliado_obligatorio = $request->radio1;
            } else {     
                $paciente->afiliado_obligatorio = 0;
            }
        

            if($request->acutalizarFoto !=null && strcmp($request->actualizar_foto, '') != 0) {
                if($request->hasfile('actualizar_foto')) {                
                    $pathName = $request->file('actualizar_foto')->store('images/obra_social_carnet/');
                    $fileName = collect(explode('/', $pathName))->last();                    
                    $image = $request->file('actualizar_foto');
                    //$name  = $image->getClientOriginalName().time().'.'.$image->getClientOriginalExtension();
                    $path = 'images/obra_social_carnet/'.$fileName;        
                    Image::make($image->getRealPath())->resize(1980, 1920)->save($path);            
                    $paciente->obra_social_foto = $fileName;            
                }
            } else {
                if($request->foto !=null && strcmp($request->foto, '') != 0) {
                    if($request->hasfile('foto')) {            
                        $pathName = $request->file('foto')->store('images/obra_social_carnet/');
                        $fileName = collect(explode('/', $pathName))->last();                        
                        $image = $request->file('foto');
                       // $name  = $image->getClientOriginalName().time().'.'.$image->getClientOriginalExtension();
                        $path = 'images/obra_social_carnet/'.$fileName;        
                        Image::make($image->getRealPath())->resize(1980, 1920)->save($path);            
                        $paciente->obra_social_foto = $fileName;            
                    }
                } 
            }
                    
            $paciente->save();        
        }        
        return $paciente;
    }

    public function cargarFotoObraSocial(Request $request) {
        $paciente = paciente::find($request->paciente_id);

        if(strcmp($request->foto, '') != 0) {
            if($request->hasfile('foto')){
                $pathName = $request->file('foto')->store('images/obra_social_carnet/');
                $name = collect(explode('/', $pathName))->last();                        
                $image = $request->file('foto');                
                $path = 'images/obra_social_carnet/'.$name;        
                Image::make($image->getRealPath())->resize(1980, 1920)->save($path);            
                $paciente->obra_social_foto = $name;
                $paciente->save();
            } 
        }
        return response()->json(array('paciente'=>$paciente));
    }

    // input 2000-10-01 output = 01/10/2000
    function convertirFechaMostrar($fecha) {
        $fecha_aux = explode('-', $fecha);        
        $fecha_res = $fecha_aux[2].'/'.$fecha_aux[1].'/'.$fecha_aux[0];                  
        return $fecha_res;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   

        $especialidades= DB::table('especialidads')->get();
        $primerControl =  $request->get('radio');        
        
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $aPartirDia = date("Y-m-d");

        $fechaLibreDisponible_aux1 = $this->turnoLibreMasCercano($request->get('medico_id'), $primerControl, 0, $aPartirDia); 
        
        $fechaLibreDisponible1Aux = explode('-', $fechaLibreDisponible_aux1);     
        $fechaLibreDisponible1 = $this->convertirFechaMostrar($fechaLibreDisponible_aux1);

        if($request->get('medico_id') != 9){
            $siguienteDia = date('Y-m-d', strtotime('+1 day' , strtotime ( $fechaLibreDisponible_aux1 )));            
            $fechaLibreDisponible_aux = $this->turnoLibreMasCercano($request->get('medico_id'), $primerControl, 0, $siguienteDia);
            $fechaLibreDisponible2 = $this->convertirFechaMostrar($fechaLibreDisponible_aux);

            $siguienteDia = date('Y-m-d', strtotime('+1 day' , strtotime ( $fechaLibreDisponible_aux )));            
            $fechaLibreDisponible_aux = $this->turnoLibreMasCercano($request->get('medico_id'), $primerControl, 0, $siguienteDia);
            $fechaLibreDisponible3 = $this->convertirFechaMostrar($fechaLibreDisponible_aux);
        } else {
            $fechaLibreDisponible2 = null;
            $fechaLibreDisponible3 = null;
        }

        $request->validate([
            'dni' => 'required|numeric'
        ], [
            'dni.required' => 'El campo DNI es requerido',
            'dni.numeric' => 'En el campo DNI debe ingresar solo numeros',
        ]);
        
        $paciente_get=DB::table('pacientes')                    
                    ->where('pacientes.dni',$request->dni)                    
                    ->first();
        
        if(!$paciente_get){                    
            $paciente = $this->crearPaciente($request);
        } else {                        
            $paciente = paciente::find($paciente_get->id);
            // voy a sacar esta condicion porque me esta pasando que el paciente selecciona un medico que no tiene obra social, entonces se le actualiza su obra social y me genera inconvenientes...
            
            //$paciente = $this->actualizarDatosPacienteStore($request, $paciente);                        
            if(strcmp ($request->get('terminos_condiciones'), 'on') == 0)           
                $paciente->terminos_condiciones = 1;
            else
                $paciente->terminos_condiciones = 0;
            $paciente->save();
        }
         
        $dni_paciente = $request->get('dni');
        $medico = DB::select('select * from medicos where id = ? and activo=1', [$request->get('medico_id')]);
        $consultorio = DB::select('select * from consultorios where id = ? and activo=1', [$medico[0]->consultorio]);
        $diasAtencionAux = DB::select('select DISTINCT(dia) from horario_medicos where consultorio= ? and medico = ? and tipo_turno = 1 and activo=1 order by dia',    
            [$medico[0]->consultorio , $request->get('medico_id')]);
        
        $dias_habilitados = $this->diasHabilitados($medico[0]->id, $consultorio[0]->id, 0, 1);
        
        $diasAtencion = $this->diasAtencion($medico[0]->id, $consultorio[0]->id, 0, 1);
        
        $dias_deshabilitados= $this->diasDeshabilitados($diasAtencion);
            
        $moduloRecetas = $this->moduloActivo($medico[0]->id, 5);  // el 5 corresponde a las recetas               
        $moduloVideollamadas = $this->moduloActivo($medico[0]->id, 6);  // el 6 corresponde a las videollamadas               
        
        $modulo = 9; // corresponde a ventana dias
        $medicoConfigAux = $this->getMedicoConfig($medico[0]->id, $modulo);
        $moduloExtraTurnos = $this->moduloActivo($medico[0]->id, 10);  // el 10 corresponde a turnos extras

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

        $fechaLibreDisponibleAux= $fechaLibreDisponible1Aux[2].'-'.$fechaLibreDisponible1Aux[1].'-'.$fechaLibreDisponible1Aux[0];                          
        $diferenciaDias = $this->getDiferenciaDiasFechas($fechaLibreDisponibleAux, $ventanaTiempoTope);

        // especialidad 2 es cardiologo
        if($medico[0]->especialidad == 2) {
            return view('turnos.seleccionar_tipo_turno_cardiologia')                    
                    ->with('medico',$medico[0])
                    ->with('consultorio',$consultorio[0])                    
                    ->with('paciente',$paciente)                    
                    ->with('primerControl',$primerControl)                    
                    ->with('moduloRecetas',$moduloRecetas);
        }

        if(($moduloVideollamadas == 1)||($moduloRecetas == 1) || ($moduloExtraTurnos == 1)) {            
            return view('turnos.seleccionar_tipo_turno')
                    ->with('esVideollamada', 1)
                    ->with('tipoTurno', 1)
                    ->with('medico',$medico[0])
                    ->with('consultorio',$consultorio[0])
                    ->with('diasAtencion',$diasAtencion)
                    ->with('paciente',$paciente)
                    ->with('fechaLibreDisponible',$fechaLibreDisponible1)
                    ->with('primerControl',$primerControl)
                    ->with('dias_habilitados',$dias_habilitados)
                    ->with('moduloRecetas',$moduloRecetas)
                    ->with('moduloVideollamadas',$moduloVideollamadas)
                    ->with('dias_deshabilitados',$dias_deshabilitados);
        } else {        
            return view('turnos.seleccionar_dia')
                    ->with('esVideollamada', 0)
                    ->with('tipoTurno', 1)
                    ->with('end_date', $end_date)
                    ->with('diferenciaDias',$diferenciaDias)
                    ->with('medico',$medico[0])
                    ->with('consultorio',$consultorio[0])
                    ->with('diasAtencion',$diasAtencion)
                    ->with('paciente',$paciente)
                    ->with('valorConsulta',$valorConsulta)
                    ->with('fechaLibreDisponible',$fechaLibreDisponible1)
                    ->with('fechaLibreDisponible1',$fechaLibreDisponible2)
                    ->with('fechaLibreDisponible2',$fechaLibreDisponible3)
                    ->with('primerControl',$primerControl)
                    ->with('dias_habilitados',$dias_habilitados)
                    ->with('moduloRecetas',$moduloRecetas)
                    ->with('moduloVideollamadas',$moduloVideollamadas)
                    ->with('dias_deshabilitados',$dias_deshabilitados);
        }
    }    

    function getMedicoConfig($medico_id, $modulo_id){
         $aux = DB::table('medico_configs')                                                       
                    ->where('medico_configs.medico', $medico_id)
                    ->where('medico_configs.modulo', $modulo_id)
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
        $valorConsulta = 0;
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
                        ->with('valorConsulta',$valorConsulta)
                        ->with('dias_deshabilitados',$dias_deshabilitados);
    }

    public function tipoTurno(Request $request) {        
        if($request->tipo_turno == 22 || $request->tipo_turno == 23) {            
            return $this->seleccionarDiaTurnoOnline($request);
        }        

        $opcion = $request->tipo_turno;
        $medico = medico::find($request->medico_id);        
        $consultorio = DB::select('select * from consultorios where id = ? and activo=1', [$request->consultorio_id]);        
        $paciente = paciente::find($request->paciente_id);
        $primerControl = $request->primerControl;
        $moduloRecetas = $request->moduloRecetas;
        $tipoTurno = 1;
        
        $modulo = 9; // corresponde a ventana dias
        $medicoConfigAux = $this->getMedicoConfig($medico->id, $modulo);
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
        $ventanaTiempoTope = date('Y-m-d', strtotime('+'.$end_date_integer.' day' , strtotime ( $diaHoy )));       // 2024-11-01

        if($opcion == 1) { // no es videollamada           
            $esVideollamada = 0;

            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $aPartirDia = date("Y-m-d");

            $fechaLibreDisponible_aux1 = $this->turnoLibreMasCercanoTipoTurno($request->get('medico_id'), $primerControl, 0, $aPartirDia, 1); 
            
            $fechaLibreDisponible1Aux = explode('-', $fechaLibreDisponible_aux1);     
            $fechaLibreDisponible1 = $this->convertirFechaMostrar($fechaLibreDisponible_aux1);

            if($request->get('medico_id') != 9) {
            //if($request->get('medico_id') != 9 && $request->get('medico_id') != 11){
                $siguienteDia = date('Y-m-d', strtotime('+1 day' , strtotime ( $fechaLibreDisponible_aux1 )));                            
                $fechaLibreDisponible_aux = $this->turnoLibreMasCercanoTipoTurno($request->get('medico_id'), $primerControl, 0, $siguienteDia, 1);
                if($fechaLibreDisponible_aux <= $ventanaTiempoTope) {
                    $fechaLibreDisponible2 = $this->convertirFechaMostrar($fechaLibreDisponible_aux);    
                } else {
                    $fechaLibreDisponible2 = null;
                }
                
                $siguienteDia = date('Y-m-d', strtotime('+1 day' , strtotime ( $fechaLibreDisponible_aux )));            
                $fechaLibreDisponible_aux = $this->turnoLibreMasCercanoTipoTurno($request->get('medico_id'), $primerControl, 0, $siguienteDia, 1);
                if($fechaLibreDisponible_aux <= $ventanaTiempoTope){
                    $fechaLibreDisponible3 = $this->convertirFechaMostrar($fechaLibreDisponible_aux);
                } else {
                    $fechaLibreDisponible3 = null;
                }                
            } else {
                $fechaLibreDisponible2 = null;
                $fechaLibreDisponible3 = null;
            }

            $dias_habilitados = $this->diasHabilitados($medico->id, $consultorio[0]->id, 0, $tipoTurno);            
            $diasAtencion = $this->diasAtencion($medico->id, $consultorio[0]->id, 0, $tipoTurno);
            $dias_deshabilitados = $this->diasDeshabilitados($diasAtencion);

            $fechaLibreDisponible2Aux= $fechaLibreDisponible1Aux[2].'-'.$fechaLibreDisponible1Aux[1].'-'.$fechaLibreDisponible1Aux[0];                          
            $diferenciaDias = $this->getDiferenciaDiasFechas($fechaLibreDisponible2Aux, $ventanaTiempoTope);
        } else {                                
            //  es videollamada     
            $esVideollamada = 1;
            $primerControl = 0;
            
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $aPartirDia = date("Y-m-d");
            
            $fechaLibreDisponible_aux = $this->turnoLibreMasCercano($medico->id, $primerControl, 1, $aPartirDia);
            $fecha_aux = explode('-',$fechaLibreDisponible_aux);        
            $fechaLibreDisponible= $fecha_aux[2].'/'.$fecha_aux[1].'/'.$fecha_aux[0];                          
            $dias_habilitados = $this->diasHabilitados($medico->id, $consultorio[0]->id, 1, $tipoTurno);            
            $diasAtencion = $this->diasAtencion($medico->id, $consultorio[0]->id, 1, $tipoTurno);
            $dias_deshabilitados = $this->diasDeshabilitados($diasAtencion);      

            $fechaLibreDisponible2Aux = $fecha_aux[2].'-'.$fecha_aux[1].'-'.$fecha_aux[0];                          
            $diferenciaDias = $this->getDiferenciaDiasFechas($fechaLibreDisponible2Aux, $ventanaTiempoTope);          
        }        

        //return $dias_deshabilitados; 
        return view('turnos.seleccionar_dia')
                        ->with('esVideollamada', $esVideollamada)
                        ->with('tipoTurno', 1)
                        ->with('end_date', $end_date)
                        ->with('medico', $medico)
                        ->with('consultorio',$consultorio[0])
                        ->with('diasAtencion',$diasAtencion)
                        ->with('paciente',$paciente)
                        ->with('valorConsulta',$valorConsulta)
                        ->with('fechaLibreDisponible',$fechaLibreDisponible1)
                        ->with('fechaLibreDisponible1',$fechaLibreDisponible2)
                        ->with('fechaLibreDisponible2',$fechaLibreDisponible3)
                        ->with('diferenciaDias',$diferenciaDias)
                        ->with('primerControl',$primerControl)
                        ->with('dias_habilitados',$dias_habilitados)
                        ->with('moduloRecetas',$moduloRecetas)
                        ->with('dias_deshabilitados',$dias_deshabilitados);
    }

    function turnoLibreMasCercanoCardiologoTipoTurnoConsulta($medicoId, $tipoTurno, $primerControl){
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $dia= date("Y-m-d");        
        $encontre = 0;             
        $diaSeleccionado = $this->getDiaSeleccionado2($dia);       
        while($encontre == 0){
            if($this->esFeriado($dia) == 0 && $diaSeleccionado != 1){
                // devuelve 1 si es feriado, 0 caso contrario                
                $hayTurnoLibre= $this->checkTurnoLibreCardiologo($medicoId, $dia, $primerControl, $tipoTurno);  
            } 
            else
               $hayTurnoLibre = 0; 
            if($hayTurnoLibre == 0){ // es decir no hay turno libre, avanzo de dia.            
                $siguienteDia = $dia;              
                $siguienteDia = date('Y-m-d', strtotime('+1 day' , strtotime ( $siguienteDia )));            
                $dia = $siguienteDia;
                $diaSeleccionado = $this->getDiaSeleccionado2($dia);
            } else {
                $encontre = 1;
            }
        }
        return $dia;
    }

    function turnoLibreMasCercanoCardiologo($medicoId, $tipoTurno, $primerControl){
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $dia= date("Y-m-d");        
        $encontre = 0;                    
        while($encontre == 0){
            if($this->esFeriado($dia) == 0){
                // devuelve 1 si es feriado, 0 caso contrario                
                $hayTurnoLibre= $this->checkTurnoLibreCardiologo($medicoId, $dia, $primerControl, $tipoTurno);  
            } 
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

    function checkTurnoLibreCardiologo($medico_id, $fecha, $primerControl, $tipoTurno){
        $primerControl = 0;
        $medico = medico::find($medico_id);            
        $consultorio = DB::table('consultorios')                                                       
                        ->where('consultorios.id',$medico->consultorio)
                        ->first();
        
        $diaSeleccionado = $this->getDiaSeleccionado($fecha);       
        if($tipoTurno == 4) {
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
            $turnos = DB::table('horario_medicos')
                    ->where('horario_medicos.medico',$medico->id)
                    ->where('horario_medicos.consultorio', $consultorio->id)
                    ->where('horario_medicos.dia', $diaSeleccionado)
                    ->where('horario_medicos.activo', 1)                        
                    ->get();

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
                $turnoActualLibre = $this->estaLibre($turnos[$i]->horario,$turnosRegistrados);                            
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
                    $turnoActualLibre = $this->estaLibre($turnos[$i]->horario, $turnosRegistrados);                    
                          
                    if(($turnos[$i]->doble==0) && ($turnoActualLibre == 0)){
                        $j=$i+1;
                        if($j<count($turnos)){
                            $turnosContiguos = 1;//$this->esTurnoContiguo($turnos[$i]->horario,$turnos[$j]->horario);
                            if($turnosContiguos==1){
                                $turnoSiguienteLibre = $this->estaLibre($turnos[$j]->horario, $turnosRegistrados);
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

    public function tipoTurnoCardiologo(Request $request) {        
        $tipoTurno = $request->tipo_turno;
        $medico = medico::find($request->medico_id);        
        $consultorio = DB::select('select * from consultorios where id = ? and activo=1', [$request->consultorio_id]);        
        $paciente = paciente::find($request->paciente_id);
        $primerControl = $request->primerControl;
        $moduloRecetas = $request->moduloRecetas;
            
        $esVideollamada = 0;
        if($tipoTurno == 4)
            $esVideollamada = 1;
        if($tipoTurno == 1){            
            $fechaLibreDisponible_aux = $this->turnoLibreMasCercanoCardiologoTipoTurnoConsulta($medico->id, $tipoTurno, $primerControl);
            $fecha_aux = explode('-',$fechaLibreDisponible_aux);        
            $fechaLibreDisponible= $fecha_aux[2].'/'.$fecha_aux[1].'/'.$fecha_aux[0];                          
            $dias_habilitados = '3';   
            $diasAtencion = array();
            array_push($diasAtencion, 'Miercoles: 14:00 a 19:00');         
            // $diasAtencion = '["Miercoles: 14:00 a 19:00"]';
            $dias_deshabilitados = '0,1,2,4,5,6';
        } else {
            $fechaLibreDisponible_aux = $this->turnoLibreMasCercanoCardiologo($medico->id, $tipoTurno, $primerControl);
            $fecha_aux = explode('-',$fechaLibreDisponible_aux);        
            $fechaLibreDisponible= $fecha_aux[2].'/'.$fecha_aux[1].'/'.$fecha_aux[0];                          
            $dias_habilitados = $this->diasHabilitados($medico->id, $consultorio[0]->id, $esVideollamada, $tipoTurno);            
            $diasAtencion = $this->diasAtencion($medico->id, $consultorio[0]->id, $esVideollamada, $tipoTurno);
            $dias_deshabilitados = $this->diasDeshabilitados($diasAtencion);
        }

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

        $fechaLibreDisponible2= $fechaLibreDisponible_aux[2].'-'.$fecha_aux[1].'-'.$fecha_aux[0];                          
        $diferenciaDias = $this->getDiferenciaDiasFechas($fechaLibreDisponible2, $ventanaTiempoTope);
        // return $dias_habilitados;
        return view('turnos.seleccionar_dia')
                        ->with('esVideollamada', $esVideollamada)
                        ->with('medico', $medico)
                        ->with('end_date',$end_date)
                        ->with('diferenciaDias',$diferenciaDias)
                        ->with('tipoTurno', $tipoTurno)
                        ->with('consultorio',$consultorio[0])
                        ->with('diasAtencion',$diasAtencion)
                        ->with('paciente',$paciente)
                        ->with('fechaLibreDisponible',$fechaLibreDisponible)
                        ->with('fechaLibreDisponible1', null)
                        ->with('fechaLibreDisponible2', null)
                        ->with('primerControl',$primerControl)
                        ->with('dias_habilitados',$dias_habilitados)
                        ->with('moduloRecetas',$moduloRecetas)
                        ->with('dias_deshabilitados',$dias_deshabilitados);
    }

    public function crearPacienteConsultorio($paciente_id, $medico_id){
        $medico = medico::find($medico_id);
        $check = DB::table('paciente_secretarias')
                    ->where('paciente_secretarias.paciente',$paciente_id)
                    ->where('paciente_secretarias.consultorio',$medico->consultorio)
                    ->where('paciente_secretarias.activo', 1)
                    ->first();
        if($check == null){
            $paciente_sec = new pacienteSecretaria;
            $paciente_sec->paciente = $paciente_id;
            $paciente_sec->consultorio = $medico->consultorio;
            $paciente_sec->activo = 1;
            $paciente_sec->save();
        }            
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

    function consultarPacienteId(Request $request){
        $paciente = Paciente::find($request->paciente_id);
        return response()->json(array('paciente'=>$paciente));
    }

    function verificarPacienteBloqueado(Request $request){
        $medico_id = $request->medico_id;
        $paciente_id = $request->paciente_id;
        $medicoPaciente_aux = DB::table('medico_pacientes')
                        ->where('medico_pacientes.medico', $medico_id)
                        ->where('medico_pacientes.paciente', $paciente_id)                                        
                        ->first();
        $response = 0;
        if($medicoPaciente_aux != null){
            if($medicoPaciente_aux->bloqueado == 1)
                $response = 1;
        }              
        return response()->json(array('response'=>$response));  
    }

    public function consultarPaciente(Request $request){
            $dni_paciente = $request->dni_paciente;   
            $consultorio = $request->consultorio;
           // $perfil = $request->perfil;
            $paciente = DB::table('pacientes')                                                                  
                    ->where('pacientes.dni',$dni_paciente)                                                      
                    ->first();
            $primerControl = 0;
            if($paciente != null){                        
                $primerControl = DB::table('turno_registrados')                                                
                    ->where('turno_registrados.paciente',$paciente->id)
                    ->where('turno_registrados.activo', 1)    
                    ->get()
                    ->count();     
             
                // si es null quiere decir que viene de un paciente...sino viene de un medico o secretaria
                /* if($consultorio!=null){                       
                    $consultorioTurnos = DB::table('turno_registrados')                       
                        ->where('turno_registrados.paciente',$paciente->id)
                        ->where('turno_registrados.consultorio',$consultorio)
                        ->where('turno_registrados.activo', 1) 
                        ->get()->count();
                   $consultorioSecretaria = DB::table('paciente_secretarias')                       
                        ->where('paciente_secretarias.paciente',$paciente->id)
                        ->where('paciente_secretarias.consultorio',$consultorio)
                        ->where('paciente_secretarias.activo', 1) 
                        ->get()->count();
                    if($consultorioTurnos == 0 && $consultorioSecretaria == 0)
                        $paciente = null;
                } */
            }            
                      
            //$primerControl = "1";
                    
        return response()->json(array('paciente'=>$paciente, 'primerControl'=>$primerControl));
    }


    public function showMedicos(Request $request)
    {           
        $medicos = DB::select('select * from medicos where especialidad = ? and activo=1', [$request->get('especialidad_id')]);
        $dni_paciente = $request->get('dni_paciente');
        
        return view('turnos.seleccionar_medico')->with('medicos',$medicos)->with('dni_paciente',$dni_paciente);
  
    }

    public function consultorioSeleccionarMedico(Request $request)
    {           
        $medicos = DB::select('select * from medicos where consultorio = ? and activo=1', [$request->get('consultorio_id')]);
        
        return view('turnos.seleccionar_medico')->with('medicos',$medicos);
  
    }

    public function seleccionarMedicoEspecialidad(Request $request){
        $especialidad_id = $request->especialidad_id;
        
        $anabelaGrecoId = 25;
        $especialidadInfectologiaId = 12;

        $sabrinaId = 16;
        $especialidadNefrologia = 15;
        $especialidad_txt = null;

        $medicos = DB::table('medicos')
                        ->select('medicos.id', 'medicos.foto', 'medicos.castigo_automatico', 'medicos.especialidad', 'medicos.nombre as m_nombre', 'medicos.apellido as m_apellido', 'especialidads.nombre as e_nombre', 'especialidads.color', 'medicos.activo')
                        ->join('especialidads','especialidads.id','=','medicos.especialidad')
                        ->where('medicos.especialidad',$especialidad_id)                                                
                        ->where('medicos.activo', 1)                        
                        ->get();

        // infectologia
        if($especialidad_id == $especialidadInfectologiaId) {
            $medicos = DB::table('medicos')
                        ->select('medicos.id', 'medicos.foto', 'medicos.castigo_automatico', 'medicos.especialidad', 'medicos.nombre as m_nombre', 'medicos.apellido as m_apellido', 'especialidads.nombre as e_nombre', 'especialidads.color', 'medicos.activo')
                        ->join('especialidads','especialidads.id','=','medicos.especialidad')
                        ->where('medicos.id', $anabelaGrecoId)  
                        ->where('medicos.activo', 1)                        
                        ->get();
            $especialidad_txt = 'Infectología';
        }

        // nefrologia
        $arrayNefro = [3, 16]; // Almacenar los IDs en un array

        if($especialidad_id == $especialidadNefrologia) {
            $medicos = DB::table('medicos')
                    ->select(
                        'medicos.id', 'medicos.foto', 'medicos.castigo_automatico', 
                        'medicos.especialidad', 'medicos.nombre as m_nombre', 
                        'medicos.apellido as m_apellido', 'especialidads.nombre as e_nombre', 
                        'especialidads.color', 'medicos.activo'
                    )
                    ->join('especialidads', 'especialidads.id', '=', 'medicos.especialidad')
                    ->whereIn('medicos.id', $arrayNefro) // Filtrar por los IDs almacenados
                    ->where('medicos.activo', 1)
                    ->get();
            $especialidad_txt = 'Nefrología Infantil';

        }

        return View('turnos.seleccionar_medico')->with('medicos',$medicos)->with('especialidad_txt', $especialidad_txt);  
    }
    

     public function showMedicosIndex(){
        $medicos = DB::select('select m.id, m.foto,m.castigo_automatico,m.especialidad, m.nombre as m_nombre, m.apellido as m_apellido, e.nombre as e_nombre, e.color from medicos m join especialidads e on m.especialidad = e.id  where m.activo=1');
        return view('turnos.mostrar_medicos_index')->with('medicos',$medicos);
    }

    // $dia-> a partir de este dia empiezo a buscar turno libre
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

        
    // $dia-> a partir de este dia empiezo a buscar turno libre
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

    // retorna 1 si hay al menos 1 turno libre, sino retorna 0.
    function checkTurnoLibreTipoTurno($medico_id, $fecha, $primerControl, $esVideollamada, $tipoTurno){        
        /*$medico = medico::find($medico_id);            
        $consultorio = DB::table('consultorios')                                                                                           
                        ->where('consultorios.id',$medico->consultorio)
                        ->first();
       
        $diaSeleccionado = $this->getDiaSeleccionado($fecha);
        
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
                    ->get();        */
        $medico = medico::find($medico_id);            
        $consultorio = DB::table('consultorios')                                                                                           
                        ->where('consultorios.id',$medico->consultorio)
                        ->first();
       
        $diaSeleccionado = $this->getDiaSeleccionado($fecha);
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
            if($medico->id == 1 || $medico->id == 5 || $medico->id == 8 || $medico->id == 13 || $medico->id == 14 || $medico->id == 15 || $medico->id == 18 || $medico->id == 19 || $medico->id == 24 || $medico->id == 26){
                if($medico->id == 1)
                    $turnos = $this->checkTurnoLibreEspecialFlor($medico->id, $consultorio->id, $diaSeleccionado, $fecha);
                if($medico->id == 5)
                    $turnos = $this->checkTurnoLibreEspecialLuciaDiomedi($medico->id, $consultorio->id, $diaSeleccionado, $fecha);
                if($medico->id == 8)
                    $turnos = $this->checkTurnoLibreEspecialMarina($medico->id, $consultorio->id, $diaSeleccionado, $fecha);
                if($medico->id == 11)
                    //$turnos = $this->checkTurnoLibreEspecialCeciCorti($medico->id, $consultorio->id, $diaSeleccionado, $fecha);
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
                if($medico->id == 19)
                    $turnos = $this->checkTurnoLibreEspecialMagali($medico->id, $consultorio->id, $diaSeleccionado, $fecha);
                if($medico->id == 24)
                    $turnos = $this->checkTurnoLibreEspecialPabloPrado($medico->id, $consultorio->id, $diaSeleccionado, $fecha);
                if($medico->id == 26)
                    $turnos = $this->checkTurnoLibreEspecialSole($medico->id, $consultorio->id, $diaSeleccionado, $fecha);
            } else {
                $turnos = DB::table('horario_medicos')
                        ->where('horario_medicos.medico',$medico->id)
                        ->where('horario_medicos.consultorio', $consultorio->id)
                        ->where('horario_medicos.dia', $diaSeleccionado)
                        ->where('horario_medicos.tipo_turno', $tipoTurno)
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
                $turnoActualLibre = $this->estaLibre($turnos[$i]->horario,$turnosRegistrados);                            
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
                    $turnoActualLibre = $this->estaLibre($turnos[$i]->horario, $turnosRegistrados);                    
                          
                    if(($turnos[$i]->doble==0) && ($turnoActualLibre == 0)){
                        $j=$i+1;
                        if($j<count($turnos)){
                            $turnosContiguos = 1;//$this->esTurnoContiguo($turnos[$i]->horario,$turnos[$j]->horario);
                            if($turnosContiguos==1){
                                $turnoSiguienteLibre = $this->estaLibre($turnos[$j]->horario, $turnosRegistrados);
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

    // retorna 1 si hay al menos 1 turno libre, sino retorna 0.
    function checkTurnoLibre($medico_id, $fecha, $primerControl, $esVideollamada){        
        $medico = medico::find($medico_id);            
        $consultorio = DB::table('consultorios')                                                                                           
                        ->where('consultorios.id',$medico->consultorio)
                        ->first();
       
        $diaSeleccionado = $this->getDiaSeleccionado($fecha);
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
            if($medico->id == 1 || $medico->id == 5 || $medico->id == 8 || $medico->id == 11 || $medico->id == 13 || $medico->id == 14 || $medico->id == 15 || $medico->id == 18 || $medico->id == 19 || $medico->id == 24 || $medico->id == 26){
                if($medico->id == 1)
                    $turnos = $this->checkTurnoLibreEspecialFlor($medico->id, $consultorio->id, $diaSeleccionado, $fecha);
                if($medico->id == 5)
                    $turnos = $this->checkTurnoLibreEspecialLuciaDiomedi($medico->id, $consultorio->id, $diaSeleccionado, $fecha);
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
                if($medico->id == 19)
                    $turnos = $this->checkTurnoLibreEspecialMagali($medico->id, $consultorio->id, $diaSeleccionado, $fecha);
                if($medico->id == 24)
                    $turnos = $this->checkTurnoLibreEspecialPabloPrado($medico->id, $consultorio->id, $diaSeleccionado, $fecha);
                if($medico->id == 26)
                    $turnos = $this->checkTurnoLibreEspecialSole($medico->id, $consultorio->id, $diaSeleccionado, $fecha);
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
                $turnoActualLibre = $this->estaLibre($turnos[$i]->horario,$turnosRegistrados);                            
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
                    $turnoActualLibre = $this->estaLibre($turnos[$i]->horario, $turnosRegistrados);                    
                          
                    if(($turnos[$i]->doble==0) && ($turnoActualLibre == 0)){
                        $j=$i+1;
                        if($j<count($turnos)){
                            $turnosContiguos = 1;//$this->esTurnoContiguo($turnos[$i]->horario,$turnos[$j]->horario);
                            if($turnosContiguos==1){
                                $turnoSiguienteLibre = $this->estaLibre($turnos[$j]->horario, $turnosRegistrados);
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

   function controlarCantidadPrimerControl($medico, $fecha){
        $dia = $this->getDiaSeleccionado($fecha);

        $cantidadPrimerControlTable = DB::table('medico_primer_controls')
                                            ->where('medico_primer_controls.medico', $medico->id)
                                            ->where('medico_primer_controls.dia', $dia)
                                            ->where('medico_primer_controls.consultorio', $medico->consultorio)
                                            ->where('medico_primer_controls.activo', 1)
                                            ->first();
        // contiene la cantidad posible de primeros controles.
        if($cantidadPrimerControlTable!=null){                                 
            $cantidadPrimerControl = $cantidadPrimerControlTable->cantidadPrimerControl;
            
            // contiene la cantidad actual de primeros controles sacados para ese dia.
            $cantidadPrimerControlActual = DB::table('turno_registrados')
                                            ->where('turno_registrados.medico', $medico->id)
                                            ->where('turno_registrados.consultorio', $medico->consultorio)
                                            ->where('turno_registrados.fechaTurno', $fecha)
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
  function estaLibre($horario, $turnosRegistrados){
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

    //$fecha = 07/09/2019   dia mes año
    public function getDiaSeleccionado($fecha){
        //$fecha_aux = explode('/',$fecha);        
        //$nuevaFecha= $fecha_aux[2].'-'.$fecha_aux[1].'-'.$fecha_aux[0];                  
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

     public function actualizarDatosPaciente(Request $request){
        $paciente_id = $request->paciente_id;
        if($paciente_id != null) {
            $pacienteAux = Paciente::find($paciente_id);
        } else {
            $paciente_dni = $request->dni;
            $pacienteAux = DB::table('pacientes')                                               
                    ->where('pacientes.dni',$paciente_dni)                                        
                    ->first();    
        }
        
        $huboUnCambio = 0;
        if($pacienteAux != null) {
            $paciente = paciente::find($pacienteAux->id);
            if(($request->dni != null) && (strcmp($request->dni, $paciente->dni) != 0)){
                $paciente->dni = $request->dni;
                $huboUnCambio = 1;
            }
            if(($request->nombre != null) && (strcmp($request->nombre, $paciente->nombre) != 0)){
                $paciente->nombre = $request->nombre;
                $huboUnCambio = 1;
            }
            if(($request->apellido != null) && (strcmp($request->apellido, $paciente->apellido) != 0)){
                $paciente->apellido = $request->apellido;
                $huboUnCambio = 1;
            }
            if(($request->fecha_nacimiento != null) && (strcmp($request->fecha_nacimiento, $paciente->fecha_nacimiento) != 0)){
                $paciente->fecha_nacimiento = $request->fecha_nacimiento;
                $huboUnCambio = 1;
            }    
             if(($request->telefono != null) && (strcmp($request->telefono, $paciente->telefono) != 0)){
                $paciente->telefono = $request->telefono;
                $huboUnCambio = 1;
            }
             if(($request->mail != null) && (strcmp($request->mail, $paciente->mail) != 0)){
                $paciente->mail = $request->mail;
                $huboUnCambio = 1;
            }        
            if(strcmp($request->obra_social,'N/A') == 0){
                $paciente->obra_social = 'PARTICULAR';
                $huboUnCambio = 1;
            } else {
                if(($request->obra_social != null) && (strcmp($request->obra_social, $paciente->obra_social) != 0)){
                  $paciente->obra_social = $request->obra_social;
                  $huboUnCambio = 1;   
                }    
            }            
             if ($request->numero_afiliado == null) {
                $paciente->numero_afiliado = "";
                $huboUnCambio = 1;  
             } else {
                if (strcmp($request->numero_afiliado, $paciente->numero_afiliado) != 0){
                    $paciente->numero_afiliado = $request->numero_afiliado;
                    $huboUnCambio = 1;      
                }
             }
             if($request->obra_social_plan == null){
                $paciente->obra_social_plan = "";
             } else {
                if(strcmp($request->obra_social_plan, $paciente->obra_social_plan) != 0) {
                    $paciente->obra_social_plan = $request->obra_social_plan;
                    $huboUnCambio = 1;
                }  
             }

            if(($request->domicilio != null) && (strcmp($request->domicilio, $paciente->domicilio) != 0)){
                $paciente->domicilio = $request->domicilio;
                $huboUnCambio = 1;
            }

            if(($request->localidad != null) && (strcmp($request->localidad, $paciente->localidad) != 0)){
                $paciente->localidad = $request->localidad;
                $huboUnCambio = 1;
            }
            
            if(($request->afiliado_obligatorio != null) && (strcmp($request->afiliado_obligatorio, $paciente->afiliado_obligatorio) != 0)){
                $paciente->afiliado_obligatorio = $request->afiliado_obligatorio;
                $huboUnCambio = 1;
            }

            $paciente->save();        
        } 
        return response()->json(array('paciente'=>$paciente,'huboUnCambio'=>$huboUnCambio));
    }


        public function altaPacienteMedicoSecretaria(Request $request){
        $medico_id = $request->get('medico_id');    
        $consultorio = $request->get('consultorio');

        $dni = $request->dni;
        $paciente_get = DB::table('pacientes')                    
                    ->where('pacientes.dni',$dni)
                    ->where('pacientes.activo', 1)                    
                    ->first();
        if($paciente_get != null){
            $paciente = Paciente::find($paciente_get->id);
        } else {
            $paciente = new paciente;    
            $paciente->fcm_token = '';
            $paciente->nombre = $request->get('nombre');
            $paciente->apellido = $request->get('apellido');
            $paciente->dni = $request->get('dni');    
            $paciente->fecha_castigo = '2000-01-01 00:00:00';
        }

        if($request->get('fecha_nacimiento')== null)        
            $paciente->fecha_nacimiento = '1000-01-01 00:00:00';
        else
            $paciente->fecha_nacimiento = $request->get('fecha_nacimiento');

        if($request->get('telefono')== null)        
            $paciente->telefono = '';
        else
            $paciente->telefono = $request->get('telefono'); 
        
        if($request->get('mail')== null)        
            $paciente->mail = '';
        else
            $paciente->mail = $request->get('mail');

        if(strcmp($request->obra_social,'N/A') == 0){
            $paciente->obra_social = 'PARTICULAR';
        } else {
            $paciente->obra_social = $request->get('obra_social');
        }
            
        if($request->get('numero_afiliado')== null)
            $paciente->numero_afiliado = '';
        else    
            $paciente->numero_afiliado = $request->get('numero_afiliado');
        
        if($request->get('plan')== null)
            $paciente->obra_social_plan = '';
        else
            $paciente->obra_social_plan = $request->get('plan');

        if($request->get('domicilio')== null)        
            $paciente->domicilio = '';
        else
            $paciente->domicilio = $request->get('domicilio'); 

        if($request->get('localidad')== null)        
            $paciente->localidad = '';
        else
            $paciente->localidad = $request->get('localidad'); 

        $paciente->obra_social_foto = ""; 
        $paciente->afiliado_obligatorio = $request->afiliado_obligatorio;
        $paciente->activo = 1;  // 2 indica que esta pendiente 1 activo, 0 desactivado
    
        $paciente->save();


        $paciente_sec_get = DB::table('paciente_secretarias')                    
                    ->where('paciente_secretarias.paciente',$paciente->id)
                    ->where('paciente_secretarias.consultorio',$consultorio)
                    ->where('paciente_secretarias.activo', 1)                    
                    ->first();
        if($paciente_sec_get == null){
            $paciente_sec = new pacienteSecretaria;
            $paciente_sec->paciente = $paciente->id;
            $paciente_sec->consultorio = $consultorio;
            $paciente_sec->activo = 1;
            $paciente_sec->save();
        }

        $paciente_medico_get = DB::table('medico_pacientes')                    
                    ->where('medico_pacientes.paciente',$paciente->id)
                    ->where('medico_pacientes.medico',$medico_id)                                
                    ->first();
        if($medico_id != null && $paciente_medico_get == null){
            $paciente_medico = new MedicoPaciente;
            $paciente_medico->paciente = $paciente->id;
            $paciente_medico->medico = $medico_id;
            $paciente_medico->bloqueado = 0;
            $paciente_medico->save();
        }

        return response()->json(array('paciente'=>$paciente));
    }

    public function solicitarNuevaReceta(Request $request){     
        $receta = new Receta;
        $receta->paciente = $request->paciente;
        $receta->medico = $request->medico;
        $receta->consultorio = $request->consultorio;
        $receta->motivo = $request->motivo;
        $receta->estado = 1; // solicitada
        $receta->foto = '';
        $receta->retira_consultorio = $request->retira;
        $receta->sms_enviado = 0;
        $receta->comentario = '';
        $receta->activo = 1;
        $receta->save();

        return response()->json(array('response'=>1));
    }
    
    public function consultarReceta(Request $request)
    {           
        $mensaje='';
        $paciente_get=DB::table('pacientes')                    
                    ->where('pacientes.dni',$request->get('dni_paciente'))
                    ->where('pacientes.activo', 1)                    
                    ->first();
        if(!$paciente_get){
            $mensaje = 'El DNI ingresado no se corresponde con un paciente.';
        }
        else {
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $dia= date("Y-m-d");        
        $recetasAux = DB::table('recetas')
                    ->join('medicos','medicos.id','=','recetas.medico')
                    ->join('pacientes','pacientes.id','=','recetas.paciente')
                    ->join('receta_estados','receta_estados.id','=','recetas.estado')                    
                    ->select('recetas.id as trid','pacientes.nombre as pnombre','pacientes.apellido as papellido','medicos.apellido','medicos.nombre','recetas.updated_at','receta_estados.estado','recetas.motivo','recetas.estado as estado_id')
                    ->where('pacientes.id',$paciente_get->id)
                    ->where('recetas.activo',1)                    
                   // ->where('recetas.estado','!=', 5) // Entregada
                    ->where('recetas.estado','!=', 6) // Cancelada
                    ->orderBy('recetas.updated_at','desc')                    
                    ->distinct()
                    ->get();                                    
            $someArray = json_decode($recetasAux, true);

//            return $someArray;
            if(!$recetasAux){                
                $mensaje =  'No hay recetas registrados para el DNI ingresado.';
            }
            return view('turnos.mis_recetas')
            ->with('recetas',$someArray)
            ->with('dni_paciente',$paciente_get->dni)
            ->with('mensaje',$mensaje);
        }
        
        return view('turnos.mis_recetas')
        ->with('dni_paciente',$request->get('dni_paciente'))
        ->with('mensaje',$mensaje);
    }

    public function verReceta(Request $request){
        $receta_id = $request->receta_id;
        $receta = DB::table('recetas')                      
                    ->join('pacientes','pacientes.id','=','recetas.paciente')                   
                    ->join('receta_estados','recetas.estado','=','receta_estados.id')                                               
                    ->select('pacientes.id as pac_id', 'pacientes.nombre as pnombre','pacientes.apellido as papellido','pacientes.telefono','pacientes.dni','pacientes.fecha_nacimiento','pacientes.obra_social','pacientes.numero_afiliado','pacientes.obra_social_plan','pacientes.mail','recetas.id as rec_id','recetas.motivo','recetas.estado as estado_id','receta_estados.estado', 'recetas.created_at as solicitud', 'recetas.medico', 'recetas.comentario')
                    ->where('recetas.id',$receta_id)                                    
                    ->where('recetas.activo',1)
                    ->first();    
      
        return response()->json(array('receta'=>$receta));
    }

    public function verRecetaPacienteFotos(Request $request){
            $recetas = DB::table('paciente_recetas')                        
                            ->where('paciente_recetas.receta', $request->receta_id)
                            ->where('paciente_recetas.activo', 1)
                            ->get();        
        return response()->json(array('recetas'=>$recetas));        
    }

    public function actualizarEstadoRecetaPaciente(Request $request){
        $receta_id = $request->receta_id;
        $estado_id = $request->estado_id;
        $receta = receta::find($receta_id);
        $receta->estado = $estado_id;           
        $motivo_rechazo = $request->motivo_rechazo;
        $receta->motivo = $receta->motivo.'||Motivo Actualizado:|'.$motivo_rechazo;
         
        $receta->save();
        return response()->json(array('receta'=>$receta));                          
    }

     function generarListadoRecetasSms() {
        $hoy = date("Y-m-d");
        $recetas = DB::table('recetas') 
                    ->join('pacientes','pacientes.id','=','recetas.paciente')
                    ->join('medicos','medicos.id','=','recetas.medico')
                    ->join('consultorios','consultorios.id','=','medicos.consultorio')
                    ->select('recetas.id as receta_id', 'pacientes.nombre as paciente_nombre', 'pacientes.apellido as paciente_apellido', 'pacientes.telefono', 'recetas.sms_enviado', 'consultorios.direccion', 'medicos.id as medico_id', 'recetas.comentario')    
                    ->where('recetas.sms_enviado', 0)                
                    ->where('recetas.estado', 7)
                    ->where('recetas.activo', 1)
                    ->get();

        $data = array();

        foreach($recetas as $receta) {
             $json['receta_id'] = $receta->receta_id;
             $json['paciente_nombre'] = $receta->paciente_nombre;
             $json['paciente_apellido'] = $receta->paciente_apellido;
             $json['telefono'] = $receta->telefono;
             $json['direccion'] = $receta->direccion;
             $json['sms_enviado'] = $receta->sms_enviado;
             $json['medico_id'] = $receta->medico_id;
             $json['comentario'] = $receta->comentario;

             $data[] = $json; 
        }

        return response()->json(array('datos'=>$data));
    }

    function setMensajeEnviadoReceta(Request $request){
        $receta_id = $request->receta_id;
        $value = $request->value;

        $receta = Receta::find($receta_id);
        $receta->sms_enviado = $value;
        $receta->save();

        return response()->json($receta);
    }
    
    public function cargarDomicilioPaciente(Request $request){        
        $paciente = paciente::find($request->paciente_id);        
        $paciente->domicilio = $request->domicilio;
        $paciente->save();

        return response()->json(array('response'=>1,'paciente'=>$paciente));                                     
    }

    public function cargarNumeroAfiliadoPaciente(Request $request){        
        $paciente = paciente::find($request->paciente_id);        
        $paciente->numero_afiliado = $request->numero_afiliado;
        $paciente->save();

        return response()->json(array('response'=>1,'paciente'=>$paciente));                                     
    }

    public function cargarFotoObraSocialv(Request $request) {
        $paciente = paciente::find($request->paciente_id); 
        $obraSocialCargada = 0;   
        if(strcmp($request->foto, '') != 0) {
            if($request->hasfile('foto')) {                
                $pathName = $request->file('foto')->store('images/obra_social_carnet/');
                $name = collect(explode('/', $pathName))->last();
                $image = $request->file('foto');
                //$name  = $image->getClientOriginalName().time().'.'.$image->getClientOriginalExtension();
                $path = 'images/obra_social_carnet/'.$name;        
                Image::make($image->getRealPath())->resize(1980, 1920)->save($path);            
                $paciente->obra_social_foto = $name;  
                $obraSocialCargada = 1;          
            }
        }                
        $paciente->save();            

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
        if(strcmp($paciente->obra_social_foto, '') != 0) {
            $tieneObraSocialCompleta = 1;
        }
        $moduloMercadoPago = 0;
        if($this->moduloActivo($medico->id, 7) == 1)  
            $moduloMercadoPago = 1;

        $medicoTrabajaConObraSocial = $this->medicoTrabajaConObraSocial($medico, $paciente);

        return view('turnos.videollamadas')
                    ->with('turnoRegistrado',$turnoRegistrado)
                    ->with('tieneObraSocialCompleta', $tieneObraSocialCompleta)
                    ->with('paciente',$paciente)
                    ->with('medico',$medico)
                    ->with('obraSocialCargada',$obraSocialCargada)
                    ->with('videollamada',$videollamada)
                    ->with('moduloMercadoPago',$moduloMercadoPago)
                    ->with('medicoTrabajaConObraSocial',$medicoTrabajaConObraSocial)
                    ->with('fechaTurno',$fecha)
                    ->with('payment',null)
                    ->with('horario',$horario);

    }    

      public function medicoTrabajaConObraSocial($medico, $paciente){
        $obrasSocialesMedico = DB::table('obra_social_medicos')
                                ->join('obra_socials', 'obra_socials.id', '=', 'obra_social_medicos.obra_social')                          
                                ->where('obra_social_medicos.medico', $medico->id)
                                ->where('obra_socials.nombre', $paciente->obra_social)
                                ->get();
        if($obrasSocialesMedico->count()>0)
            return 1;
        else
            return 0;
    }

    function checkTieneFotoOs(Request $request) {
        $paciente_id = $request->paciente_id;
        $paciente = Paciente::find($paciente_id);    
        if(($paciente->obra_social_foto!=null)||(strcmp($paciente->obra_social, 'PARTICULAR') == 0))
            return response()->json(array('response'=>1,'paciente'=>$paciente)); 
        else
            return response()->json(array('response'=>0,'paciente'=>$paciente)); 

    }

    function verificarPacienteMedicoObraSocial(Request $request){
        $paciente_dni = $request->dni;        
        $medico = Medico::find($request->medico_id);    

        $paciente = DB::table('pacientes')                                
                        ->where('pacientes.dni', $paciente_dni)
                        ->where('pacientes.activo', 1)
                        ->first();

        $response = 0;
        if($paciente != null){

            $obra_social_id = DB::table('obra_socials')                                
                        ->where('obra_socials.nombre', $paciente->obra_social)
                        ->where('obra_socials.activo', 1)
                        ->first();

            if($obra_social_id != null) {
                    $aux = DB::table('obra_social_medicos')      
                        ->join('medicos', 'medicos.id', 'obra_social_medicos.medico')                          
                        ->where('obra_social_medicos.medico', $medico->id)
                        ->where('obra_social_medicos.obra_social', $obra_social_id->id)
                        ->where('medicos.activo', 1)
                        ->where('obra_social_medicos.activo', 1)
                        ->first();
            }

            if($aux == null) {
                $response = 1;
            }
            if(strcmp ($paciente->obra_social, 'PARTICULAR') == 0)
                $response = 0;
        }

        return response()->json(array('response'=>$response,'paciente'=>$paciente, 'medico'=>$medico));        
    }

    function validarAsistioUltimaVes(Request $request){
        $medico_id = $request->medico_id;
        $paciente_id = $request->paciente_id;

        $turno = DB::table('turno_registrados')                          
                        ->where('turno_registrados.medico', $medico_id)
                        ->where('turno_registrados.paciente', $paciente_id)
                        ->where('turno_registrados.activo', 1)                        
                        ->orderBy('turno_registrados.id', 'desc')
                        ->first();        

        return response()->json(array('turno'=>$turno));           
    }


    function bloquearPaciente(Request $request) {
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

}
    