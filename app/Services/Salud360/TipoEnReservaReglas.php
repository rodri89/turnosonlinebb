<?php

namespace App\Services\Salud360;

/**
 * Qué `tipo_turno` acepta la API de Salud 360 al cargar un horario fijo y al reservar un turno, sin base de
 * datos (se prueban con phpunit). Es lo que la web ya hace (MedicoController::guardarHorarioFijo y
 * registrarAsignarTurno), con una diferencia: la web corrige en silencio un tipo que no corresponde, la API
 * lo rechaza con un código para que la app lo muestre.
 *
 *  - `POST horarios`: el tipo del horario nuevo tiene que estar entre los habilitados del médico
 *    (paraHorarioNuevo). Un médico sin ningún tipo habilitado ofrece solo «Consulta» (1).
 *  - `POST turnos` y `POST turnos/sobreturno`: sobre un horario de plantilla de tipo T, el turno puede ser de
 *    ese tipo T o de una de sus opciones vigentes para el médico (paraTurno). La opción es la que queda guardada.
 *  - Excepción en ambos: un médico con a lo sumo UN tipo habilitado acepta un `tipo_turno` = 1 (el valor por
 *    omisión de la app ya publicada). En un turno se guarda el tipo T del horario, no el 1.
 *
 * Solo se valida lo que viene explícito en el cuerpo (leerTipo): un `tipo_turno` ausente deja el
 * comportamiento de siempre, que es el que espera la app ya publicada.
 *
 * Compatible con PHP 7.1 / Laravel 5.8.
 */
class TipoEnReservaReglas
{
    /** Tipo «Consulta»: el de un horario o un turno cuando no se indica otro. */
    const TIPO_CONSULTA = 1;

    /** Videollamada: sus horarios y turnos tienen su propio circuito (tablas `*_videollamadas`). */
    const TIPO_VIDEOLLAMADA = 4;

    /**
     * Lee `tipo_turno` tal como llegó. Ausente, nulo o vacío (el middleware convierte "" en null) → null,
     * «no vino». Un número o un texto numérico → entero. Cualquier otra cosa (texto, lista, booleano) → 0,
     * que no es ningún tipo del catálogo y por eso nunca coincide con nada.
     *
     * @param mixed $valor lo que vino en `tipo_turno`
     * @return int|null
     */
    public static function leerTipo($valor)
    {
        if ($valor === null || $valor === '') {
            return null;
        }
        if (is_array($valor) || is_bool($valor) || is_object($valor)) {
            return 0;
        }
        return (int) $valor;
    }

    /**
     * Tipo de un horario fijo nuevo (`POST horarios`).
     *
     *  - Sin tipo (`null`): «Consulta» (1), como hasta ahora; no se valida nada.
     *  - Con tipo: tiene que estar entre los `$habilitados` del médico. Si el médico no tiene ninguno
     *    habilitado, el único tipo posible es «Consulta» (1), como la web con un solo tipo.
     *  - Excepción: con 0 o 1 tipo habilitado, un 1 explícito se acepta aunque el habilitado sea otro (la app
     *    publicada manda 1 por omisión y la web lo grababa en silencio). Con 2 o más rige la regla anterior.
     *  - Si no → `tipo_no_habilitado`.
     *
     * @param int|null $tipoRecibido ya pasado por leerTipo
     * @param array    $habilitados  ids de tipo habilitados del médico (`TipoTurnoService::habilitados`)
     * @param array    $nombres      id => nombre, solo para el mensaje (opcional)
     * @return array ['tipo' => int] o ['error' => 'tipo_no_habilitado', 'mensaje' => ...]
     */
    public static function paraHorarioNuevo($tipoRecibido, array $habilitados, array $nombres = [])
    {
        if ($tipoRecibido === null) {
            return ['tipo' => self::TIPO_CONSULTA];
        }
        $permitidos = self::enteros($habilitados);
        if (count($permitidos) === 0) {
            $permitidos = [self::TIPO_CONSULTA];
        }
        if (self::aceptaConsultaPorOmision($tipoRecibido, $habilitados)) {
            return ['tipo' => self::TIPO_CONSULTA];
        }
        if (!in_array((int) $tipoRecibido, $permitidos, true)) {
            return [
                'error' => 'tipo_no_habilitado',
                'mensaje' => 'El tipo ' . self::etiqueta((int) $tipoRecibido, $nombres) . ' no está habilitado para este médico.',
            ];
        }
        return ['tipo' => (int) $tipoRecibido];
    }

