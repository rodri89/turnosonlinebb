<?php

namespace App\Services\Salud360;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tokens de acceso propios de la API de Salud 360 (tabla `salud360_tokens`).
 *
 * No depende de Passport: el modelo `User` de este proyecto no usa el trait `HasApiTokens`
 * y la instalación de Passport (claves, cliente personal) no está garantizada en el hosting.
 * Se guarda solo el hash SHA-256 del token; el token en claro se entrega una única vez al ingresar.
 *
 * Compatible con PHP 7.1 / Laravel 5.8.
 */
class TokenService
{
    const TABLA = 'salud360_tokens';

    /** Crea la tabla si todavía no existe (por si el hosting no corre migraciones). */
    public function asegurarTabla()
    {
        if (Schema::hasTable(self::TABLA)) {
            return;
        }
        Schema::create(self::TABLA, function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->unsignedBigInteger('user_id')->index();
            $t->string('nombre', 100)->default('Salud360');
            $t->string('token_hash', 64)->unique();
            $t->timestamp('expira_en')->nullable();
            $t->timestamp('ultimo_uso_en')->nullable();
            $t->timestamps();
        });
    }

    /**
     * Emite un token para el usuario. Devuelve ['token' => <en claro>, 'expira' => 'Y-m-d H:i:s'].
     */
    public function crear($userId, $nombre = 'Salud360', $meses = 6)
    {
        $this->asegurarTabla();
        $plano = bin2hex(random_bytes(32));
        $expira = Carbon::now()->addMonths($meses);
        DB::table(self::TABLA)->insert([
            'user_id' => (int) $userId,
            'nombre' => $nombre,
            'token_hash' => $this->hash($plano),
            'expira_en' => $expira,
            'ultimo_uso_en' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        return ['token' => $plano, 'expira' => $expira->toDateTimeString()];
    }

    /** Usuario dueño de un token vigente, o null. */
    public function usuarioPorToken($plano)
    {
        if (!is_string($plano) || $plano === '' || !Schema::hasTable(self::TABLA)) {
            return null;
        }
        $fila = DB::table(self::TABLA)->where('token_hash', $this->hash($plano))->first();
        if ($fila === null) {
            return null;
        }
        if ($fila->expira_en !== null && Carbon::parse($fila->expira_en)->isPast()) {
            return null;
        }
        $user = User::find($fila->user_id);
        if ($user === null) {
            return null;
        }
        DB::table(self::TABLA)->where('id', $fila->id)->update(['ultimo_uso_en' => Carbon::now()]);
        return $user;
    }

    /** Revoca un token (cierre de sesión). */
    public function revocar($plano)
    {
        if (!is_string($plano) || $plano === '' || !Schema::hasTable(self::TABLA)) {
            return;
        }
        DB::table(self::TABLA)->where('token_hash', $this->hash($plano))->delete();
    }

    /** Revoca todos los tokens del usuario (por ejemplo al cambiar la contraseña). */
    public function revocarTodos($userId)
    {
        if (!Schema::hasTable(self::TABLA)) {
            return;
        }
        DB::table(self::TABLA)->where('user_id', (int) $userId)->delete();
    }

    /**
     * Token enviado en el pedido: `Authorization: Bearer ...` o, si el hosting descarta ese
     * encabezado (habitual con PHP como CGI), `X-Salud360-Token`.
     */
    public function tokenDelPedido($request)
    {
        $t = $request->bearerToken();
        if ($t) {
            return $t;
        }
        $t = $request->header('X-Salud360-Token');
        return $t ? trim($t) : null;
    }

    private function hash($plano)
    {
        return hash('sha256', $plano);
    }
}
