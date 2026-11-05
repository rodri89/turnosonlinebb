<?php

namespace App\Services\Salud360;

/**
 * Reglas de `PUT /api/salud360/medicos/{id}/historias-clinicas`, sin base de datos (se prueban con phpunit).
 *
 * El cuerpo trae la lista EXACTA de historias clínicas que debe quedar habilitada al médico
 * (`{"historias_clinicas": ["pediatria", "clinica"]}`; `[]` las deshabilita todas). Acá solo se valida
 * y se normaliza; guardar es cosa de HistoriaClinicaService::guardar.
 *
 * Compatible con PHP 7.1 / Laravel 5.8.
 */
class HistoriasClinicasReglas
{
    const CAMPO = 'historias_clinicas';

    /**
     * Valida el cuerpo del pedido (el 403 y el 404 ya pasaron).
     *
     *  1. Falta `historias_clinicas`, no es una lista (un objeto JSON, un texto, `null`...) o algún
     *     elemento no es texto → datos_invalidos.
     *  2. Algún código que no está en el catálogo (se compara exacto: no se ignoran mayúsculas ni
     *     espacios) → datos_invalidos, con el mensaje nombrando los códigos desconocidos. No se
     *     descarta ninguno en silencio y, si hay uno, no se guarda nada.
     *  3. Los repetidos se juntan y la lista queda en el orden del catálogo.
     *
     * @param array $cuerpo   cuerpo del pedido (`$request->all()`)
     * @param array $catalogo códigos válidos, en el orden del catálogo (`array_keys(HistoriaClinicaService::CATALOGO)`)
     * @return array ['historias_clinicas' => string[]] (lista normalizada, posiblemente vacía)
     *               o ['error' => 'datos_invalidos', 'mensaje' => ...]
     */
    public static function validar(array $cuerpo, array $catalogo)
    {
        $catalogo = array_values($catalogo);
        $validos = ' Códigos válidos: ' . implode(', ', $catalogo) . '.';

        if (!array_key_exists(self::CAMPO, $cuerpo)) {
            return self::falla('Falta historias_clinicas: la lista de códigos que quedan habilitadas ([] para ninguna).' . $validos);
        }
        $lista = $cuerpo[self::CAMPO];
        if (!is_array($lista) || array_keys($lista) !== array_keys(array_values($lista))) {
            return self::falla('historias_clinicas tiene que ser una lista de códigos ([] para ninguna).' . $validos);
        }
        foreach ($lista as $codigo) {
            if (!is_string($codigo)) {
                return self::falla('Cada elemento de historias_clinicas tiene que ser un código de texto.' . $validos);
            }
        }

        $desconocidos = array_values(array_unique(array_diff($lista, $catalogo)));
        if (count($desconocidos) > 0) {
            $nombres = implode(', ', array_map(function ($codigo) {
                return '«' . $codigo . '»';
            }, $desconocidos));
            $prefijo = count($desconocidos) === 1 ? 'Historia clínica desconocida: ' : 'Historias clínicas desconocidas: ';
            return self::falla($prefijo . $nombres . '.' . $validos);
        }

        return ['historias_clinicas' => array_values(array_intersect($catalogo, $lista))];
    }

    private static function falla($mensaje)
    {
        return ['error' => 'datos_invalidos', 'mensaje' => $mensaje];
    }
}
