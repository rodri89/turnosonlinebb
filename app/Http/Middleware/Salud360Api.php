<?php

namespace App\Http\Middleware;

use App\Services\Salud360\TokenService;
use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Autenticación de la API de Salud 360 con tokens propios (tabla `salud360_tokens`)
 * y restricción a administradores (1), médicos (2) y secretarias (3).
 *
 * El token se envía como `Authorization: Bearer <token>` o, si el servidor descarta ese
 * encabezado, como `X-Salud360-Token: <token>`.
 */
class Salud360Api
{
    /** @var TokenService */
    private $tokens;

    public function __construct(TokenService $tokens)
    {
        $this->tokens = $tokens;
    }

    public function handle($request, Closure $next)
    {
        $token = $this->tokens->tokenDelPedido($request);
        if (!$token) {
            return response()->json(['ok' => false, 'mensaje' => 'No autenticado.', 'codigo' => 'sin_token'], 401);
        }
        $user = $this->tokens->usuarioPorToken($token);
        if ($user === null) {
            return response()->json(['ok' => false, 'mensaje' => 'Token inválido o vencido.', 'codigo' => 'token'], 401);
        }
        if (!in_array((int) $user->usuario_tipo, [1, 2, 3], true)) {
            return response()->json(['ok' => false, 'mensaje' => 'Este usuario no puede usar Salud 360.', 'codigo' => 'sin_permiso'], 403);
        }
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        Auth::setUser($user);
        return $next($request);
    }
}
