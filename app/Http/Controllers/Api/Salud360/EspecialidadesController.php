<?php

namespace App\Http\Controllers\Api\Salud360;

use App\Services\Salud360\EspecialidadesReglas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Especialidades desde el panel del administrador de la app Salud 360 (spec 007): el catálogo completo y el alta.
 *
 * El catálogo común (`GET catalogos`) solo trae las activas y no cambia: este es aparte, del administrador. Las reglas
 * del nombre y de crear / reactivar / duplicado están en EspecialidadesReglas.
 *
 * Solo para el administrador (`403 sin_permiso` para los demás).
 *
 * Compatible con PHP 7.1 / Laravel 5.8.
 */
class EspecialidadesController extends Salud360Controller
{
    /**
     * GET especialidades
     *
     * Todas, activas e inactivas, en orden de id (el de turnos), con cuántos médicos activos tiene cada una:
     * `{ok, especialidades: [{id, nombre, activo, medicos_activos}]}`.
     */
    public function index(Request $request)
    {
        if (!$this->esAdmin($request->user())) {
            return $this->error('Solo el administrador puede ver el catálogo de especialidades.', 403, 'sin_permiso');
        }
        $medicos = $this->medicosActivosPorEspecialidad();
        $especialidades = DB::table('especialidads')->orderBy('id')->get(['id', 'nombre', 'activo'])
            ->map(function ($e) use ($medicos) {
                return EspecialidadesReglas::fila($e, isset($medicos[(int) $e->id]) ? $medicos[(int) $e->id] : 0);
            })
            ->values()
            ->all();
        return $this->ok(['especialidades' => $especialidades]);
    }

    /**
     * POST especialidades  {nombre}
     *
     * - Nombre vacío o más largo que lo que admite la columna → `422 datos_invalidos` (el mensaje dice el máximo).
     * - Una activa con el mismo nombre normalizado → `409 duplicado` con `especialidad` (la existente); no cambia nada.
     * - Una inactiva con ese nombre → la reactiva (solo `activo` y `updated_at`): `200` con `resultado: reactivada`.
     * - Si no → la crea activa, con la foto vacía y sin color (como la pantalla web sin foto): `201` con
     *   `resultado: creada`.
     * La respuesta trae `especialidad: {id, nombre, activo, medicos_activos}`.
     */
    public function crear(Request $request)
    {
        if (!$this->esAdmin($request->user())) {
            return $this->error('Solo el administrador puede agregar especialidades.', 403, 'sin_permiso');
        }

        $leido = EspecialidadesReglas::validarNombre($request->input('nombre'), EspecialidadesReglas::maximoEfectivo($this->largoDeLaColumna()));
        if (isset($leido['error'])) {
            return $this->error($leido['mensaje'], 422, $leido['error']);
        }
        $nombre = $leido['nombre'];

        // Leer, decidir y escribir con la tabla bloqueada: dos altas a la vez con el mismo nombre no crean dos.
        $resultado = DB::transaction(function () use ($nombre) {
            $existentes = DB::table('especialidads')->lockForUpdate()->orderBy('id')->get(['id', 'nombre', 'activo'])->all();
            $decision = EspecialidadesReglas::decidirAlta($nombre, $existentes);
            $ahora = now();
            switch ($decision['accion']) {
                case EspecialidadesReglas::DUPLICADO:
                    return ['accion' => EspecialidadesReglas::DUPLICADO, 'id' => $decision['id']];
                case EspecialidadesReglas::REACTIVAR:
                    DB::table('especialidads')->where('id', $decision['id'])->update(['activo' => 1, 'updated_at' => $ahora]);
                    return ['accion' => EspecialidadesReglas::REACTIVAR, 'id' => $decision['id']];
                default:
                    $id = DB::table('especialidads')->insertGetId([
                        'nombre' => $nombre,
                        'foto' => '',
                        'color' => '',
                        'activo' => 1,
                        'created_at' => $ahora,
                        'updated_at' => $ahora,
                    ]);
                    return ['accion' => EspecialidadesReglas::CREAR, 'id' => (int) $id];
            }
        });

        $especialidad = $this->especialidad($resultado['id']);
        switch ($resultado['accion']) {
            case EspecialidadesReglas::DUPLICADO:
                return $this->error('Ya existe la especialidad «' . $especialidad['nombre'] . '» en turnos.', 409, 'duplicado', ['especialidad' => $especialidad]);
            case EspecialidadesReglas::REACTIVAR:
                return $this->ok(['resultado' => 'reactivada', 'especialidad' => $especialidad]);
            default:
                return $this->ok(['resultado' => 'creada', 'especialidad' => $especialidad], 201);
        }
    }

    /** @return array [especialidad_id => cantidad de médicos con `activo` 1] */
    private function medicosActivosPorEspecialidad()
    {
        return DB::table('medicos')->where('activo', 1)
            ->groupBy('especialidad')
            ->select('especialidad', DB::raw('COUNT(*) AS cantidad'))
            ->pluck('cantidad', 'especialidad')
            ->map(function ($n) {
                return (int) $n;
            })
            ->all();
    }

    private function especialidad($id)
    {
        $fila = DB::table('especialidads')->where('id', (int) $id)->first(['id', 'nombre', 'activo']);
        $medicos = DB::table('medicos')->where('activo', 1)->where('especialidad', (int) $id)->count();
        return EspecialidadesReglas::fila($fila, $medicos);
    }

    /** Largo de `especialidads.nombre` según el esquema, o null si no se pudo leer (vale LARGO_COLUMNA_CONOCIDO). */
    private function largoDeLaColumna()
    {
        try {
            $fila = DB::selectOne(
                'SELECT CHARACTER_MAXIMUM_LENGTH AS largo FROM information_schema.COLUMNS'
                . ' WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                ['especialidads', 'nombre']
            );
            return $fila !== null ? $fila->largo : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
