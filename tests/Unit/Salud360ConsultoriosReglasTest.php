<?php

namespace Tests\Unit;

use App\Services\Salud360\ConsultoriosReglas;
use PHPUnit\Framework\TestCase;

/**
 * Reglas de los consultorios del panel del administrador (spec 007, RF-35 a RF-43), sin base de datos: el teléfono, la
 * validación del alta (con el largo real de las columnas), crear / reactivar / duplicado y si se puede desactivar.
 *
 * Los casos con la base (403, 409 con_turnos, 404, conteos) se prueban con curl contra la copia local; ver
 * md/API_SALUD360.md.
 */
class Salud360ConsultoriosReglasTest extends TestCase
{
    private function cuerpo(array $cambios = [])
    {
        return array_merge(['nombre' => 'Consultorio Prueba', 'direccion' => 'Calle Inventada 123', 'telefono' => '291 455-0000'], $cambios);
    }

    private function validar(array $cuerpo, $nombre = 150, $direccion = 191)
    {
        return ConsultoriosReglas::validarAlta($cuerpo, $nombre, $direccion);
    }

    // ------------------------------------------------------------------
    // Teléfono
    // ------------------------------------------------------------------

    /** @dataProvider telefonosValidos */
    public function testElTelefonoSeLimpiaDeEspaciosGuionesYParentesis($escrito, $guardado)
    {
        $this->assertSame($guardado, ConsultoriosReglas::limpiarTelefono($escrito));
    }

    public function telefonosValidos()
    {
        return [
            'con espacios y guion' => ['291 455-0000', '2914550000'],
            'con paréntesis' => ['(0291) 455 0000', '02914550000'],
            'solo dígitos' => ['2914550000', '2914550000'],
            'número JSON' => [2914550000, '2914550000'],
            '15 dígitos' => ['123456789012345', '123456789012345'],
            'espacios en los extremos' => ['  2914550000  ', '2914550000'],
        ];
    }

    /** @dataProvider telefonosInvalidos */
    public function testUnTelefonoConOtraCosaQueDigitosNoSirve($escrito)
    {
        $this->assertNull(ConsultoriosReglas::limpiarTelefono($escrito));
    }

    public function telefonosInvalidos()
    {
        return [
            'letras' => ['291-ABC'],
            'más' => ['+54 291 4550000'],
            'punto' => ['291.4550000'],
            '16 dígitos' => ['1234567890123456'],
            'vacío' => [''],
            'solo separadores' => [' - () '],
            'null' => [null],
            'decimal' => [291.5],
            'negativo' => [-291],
            'lista' => [['291']],
        ];
    }

    public function testQuinceDigitosEntranEnUnBigint()
    {
        // bigint con signo: hasta 9223372036854775807 (19 dígitos). 15 nueves entran con margen.
        $this->assertSame(15, ConsultoriosReglas::MAXIMO_DIGITOS_TELEFONO);
        $this->assertTrue((int) ConsultoriosReglas::limpiarTelefono('999999999999999') <= PHP_INT_MAX);
        $this->assertSame('999999999999999', (string) (int) ConsultoriosReglas::limpiarTelefono('999999999999999'));
    }

    // ------------------------------------------------------------------
    // Validación del alta (largo real de las columnas)
    // ------------------------------------------------------------------

    public function testUnAltaValidaDevuelveLosDatosComoSeGuardan()
    {
        $this->assertSame(
            ['nombre' => 'Consultorio Prueba', 'direccion' => 'Calle Inventada 123', 'telefono' => '2914550000'],
            $this->validar($this->cuerpo(['nombre' => '  Consultorio   Prueba ', 'direccion' => ' Calle  Inventada 123 ']))
        );
    }

    /** @dataProvider faltantes */
    public function testCadaCampoEsObligatorio($campo, $valor, $texto)
    {
        $cuerpo = $this->cuerpo();
        if ($valor === '__falta__') {
            unset($cuerpo[$campo]);
        } else {
            $cuerpo[$campo] = $valor;
        }
        $r = $this->validar($cuerpo);
        $this->assertSame('datos_invalidos', $r['error']);
        $this->assertStringContainsString($texto, $r['mensaje']);
    }

    public function faltantes()
    {
        return [
            'sin nombre' => ['nombre', '__falta__', 'nombre'],
            'nombre en blanco' => ['nombre', '   ', 'nombre'],
            'sin dirección' => ['direccion', '__falta__', 'dirección'],
            'dirección no texto' => ['direccion', 123, 'dirección'],
            'sin teléfono' => ['telefono', '__falta__', 'teléfono'],
            'teléfono vacío' => ['telefono', '', 'teléfono'],
        ];
    }

    public function testElTelefonoConLetrasTieneSuAviso()
    {
        $r = $this->validar($this->cuerpo(['telefono' => '291-ABC']));
        $this->assertSame(['error' => 'datos_invalidos', 'mensaje' => 'El teléfono tiene que tener solo números (hasta 15).'], $r);
    }

    public function testLosLargosSonLosDeLasColumnas()
    {
        $this->assertSame(['nombre' => str_repeat('ó', 150)], array_intersect_key($this->validar($this->cuerpo(['nombre' => str_repeat('ó', 150)])), ['nombre' => 1]));
        $r = $this->validar($this->cuerpo(['nombre' => str_repeat('ó', 151)]));
        $this->assertSame('datos_invalidos', $r['error']);
        $this->assertStringContainsString('nombre del consultorio puede tener hasta 150 caracteres', $r['mensaje']);
        $r = $this->validar($this->cuerpo(['direccion' => str_repeat('a', 192)]));
        $this->assertStringContainsString('dirección puede tener hasta 191 caracteres', $r['mensaje']);
        $this->assertArrayNotHasKey('error', $this->validar($this->cuerpo(['direccion' => str_repeat('a', 191)])));
    }

