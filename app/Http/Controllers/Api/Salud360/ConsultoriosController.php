<?php

namespace App\Http\Controllers\Api\Salud360;

use App\Services\Salud360\AgendaService;
use App\Services\Salud360\ConsultoriosReglas;
use App\Services\Salud360\EspecialidadesReglas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Consultorios desde el panel del administrador de la app Salud 360 (spec 007): el catálogo completo, el alta o
 * reactivación y activar/desactivar. El catálogo común (`GET catalogos`) sigue trayendo solo los activos.
 *
 * Desactivar no toca médicos, horarios, turnos ni el vínculo de las secretarias (que dejan de verlo porque las consultas
 * de la secretaria filtran `consultorios.activo = 1`, y lo vuelven a ver al reactivarlo). Un consultorio con turnos por
 * delante no se desactiva (`409 con_turnos`). Las reglas están en ConsultoriosReglas.
 *
 * Solo para el administrador (`403 sin_permiso` para los demás).
 *
 * Compatible con PHP 7.1 / Laravel 5.8.
 */
class ConsultoriosController extends Salud360Controller
{
    /**
     * GET consultorios-admin
     *
     * Todos, activos e inactivos, en orden de id: `{ok, consultorios: [{id, nombre, direccion, telefono, activo,
     * medicos_activos, secretarias_activas, turnos_por_delante}]}`.
     */
    public function index(Request $request)
    {
        if (!$this->esAdmin($request->user())) {
            return $this->error('Solo el administrador puede ver el catálogo de consultorios.', 403, 'sin_permiso');
        }
        $medicos = $this->medicosActivos();
        $secretarias = $this->secretariasActivas();
        $turnos = $this->turnosPorDelante();
        $consultorios = DB::table('consultorios')->orderBy('id')->get(['id', 'nombre', 'direccion', 'telefono', 'activo'])
            ->map(function ($c) use ($medicos, $secretarias, $turnos) {
                $id = (int) $c->id;
                return ConsultoriosReglas::fila(
                    $c,
                    isset($medicos[$id]) ? $medicos[$id] : 0,
                    isset($secretarias[$id]) ? $secretarias[$id] : 0,
                    isset($turnos[$id]) ? $turnos[$id] : 0
                );
            })
            ->values()
            ->all();
        return $this->ok(['consultorios' => $consultorios]);
    }

