<?php

namespace App\Http\Controllers\Api\Salud360;

use App\Services\Salud360\AgendaMedicoReglas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Alta de quien va a usar Salud 360, desde el panel de administración de la app.
 *
 * Es el mismo alta que la web de turnos hace en dos pantallas ("Registrar Usuario" y después la ficha
 * del médico), en un solo pedido: el usuario con su contraseña, y la ficha de médico o de secretaria.
 *
 * **Por qué vive acá y no en la historia clínica.** La identidad es de turnosonlinebb: es la única
 * contraseña que el médico usa, y pediatría resuelve la sesión contra este sistema. Un alta en
 * pediatría no serviría para entrar.
 *
 * **El médico que solo usa historia clínica.** Va sin agenda (`tiene_agenda = false`) y con
 * `mostrar_en_turnos = false`, que deja `castigo_automatico = 0`: la ficha queda activa —si no, no
 * podría entrar, porque la API busca al médico con `activo = 1`— pero no aparece en las listas donde
 * el paciente elige a quién pedirle turno. Es el estado que ya tienen varios médicos, no un invento
 * de la app.
 *
 * **Agenda del médico (spec 005).** `tiene_agenda` (opcional) y `mostrar_en_turnos` son dos datos
 * independientes; las reglas están en AgendaMedicoReglas::resolverAlta. Por omisión el médico queda
 * sin agenda y no visible; la app publicada, que no manda `tiene_agenda`, sigue funcionando: ahí vale
 * lo que diga `mostrar_en_turnos`. Solo el rol médico: la secretaria y el administrador no tienen
 * ficha de médico, no se escribe nada de esto.
 *
 * Lo que este alta **no** hace es vincular la ficha de pediatría (`users.medico_id_tobb`): esa
 * columna vive en la otra base y es de la otra aplicación.
 *
 * Solo para el administrador.
 *
 * Compatible con PHP 7.1 / Laravel 5.8.
 */
class AltaUsuarioController extends Salud360Controller
{
    /** Foto por defecto: la que usa la web cuando el médico no subió ninguna. */
    const FOTO_VACIA = 'medico_sin_foto.png';

