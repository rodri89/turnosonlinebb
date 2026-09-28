<?php

namespace App\Http\Controllers\Api\Salud360;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Quiénes pueden entrar a Salud 360, para el panel de administración de la app.
 *
 * Los médicos, los consultorios y las especialidades ya viajan en `catalogos`; lo que faltaba era esto:
 * la tabla de usuarios y las secretarias con los consultorios que atienden.
 *
 * **Acá no hay pacientes.** En turnosonlinebb viven en su propia tabla, así que `users` son únicamente
 * el administrador, los médicos y las secretarias: unas decenas de filas, no miles.
 *
 * Solo para el administrador: es la lista de quién tiene acceso al sistema, con los mails de todos.
 *
 * Compatible con PHP 7.1 / Laravel 5.8.
 */
class UsuarioController extends Salud360Controller
{
    /** GET usuarios */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$this->esAdmin($user)) {
            return $this->error('Solo el administrador puede ver los usuarios.', 403, 'sin_permiso');
        }

        // El médico y la secretaria de cada usuario, en dos consultas, para no hacer una por fila.
        $medicos = DB::table('medicos')->get()->keyBy('user_id');
        $secretarias = DB::table('secretarias')->get()->keyBy('user_id');

        $out = [];
        foreach (DB::table('users')->orderBy('name')->get() as $u) {
            $item = [
                'id' => (int) $u->id,
                'nombre' => (string) $u->name,
                'email' => (string) $u->email,
                'tipo' => (int) $u->usuario_tipo,
                'rol' => $this->rol($u),
                'medico_id' => null,
                'secretaria_id' => null,
                'activo' => 1,
            ];
            if (isset($medicos[$u->id])) {
                $m = $medicos[$u->id];
                $item['medico_id'] = (int) $m->id;
                // Un médico dado de baja en turnos no puede entrar: la API lo busca con `activo = 1`.
                $item['activo'] = (int) $m->activo;
            }
            if (isset($secretarias[$u->id])) {
                $item['secretaria_id'] = (int) $secretarias[$u->id]->id;
            }
            $out[] = $item;
        }
        return $this->ok(['usuarios' => $out]);
    }

    /** GET secretarias */
    public function secretarias(Request $request)
    {
        $user = $request->user();
        if (!$this->esAdmin($user)) {
            return $this->error('Solo el administrador puede ver las secretarias.', 403, 'sin_permiso');
        }

        // Los consultorios de cada una, en una sola consulta y agrupados acá.
        $porSecretaria = [];
        foreach (DB::table('secretaria_consultorios')->where('activo', 1)->get() as $sc) {
            $porSecretaria[(int) $sc->secretaria_id][] = (int) $sc->consultorio_id;
        }

        $usuarios = DB::table('users')->get()->keyBy('id');
        $out = [];
        foreach (DB::table('secretarias')->orderBy('apellido')->orderBy('nombre')->get() as $s) {
            $u = isset($usuarios[$s->user_id]) ? $usuarios[$s->user_id] : null;
            $out[] = [
                'id' => (int) $s->id,
                'nombre' => (string) $s->nombre,
                'apellido' => (string) $s->apellido,
                'user_id' => (int) $s->user_id,
                'email' => $u === null ? '' : (string) $u->email,
                'consultorios' => isset($porSecretaria[(int) $s->id]) ? $porSecretaria[(int) $s->id] : [],
            ];
        }
        return $this->ok(['secretarias' => $out]);
    }
}
