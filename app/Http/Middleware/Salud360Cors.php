<?php

namespace App\Http\Middleware;

use Closure;

/**
 * CORS para la API de Salud 360 (`/api/salud360/...`).
 *
 * Permite llamar a la API desde la versión web de la app, que corre en otro origen (localhost en
 * desarrollo, el dominio donde se publique). La API autentica con tokens propios en encabezados, no con
 * cookies, por eso se admite cualquier origen. Va como middleware global (Kernel `$middleware`) para
 * contestar también el preflight OPTIONS, que Laravel responde antes de llegar a las rutas.
 */
class Salud360Cors
{
    public function handle($request, Closure $next)
    {
        if (!$request->is('api/salud360') && !$request->is('api/salud360/*')) {
            return $next($request);
        }
        if ($request->getMethod() === 'OPTIONS') {
            return $this->conCors(response('', 204));
        }
        return $this->conCors($next($request));
    }

    private function conCors($response)
    {
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Authorization, Content-Type, Accept, X-Requested-With, X-Salud360-Token');
        $response->headers->set('Access-Control-Max-Age', '86400');
        return $response;
    }
}
