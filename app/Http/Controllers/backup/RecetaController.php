<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Receta;
use App\PacienteRecetas;
use Date;
use Image;

class RecetaController extends Controller
{

	public function __construct()
    {    	
        $this->middleware('auth');
    }

    public function secretariaRecetas(Request $request){
    	$secretaria_user_id = $request->secretaria_id;

    	$secretaria = DB::table('secretarias')->where('user_id', $secretaria_user_id)->first();
    	
    	$consultorio = DB::table('secretaria_consultorios')->where('secretaria_id', $secretaria->id)->get();
    	
    	$recetas = DB::table('recetas')						
					->join('pacientes','pacientes.id','=','recetas.paciente')					
					->join('receta_estados','recetas.estado','=','receta_estados.id')					
					->select('pacientes.id as pac_id','pacientes.nombre','pacientes.apellido','pacientes.telefono','pacientes.dni','pacientes.mail','recetas.id as rec_id','recetas.motivo','receta_estados.estado', 'recetas.created_at as fecha' )
					->where('recetas.consultorio',$consultorio[0]->consultorio_id)
					->where('recetas.estado','!=',5)					
					->where('recetas.activo',1)
					->where('pacientes.activo',1)
					->where('receta_estados.activo',1)
					->orderby('recetas.created_at')
					->get();

		return View('turnos_admin_secretaria.sadmin_recetas')
		    	->with('consultorio',$consultorio[0]->id)
		    	->with('recetas',$recetas); 	
    }

    public function medicoRecetas(Request $request){
    	$medico = $this->getMedico();   
    	
    	$consultorio = $medico->consultorio;
    	
		$recetas = null;

		return View('turnos_admin_medico.admin_recetas')
		    	->with('consultorio',$consultorio)
		    	->with('recetas',$recetas); 	
    }

    function recetaListaEnviar(Request $request) {    	
    	$receta = Receta::find($request->receta_id);
    	$receta->estado = 7;
    	$receta->save();

    	return response()->json(array('receta'=>$receta));
    }