    /**
     * Tipo de un turno sobre un horario de plantilla (`POST turnos`, `POST turnos/sobreturno`).
     *
     * Se valida solo si vino un tipo y el horario (misma fecha, hora y consultorio) es de la plantilla semanal
     * vigente (`$tiposDelHorario` no vacío): el tipo recibido tiene que ser el del horario o una opción vigente
     * de él. Si no hay horario de plantilla (fecha especial, sobreturno a mano) o no vino tipo, no se valida.
     * La videollamada (4) tampoco: tiene su propio circuito y se acepta como hasta ahora.
     *
     * @param int|null $tipoRecibido     ya pasado por leerTipo
     * @param array    $tiposDelHorario  tipos de los horarios de plantilla vigentes en ese slot (vacío = no hay)
     * @param array    $opcionesPorPadre tipo padre => ids de sus opciones vigentes para el médico
     * El «1» de la app publicada: con `$habilitados` (ids habilitados del médico) de 0 o 1 tipo, un `tipo_turno`
     * igual a 1 sobre un horario de un único tipo T ≠ 1 se acepta (aceptaConsultaPorOmision) y el turno se
     * guarda con T, el tipo del horario, no con 1: el listado y la ocupación filtran por el tipo del horario.
     * Con 2 o más habilitados, o sin pasar la lista (null), el 1 sobre un horario de otro tipo sigue siendo
     * `tipo_invalido`. Si el slot tiene varios tipos distintos de 1 no hay un T único y también se rechaza.
     *
     * @param array    $nombres          id => nombre, solo para el mensaje (opcional)
     * @param array|null $habilitados    ids de tipo habilitados del médico (`TipoTurnoService::habilitados`);
     *                                   null = no se sabe, rige la regla de siempre
     * @return array ['padre' => null] si el tipo es el del horario o no se valida; ['padre' => T] si es una
     *               opción del tipo T del horario; ['padre' => T, 'tipo' => T] si era el 1 de un médico con a lo
     *               sumo un tipo y hay que guardar T; o ['error' => 'tipo_invalido', 'mensaje' => ...]
     */
    public static function paraTurno($tipoRecibido, array $tiposDelHorario, array $opcionesPorPadre, array $nombres = [], array $habilitados = null)
    {
        $delHorario = self::enteros($tiposDelHorario);
        if ($tipoRecibido === null || count($delHorario) === 0 || (int) $tipoRecibido === self::TIPO_VIDEOLLAMADA) {
            return ['padre' => null];
        }
        $tipo = (int) $tipoRecibido;
        if (in_array($tipo, $delHorario, true)) {
            return ['padre' => null];
        }
        if ($habilitados !== null && count($delHorario) === 1 && self::aceptaConsultaPorOmision($tipo, $habilitados)) {
            return ['padre' => $delHorario[0], 'tipo' => $delHorario[0]];
        }
        foreach ($delHorario as $padre) {
            $opciones = isset($opcionesPorPadre[$padre]) ? self::enteros($opcionesPorPadre[$padre]) : [];
            if (in_array($tipo, $opciones, true)) {
                return ['padre' => $padre];
            }
        }
        $etiquetaTipo = self::etiqueta($tipo, $nombres);
        $etiquetasHorario = array_map(function ($t) use ($nombres) {
            return self::etiqueta($t, $nombres);
        }, $delHorario);
        return [
            'error' => 'tipo_invalido',
            'mensaje' => 'El tipo ' . $etiquetaTipo . ' no corresponde a ese horario: es de tipo '
                . implode(' o ', $etiquetasHorario) . ' y ' . $etiquetaTipo . ' no es una de sus opciones.',
        ];
    }

    /**
     * ¿El `tipo_turno` recibido es el 1 por omisión de la app publicada y el médico tiene a lo sumo un tipo
     * habilitado (0 o 1)? La web grababa 1 en silencio en ese caso; con 2 o más habilitados el médico elige
     * entre tipos y el 1 tiene que estar entre ellos como cualquier otro.
     *
     * @param int   $tipoRecibido
     * @param array $habilitados  ids de tipo habilitados del médico
     */
    private static function aceptaConsultaPorOmision($tipoRecibido, array $habilitados)
    {
        return (int) $tipoRecibido === self::TIPO_CONSULTA && count(self::enteros($habilitados)) <= 1;
    }

    /** @return int[] */
    private static function enteros(array $valores)
    {
        return array_values(array_map('intval', $valores));
    }

    /** «Nombre (id)» si se conoce el nombre, si no solo el id. */
    private static function etiqueta($id, array $nombres)
    {
        $nombre = isset($nombres[$id]) ? trim((string) $nombres[$id]) : '';
        return $nombre === '' ? (string) $id : $nombre . ' (' . $id . ')';
    }
}
