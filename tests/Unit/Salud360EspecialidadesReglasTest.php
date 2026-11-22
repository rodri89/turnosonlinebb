<?php

namespace Tests\Unit;

use App\Services\Salud360\EspecialidadesReglas;
use PHPUnit\Framework\TestCase;

/**
 * Reglas del alta de especialidades del administrador (spec 007, RF-16 a RF-18), sin base de datos: el nombre
 * normalizado, la validación y si el alta crea, reactiva una inactiva o es un duplicado de una activa.
 *
 * Los casos con la base (403, 409, 404, conteos) se prueban con curl contra la copia local; ver md/API_SALUD360.md.
 */
class Salud360EspecialidadesReglasTest extends TestCase
{
    // ------------------------------------------------------------------
    // Nombre normalizado
    // ------------------------------------------------------------------

    /** @dataProvider mismosNombres */
    public function testElNombreNormalizadoIgnoraTildesMayusculasYEspacios($a, $b)
    {
        $this->assertSame(EspecialidadesReglas::normalizarNombre($a), EspecialidadesReglas::normalizarNombre($b));
    }

    public function mismosNombres()
    {
        return [
            'tilde' => ['Odontología', 'odontologia'],
            'mayúsculas y espacios' => ['Odontología', '  ODONTOLOGIA  '],
            'espacios en el medio' => ['Clínica   Médica', 'clinica medica'],
            'tabulaciones y saltos' => ["Clínica\t\nMédica", 'CLINICA MEDICA'],
            'mayúscula con tilde' => ['ÉNDOCRINOLOGÍA', 'endocrinologia'],
            'diéresis' => ['Lingüística', 'linguistica'],
            'todas las vocales' => ['áéíóú ÁÉÍÓÚ', 'aeiou aeiou'],
        ];
    }

    public function testLaFormaNormalizada()
    {
        $this->assertSame('odontologia', EspecialidadesReglas::normalizarNombre(' Odontología '));
        $this->assertSame('clinica medica', EspecialidadesReglas::normalizarNombre("Clínica \t Médica"));
    }

    public function testLaEnieNoEsUnaTilde()
    {
        // «sin tildes ni diéresis»: la ñ es otra letra (Peña no es Pena).
        $this->assertSame('peña', EspecialidadesReglas::normalizarNombre('PEÑA'));
        $this->assertNotSame(EspecialidadesReglas::normalizarNombre('Peña'), EspecialidadesReglas::normalizarNombre('Pena'));
    }

    public function testNombresDistintosNoSeConfunden()
    {
        $this->assertNotSame(EspecialidadesReglas::normalizarNombre('Pediatría'), EspecialidadesReglas::normalizarNombre('Odontología'));
        $this->assertNotSame(EspecialidadesReglas::normalizarNombre('Clínica médica'), EspecialidadesReglas::normalizarNombre('Clínica'));
    }

    public function testUnNombreQueNoEsTextoNormalizaVacio()
    {
        $this->assertSame('', EspecialidadesReglas::normalizarNombre(null));
        $this->assertSame('', EspecialidadesReglas::normalizarNombre(['x']));
    }

    // ------------------------------------------------------------------
    // Validación
    // ------------------------------------------------------------------

    /** @dataProvider vacios */
    public function testUnNombreVacioEsDatosInvalidos($nombre)
    {
        $r = EspecialidadesReglas::validarNombre($nombre);
        $this->assertSame('datos_invalidos', $r['error']);
        $this->assertNotEmpty($r['mensaje']);
    }

    public function vacios()
    {
        return ['vacío' => [''], 'solo espacios' => ["   \t "], 'null' => [null], 'no es texto' => [['Odontología']], 'número' => [123]];
    }

    public function testElNombreSeGuardaSinEspaciosDeMasYConSusTildes()
    {
        $this->assertSame(['nombre' => 'Clínica Médica'], EspecialidadesReglas::validarNombre("  Clínica   Médica "));
    }

