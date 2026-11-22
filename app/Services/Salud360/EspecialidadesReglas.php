<?php

namespace App\Services\Salud360;

/**
 * Reglas del alta de especialidades desde el panel del administrador de Salud 360 (spec 007, RF-16 a RF-18), sin base
 * de datos (se prueban con phpunit). Guardar es cosa del controlador.
 *
 * - Nombre normalizado: sin espacios al principio ni al final, un solo espacio entre palabras, en minúsculas y sin
 *   tildes ni diéresis («Odontología» = «odontologia» = « ODONTOLOGIA »). La ñ no es una tilde: queda como ñ. La app
 *   usa la misma regla.
 * - Alta: una **activa** con el mismo nombre normalizado → duplicado (con el nombre existente); si no, una **inactiva**
 *   → se reactiva (la de menor id si hay varias); si no, se crea.
 *
 * Compatible con PHP 7.1 / Laravel 5.8 (usa mbstring, que Laravel ya exige; no usa intl).
 */
class EspecialidadesReglas
{
    const CREAR = 'crear';
    const REACTIVAR = 'reactivar';
    const DUPLICADO = 'duplicado';

    /** Largo máximo del nombre, en caracteres (spec 007, dependencia 1). */
    const MAXIMO_NOMBRE = 255;

    /** Vocales con tilde o diéresis (minúsculas, ya pasadas por mb_strtolower) y su forma sin marca. */
    private static $sinMarcas = [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
        'â' => 'a', 'ê' => 'e', 'î' => 'i', 'ô' => 'o', 'û' => 'u',
        'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
    ];

    /** El nombre normalizado, para comparar; '' si no es texto. */
    public static function normalizarNombre($nombre)
    {
        $limpio = self::limpiar($nombre);
        return strtr(mb_strtolower($limpio, 'UTF-8'), self::$sinMarcas);
    }

    /**
     * Valida el nombre del alta. Devuelve ['nombre' => <como se guarda: sin espacios de más, con sus mayúsculas y
     * tildes>] o ['error' => 'datos_invalidos', 'mensaje' => ...] si está vacío o pasa de [MAXIMO_NOMBRE] caracteres.
     */
    public static function validarNombre($nombre)
    {
        $limpio = self::limpiar($nombre);
        if ($limpio === '') {
            return ['error' => 'datos_invalidos', 'mensaje' => 'Falta el nombre de la especialidad.'];
        }
        if (mb_strlen($limpio, 'UTF-8') > self::MAXIMO_NOMBRE) {
            return [
                'error' => 'datos_invalidos',
                'mensaje' => 'El nombre de la especialidad puede tener hasta ' . self::MAXIMO_NOMBRE . ' caracteres.',
            ];
        }
        return ['nombre' => $limpio];
    }

    /**
     * Qué hace el alta de [nombre] frente a las especialidades [existentes] (filas con `id`, `nombre` y `activo`, como
     * arrays u objetos de `DB::table`):
     *  - ['accion' => 'duplicado', 'id' => ..., 'nombre' => <el existente>] si hay una activa con el mismo nombre normalizado;
     *  - ['accion' => 'reactivar', 'id' => ..., 'nombre' => <el existente>] si solo hay inactivas (la de menor id);
     *  - ['accion' => 'crear'] si no hay ninguna.
     */
    public static function decidirAlta($nombre, array $existentes)
    {
        $buscado = self::normalizarNombre($nombre);
        $activa = null;
        $inactiva = null;
        foreach ($existentes as $fila) {
            $fila = (array) $fila;
            if (!isset($fila['id'], $fila['nombre']) || self::normalizarNombre($fila['nombre']) !== $buscado) {
                continue;
            }
            $id = (int) $fila['id'];
            $esActiva = isset($fila['activo']) && (int) $fila['activo'] === 1;
            $candidata = ['id' => $id, 'nombre' => (string) $fila['nombre']];
            if ($esActiva) {
                if ($activa === null || $id < $activa['id']) {
                    $activa = $candidata;
                }
            } elseif ($inactiva === null || $id < $inactiva['id']) {
                $inactiva = $candidata;
            }
        }
        if ($activa !== null) {
            return ['accion' => self::DUPLICADO, 'id' => $activa['id'], 'nombre' => $activa['nombre']];
        }
        if ($inactiva !== null) {
            return ['accion' => self::REACTIVAR, 'id' => $inactiva['id'], 'nombre' => $inactiva['nombre']];
        }
        return ['accion' => self::CREAR];
    }

    /** Sin espacios al principio ni al final y con uno solo entre palabras; '' si no es texto. */
    private static function limpiar($nombre)
    {
        if (!is_string($nombre)) {
            return '';
        }
        $limpio = preg_replace('/\s+/u', ' ', $nombre);
        return trim($limpio === null ? $nombre : $limpio);
    }
}
