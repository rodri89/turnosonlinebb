<?php

namespace App\Services\Salud360;

/**
 * Regla de `POST /api/salud360/pacientes`, sin base de datos (se prueba con phpunit).
 *
 * El DNI identifica a la persona en toda la plataforma (`pacientes.dni` es único), así que un médico que da de
 * alta un DNI que ya existe no crea otra ficha: se asocia a la que hay. Pero los datos de esa ficha son de quien
 * la cargó o del propio paciente; solo los puede cambiar un médico que ya la tenía asociada.
 *
 * «Asociado al médico» = tiene fila en `medico_pacientes` con él (bloqueado o no) o algún turno con él en
 * `turno_registrados` (de cualquier estado: un turno cancelado también cuenta como «tuvo turno»).
 *
 * Compatible con PHP 7.1 / Laravel 5.8.
 */
class PacienteAltaReglas
{
    /**
     * ¿Se escriben en `pacientes` los datos que vinieron en el pedido?
     *
     *  - DNI nuevo → sí (se crea la ficha con esos datos).
     *  - DNI existente y paciente ya asociado al médico → sí (se actualiza, como siempre).
     *  - DNI existente y paciente NO asociado al médico → no: no se toca ningún campo (ni los vacíos) y la
     *    respuesta trae la ficha tal cual está; solo se crea el vínculo.
     *
     * @param bool $existe             ya hay un paciente con ese DNI
     * @param bool $vinculadoAlMedico  tiene fila en `medico_pacientes` con ese médico o algún turno con él
     * @return bool
     */
    public static function debeActualizarDatos($existe, $vinculadoAlMedico)
    {
        return !$existe || (bool) $vinculadoAlMedico;
    }
}
