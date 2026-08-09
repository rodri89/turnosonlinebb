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
        $consultorio->config = $this->buildConfigFromRequest($request);
    
        $consultorio->save();
		
        //return;//.$request->get('nombre');
        return redirect('admin_consultorios');
        //return view('turnos.turno_registrado')->with('name',$paciente->nombre);

    }

    /**
     * Construye el array de configuración a partir del request.
     */
    private function buildConfigFromRequest(Request $request)
    {
        $config = [];
        if ($request->has('color_primario'))     $config['color_primario']     = $request->get('color_primario');
        if ($request->has('color_secundario'))   $config['color_secundario']   = $request->get('color_secundario');
        if ($request->has('color_terciario'))    $config['color_terciario']    = $request->get('color_terciario');
        if ($request->has('titulo_color'))       $config['titulo_color']       = $request->get('titulo_color');
        if ($request->has('subtitulo_color'))    $config['subtitulo_color']    = $request->get('subtitulo_color');
        if ($request->has('titulo_tipo_letra'))  $config['titulo_tipo_letra']  = $request->get('titulo_tipo_letra');
        return $config;
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

    /**
     * Actualizar la configuración de un consultorio existente.
     */
    public function actualizarConfig(Request $request)
    {
        $consultorio = Consultorio::find($request->get('consultorio_id'));
        if (!$consultorio) {
            return redirect('admin_consultorios')->with('error', 'Consultorio no encontrado');
        }

        $consultorio->config = $this->buildConfigFromRequest($request);
        $consultorio->save();

        return redirect('admin_consultorios')->with('success', 'Configuración actualizada correctamente');
    }

}