    public function testHasta255CaracteresEsValidoYMasNo()
    {
        $this->assertSame(255, EspecialidadesReglas::MAXIMO_NOMBRE);
        // Se cuentan caracteres, no bytes: 255 letras con tilde son válidas.
        $justo = str_repeat('á', 255);
        $this->assertSame(['nombre' => $justo], EspecialidadesReglas::validarNombre($justo));
        $r = EspecialidadesReglas::validarNombre(str_repeat('á', 256));
        $this->assertSame('datos_invalidos', $r['error']);
        $this->assertStringContainsString('255', $r['mensaje']);
        // Los espacios de más no cuentan.
        $this->assertSame(['nombre' => str_repeat('a', 255)], EspecialidadesReglas::validarNombre('   ' . str_repeat('a', 255) . '   '));
    }

    // ------------------------------------------------------------------
    // Crear / reactivar / duplicado
    // ------------------------------------------------------------------

    private function existentes()
    {
        return [
            ['id' => 3, 'nombre' => 'Pediatría', 'activo' => 1],
            ['id' => 7, 'nombre' => 'Odontología', 'activo' => 0],
            (object) ['id' => 9, 'nombre' => 'Clínica Médica', 'activo' => '1'],
        ];
    }

    public function testSinCoincidenciasSeCrea()
    {
        $this->assertSame(['accion' => EspecialidadesReglas::CREAR], EspecialidadesReglas::decidirAlta('Cardiología', $this->existentes()));
        $this->assertSame(['accion' => EspecialidadesReglas::CREAR], EspecialidadesReglas::decidirAlta('Cardiología', []));
    }

    public function testUnaInactivaConElMismoNombreSeReactiva()
    {
        $this->assertSame(
            ['accion' => EspecialidadesReglas::REACTIVAR, 'id' => 7, 'nombre' => 'Odontología'],
            EspecialidadesReglas::decidirAlta(' ODONTOLOGIA ', $this->existentes())
        );
    }

    public function testUnaActivaConElMismoNombreEsDuplicadoConElNombreExistente()
    {
        $this->assertSame(
            ['accion' => EspecialidadesReglas::DUPLICADO, 'id' => 3, 'nombre' => 'Pediatría'],
            EspecialidadesReglas::decidirAlta('pediatria', $this->existentes())
        );
        // También con filas como objetos (lo que devuelve DB::table) y `activo` como texto.
        $this->assertSame(
            ['accion' => EspecialidadesReglas::DUPLICADO, 'id' => 9, 'nombre' => 'Clínica Médica'],
            EspecialidadesReglas::decidirAlta('clinica  medica', $this->existentes())
        );
    }

    public function testSiHayActivaEInactivaGanaElDuplicado()
    {
        $existentes = [
            ['id' => 2, 'nombre' => 'Odontologia', 'activo' => 0],
            ['id' => 5, 'nombre' => 'ODONTOLOGÍA', 'activo' => 1],
        ];
        $this->assertSame(
            ['accion' => EspecialidadesReglas::DUPLICADO, 'id' => 5, 'nombre' => 'ODONTOLOGÍA'],
            EspecialidadesReglas::decidirAlta('Odontología', $existentes)
        );
    }

    public function testConVariasInactivasSeReactivaLaDeMenorId()
    {
        $existentes = [
            ['id' => 8, 'nombre' => 'Odontología', 'activo' => 0],
            ['id' => 4, 'nombre' => 'odontologia', 'activo' => 0],
        ];
        $this->assertSame(
            ['accion' => EspecialidadesReglas::REACTIVAR, 'id' => 4, 'nombre' => 'odontologia'],
            EspecialidadesReglas::decidirAlta('Odontología', $existentes)
        );
    }

    public function testLasAccionesSonTextosEstables()
    {
        $this->assertSame('crear', EspecialidadesReglas::CREAR);
        $this->assertSame('reactivar', EspecialidadesReglas::REACTIVAR);
        $this->assertSame('duplicado', EspecialidadesReglas::DUPLICADO);
    }
}
