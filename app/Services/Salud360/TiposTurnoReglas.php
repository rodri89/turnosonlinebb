<?php

namespace App\Services\Salud360;

/**
 * Reglas de la configuración de tipos de turno de la API Salud 360, sin base de datos (se prueban con phpunit):
 *
 *  - `POST tipos-turno`: el nombre del tipo nuevo (validarNombre).
 *  - `PUT medicos/{id}/tipos-turno`: la lista exacta de tipos que queda habilitada al médico, con su
 *    condición (`primer_control`) y, si se quiere, sus opciones (validarConfiguracion).
 *
 * Acá solo se valida y se normaliza; guardar es cosa de TipoTurnoService (`guardar`, `guardarOpciones`,
 * `crear`), el mismo que usa la web de administración.
 *
 * Compatible con PHP 7.1 / Laravel 5.8.
 */
class TiposTurnoReglas
{
    /** Largo máximo del nombre de un tipo (la columna admite 255, pero 60 alcanza para un listado). */
    const NOMBRE_MAX = 60;

    /**
     * Valida el cuerpo de `PUT medicos/{id}/tipos-turno` (el 403 y el 404 ya pasaron).
     *
     *  1. Forma (JSON estricto), sin mirar el catálogo: `tipos` tiene que estar y ser una lista; cada
     *     elemento un objeto con `id` entero positivo; `primer_control` (si viene) exactamente `null`, `0`
     *     o `1` (ni "1", ni `true`, ni `2`); `opciones` (si viene) una lista de enteros positivos.
     *     Si `primer_control` no viene, es «siempre» (`null`), igual que un id suelto en la web.
     *  2. Catálogo: cada `id` tiene que estar entre los activos y no repetirse; cada opción tiene que estar
     *     entre los activos y no ser su propio padre. Los mensajes nombran el id.
     *
     * Si algo falla, no se guarda nada. Las opciones repetidas de un mismo tipo se juntan.
     *
     * @param array $cuerpo                 cuerpo del pedido (`$request->all()`)
     * @param int[] $idsActivosDelCatalogo  ids de `tipo_turnos` con `activo = 1`
     * @return array ['tipos' => [['id' => int, 'primer_control' => null|0|1, 'opciones' => null|int[]], ...]]
     *               (en el orden enviado; `opciones` nulo = no tocar) o ['error' => 'datos_invalidos', 'mensaje' => ...]
     */
    public static function validarConfiguracion(array $cuerpo, array $idsActivosDelCatalogo)
    {
        if (!array_key_exists('tipos', $cuerpo)) {
            return self::falla('Falta tipos: la lista de tipos de turno que quedan habilitados ([] para ninguno).');
        }
        $tipos = $cuerpo['tipos'];
        if (!self::esLista($tipos)) {
            return self::falla('tipos tiene que ser una lista de objetos {id, primer_control, opciones} ([] para ninguno).');
        }

        // Primero la forma de todos los elementos, después el catálogo.
        $leidos = [];
        foreach ($tipos as $i => $tipo) {
            $donde = 'tipos[' . $i . ']';
            if (!is_array($tipo) || (count($tipo) > 0 && self::esLista($tipo))) {
                return self::falla($donde . ' tiene que ser un objeto {id, primer_control, opciones}.');
            }
            if (!array_key_exists('id', $tipo) || !self::esEnteroPositivo($tipo['id'])) {
                return self::falla($donde . '.id tiene que ser un número entero mayor que cero.');
            }

            $primerControl = null;
            if (array_key_exists('primer_control', $tipo)) {
                $primerControl = $tipo['primer_control'];
                if ($primerControl !== null && $primerControl !== 0 && $primerControl !== 1) {
                    return self::falla($donde . '.primer_control tiene que ser null (siempre), 1 (solo primera consulta) o 0 (solo consultas de seguimiento).');
                }
            }

            $opciones = null;
            if (array_key_exists('opciones', $tipo)) {
                $opciones = $tipo['opciones'];
                if (!self::esLista($opciones)) {
                    return self::falla($donde . '.opciones tiene que ser una lista de ids de tipo de turno ([] para ninguna).');
                }
                foreach ($opciones as $opcion) {
                    if (!self::esEnteroPositivo($opcion)) {
                        return self::falla($donde . '.opciones tiene que tener solo números enteros mayores que cero.');
                    }
                }
                $opciones = array_values(array_unique($opciones));
            }

            $leidos[] = ['id' => $tipo['id'], 'primer_control' => $primerControl, 'opciones' => $opciones];
        }

        $activos = array_map('intval', $idsActivosDelCatalogo);
        $vistos = [];
        foreach ($leidos as $i => $tipo) {
            $id = $tipo['id'];
            if (!in_array($id, $activos, true)) {
                return self::falla('El tipo de turno ' . $id . ' (tipos[' . $i . '].id) no existe en el catálogo o está inactivo.');
            }
            if (isset($vistos[$id])) {
                return self::falla('El tipo de turno ' . $id . ' está repetido en tipos.');
            }
            $vistos[$id] = true;

            foreach ((array) $tipo['opciones'] as $opcion) {
                if ($opcion === $id) {
                    return self::falla('El tipo de turno ' . $id . ' no puede ser opción de sí mismo (tipos[' . $i . '].opciones).');
                }
                if (!in_array($opcion, $activos, true)) {
                    return self::falla('La opción ' . $opcion . ' (tipos[' . $i . '].opciones) no existe en el catálogo o está inactiva.');
                }
            }
        }

        return ['tipos' => $leidos];
    }

