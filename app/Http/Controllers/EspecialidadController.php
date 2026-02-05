<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Especialidad;
use App\ObraSocial;
use Image;
use App\PacienteSecretaria;

class EspecialidadController extends Controller
{
    public function index()
    {        
      $especialidades= DB::table('especialidads')
                              ->where('especialidads.activo', 1)
                              ->orderBy('especialidads.nombre')
                              ->get();      
      return View('turnos.seleccionar_especialidad')->with('especialidades',$especialidades);             
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        
        if($request->hasfile('foto')){     
          $nombre_especialidad = $request->get('nombre');
          $image = $request->file('foto');
          $filename  = $nombre_especialidad.time().'.'.$image->getClientOriginalExtension();
          $path = 'images/especialidad/'.$filename;
           
          Image::make($image->getRealPath())->resize(300, 300)->save($path);
        } else {
          $filename = '';
        }
          $especialidad = new especialidad;
          $especialidad->nombre = $request->get('nombre');
          $especialidad->color = $request->get('color');
          $especialidad->foto = $filename;
          $especialidad->activo = 1;
          $especialidad->save();  
          
          return redirect('admin_especialidad');                      
    }

    function test(){
      $pacientes = DB::table('turno_registrados')                                          
                    ->distinct('paciente','consultorio')
                    ->get();
      
      foreach($pacientes as $paciente){
        $check = DB::table('paciente_secretarias')
                    ->where('paciente_secretarias.paciente',$paciente->paciente)
                    ->where('paciente_secretarias.consultorio',$paciente->consultorio)                                           
                    ->get();

        if($check->count()==0){
            $paciente_sec = new pacienteSecretaria;
            $paciente_sec->paciente = $paciente->paciente;
            $paciente_sec->consultorio = $paciente->consultorio;
            $paciente_sec->activo = 1;
            $paciente_sec->save();
        }
      } 
    }

    public function adminObraSocial(){
      $obraSociales = DB::table('obra_socials')
                    ->where('obra_socials.activo', 1)                    
                    ->get();   

      return view('turnos_admin.admin_obra_social')
                  ->with('obraSociales', $obraSociales);      
    }

    public function altaObraSocial(Request $request){        
        $obraSocial = new ObraSocial;
        $obraSocial->nombre = strtoupper($request->nombre);
        $obraSocial->activo = 1;
        $obraSocial->save();

        $obraSociales = DB::table('obra_socials')
                    ->where('obra_socials.activo', 1)                    
                    ->get();   

        return view('turnos_admin.admin_obra_social')
                  ->with('obraSociales', $obraSociales);       
    }
}
