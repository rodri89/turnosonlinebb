<?php

namespace App\Services\Salud360;

/**
 * Reglas de disponibilidad de horarios, sin base de datos (se prueban con phpunit).
 *
 * Regla de los bloqueos: un turno del paciente «bloqueo» (DNI 99999) ocupa el horario sin importar
 * su `tipo_turno`. Los bloqueos nacen con tipo 1, pero el horario puede ser de otro tipo (médicos con
 * varios tipos de turno): si la lista de libres filtrara el bloqueo por tipo, el horario se vería
 * libre para quien elige otro tipo y recién al reservar daría «ocupado» (la validación no filtra por tipo).
 *
 * La reserva interna de la secretaria usa la misma regla de «ocupado»: cualquier turno activo en el
 * mismo horario ocupa, sin importar el tipo (ver `turnoDestinoLibre`).
 *
 * Compatible con PHP 7.1 / Laravel 5.8.
 */
class DisponibilidadReglas
{
    /** DNI del paciente que usa el consultorio para bloquear horarios. */
    const DNI_BLOQUEO = 99999;

    /** Tipo de turno con el que se guarda un bloqueo cuando el horario no trae el suyo. */
    const TIPO_TURNO_POR_DEFECTO = 1;

    /** true si el DNI es el del paciente «bloqueo». */
    public static function esDniBloqueo($dni)
    {
        return is_numeric($dni) && (int) $dni === self::DNI_BLOQUEO;
    }

    /**
     * ¿Un turno registrado ocupa el horario en la lista de libres para el tipo elegido?
     *
     *  - Bloqueo: siempre (sin mirar el tipo).
     *  - Turno real, tipo con opciones configuradas: siempre (su tipo pudo cambiar a la opción elegida
     *    tras el insert), salvo que `$siempreFiltrarTipo` (turnos dobles) fuerce el filtro.
     *  - Turno real, caso general: solo si su tipo es el elegido.
     *
     * @param mixed $tipoTurnoRegistrado `turno_registrados.tipo_turno`
     * @param bool  $esBloqueo           el turno es del paciente «bloqueo»
     * @param mixed $tipoElegido         tipo de turno que está eligiendo el paciente
     * @param bool  $tieneOpciones       `TipoTurnoService::tieneOpciones` para ese médico y tipo
     * @param bool  $siempreFiltrarTipo  filtrar por tipo aunque el tipo tenga opciones
     */
    public static function ocupaHorario($tipoTurnoRegistrado, $esBloqueo, $tipoElegido, $tieneOpciones, $siempreFiltrarTipo = false)
    {
        if ($esBloqueo) {
            return true;
        }
        if ($siempreFiltrarTipo || !$tieneOpciones) {
            return $tipoTurnoRegistrado !== null && $tipoElegido !== null
                && (int) $tipoTurnoRegistrado === (int) $tipoElegido;
        }
        return true;
    }

    /**
     * De los turnos activos del día (objetos con `tipo_turno` y `paciente_dni`), deja los que ocupan
     * horario para el tipo elegido. Conserva el orden recibido.
     *
     * @param iterable $turnos
     * @return array lista de objetos
     */
    public static function turnosQueOcupan($turnos, $tipoElegido, $tieneOpciones, $siempreFiltrarTipo = false)
    {
        $out = [];
        foreach ($turnos as $t) {
            $dni = isset($t->paciente_dni) ? $t->paciente_dni : null;
            $tipo = isset($t->tipo_turno) ? $t->tipo_turno : null;
            if (self::ocupaHorario($tipo, self::esDniBloqueo($dni), $tipoElegido, $tieneOpciones, $siempreFiltrarTipo)) {
                $out[] = $t;
            }
        }
        return $out;
    }

    /**
     * Reserva interna (panel de la secretaria): ¿el horario de destino está libre?
     * Libre si ningún turno activo (`activo` = 1) lo ocupa, SIN mirar `tipo_turno` ni si es un bloqueo:
     * igual que el panel del médico, la API y la web pública. `$ignorarTurnoId` es el turno que se está
     * moviendo (no choca consigo mismo).
     *
     * @param iterable $turnosDelSlot objetos o arrays con `activo` y, opcionalmente, `id`
     * @param mixed    $ignorarTurnoId
     */
    public static function turnoDestinoLibre($turnosDelSlot, $ignorarTurnoId = null)
    {
        foreach ($turnosDelSlot as $t) {
            $t = (object) $t;
            $activo = isset($t->activo) ? (int) $t->activo : 0;
            if ($activo !== 1) {
                continue;
            }
            $id = isset($t->id) ? $t->id : null;
            if ($ignorarTurnoId !== null && $id !== null && (int) $id === (int) $ignorarTurnoId) {
                continue;
            }
            return false;
        }
        return true;
    }

    /**
     * Turnos dobles: todos los horarios del par tienen que estar libres (si uno está ocupado no se
     * reserva ninguno). Sin horarios que comprobar devuelve false.
     *
     * @param array $slots lista de listas de turnos, una por horario
     */
    public static function destinosLibres(array $slots, $ignorarTurnoId = null)
    {
        if (count($slots) === 0) {
            return false;
        }
        foreach ($slots as $turnosDelSlot) {
            if (!self::turnoDestinoLibre($turnosDelSlot, $ignorarTurnoId)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Horarios que bloquea `bloquear-dia`: todos los del día sin importar el tipo de turno, uno por
     * horario (si dos tipos coinciden en la misma hora, se bloquea una vez; gana el tipo más bajo).
     * Cada bloqueo lleva el `tipo_turno` de su propio horario o `$tipoPorDefecto` si no tiene.
     *
     * @param iterable $horarios objetos con `horario` y, opcionalmente, `tipo_turno`
     * @param int      $tipoPorDefecto
     * @return array lista de ['horario' => string, 'tipo_turno' => int] ordenada por horario
     */
    public static function horariosParaBloquear($horarios, $tipoPorDefecto = self::TIPO_TURNO_POR_DEFECTO)
    {
        $filas = [];
        foreach ($horarios as $h) {
            $tipo = isset($h->tipo_turno) ? (int) $h->tipo_turno : 0;
            $filas[] = [
                'horario' => (string) $h->horario,
                'tipo_turno' => $tipo > 0 ? $tipo : (int) $tipoPorDefecto,
            ];
        }
        usort($filas, function ($a, $b) {
            $c = strcmp($a['horario'], $b['horario']);
            return $c !== 0 ? $c : $a['tipo_turno'] - $b['tipo_turno'];
        });
        $out = [];
        $vistos = [];
        foreach ($filas as $f) {
            if (isset($vistos[$f['horario']])) {
                continue;
            }
            $vistos[$f['horario']] = true;
            $out[] = $f;
        }
        return $out;
    }
}