    /** POST usuarios */
    public function crear(Request $request)
    {
        $user = $request->user();
        if (!$this->esAdmin($user)) {
            return $this->error('Solo el administrador puede dar de alta usuarios.', 403, 'sin_permiso');
        }

        $nombre = trim((string) $request->input('nombre'));
        $apellido = trim((string) $request->input('apellido'));
        $email = strtolower(trim((string) $request->input('email')));
        $password = (string) $request->input('password');
        $rol = strtolower(trim((string) $request->input('rol')));

        if ($nombre === '' || $apellido === '') {
            return $this->error('Faltan el nombre y el apellido.', 422, 'datos');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->error('El mail no es válido.', 422, 'datos');
        }
        if (strlen($password) < 6) {
            return $this->error('La contraseña tiene que tener al menos 6 caracteres.', 422, 'datos');
        }
        $tipo = $this->tipoDeRol($rol);
        if ($tipo === null) {
            return $this->error('Rol desconocido: ' . $rol, 422, 'datos');
        }
        if (DB::table('users')->whereRaw('LOWER(email) = ?', [$email])->exists()) {
            return $this->error('Ya hay un usuario con ese mail.', 409, 'mail_repetido');
        }

        // El médico necesita especialidad y consultorio: las dos columnas son obligatorias del otro
        // lado. El consultorio es dónde figura, no dónde atiende: el que solo usa historia clínica
        // no tiene horarios cargados en ninguno.
        $especialidadId = (int) $request->input('especialidad_id');
        $consultorioId = (int) $request->input('consultorio_id');
        $agenda = null;
        if ($tipo === self::TIPO_MEDICO) {
            if (!DB::table('especialidads')->where('id', $especialidadId)->exists()) {
                return $this->error('Falta la especialidad del médico.', 422, 'especialidad');
            }
            if (!DB::table('consultorios')->where('id', $consultorioId)->exists()) {
                return $this->error('Falta el consultorio del médico.', 422, 'consultorio');
            }
            $agenda = AgendaMedicoReglas::resolverAlta($request->all());
            if (isset($agenda['error'])) {
                return $this->error($agenda['mensaje'], 422, $agenda['error']);
            }
        }
        // Si la columna todavía no existe (falta correr la migración) el alta sigue como antes.
        $escribeAgenda = $agenda !== null && Schema::hasColumn('medicos', 'tiene_agenda');

        $ahora = Carbon::now();
        $medicoId = null;
        $secretariaId = null;
        $usuarioId = null;

        // Todo junto: un usuario sin su ficha no puede entrar y no se ve en ninguna lista, así que
        // sería un alta a medias imposible de encontrar después.
        DB::transaction(function () use (
            $request, $nombre, $apellido, $email, $password, $tipo, $especialidadId, $consultorioId, $ahora,
            $agenda, $escribeAgenda,
            &$medicoId, &$secretariaId, &$usuarioId
        ) {
            $usuarioId = DB::table('users')->insertGetId([
                // La web guarda acá el nombre de pila; el apellido vive en la ficha.
                'name' => $nombre,
                'email' => $email,
                'password' => Hash::make($password),
                'usuario_tipo' => $tipo,
                'perfil' => 1,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ]);

            if ($tipo === self::TIPO_MEDICO) {
                $telefono = preg_replace('/\D/', '', (string) $request->input('telefono'));
                $fichaMedico = [
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'especialidad' => $especialidadId,
                    'consultorio' => $consultorioId,
                    'telefono' => $telefono === '' ? 0 : (float) $telefono,
                    'mail' => $email,
                    // "Mostrar Médico": con 0 no aparece en las listas para pedir turno.
                    'castigo_automatico' => $agenda['columnas']['castigo_automatico'],
                    'foto' => self::FOTO_VACIA,
                    'activo' => 1,
                    'user_id' => $usuarioId,
                    'perfil' => 1,
                    'sexo' => substr((string) $request->input('sexo'), 0, 1),
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];
                if ($escribeAgenda) {
                    // Siempre explícito: el DEFAULT 1 de la columna daría agenda a quien no la pidió.
                    $fichaMedico['tiene_agenda'] = $agenda['columnas']['tiene_agenda'];
                }
                $medicoId = DB::table('medicos')->insertGetId($fichaMedico);
                $this->habilitarHistoriasClinicas($medicoId, $request->input('historias_clinicas'), $ahora);
            }

            if ($tipo === self::TIPO_SECRETARIA) {
                $secretariaId = DB::table('secretarias')->insertGetId([
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'user_id' => $usuarioId,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ]);
            }
        });

        return $this->ok([
            'usuario' => [
                'id' => (int) $usuarioId,
                'nombre' => $nombre,
                'apellido' => $apellido,
                'email' => $email,
                'tipo' => $tipo,
                'rol' => $rol,
                'medico_id' => $medicoId === null ? null : (int) $medicoId,
                'secretaria_id' => $secretariaId === null ? null : (int) $secretariaId,
                'activo' => 1,
            ],
        ], 201);
    }

    /** Las historias clínicas que el médico va a poder abrir desde la app. */
    private function habilitarHistoriasClinicas($medicoId, $codigos, $ahora)
    {
        if (!is_array($codigos)) {
            return;
        }
        foreach ($codigos as $codigo) {
            $codigo = trim((string) $codigo);
            if ($codigo === '') {
                continue;
            }
            $ya = DB::table('salud360_medico_hc')
                ->where('medico_id', (int) $medicoId)->where('hc_codigo', $codigo)->first();
            if ($ya === null) {
                DB::table('salud360_medico_hc')->insert([
                    'medico_id' => (int) $medicoId,
                    'hc_codigo' => $codigo,
                    'activo' => 1,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ]);
            } else {
                DB::table('salud360_medico_hc')->where('id', $ya->id)->update(['activo' => 1, 'updated_at' => $ahora]);
            }
        }
    }

    /** `usuario_tipo` a partir del rol que manda la app, que lo escribe en mayúsculas. */
    private function tipoDeRol($rol)
    {
        switch ($rol) {
            case 'admin':
                return self::TIPO_ADMIN;
            case 'medico':
                return self::TIPO_MEDICO;
            case 'secretaria':
                return self::TIPO_SECRETARIA;
            default:
                return null;
        }
    }
}
