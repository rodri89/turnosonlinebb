<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Consultorio;
use Image;

class ConsultorioController extends Controller
{

	public function store(Request $request)
    {        
        if($request->hasfile('foto')){
            $image = $request->file('foto');
            $name  = $image->getClientOriginalName().time().'.'.$image->getClientOriginalExtension();
            $path = 'images/consultorios/'.$name;        
            Image::make($image->getRealPath())->resize(300, 300)->save($path);                    
        } else {
            $name = 'consultorio_sin_foto.png';
        }

        $consultorio = new consultorio;
        $consultorio->nombre = $request->get('nombre');
        $consultorio->direccion = $request->get('direccion');
        $consultorio->telefono = $request->get('telefono');
        $consultorio->foto = $name;
        $consultorio->activo = 1;
    
        $consultorio->save();
		
        //return;//.$request->get('nombre');
        return redirect('admin_consultorios');
        //return view('turnos.turno_registrado')->with('name',$paciente->nombre);

    }

    public function actualizarFotoConsultorio(Request $request){
        $consultorio_aux = explode('-',$request->get('consultorio'));
        $consultorio_id = $consultorio_aux[0];

        if($request->hasfile('foto')){
            $image = $request->file('foto');
            $name  = $image->getClientOriginalName().time().'.'.$image->getClientOriginalExtension();
            $path = 'images/consultorios/'.$name;        
            Image::make($image->getRealPath())->resize(300, 300)->save($path);
                        
        } else {
            $name = 'consultorio_sin_foto.png';
        }

        $consultorio = Consultorio::find($consultorio_id);
    
        $consultorio->foto = $name;
        $consultorio->save();
        
        return redirect('admin_consultorios');

    }


}