	public function recetaListado(){
		$usuario_actual = \Auth::user(); //obtengo los datos del usuario en sesion
		if($usuario_actual->usuario_tipo == 2) {
			$medico = $this->getMedico();
			$consultorio_id = $medico->consultorio;

			 $recetas = DB::table('recetas')						
					->join('pacientes','pacientes.id','=','recetas.paciente')					
					->join('receta_estados','recetas.estado','=','receta_estados.id')					
					->join('medicos','recetas.medico','=','medicos.id')					
					->select('pacientes.id as pac_id', 'pacientes.nombre as pnombre','pacientes.apellido as papellido','pacientes.telefono','pacientes.dni','pacientes.mail','recetas.id as rec_id','recetas.motivo','receta_estados.estado as estado','recetas.estado as estado_id', 'recetas.created_at as solicitud','medicos.nombre as mnombre', 'medicos.apellido as mapellido', 'recetas.foto', 'recetas.sms_enviado' )
					->where('recetas.consultorio',$consultorio_id)
					->where('recetas.medico',$medico->id)
					->where('recetas.estado','!=',5)
					->where('recetas.estado','!=',6)					
					->where('recetas.activo',1)
					->where('pacientes.activo',1)
					->where('receta_estados.activo',1)
					->where('medicos.activo',1)
					->orderby('recetas.created_at','asc');
		} else {
			$usuario_id = $usuario_actual->id;		
			$secretaria = DB::table('secretarias')->where('user_id', $usuario_id)->first();	
			$secretaria_consultorios = DB::table('secretaria_consultorios')
										->where('secretaria_id', $secretaria->id)
										->where('activo', 1)->first();
			$consultorio_id = $secretaria_consultorios->consultorio_id;
			        
       		$recetas = DB::table('recetas')						
					->join('pacientes','pacientes.id','=','recetas.paciente')					
					->join('receta_estados','recetas.estado','=','receta_estados.id')					
					->join('medicos','recetas.medico','=','medicos.id')					
					->select('pacientes.id as pac_id', 'pacientes.nombre as pnombre','pacientes.apellido as papellido','pacientes.telefono','pacientes.dni','pacientes.mail','recetas.id as rec_id','recetas.motivo','receta_estados.estado as estado', 'recetas.estado as estado_id', 'recetas.created_at as solicitud','medicos.nombre as mnombre', 'medicos.apellido as mapellido', 'recetas.foto', 'recetas.sms_enviado' )
					->where('recetas.consultorio',$consultorio_id)
					->where('recetas.estado','!=',5)
					->where('recetas.estado','!=',6)					
					->where('recetas.activo',1)
					->where('pacientes.activo',1)
					->where('receta_estados.activo',1)
					->where('medicos.activo',1)
					->orderby('recetas.created_at','asc');
			}
		 
		return datatables()->of($recetas)
    					   ->addIndexColumn()
    					   ->addColumn('solicitud', function($row){        					   	       					   	   
    					   	   $fechaAux = explode(' ',$row->solicitud)[0];
            				   $fechaAux2 = explode('-', $fechaAux);
            				   $solicitud = $fechaAux2[2].'/'.$fechaAux2[1].'/'.$fechaAux2[0];	                      
 		                       return $solicitud;
		                    })
    					   ->addColumn('paciente', function($row){        					   	       	 	   
            				   $paciente = $row->papellido.', '.$row->pnombre;	                      
 		                       return $paciente;
		                    })
    					   ->addColumn('medico', function($row){        					   	       	 	   
            				   $medico = $row->mapellido.', '.$row->mnombre;	                      
 		                       return $medico;
		                    })
    					   ->addColumn('motivo_aux', function($row){     
            				   $motivo_aux = substr($row->motivo, 0, 20).'...';
 		                       return $motivo_aux;
		                    })
    					   ->addColumn('_estado', function($row){     
            				   $texto = "";
            				   if($row->estado_id == 7){
            				   	$texto = "Completo";
            				   } else {
            				   	$texto = $row->estado;
            				   }
 		                       return $texto;
		                    })

    					   ->addColumn('action', function($row){        					   	   
    					   	   $val = $row->rec_id;
    					   	   
					   	   		if($row->estado_id == 3){
		                           	$btn = "<button onclick=verReceta($val) class='rodri_button_aceptar_si'>...</button><button onclick=recetaListaEnviar($val) class='rodri_button_aceptar_si' style='background-color:white'><img class='card-img-top' src='images/iconos/receta1.png'></button>";	
		                           } else {
		                           		if($row->estado_id == 7){
			                           		if($row->sms_enviado == 1){
			                           			$btn = "<button onclick=verReceta($val) class='rodri_button_aceptar_si'>...</button><button onclick=recetaListaEnviar($val) class='rodri_button_aceptar_si' style='background-color:green'><img class='card-img-top' src='images/iconos/receta1.png'></button>";	
			                           		} else {
			                           			$btn = "<button onclick=verReceta($val) class='rodri_button_aceptar_si'>...</button><button onclick=recetaListaEnviar($val) class='rodri_button_aceptar_si' style='background-color:yellow'><img class='card-img-top' src='images/iconos/receta1.png'></button>";	
			                           		}		                           	
		                           		} else {
		                           			$btn = "<button onclick=verReceta($val) class='rodri_button_aceptar_si'>...</button>";
		                           		}
		                           }			                    
 		                       return $btn;
		                    })
    					   ->addColumn('fotto', function($row){
    					   	   $val = $row->rec_id;
    					   	   $foto = $row->foto;
    					   	   if($this->tieneFotos($val) == 1){
    					   	   		$btn = "<button onclick=verFoto($val) class='rodri_button_aceptar'>Ver</button>";	    			   	
    					   		} else {
	    					   	   if($row->foto ==null || strcmp($row->foto, '') == 0){
	 		                       		$btn = "<button disabled onclick=cargarRecetaFoto($val) class='rodri_button_aceptar'>Ver</button>";
	 		                   	   }
 		                   		}
 		                   		return $btn;
		                    })
		                    ->rawColumns(['_estado','action','fotto'])
		            		->make(true);
	} 

