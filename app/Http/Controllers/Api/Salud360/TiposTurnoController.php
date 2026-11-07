<?php

namespace App\Http\Controllers\Api\Salud360;

use App\Services\Salud360\TiposTurnoReglas;
use App\Services\TipoTurnoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Catálogo global de tipos de turno (`tipo_turnos`), desde el panel de administración de la app.
 *
 * Es lo mismo que la web hace con `POST admin_crear_tipo_turno` (TipoTurnoService::crear), pero con
 * token y con una regla más: no se crea un tipo cuyo nombre ya existe. Las reglas del nombre están en
 * TiposTurnoReglas.
 *
 * Solo para el administrador. No toca lo que cada médico tiene habilitado (ver MedicoTiposTurnoController).
 *
 * Compatible con PHP 7.1 / Laravel 5.8.
 */
class TiposTurnoController extends Salud360Controller
{
    /**
     * GET tipos-turno
     *
     * El catálogo completo, también los tipos inactivos (`activo`), en orden de id (el de la web).
     */
    public function index(Request $request)
    {
        if (!$this->esAdmin($request->user())) {
            return $this->error('Solo el administrador puede ver el catálogo de tipos de turno.', 403, 'sin_permiso');
        }

        return $this->ok(['tipos' => $this->catalogoCompleto()]);
    }

    /**
     * POST tipos-turno  {nombre}
     *
     * Crea el tipo (activo) y responde 201 con su ficha. Si ya hay uno con ese nombre (sin distinguir
     * mayúsculas ni espacios de los extremos) responde 409 `nombre_repetido` con la ficha del existente y no
     * crea nada.
     */
    public function crear(Request $request)
    {
        if (!$this->esAdmin($request->user())) {
            return $this->error('Solo el administrador puede crear tipos de turno.', 403, 'sin_permiso');
        }

        $nombre = $request->input('nombre');

        // La lectura del catálogo, la comprobación y el alta van juntas, con el catálogo bloqueado:
        // dos pedidos a la vez con el mismo nombre no crean dos tipos.
        $resultado = DB::transaction(function () use ($nombre) {
            DB::table('tipo_turnos')->lockForUpdate()->get(['id']);
            $leido = TiposTurnoReglas::validarNombre($nombre, $this->catalogoCompleto());
            if (isset($leido['error'])) {
                return $leido;
            }
            $id = app(TipoTurnoService::class)->crear($leido['nombre']);
            return ['tipo' => ['id' => (int) $id, 'nombre' => $leido['nombre'], 'activo' => 1]];
        });

        if (isset($resultado['error'])) {
            if ($resultado['error'] === 'nombre_repetido') {
                return $this->error($resultado['mensaje'], 409, 'nombre_repetido', ['tipo' => $resultado['existente']]);
            }
            return $this->error($resultado['mensaje'], 422, $resultado['error']);
        }

        return $this->ok(['tipo' => $resultado['tipo']], 201);
    }

    /** @return array [['id' => int, 'nombre' => string, 'activo' => int], ...] por id, activos e inactivos */
    private function catalogoCompleto()
    {
        return DB::table('tipo_turnos')
            ->orderBy('id')
            ->get(['id', 'nombre', 'activo'])
            ->map(function ($t) {
                return ['id' => (int) $t->id, 'nombre' => $t->nombre, 'activo' => (int) $t->activo];
            })
            ->values()
            ->all();
    }
}
