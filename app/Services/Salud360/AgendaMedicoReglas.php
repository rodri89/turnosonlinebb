<?php

namespace App\Services\Salud360;

/**
 * Reglas de `PUT /api/salud360/medicos/{id}/agenda`, sin base de datos (se prueban con phpunit).
 *
 * Son dos datos independientes del médico:
 *  - «tiene agenda»  → columna `medicos.tiene_agenda` (el médico maneja turnos);
 *  - «visible para pacientes» → `medicos.castigo_automatico` («Mostrar Médico»: 1 = se muestra).
 *
 * Combinaciones válidas: sin agenda; con agenda interna (no visible); con agenda pública (visible).
 * «Sin agenda y visible» no se puede escribir desde la API (sí puede existir ya en la base: se informa
 * tal cual y se puede limpiar).
 *
 * Compatible con PHP 7.1 / Laravel 5.8.
 */
class AgendaMedicoReglas
{
    const CAMPO_AGENDA = 'tiene_agenda';
    const CAMPO_VISIBLE = 'visible_en_turnos';

    /**
     * Lee el cuerpo del pedido. Al menos uno de los dos campos y, los que vengan, booleanos
     * (true/false, 1/0 o sus textos). Lo demás del cuerpo se ignora.
     *
     * @return array ['pedido' => ['tiene_agenda' => bool|null, 'visible_en_turnos' => bool|null]]
     *               o ['error' => 'datos_invalidos', 'mensaje' => ...]
     */
    public static function leerPedido(array $cuerpo)
    {
        $pedido = [self::CAMPO_AGENDA => null, self::CAMPO_VISIBLE => null];
        $vino = false;
        foreach ($pedido as $campo => $_) {
            if (!array_key_exists($campo, $cuerpo)) {
                continue;
            }
            $vino = true;
            $valor = self::comoBooleano($cuerpo[$campo]);
            if ($valor === null) {
                return self::falla('datos_invalidos', 'El campo ' . $campo . ' tiene que ser verdadero o falso.');
            }
            $pedido[$campo] = $valor;
        }
        if (!$vino) {
            return self::falla('datos_invalidos', 'Indicá tiene_agenda y/o visible_en_turnos (verdadero o falso).');
        }
        return ['pedido' => $pedido];
    }

    /**
     * Aplica las reglas al estado actual del médico.
     *
     * Orden (el 403, el 404 y la lectura del cuerpo ya pasaron):
     *  1. `tiene_agenda = false` deja también `castigo_automatico = 0` (RF-5); si además pide visible → agenda_requerida.
     *  2. `visible_en_turnos = true` sin agenda (ni ya tenida ni pedida en este mismo pedido) → agenda_requerida (RF-6).
     *  3. Solo `tiene_agenda = true`: la visibilidad no cambia.
     *
     * @param bool  $agendaActual  `medicos.tiene_agenda` = 1
     * @param bool  $visibleActual `medicos.castigo_automatico` = 1
     * @param array $pedido        lo que devuelve `leerPedido()['pedido']`
     * @return array ['tiene_agenda' => bool, 'visible_en_turnos' => bool, 'columnas' => [columna => 0|1]]
     *               o ['error' => 'agenda_requerida', 'mensaje' => ...]. `columnas` es lo que el pedido quiere
     *               dejar escrito (el controlador solo graba las que difieren de lo que ya hay).
     */
    public static function resolver($agendaActual, $visibleActual, array $pedido)
    {
        $agendaPedida = $pedido[self::CAMPO_AGENDA];
        $visiblePedido = $pedido[self::CAMPO_VISIBLE];

        $agenda = $agendaPedida === null ? (bool) $agendaActual : $agendaPedida;
        $visible = (bool) $visibleActual;
        $columnas = [];

        if ($agendaPedida !== null) {
            $columnas['tiene_agenda'] = $agendaPedida ? 1 : 0;
        }

        if ($agendaPedida === false) {
            if ($visiblePedido === true) {
                return self::falla('agenda_requerida', 'No se puede mostrar a los pacientes un médico sin agenda de turnos.');
            }
            $visible = false;
            $columnas['castigo_automatico'] = 0;
        } elseif ($visiblePedido !== null) {
            if ($visiblePedido === true && !$agenda) {
                return self::falla('agenda_requerida', 'Para mostrar al médico a los pacientes primero tiene que tener agenda de turnos.');
            }
            $visible = $visiblePedido;
            $columnas['castigo_automatico'] = $visiblePedido ? 1 : 0;
        }

        return ['tiene_agenda' => $agenda, 'visible_en_turnos' => $visible, 'columnas' => $columnas];
    }

    /**
     * Agenda y visibilidad de un médico que se da de alta (`POST usuarios`, rol médico).
     *
     *  - `tiene_agenda` viene → se guarda tal cual; con `mostrar_en_turnos` verdadero y `tiene_agenda = false` → agenda_requerida.
     *  - `tiene_agenda` no viene (la app publicada no lo manda) → vale lo mismo que `mostrar_en_turnos`: un alta con
     *    `mostrar_en_turnos = true` queda con agenda y visible.
     *  - Sin ninguno de los dos → sin agenda y no visible (RF-14).
     *  - `tiene_agenda` presente pero que no es booleano (incluido `null`) → datos_invalidos.
     *
     * `mostrar_en_turnos` se lee como siempre lo leyó el alta (verdadero según PHP), para no cambiar a la app publicada.
     *
     * @param array $cuerpo cuerpo del pedido (`$request->all()`)
     * @return array ['tiene_agenda' => bool, 'visible_en_turnos' => bool, 'columnas' => ['tiene_agenda' => 0|1, 'castigo_automatico' => 0|1]]
     *               o ['error' => 'datos_invalidos'|'agenda_requerida', 'mensaje' => ...]
     */
    public static function resolverAlta(array $cuerpo)
    {
        $visible = !empty($cuerpo['mostrar_en_turnos']);
        if (array_key_exists(self::CAMPO_AGENDA, $cuerpo)) {
            $agenda = self::comoBooleano($cuerpo[self::CAMPO_AGENDA]);
            if ($agenda === null) {
                return self::falla('datos_invalidos', 'El campo ' . self::CAMPO_AGENDA . ' tiene que ser verdadero o falso.');
            }
            if ($visible && !$agenda) {
                return self::falla('agenda_requerida', 'No se puede mostrar a los pacientes un médico sin agenda de turnos.');
            }
        } else {
            $agenda = $visible;
        }

        return [
            'tiene_agenda' => $agenda,
            'visible_en_turnos' => $visible,
            'columnas' => ['tiene_agenda' => $agenda ? 1 : 0, 'castigo_automatico' => $visible ? 1 : 0],
        ];
    }

    /** true/false para true, false, 1, 0 y los textos "true", "false", "1", "0"; null para cualquier otra cosa. */
    private static function comoBooleano($valor)
    {
        if (is_bool($valor)) {
            return $valor;
        }
        if (is_int($valor) && ($valor === 0 || $valor === 1)) {
            return $valor === 1;
        }
        if (is_string($valor)) {
            $texto = strtolower(trim($valor));
            if ($texto === 'true' || $texto === '1') {
                return true;
            }
            if ($texto === 'false' || $texto === '0') {
                return false;
            }
        }
        return null;
    }

    private static function falla($codigo, $mensaje)
    {
        return ['error' => $codigo, 'mensaje' => $mensaje];
    }
}