	public function tieneFotos($receta_id){
		$recetas = DB::table('paciente_recetas')						
							->where('paciente_recetas.receta', $receta_id)
							->where('paciente_recetas.activo', 1)
							->get();
		if($recetas->count()>0)
			return 1;
		else
			return 0;
	}


	public function verReceta(Request $request){
		$receta_id = $request->receta_id;
		$receta = DB::table('recetas')						
					->join('pacientes','pacientes.id','=','recetas.paciente')					
					->join('receta_estados','recetas.estado','=','receta_estados.id')												
					->select('pacientes.id as pac_id', 'pacientes.nombre as pnombre','pacientes.apellido as papellido','pacientes.telefono','pacientes.dni','pacientes.fecha_nacimiento','pacientes.obra_social','pacientes.numero_afiliado','pacientes.obra_social_plan','pacientes.mail','recetas.id as rec_id','recetas.motivo','recetas.estado as estado_id','receta_estados.estado', 'recetas.created_at as solicitud', 'recetas.medico','pacientes.domicilio', 'recetas.retira_consultorio', 'recetas.comentario')
					->where('recetas.id',$receta_id)									
					->where('recetas.activo',1)
					->first();    

		$fechaUltimaConsulta = $this->getFechaUltimaConsulta($receta->pac_id, $receta->medico);

		return response()->json(array('receta'=>$receta, 'fechaUltimaConsulta'=>$fechaUltimaConsulta));
	}

	public function actualizarEstadoReceta(Request $request){
		$receta_id = $request->receta_id;
		$estado_id = $request->estado_id;

		if($request->comentario != null && strcmp($request->comentario, '') != 0){
			$comentario = $request->comentario;
		} else {
			$comentario = '';
		}
		
		$receta = receta::find($receta_id);
		$receta->estado = $estado_id;	
		$receta->comentario = $comentario;	
		if($estado_id == 4){ // receta fue rechazada
			$motivo_rechazo = $request->motivo_rechazo;
			$receta->motivo = $receta->motivo.'||Motivo Rechazo:|'.$motivo_rechazo;
		} 
		$receta->save();
		return response()->json(array('receta'=>$receta));	            			
	}

