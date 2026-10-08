<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Catálogo de tipos de turno (tabla tipo_turnos) y qué tipos ofrece cada médico
 * (tabla medico_tipo_turnos). Reemplaza el patrón de hardcodear `medico->id` para
 * decidir qué tipos de turno mostrarle al paciente: un médico con más de un tipo
 * habilitado ofrece al paciente un selector, y cada horario fijo que carga queda
 * asociado a uno de esos tipos.
 *
 * Para el resto de los médicos (un solo tipo habilitado, "Consulta") el comportamiento
 * no cambia: todo sigue grabando/filtrando tipo_turno = 1 como hasta ahora.
 */
class TipoTurnoService
{
    /** Catálogo completo de tipos de turno activos, en orden de id. */
    public function catalogo()
    {
        return DB::table('tipo_turnos')->where('activo', 1)->orderBy('id')->get();
    }

    /** Ids de tipo_turno habilitados (activos) para un médico, ordenados. */
    public function habilitados($medicoId)
    {
        return DB::table('medico_tipo_turnos')
            ->where('medico', (int) $medicoId)
            ->where('activo', 1)
            ->orderBy('tipo_turno')
            ->pluck('tipo_turno')
            ->map(function ($id) {
                return (int) $id;
            })
            ->all();
    }

    /** true si el médico tiene más de un tipo de turno habilitado (dispara el selector). */
    public function tieneMultiplesTipos($medicoId)
    {
        return count($this->habilitados($medicoId)) > 1;
    }

    /** true si $tipoTurnoId está entre los tipos habilitados del médico. */
    public function esTipoValidoParaMedico($medicoId, $tipoTurnoId)
    {
        return in_array((int) $tipoTurnoId, $this->habilitados($medicoId), true);
    }

    /** Catálogo con el estado (habilitado o no) de cada tipo para un médico, para la pantalla admin. */
    public function estadoPara($medicoId)
    {
        $habilitados = $this->habilitados($medicoId);
        $out = [];
        foreach ($this->catalogo() as $tipo) {
            $out[] = [
                'id' => (int) $tipo->id,
                'nombre' => $tipo->nombre,
                'activo' => in_array((int) $tipo->id, $habilitados, true) ? 1 : 0,
            ];
        }
        return $out;
    }

    /** Solo los tipos habilitados del médico, con nombre, para renderizar el selector. */
    public function habilitadosConNombre($medicoId)
    {
        $habilitados = $this->habilitados($medicoId);
        if (empty($habilitados)) {
            return [];
        }
        return DB::table('tipo_turnos')
            ->whereIn('id', $habilitados)
            ->orderBy('id')
            ->get(['id', 'nombre'])
            ->map(function ($t) {
                return ['id' => (int) $t->id, 'nombre' => $t->nombre];
            })
            ->all();
    }

    /** Nombre de un tipo de turno por id (para mostrar en listados). Vacío si no existe. */
    public function nombre($tipoTurnoId)
    {
        $tipo = DB::table('tipo_turnos')->where('id', (int) $tipoTurnoId)->first();
        return $tipo ? $tipo->nombre : '';
    }

    /** Mapa id => nombre de todo el catálogo (para listados que muestran varios tipos). */
    public function nombresPorId()
    {
        return DB::table('tipo_turnos')->pluck('nombre', 'id')->all();
    }

    /** Deja habilitados para el médico exactamente los ids indicados (los demás quedan inactivos). */
    public function guardar($medicoId, array $ids)
    {
        $medicoId = (int) $medicoId;
        $catalogoIds = DB::table('tipo_turnos')->where('activo', 1)->pluck('id')->map(function ($id) {
            return (int) $id;
        })->all();
        $validos = array_values(array_intersect(array_map('intval', $ids), $catalogoIds));
        $ahora = now();

        DB::table('medico_tipo_turnos')
            ->where('medico', $medicoId)
            ->whereNotIn('tipo_turno', $validos)
            ->update(['activo' => 0, 'updated_at' => $ahora]);

        foreach ($validos as $tipoTurnoId) {
            $existe = DB::table('medico_tipo_turnos')
                ->where('medico', $medicoId)
                ->where('tipo_turno', $tipoTurnoId)
                ->first();
            if ($existe === null) {
                DB::table('medico_tipo_turnos')->insert([
                    'medico' => $medicoId,
                    'tipo_turno' => $tipoTurnoId,
                    'activo' => 1,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ]);
            } elseif ((int) $existe->activo !== 1) {
                DB::table('medico_tipo_turnos')->where('id', $existe->id)->update(['activo' => 1, 'updated_at' => $ahora]);
            }
        }

        return $validos;
    }
}
