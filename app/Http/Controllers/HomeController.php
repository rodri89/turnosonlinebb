<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;

class HomeController extends Controller
{

    use AuthenticatesUsers;

    protected $redirectTo = '/homes';
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        //$this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function irHome(){           
       if (Auth::check()) { 
            
            $usuario_actual=\Auth::user();
            $us_tipo = $usuario_actual->usuario_tipo;
            
            switch($us_tipo)
                {
                case '1': //Administrador
                    return redirect('/admin_show_medicos');
                    break;
                case '2': //medico
                    return redirect('/medico_home');
                    break;
                case '3': //secretaria
                    return redirect('/secretaria_home');                
                    break;  
                }   
        }
        else 
        {
            return redirect('/login');
        }    
    }
}