    public function testElMaximoEfectivo()
    {
        $this->assertSame(150, ConsultoriosReglas::LARGO_NOMBRE_CONOCIDO);
        $this->assertSame(191, ConsultoriosReglas::LARGO_DIRECCION_CONOCIDO);
        $this->assertSame(150, ConsultoriosReglas::maximoEfectivo(150, ConsultoriosReglas::LARGO_NOMBRE_CONOCIDO));
        $this->assertSame(150, ConsultoriosReglas::maximoEfectivo(null, ConsultoriosReglas::LARGO_NOMBRE_CONOCIDO));
        $this->assertSame(191, ConsultoriosReglas::maximoEfectivo('x', ConsultoriosReglas::LARGO_DIRECCION_CONOCIDO));
        // Una columna más larga no sube el tope de la spec (255).
        $this->assertSame(255, ConsultoriosReglas::maximoEfectivo(1000, 150));
    }

    // ------------------------------------------------------------------
    // Crear / reactivar / duplicado (nombre normalizado de EspecialidadesReglas)
    // ------------------------------------------------------------------

    private function existentes()
    {
        return [
            (object) ['id' => 1, 'nombre' => null, 'activo' => 1],
            (object) ['id' => 3, 'nombre' => 'Donado 433', 'activo' => 1],
            (object) ['id' => 8, 'nombre' => 'Donado 433', 'activo' => 1],
            (object) ['id' => 9, 'nombre' => 'Rincón 615', 'activo' => 0],
        ];
    }

    public function testDecidirAlta()
    {
        $this->assertSame(['accion' => 'crear'], ConsultoriosReglas::decidirAlta('Estomba 366', $this->existentes()));
        $this->assertSame(['accion' => 'duplicado', 'id' => 3, 'nombre' => 'Donado 433'], ConsultoriosReglas::decidirAlta('DONADO  433', $this->existentes()));
        $this->assertSame(['accion' => 'reactivar', 'id' => 9, 'nombre' => 'Rincón 615'], ConsultoriosReglas::decidirAlta('rincon 615', $this->existentes()));
    }

    public function testUnConsultorioSinNombreNoCoincideConNada()
    {
        $this->assertSame(['accion' => 'crear'], ConsultoriosReglas::decidirAlta('Nuevo', [(object) ['id' => 1, 'nombre' => null, 'activo' => 0]]));
    }

    // ------------------------------------------------------------------
    // Desactivar
    // ------------------------------------------------------------------

    public function testSoloSeDesactivaSinTurnosPorDelante()
    {
        $this->assertTrue(ConsultoriosReglas::puedeDesactivar(0));
        $this->assertTrue(ConsultoriosReglas::puedeDesactivar('0'));
        $this->assertFalse(ConsultoriosReglas::puedeDesactivar(1));
        $this->assertFalse(ConsultoriosReglas::puedeDesactivar('12'));
    }

    public function testElRechazoConTurnosDiceLaCantidad()
    {
        $this->assertSame(
            '«Donado 433» tiene 3 turnos por delante: no se puede desactivar hasta que no tenga turnos pendientes.',
            ConsultoriosReglas::mensajeConTurnos('Donado 433', 3)
        );
    }

    public function testElActivoTieneQueSerUnBooleano()
    {
        $this->assertSame(['activo' => false], ConsultoriosReglas::validarActivo(['activo' => false]));
        $this->assertSame('datos_invalidos', ConsultoriosReglas::validarActivo(['activo' => 0])['error']);
    }

    public function testElAvisoDelEstadoQueQuedo()
    {
        $this->assertSame('Consultorio «Donado 433» desactivado en turnos.', ConsultoriosReglas::avisoDeEstado('Donado 433', 0));
        $this->assertSame('Consultorio «Donado 433» activado en turnos.', ConsultoriosReglas::avisoDeEstado('Donado 433', 1));
    }

    // ------------------------------------------------------------------
    // Fila del catálogo del administrador
    // ------------------------------------------------------------------

    public function testLaFilaDelCatalogo()
    {
        $c = (object) ['id' => '3', 'nombre' => 'Donado 433', 'direccion' => 'Donado 433', 'telefono' => 2914550000, 'activo' => '1', 'foto' => 'x.png', 'config' => null];
        $this->assertSame(
            ['id' => 3, 'nombre' => 'Donado 433', 'direccion' => 'Donado 433', 'telefono' => '2914550000', 'activo' => 1,
             'medicos_activos' => 2, 'secretarias_activas' => 1, 'turnos_por_delante' => 7],
            ConsultoriosReglas::fila($c, 2, '1', 7)
        );
        // Sin nombre (consultorios viejos): texto vacío, no null.
        $sinNombre = ConsultoriosReglas::fila((object) ['id' => 1, 'nombre' => null, 'direccion' => 'X', 'telefono' => 0, 'activo' => 0], null, null, null);
        $this->assertSame('', $sinNombre['nombre']);
        $this->assertSame(0, $sinNombre['activo']);
        $this->assertSame(0, $sinNombre['turnos_por_delante']);
    }
}
