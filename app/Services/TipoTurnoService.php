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

    /**
     * Ids de tipo_turno habilitados (activos) para un médico, ordenados.
     * $primerControl (0/1/null): si se indica, solo devuelve los tipos que aplican a ese caso
     * (primer_control NULL en medico_tipo_turnos = "siempre", o que coincida con el valor pedido).
     * Null (default) = sin filtrar, para pantallas admin que necesitan ver todo lo habilitado
     * (ej. "Mis horarios fijos", donde el tipo tagea el horario y no depende de si es primera consulta).
     */
    public function habilitados($medicoId, $primerControl = null)
    {
        $query = DB::table('medico_tipo_turnos')
            ->where('medico', (int) $medicoId)
            ->where('activo', 1);

        if ($primerControl !== null) {
            $primerControl = (int) $primerControl;
            $query->where(function ($q) use ($primerControl) {
                $q->whereNull('primer_control')->orWhere('primer_control', $primerControl);
            });
        }

        return $query->orderBy('tipo_turno')
            ->pluck('tipo_turno')
            ->map(function ($id) {
                return (int) $id;
            })
            ->all();
    }

    /** true si el médico tiene más de un tipo de turno habilitado para ese caso (dispara el selector). */
    public function tieneMultiplesTipos($medicoId, $primerControl = null)
    {
        return count($this->habilitados($medicoId, $primerControl)) > 1;
    }

    /** true si $tipoTurnoId está entre los tipos habilitados del médico para ese caso. */
    public function esTipoValidoParaMedico($medicoId, $tipoTurnoId, $primerControl = null)
    {
        return in_array((int) $tipoTurnoId, $this->habilitados($medicoId, $primerControl), true);
    }

    /**
     * Catálogo con el estado completo (habilitado y scoping de primer_control) de cada tipo
     * para un médico, para la pantalla admin. Sin filtrar por primer_control: ahí se edita todo.
     */
    public function estadoPara($medicoId)
    {
        $medicoId = (int) $medicoId;
        $filas = DB::table('medico_tipo_turnos')
            ->where('medico', $medicoId)
            ->where('activo', 1)
            ->get()
            ->keyBy(function ($fila) {
                return (int) $fila->tipo_turno;
            });

        $out = [];
        foreach ($this->catalogo() as $tipo) {
            $fila = $filas->get((int) $tipo->id);
            $out[] = [
                'id' => (int) $tipo->id,
                'nombre' => $tipo->nombre,
                'activo' => $fila ? 1 : 0,
                'primer_control' => ($fila && $fila->primer_control !== null) ? (int) $fila->primer_control : null,
            ];
        }
        return $out;
    }

    /**
     * Tipos habilitados del médico, con nombre, para renderizar el selector del paciente.
     * $primerControl: ver habilitados().
     */
    public function habilitadosConNombre($medicoId, $primerControl = null)
    {
        $habilitados = $this->habilitados($medicoId, $primerControl);
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

    /**
     * Deja habilitados para el médico exactamente los tipos indicados (los demás quedan inactivos),
     * con su scoping de primer_control.
     * $items acepta, por compatibilidad, tanto ids sueltos (int, sin scoping = "siempre") como
     * arrays/objetos ['id' => ..., 'primer_control' => null|0|1|''].
     */
    public function guardar($medicoId, array $items)
    {
        $medicoId = (int) $medicoId;
        $catalogoIds = DB::table('tipo_turnos')->where('activo', 1)->pluck('id')->map(function ($id) {
            return (int) $id;
        })->all();

        $normalizados = []; // tipo_turno_id => primer_control (null|0|1)
        foreach ($items as $item) {
            if (is_array($item)) {
                $id = (int) ($item['id'] ?? 0);
                $primerControl = $item['primer_control'] ?? null;
            } else {
                $id = (int) $item;
                $primerControl = null;
            }
            if ($id <= 0 || !in_array($id, $catalogoIds, true)) {
                continue;
            }
            $normalizados[$id] = ($primerControl === null || $primerControl === '') ? null : (int) $primerControl;
        }

        $validos = array_keys($normalizados);
        $ahora = now();

        DB::table('medico_tipo_turnos')
            ->where('medico', $medicoId)
            ->whereNotIn('tipo_turno', $validos)
            ->update(['activo' => 0, 'updated_at' => $ahora]);

        foreach ($normalizados as $tipoTurnoId => $primerControl) {
            $existe = DB::table('medico_tipo_turnos')
                ->where('medico', $medicoId)
                ->where('tipo_turno', $tipoTurnoId)
                ->first();
            if ($existe === null) {
                DB::table('medico_tipo_turnos')->insert([
                    'medico' => $medicoId,
                    'tipo_turno' => $tipoTurnoId,
                    'primer_control' => $primerControl,
                    'activo' => 1,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ]);
            } else {
                DB::table('medico_tipo_turnos')->where('id', $existe->id)->update([
                    'activo' => 1,
                    'primer_control' => $primerControl,
                    'updated_at' => $ahora,
                ]);
            }
        }

        return $validos;
    }

    /** Crea un tipo de turno nuevo en el catálogo global. Devuelve el id creado. */
    public function crear($nombre)
    {
        $nombre = trim((string) $nombre);
        if ($nombre === '') {
            throw new \InvalidArgumentException('El nombre del tipo de turno es requerido');
        }
        $ahora = now();
        return DB::table('tipo_turnos')->insertGetId([
            'nombre' => $nombre,
            'activo' => 1,
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ]);
    }

    /**
     * Opciones activas (con nombre) configuradas para un médico+tipo padre, para renderizar
     * el radio-group del modal de confirmación y la pantalla admin.
     */
    public function opciones($medicoId, $tipoTurnoPadreId)
    {
        $ids = DB::table('tipo_turno_opciones')
            ->where('medico', (int) $medicoId)
            ->where('tipo_turno_padre', (int) $tipoTurnoPadreId)
            ->where('activo', 1)
            ->pluck('tipo_turno_opcion');

        if ($ids->isEmpty()) {
            return [];
        }

        return DB::table('tipo_turnos')
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->get(['id', 'nombre'])
            ->map(function ($t) {
                return ['id' => (int) $t->id, 'nombre' => $t->nombre];
            })
            ->all();
    }

    /** true si el médico tiene al menos una opción configurada para ese tipo padre. */
    public function tieneOpciones($medicoId, $tipoTurnoPadreId)
    {
        return count($this->opciones($medicoId, $tipoTurnoPadreId)) > 0;
    }

    /**
     * Deja configuradas para el médico+tipo padre exactamente las opciones indicadas
     * (las demás quedan inactivas). Idempotente, mismo patrón que guardar().
     */
    public function guardarOpciones($medicoId, $tipoTurnoPadreId, array $opcionIds)
    {
        $medicoId = (int) $medicoId;
        $tipoTurnoPadreId = (int) $tipoTurnoPadreId;

        $catalogoIds = DB::table('tipo_turnos')->where('activo', 1)->pluck('id')->map(function ($id) {
            return (int) $id;
        })->all();

        $validos = array_values(array_unique(array_filter(array_map('intval', $opcionIds), function ($id) use ($catalogoIds, $tipoTurnoPadreId) {
            return $id > 0 && in_array($id, $catalogoIds, true);
        })));

        $ahora = now();

        DB::table('tipo_turno_opciones')
            ->where('medico', $medicoId)
            ->where('tipo_turno_padre', $tipoTurnoPadreId)
            ->whereNotIn('tipo_turno_opcion', $validos)
            ->update(['activo' => 0, 'updated_at' => $ahora]);

        foreach ($validos as $opcionId) {
            $existe = DB::table('tipo_turno_opciones')
                ->where('medico', $medicoId)
                ->where('tipo_turno_padre', $tipoTurnoPadreId)
                ->where('tipo_turno_opcion', $opcionId)
                ->first();
            if ($existe === null) {
                DB::table('tipo_turno_opciones')->insert([
                    'medico' => $medicoId,
                    'tipo_turno_padre' => $tipoTurnoPadreId,
                    'tipo_turno_opcion' => $opcionId,
                    'activo' => 1,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ]);
            } else {
                DB::table('tipo_turno_opciones')->where('id', $existe->id)->update([
                    'activo' => 1,
                    'updated_at' => $ahora,
                ]);
            }
        }

        return $validos;
    }

    /** Validación server-side: ¿$opcionId es una opción vigente para $medicoId+$tipoTurnoPadreId? */
    public function esOpcionValidaParaPadre($medicoId, $tipoTurnoPadreId, $opcionId)
    {
        return in_array((int) $opcionId, array_column($this->opciones($medicoId, $tipoTurnoPadreId), 'id'), true);
    }
}
