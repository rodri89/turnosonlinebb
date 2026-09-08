<?php

namespace App\Http\Controllers\Api\Salud360;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Ingreso de médicos, secretarias y administrador desde Salud 360.
 * Usa los mismos usuarios y contraseñas de la web (tabla `users`) y emite tokens Passport.
 */
class AuthController extends Salud360Controller
{
    /**
     * POST /api/salud360/auth/login  {email, password}
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return $this->error('Mail o contraseña incorrectos.', 401, 'credenciales');
        }
        $user = Auth::user();
        if (!in_array((int) $user->usuario_tipo, [self::TIPO_ADMIN, self::TIPO_MEDICO, self::TIPO_SECRETARIA], true)) {
            return $this->error('Este usuario no puede usar Salud 360.', 403, 'sin_permiso');
        }

        $tokenResult = $user->createToken('Salud360');
        $token = $tokenResult->token;
        $token->expires_at = Carbon::now()->addMonths(6);
        $token->save();

        return $this->ok([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($token->expires_at)->toDateTimeString(),
            'perfil' => $this->armarPerfil($user),
        ]);
    }

    /**
     * GET /api/salud360/auth/perfil
     */
    public function perfil(Request $request)
    {
        return $this->ok(['perfil' => $this->armarPerfil($request->user())]);
    }

    /**
     * POST /api/salud360/auth/logout
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return $this->ok(['mensaje' => 'Sesión cerrada.']);
    }

    /**
     * POST /api/salud360/auth/password  {password_actual, password_nueva}
     */
    public function cambiarPassword(Request $request)
    {
        $request->validate([
            'password_actual' => 'required|string',
            'password_nueva' => 'required|string|min:6',
        ]);
        $user = $request->user();
        if (!\Hash::check($request->password_actual, $user->password)) {
            return $this->error('La contraseña actual no es correcta.', 422, 'credenciales');
        }
        $user->password = bcrypt($request->password_nueva);
        $user->save();
        return $this->ok(['mensaje' => 'Contraseña actualizada.']);
    }

    /**
     * Perfil completo: rol, médico (con módulos y configuración) o secretaria (con consultorios y médicos).
     */
    protected function armarPerfil($user)
    {
        $perfil = [
            'usuario' => [
                'id' => (int) $user->id,
                'nombre' => $user->name,
                'email' => $user->email,
                'tipo' => (int) $user->usuario_tipo,
                'perfil' => (int) $user->perfil,
            ],
            'rol' => $this->rol($user),
            'medico' => null,
            'secretaria' => null,
        ];

        if ((int) $user->usuario_tipo === self::TIPO_MEDICO) {
            $m = $this->medicoPropio($user);
            if ($m !== null) {
                $perfil['medico'] = $this->detalleMedico($m);
            }
        }

        if ((int) $user->usuario_tipo === self::TIPO_SECRETARIA) {
            $s = $this->secretariaPropia($user);
            if ($s !== null) {
                $consultorios = $this->consultoriosDeSecretaria($s->id);
                $ids = $consultorios->pluck('id')->all();
                $medicos = [];
                if (count($ids) > 0) {
                    $filas = DB::table('medicos')
                        ->whereIn('consultorio', $ids)
                        ->where('activo', 1)
                        ->orderBy('apellido')
                        ->get();
                    foreach ($filas as $f) {
                        $medicos[] = $this->detalleMedico($f);
                    }
                }
                $perfil['secretaria'] = [
                    'id' => (int) $s->id,
                    'nombre' => $s->nombre,
                    'apellido' => $s->apellido,
                    'consultorios' => $consultorios->map(function ($c) {
                        return ['id' => (int) $c->id, 'nombre' => $c->nombre, 'direccion' => $c->direccion, 'telefono' => $c->telefono];
                    })->values()->all(),
                    'medicos' => $medicos,
                ];
            }
        }

        return $perfil;
    }

    protected function detalleMedico($m)
    {
        $consultorio = DB::table('consultorios')->where('id', $m->consultorio)->first();
        $base = $this->formatearMedico($m);
        $base['consultorio'] = $consultorio === null ? null : [
            'id' => (int) $consultorio->id,
            'nombre' => $consultorio->nombre,
            'direccion' => $consultorio->direccion,
            'telefono' => $consultorio->telefono,
        ];
        $base['modulos'] = $this->agenda->modulosActivos($m->id);
        $base['ventana_dias'] = $this->agenda->ventanaDias($m->id);
        $base['cupo_primer_control'] = $this->agenda->cupoPrimerControl($m->id);
        return $base;
    }
}
