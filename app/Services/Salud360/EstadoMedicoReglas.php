<?php

namespace App\Services\Salud360;

/**
 * Reglas de `PUT /api/salud360/medicos/{id}/activo`, sin base de datos (se prueban con phpunit).
 *
 * El cuerpo es `{"activo": true|false}`. Se acepta solo un booleano JSON de verdad: ni `1`/`0`, ni
 * `"true"`/`"false"`, ni `null`. Es una baja o un alta de acceso: ante la duda no se escribe nada.
 *
 * Compatible con PHP 7.1 / Laravel 5.8.
 */
class EstadoMedicoReglas
{
    const CAMPO = 'activo';

    /**
     * Valida el cuerpo del pedido (el 403 y el 404 ya pasaron). Lo demás del cuerpo se ignora.
     *
     * @param array $cuerpo cuerpo del pedido (`$request->all()`)
     * @return array ['activo' => bool] o ['error' => 'datos_invalidos', 'mensaje' => ...]
     */
    public static function validar(array $cuerpo)
    {
        if (!array_key_exists(self::CAMPO, $cuerpo)) {
            return self::falla('Falta activo: tiene que ser verdadero o falso.');
        }
        if (!is_bool($cuerpo[self::CAMPO])) {
            return self::falla('El campo activo tiene que ser verdadero o falso (un booleano, no un número ni un texto).');
        }
        return [self::CAMPO => $cuerpo[self::CAMPO]];
    }

    private static function falla($mensaje)
    {
        return ['error' => 'datos_invalidos', 'mensaje' => $mensaje];
    }
}