    /**
     * Valida el nombre de `POST tipos-turno`.
     *
     *  1. Tiene que ser un texto; se le sacan los espacios de los extremos; no puede quedar vacío ni pasar
     *     de NOMBRE_MAX caracteres → datos_invalidos.
     *  2. Si ya hay un tipo con ese nombre (sin distinguir mayúsculas ni espacios de los extremos, activo o
     *     no) → nombre_repetido, con la ficha del existente en `existente`. La web no valida esto.
     *
     * @param mixed $nombre   lo que vino en `nombre`
     * @param array $catalogo todo el catálogo, también los inactivos: [['id' => int, 'nombre' => string, 'activo' => int], ...]
     * @return array ['nombre' => string] (ya sin espacios extremos), ['error' => 'datos_invalidos', 'mensaje' => ...]
     *               o ['error' => 'nombre_repetido', 'mensaje' => ..., 'existente' => ['id', 'nombre', 'activo']]
     */
    public static function validarNombre($nombre, array $catalogo)
    {
        if (!is_string($nombre)) {
            // Un texto vacío o solo de espacios llega acá como null (ConvertEmptyStringsToNull).
            return self::falla('nombre tiene que ser un texto, el nombre del tipo de turno (no puede faltar ni estar vacío).');
        }
        $limpio = self::sinEspaciosExtremos($nombre);
        if ($limpio === null) {
            return self::falla('nombre tiene que ser un texto UTF-8 válido.');
        }
        if ($limpio === '') {
            return self::falla('nombre no puede estar vacío.');
        }
        if (mb_strlen($limpio, 'UTF-8') > self::NOMBRE_MAX) {
            return self::falla('nombre no puede pasar de ' . self::NOMBRE_MAX . ' caracteres.');
        }

        $buscado = mb_strtolower($limpio, 'UTF-8');
        foreach ($catalogo as $tipo) {
            $existente = self::sinEspaciosExtremos((string) $tipo['nombre']);
            if ($existente !== null && mb_strtolower($existente, 'UTF-8') === $buscado) {
                return [
                    'error' => 'nombre_repetido',
                    'mensaje' => 'Ya existe un tipo de turno con ese nombre: «' . $tipo['nombre'] . '» (id ' . (int) $tipo['id'] . ').',
                    'existente' => ['id' => (int) $tipo['id'], 'nombre' => $tipo['nombre'], 'activo' => (int) $tipo['activo']],
                ];
            }
        }

        return ['nombre' => $limpio];
    }

    /** Lista JSON: arreglo con claves 0..n-1 (un objeto JSON, texto, número o null no lo son). */
    private static function esLista($valor)
    {
        return is_array($valor) && array_keys($valor) === array_keys(array_values($valor));
    }

    private static function esEnteroPositivo($valor)
    {
        return is_int($valor) && $valor > 0;
    }

    /** Saca espacios, tabulaciones, saltos y espacios Unicode de los extremos. Null si no es UTF-8 válido. */
    private static function sinEspaciosExtremos($texto)
    {
        return preg_replace('/^[\s\p{Z}]+|[\s\p{Z}]+$/u', '', $texto);
    }

    private static function falla($mensaje)
    {
        return ['error' => 'datos_invalidos', 'mensaje' => $mensaje];
    }
}
