<?php

namespace App\Http\Middleware;

use App\Http\Controllers\SecretariaController;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Closure;

class usuarioMedico
{

    use AuthenticatesUsers;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $usuario_actual = \Auth::user();
        if ($usuario_actual->usuario_tipo == 2) {
            return $next($request);
        }
        if ($usuario_actual->usuario_tipo == 3) {
            $medicoId = (int) session('secretaria_context_medico_id', 0);
            if ($medicoId && SecretariaController::puedeGestionarMedicoPorId($usuario_actual, $medicoId)) {
                return $next($request);
            }
            return redirect()->route('secretaria_home');
        }
        return $this->logout($request);
    }
}