    /**
     * POST consultorios  {nombre, direccion, telefono}
     *
     * - Datos inválidos → `422 datos_invalidos` (el mensaje dice el campo y, si es largo, el máximo de su columna).
     * - Uno activo con el mismo nombre normalizado → `409 duplicado` con `consultorio` (el existente); no cambia nada.
     * - Uno inactivo con ese nombre → lo reactiva **sin cambiar su dirección ni su teléfono** (solo `activo` y
     *   `updated_at`): `200 resultado: reactivado`.
     * - Si no → lo crea activo con la foto «consultorio_sin_foto.png» y `config` nulo (como la pantalla web):
     *   `201 resultado: creado`.
     */
    public function crear(Request $request)
    {
        if (!$this->esAdmin($request->user())) {
            return $this->error('Solo el administrador puede agregar consultorios.', 403, 'sin_permiso');
        }
        $leido = ConsultoriosReglas::validarAlta(
            $request->all(),
            ConsultoriosReglas::maximoEfectivo($this->largoDeLaColumna('nombre'), ConsultoriosReglas::LARGO_NOMBRE_CONOCIDO),
            ConsultoriosReglas::maximoEfectivo($this->largoDeLaColumna('direccion'), ConsultoriosReglas::LARGO_DIRECCION_CONOCIDO)
        );
        if (isset($leido['error'])) {
            return $this->error($leido['mensaje'], 422, $leido['error']);
        }

        // Leer, decidir y escribir con la tabla bloqueada: dos altas a la vez con el mismo nombre no crean dos.
        $resultado = DB::transaction(function () use ($leido) {
            $existentes = DB::table('consultorios')->lockForUpdate()->orderBy('id')->get(['id', 'nombre', 'activo'])->all();
            $decision = ConsultoriosReglas::decidirAlta($leido['nombre'], $existentes);
            $ahora = now();
            switch ($decision['accion']) {
                case EspecialidadesReglas::DUPLICADO:
                    return ['accion' => EspecialidadesReglas::DUPLICADO, 'id' => $decision['id']];
                case EspecialidadesReglas::REACTIVAR:
                    DB::table('consultorios')->where('id', $decision['id'])->update(['activo' => 1, 'updated_at' => $ahora]);
                    return ['accion' => EspecialidadesReglas::REACTIVAR, 'id' => $decision['id']];
                default:
                    $id = DB::table('consultorios')->insertGetId([
                        'nombre' => $leido['nombre'],
                        'direccion' => $leido['direccion'],
                        'telefono' => $leido['telefono'],
                        'foto' => 'consultorio_sin_foto.png',
                        'config' => null,
                        'activo' => 1,
                        'created_at' => $ahora,
                        'updated_at' => $ahora,
                    ]);
                    return ['accion' => EspecialidadesReglas::CREAR, 'id' => (int) $id];
            }
        });

        $consultorio = $this->consultorio($resultado['id']);
        switch ($resultado['accion']) {
            case EspecialidadesReglas::DUPLICADO:
                return $this->error('Ya existe el consultorio «' . $consultorio['nombre'] . '» en turnos.', 409, 'duplicado', ['consultorio' => $consultorio]);
            case EspecialidadesReglas::REACTIVAR:
                return $this->ok(['resultado' => 'reactivado', 'consultorio' => $consultorio]);
            default:
                return $this->ok(['resultado' => 'creado', 'consultorio' => $consultorio], 201);
        }
    }

    /**
     * PUT consultorios/{id}/activo  {activo: true|false}
     *
     * Cambia solo `activo` (y `updated_at` si cambia). Para desactivar, no tiene que tener turnos por delante (`409
     * con_turnos` con `turnos_por_delante`); si no existe, `404 consultorio_no_encontrado`. Responde `{consultorio,
     * mensaje}` con el estado que quedó.
     */
    public function cambiarActivo(Request $request, $id)
    {
        if (!$this->esAdmin($request->user())) {
            return $this->error('Solo el administrador puede activar o desactivar consultorios.', 403, 'sin_permiso');
        }
        $consultorioId = (int) $id;
        $leido = ConsultoriosReglas::validarActivo($request->all());
        if (isset($leido['error'])) {
            return $this->error($leido['mensaje'], 422, $leido['error']);
        }

        $resultado = DB::transaction(function () use ($consultorioId, $leido) {
            $fila = DB::table('consultorios')->where('id', $consultorioId)->lockForUpdate()->first(['id', 'nombre', 'activo']);
            if ($fila === null) {
                return ['error' => 'no_existe'];
            }
            $nuevo = $leido['activo'] ? 1 : 0;
            if ($nuevo === 0 && (int) $fila->activo === 1) {
                $turnos = $this->turnosPorDelanteDe($consultorioId);
                if (!ConsultoriosReglas::puedeDesactivar($turnos)) {
                    return ['error' => 'con_turnos', 'turnos' => $turnos, 'nombre' => (string) $fila->nombre];
                }
            }
            if ((int) $fila->activo !== $nuevo) {
                DB::table('consultorios')->where('id', $consultorioId)->update(['activo' => $nuevo, 'updated_at' => now()]);
            }
            return [];
        });

        if (isset($resultado['error']) && $resultado['error'] === 'no_existe') {
            return $this->error('El consultorio ya no existe en turnos.', 404, 'consultorio_no_encontrado');
        }
        $consultorio = $this->consultorio($consultorioId);
        if (isset($resultado['error'])) {
            return $this->error(
                ConsultoriosReglas::mensajeConTurnos($resultado['nombre'], $resultado['turnos']),
                409,
                'con_turnos',
                ['turnos_por_delante' => (int) $resultado['turnos'], 'consultorio' => $consultorio]
            );
        }
        return $this->ok([
            'consultorio' => $consultorio,
            'mensaje' => ConsultoriosReglas::avisoDeEstado($consultorio['nombre'], $consultorio['activo']),
        ]);
    }

