<?php

namespace App\Http\Middleware;

use Closure;

/**
 * Restringe la API de Salud 360 a administradores (1), médicos (2) y secretarias (3).
 * Los pacientes autenticados con Passport no pueden usar estos endpoints.
 */
class Salud360Api
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        if ($user === null) {
            return response()->json(['ok' => false, 'mensaje' => 'No autenticado.'], 401);
        }
        if (!in_array((int) $user->usuario_tipo, [1, 2, 3], true)) {
            return response()->json(['ok' => false, 'mensaje' => 'Este usuario no puede usar Salud 360.'], 403);
        }
        return $next($request);
    }
}