	public function getFechaUltimaConsulta($paciente_id, $medico_id){
		date_default_timezone_set('America/Argentina/Buenos_Aires');
        $fechaHoy= date("Y-m-d"); 
		$turnosMedicoPaciente = DB::table('turno_registrados')
								->where('turno_registrados.paciente',$paciente_id)
								->where('turno_registrados.medico',$medico_id)
								->where('turno_registrados.activo',1)
								->where('turno_registrados.fechaTurno','<=',$fechaHoy)
								->orderby('turno_registrados.fechaTurno','desc')
								->first();  	
		if($turnosMedicoPaciente!=null){
			return $this->convertirFechaParaMostrar($turnosMedicoPaciente->fechaTurno);
		} else {
			return 'No se registra fecha';
		}
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

    public function getMedico(){
        $usuario_actual=\Auth::user();        
        $medico = DB::table('medicos')                                                                
                ->where('medicos.user_id',$usuario_actual->id)                                                      
                ->first();
        return $medico;
    }

    public function cargarFotoReceta(Request $request){
		//$receta = receta::find($request->foto_receta_id);        
        $cantidadFotos = intval($request->cantidad_fotos);

        for ($i = 1; $i <= $cantidadFotos; $i++) {
        	$request_foto = 'foto-'.$i;

        	if($request->$request_foto != null){
		        
		        if($request->hasfile($request_foto)){
		        	$paciente_receta = new PacienteRecetas;
		        	$paciente_receta->activo = 1;
		        	$paciente_receta->receta = $request->foto_receta_id;

		            $pathName = $request->file($request_foto)->store('images/recetas/');
		            $name = collect(explode('/', $pathName))->last();
		            $image = $request->file($request_foto);
		            //$name  = $image->getClientOriginalName().time().'.'.$image->getClientOriginalExtension();
		            $path = 'images/recetas/'.$name;        
		            // Image::make($image->getRealPath())->resize(1980, 1920)->save($path); 
		            Image::make($image->getRealPath())->save($path);  
		            $paciente_receta->foto = $name;
		            $paciente_receta->save();           
		        }		        
	    	} 
    	}

        $medico = $this->getMedico();   
    	
    	$consultorio = $medico->consultorio;
    	
    	$recetas = DB::table('recetas')						
					->join('pacientes','pacientes.id','=','recetas.paciente')					
					->join('receta_estados','recetas.estado','=','receta_estados.id')					
					->select('pacientes.id as pac_id','pacientes.nombre','pacientes.apellido','pacientes.telefono','pacientes.dni','pacientes.mail','recetas.id as rec_id','recetas.motivo','receta_estados.estado', 'recetas.created_at as fecha' )
					->where('recetas.consultorio',$consultorio)
					->where('recetas.estado','!=',5)					
					->where('recetas.activo',1)
					->where('pacientes.activo',1)
					->where('receta_estados.activo',1)
					->orderby('recetas.created_at')
					->get();		

		return redirect()->route('medicorecetas2');
		return  View('turnos_admin_medico.admin_recetas')
		    	->with('consultorio',$consultorio)
		    	->with('recetas',$recetas); 	    	
    }

    public function cargarFotoReceta2(Request $request){
		//$receta = receta::find($request->foto_receta_id);        
        $cantidadFotos = intval($request->cantidad_fotos);
        $modal_receta_id = $request->modal_receta_id;       

        for ($i = 1; $i <= $cantidadFotos; $i++) {
        	$request_foto = 'foto_'.$i;
        	// return $request_foto;
        	if($request->$request_foto != null){
		        
		        if($request->hasfile($request_foto)){
		        	$paciente_receta = new PacienteRecetas;
		        	$paciente_receta->activo = 1;
		        	$paciente_receta->receta = $modal_receta_id;

		            $pathName = $request->file($request_foto)->store('images/recetas/');
		            $name = collect(explode('/', $pathName))->last();
		            $image = $request->file($request_foto);
		            //$name  = $image->getClientOriginalName().time().'.'.$image->getClientOriginalExtension();
		            $path = 'images/recetas/rodri_'.$name;        
		            // Image::make($image->getRealPath())->resize(1980, 1920)->save($path); 
		            Image::make($image->getRealPath())->save($path);  
		            $paciente_receta->foto = $name;
		            $paciente_receta->save();           
		        }		        
	    	} 
    	}

    	if($request->modal_receta_comentario_secre != null && strcmp($request->modal_receta_comentario_secre, '') != 0){
			$comentario = $request->modal_receta_comentario_secre;
		} else {
			$comentario = '';
		}
		

    	$receta = Receta::find($modal_receta_id);
    	$receta->estado = 3;
    	$receta->comentario = $comentario;
    	$receta->save();

        // $medico = $this->getMedico();   
        $usuario_actual=\Auth::user();
        if($usuario_actual->usuario_tipo == 2){
        	$medico = $this->getMedico();   
        	$consultorio = $medico->consultorio;
        } else {
        	$secretaria = DB::table('secretarias')                                                                
			                ->where('secretarias.user_id',$usuario_actual->id)
			                ->first();
            $secretaria_consultorio = DB::table('secretaria_consultorios')
			                ->where('secretaria_consultorios.secretaria_id',$secretaria->id)
			                ->first();
			$consultorio = $secretaria_consultorio->consultorio_id;
        }     	    
    	
    	$recetas = DB::table('recetas')						
					->join('pacientes','pacientes.id','=','recetas.paciente')					
					->join('receta_estados','recetas.estado','=','receta_estados.id')					
					->select('pacientes.id as pac_id','pacientes.nombre','pacientes.apellido','pacientes.telefono','pacientes.dni','pacientes.mail','recetas.id as rec_id','recetas.motivo','receta_estados.estado', 'recetas.created_at as fecha' )
					->where('recetas.consultorio',$consultorio)
					->where('recetas.estado','!=',5)					
					->where('recetas.activo',1)
					->where('pacientes.activo',1)
					->where('receta_estados.activo',1)
					->orderby('recetas.created_at')
					->get();
		if($usuario_actual->usuario_tipo == 2)
			return redirect()->route('medicorecetas2');		   
		else
			return redirect()->route('secretariarecetas2');		   
    }

    function cargarPdfReceta2(Request $request) {
    	       // Valida que el archivo sea un PDF
        $request->validate([
            'file' => 'required|mimes:pdf|max:2048',
        ]);

        // Puedes guardar la ruta en la base de datos si lo necesitas
        // PDF::create(['path' => $path]);

        $cantidadFotos = intval($request->cantidad_fotos);
        $modal_receta_id = $request->modal_receta_id;       
        
        for ($i = 1; $i <= $cantidadFotos; $i++) {

        	$request_foto = 'foto_'.$i;
	                	
        	// return $request_foto;
        	if($request->$request_foto != null){
		        
		        if($request->hasfile($request_foto)){

		        	// Obtén el archivo del request
			        $file = $request->file($request_foto);

			        // Genera un nombre único para el archivo
			        $fileName = time() . '-' . $file->getClientOriginalName();

			        // Guarda el archivo en el sistema de archivos configurado (por defecto en 'storage/app/public')
			        $path = $file->storeAs('pdfs', $fileName, 'public');


		        	$paciente_receta = new PacienteRecetas;
		        	$paciente_receta->activo = 1;
		        	$paciente_receta->receta = $modal_receta_id;

		            //$pathName = $request->file($request_foto)->store('images/recetas/');
		            //$name = collect(explode('/', $pathName))->last();
		            //$image = $request->file($request_foto);
		            //$name  = $image->getClientOriginalName().time().'.'.$image->getClientOriginalExtension();
		            $path = 'images/recetas/'.$fileName;        
		            // Image::make($image->getRealPath())->resize(1980, 1920)->save($path); 
		            //Image::make($image->getRealPath())->save($path);  
		            $paciente_receta->foto = $name;
		            $paciente_receta->save();           
		        }		        
	    	} 
    	}

    	if($request->modal_receta_comentario_secre != null && strcmp($request->modal_receta_comentario_secre, '') != 0){
			$comentario = $request->modal_receta_comentario_secre;
		} else {
			$comentario = '';
		}
		

    	$receta = Receta::find($modal_receta_id);
    	$receta->estado = 3;
    	$receta->comentario = $comentario;
    	$receta->save();

        // $medico = $this->getMedico();   
        $usuario_actual=\Auth::user();
        if($usuario_actual->usuario_tipo == 2){
        	$medico = $this->getMedico();   
        	$consultorio = $medico->consultorio;
        } else {
        	$secretaria = DB::table('secretarias')                                                                
			                ->where('secretarias.user_id',$usuario_actual->id)
			                ->first();
            $secretaria_consultorio = DB::table('secretaria_consultorios')
			                ->where('secretaria_consultorios.secretaria_id',$secretaria->id)
			                ->first();
			$consultorio = $secretaria_consultorio->consultorio_id;
        }     	    
    	
    	$recetas = DB::table('recetas')						
					->join('pacientes','pacientes.id','=','recetas.paciente')					
					->join('receta_estados','recetas.estado','=','receta_estados.id')					
					->select('pacientes.id as pac_id','pacientes.nombre','pacientes.apellido','pacientes.telefono','pacientes.dni','pacientes.mail','recetas.id as rec_id','recetas.motivo','receta_estados.estado', 'recetas.created_at as fecha' )
					->where('recetas.consultorio',$consultorio)
					->where('recetas.estado','!=',5)					
					->where('recetas.activo',1)
					->where('pacientes.activo',1)
					->where('receta_estados.activo',1)
					->orderby('recetas.created_at')
					->get();
		if($usuario_actual->usuario_tipo == 2)
			return redirect()->route('medicorecetas2');		   
		else
			return redirect()->route('secretariarecetas2');		   
    }

    public function obtenerFotosReceta(Request $request){
		$recetas = DB::table('paciente_recetas')						
							->where('paciente_recetas.receta', $request->receta_id)
							->where('paciente_recetas.activo', 1)
							->get();    	
		return response()->json(array('recetas'=>$recetas));		
    }
}
