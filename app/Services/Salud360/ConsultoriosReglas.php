<?php

namespace App\Services\Salud360;

/**
 * Reglas de los consultorios desde el panel del administrador de Salud 360 (spec 007, RF-35 a RF-43), sin base de datos
 * (se prueban con phpunit). Guardar es cosa del controlador.
 *
 * - Teléfono: se quitan espacios, guiones y paréntesis; tiene que quedar **solo dígitos, hasta 15** (la columna es
 *   `bigint`: 15 dígitos entran con margen; un 0 adelante se pierde al guardar, como en la pantalla web).
 * - Nombre y dirección: obligatorios, sin espacios de más, hasta el largo real de su columna (como mucho 255).
 * - Alta: el mismo nombre normalizado y la misma decisión que las especialidades (EspecialidadesReglas): una activa →
 *   duplicado; si no, una inactiva → se reactiva **sin cambiar sus datos**; si no, se crea.
 * - Desactivar: solo sin turnos por delante.
 *
 * Compatible con PHP 7.1 / Laravel 5.8.
 */
class ConsultoriosReglas
{
    const MAXIMO_DIGITOS_TELEFONO = 15;

    /** Lo que pide la spec 007 para nombre y dirección. */
    const MAXIMO_TEXTO = 255;

    /** Largos de `consultorios.nombre` (varchar(150)) y `consultorios.direccion` (varchar(191)), si no se leen del esquema. */
    const LARGO_NOMBRE_CONOCIDO = 150;
    const LARGO_DIRECCION_CONOCIDO = 191;

    /** El teléfono con solo dígitos (como texto), o null si no sirve. */
    public static function limpiarTelefono($telefono)
    {
        if (is_int($telefono)) {
            if ($telefono < 0) {
                return null;
            }
            $telefono = (string) $telefono;
        }
        if (!is_string($telefono)) {
            return null;
        }
        $limpio = str_replace([' ', "\t", '-', '(', ')'], '', trim($telefono));
        $patron = '/^[0-9]{1,' . self::MAXIMO_DIGITOS_TELEFONO . '}$/';
        return preg_match($patron, $limpio) === 1 ? $limpio : null;
    }

    /** El máximo que se valida: el de la columna si es menor que 255; sin dato del esquema, [conocido]. */
    public static function maximoEfectivo($largoColumna, $conocido)
    {
        $columna = is_numeric($largoColumna) && (int) $largoColumna > 0 ? (int) $largoColumna : (int) $conocido;
        return min(self::MAXIMO_TEXTO, $columna);
    }

    /**
     * Valida el cuerpo de `POST consultorios` (`nombre`, `direccion`, `telefono`) con los máximos de cada columna.
     * Devuelve ['nombre', 'direccion', 'telefono' (solo dígitos)] o ['error' => 'datos_invalidos', 'mensaje' => ...].
     */
    public static function validarAlta(array $cuerpo, $maximoNombre, $maximoDireccion)
    {
        $nombre = self::limpiar(isset($cuerpo['nombre']) ? $cuerpo['nombre'] : null);
        if ($nombre === '') {
            return self::falla('Falta el nombre del consultorio.');
        }
        if (mb_strlen($nombre, 'UTF-8') > (int) $maximoNombre) {
            return self::falla('El nombre del consultorio puede tener hasta ' . (int) $maximoNombre . ' caracteres.');
        }
        $direccion = self::limpiar(isset($cuerpo['direccion']) ? $cuerpo['direccion'] : null);
        if ($direccion === '') {
            return self::falla('Falta la dirección del consultorio.');
        }
        if (mb_strlen($direccion, 'UTF-8') > (int) $maximoDireccion) {
            return self::falla('La dirección puede tener hasta ' . (int) $maximoDireccion . ' caracteres.');
        }
        $escrito = isset($cuerpo['telefono']) ? $cuerpo['telefono'] : null;
        if ($escrito === null || (is_string($escrito) && trim($escrito) === '')) {
            return self::falla('Falta el teléfono del consultorio.');
        }
        $telefono = self::limpiarTelefono($escrito);
        if ($telefono === null) {
            return self::falla('El teléfono tiene que tener solo números (hasta ' . self::MAXIMO_DIGITOS_TELEFONO . ').');
        }
        return ['nombre' => $nombre, 'direccion' => $direccion, 'telefono' => $telefono];
    }

    /** Crear / reactivar / duplicado, con el nombre normalizado de las especialidades (un consultorio sin nombre no coincide). */
    public static function decidirAlta($nombre, array $existentes)
    {
        return EspecialidadesReglas::decidirAlta($nombre, array_values(array_filter($existentes, function ($c) {
            $c = (array) $c;
            return isset($c['nombre']) && is_string($c['nombre']) && trim($c['nombre']) !== '';
        })));
    }

    /** RF-41: solo se desactiva si no tiene turnos por delante. */
    public static function puedeDesactivar($turnosPorDelante)
    {
        return (int) $turnosPorDelante === 0;
    }

    public static function mensajeConTurnos($nombre, $turnosPorDelante)
    {
        return '«' . $nombre . '» tiene ' . (int) $turnosPorDelante
            . ' turnos por delante: no se puede desactivar hasta que no tenga turnos pendientes.';
    }

    /** El cuerpo de `PUT consultorios/{id}/activo`: `{"activo": true|false}` (la regla de EstadoMedicoReglas). */
    public static function validarActivo(array $cuerpo)
    {
        return EstadoMedicoReglas::validar($cuerpo);
    }

    public static function avisoDeEstado($nombre, $activo)
    {
        return 'Consultorio «' . $nombre . '» ' . ((int) $activo === 1 ? 'activado' : 'desactivado') . ' en turnos.';
    }

    /** Un consultorio del catálogo del administrador (`GET consultorios-admin`). */
    public static function fila($c, $medicosActivos, $secretariasActivas, $turnosPorDelante)
    {
        $c = (array) $c;
        $numero = function ($n) {
            return is_numeric($n) ? (int) $n : 0;
        };
        return [
            'id' => (int) $c['id'],
            'nombre' => isset($c['nombre']) ? (string) $c['nombre'] : '',
            'direccion' => isset($c['direccion']) ? (string) $c['direccion'] : '',
            'telefono' => isset($c['telefono']) ? (string) $c['telefono'] : '',
            'activo' => isset($c['activo']) && (int) $c['activo'] === 1 ? 1 : 0,
            'medicos_activos' => $numero($medicosActivos),
            'secretarias_activas' => $numero($secretariasActivas),
            'turnos_por_delante' => $numero($turnosPorDelante),
        ];
    }

    private static function limpiar($texto)
    {
        if (!is_string($texto)) {
            return '';
        }
        $limpio = preg_replace('/\s+/u', ' ', $texto);
        return trim($limpio === null ? $texto : $limpio);
    }

    private static function falla($mensaje)
    {
        return ['error' => 'datos_invalidos', 'mensaje' => $mensaje];
    }
}