    // ------------------------------------------------------------------
    // Conteos
    // ------------------------------------------------------------------

    /** Médicos activos que atienden en cada consultorio: el suyo (`medicos.consultorio`) o con un horario activo ahí. */
    private function medicosActivos()
    {
        $porConsultorio = [];
        $propios = DB::table('medicos')->where('activo', 1)->get(['id', 'consultorio']);
        foreach ($propios as $m) {
            $porConsultorio[(int) $m->consultorio][(int) $m->id] = true;
        }
        $conHorario = DB::table('horario_medicos')
            ->join('medicos', 'medicos.id', '=', 'horario_medicos.medico')
            ->where('horario_medicos.activo', 1)->where('medicos.activo', 1)
            ->distinct()->get(['horario_medicos.consultorio', 'horario_medicos.medico']);
        foreach ($conHorario as $h) {
            $porConsultorio[(int) $h->consultorio][(int) $h->medico] = true;
        }
        return array_map('count', $porConsultorio);
    }

    /** Secretarias con el vínculo activo (`secretaria_consultorios.activo = 1`) y su ficha y su usuario existentes. */
    private function secretariasActivas()
    {
        return DB::table('secretaria_consultorios')
            ->join('secretarias', 'secretarias.id', '=', 'secretaria_consultorios.secretaria_id')
            ->join('users', 'users.id', '=', 'secretarias.user_id')
            ->where('secretaria_consultorios.activo', 1)
            ->groupBy('secretaria_consultorios.consultorio_id')
            ->select('secretaria_consultorios.consultorio_id', DB::raw('COUNT(DISTINCT secretarias.id) AS cantidad'))
            ->pluck('cantidad', 'consultorio_id')
            ->map(function ($n) {
                return (int) $n;
            })
            ->all();
    }

    /**
     * Turnos por delante de cada consultorio: los vigentes (`activo` 1 o 2, lo que ocupa la agenda) con fecha de hoy en
     * adelante, con «hoy» en hora de Argentina (AgendaService::hoy).
     */
    private function turnosPorDelante()
    {
        return DB::table('turno_registrados')
            ->whereIn('activo', AgendaService::ACTIVOS_QUE_OCUPAN)
            ->where('fechaTurno', '>=', $this->agenda->hoy())
            ->groupBy('consultorio')
            ->select('consultorio', DB::raw('COUNT(*) AS cantidad'))
            ->pluck('cantidad', 'consultorio')
            ->map(function ($n) {
                return (int) $n;
            })
            ->all();
    }

    private function turnosPorDelanteDe($consultorioId)
    {
        return (int) DB::table('turno_registrados')
            ->where('consultorio', $consultorioId)
            ->whereIn('activo', AgendaService::ACTIVOS_QUE_OCUPAN)
            ->where('fechaTurno', '>=', $this->agenda->hoy())
            ->count();
    }

    private function consultorio($id)
    {
        $fila = DB::table('consultorios')->where('id', (int) $id)->first(['id', 'nombre', 'direccion', 'telefono', 'activo']);
        $medicos = $this->medicosActivos();
        $secretarias = $this->secretariasActivas();
        $id = (int) $id;
        return ConsultoriosReglas::fila(
            $fila,
            isset($medicos[$id]) ? $medicos[$id] : 0,
            isset($secretarias[$id]) ? $secretarias[$id] : 0,
            $this->turnosPorDelanteDe($id)
        );
    }

    /** Largo de una columna de `consultorios` según el esquema, o null si no se pudo leer. */
    private function largoDeLaColumna($columna)
    {
        try {
            $fila = DB::selectOne(
                'SELECT CHARACTER_MAXIMUM_LENGTH AS largo FROM information_schema.COLUMNS'
                . ' WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                ['consultorios', $columna]
            );
            return $fila !== null ? $fila->largo : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
