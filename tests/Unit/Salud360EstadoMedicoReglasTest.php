<?php

namespace Tests\Unit;

use App\Services\Salud360\EstadoMedicoReglas;
use PHPUnit\Framework\TestCase;

/**
 * Reglas de PUT /api/salud360/medicos/{id}/activo (sin base de datos).
 *
 * Los casos de la base (403, 404, transacción, idempotencia) se prueban con curl contra la copia
 * local; ver md/API_SALUD360.md.
 */
class Salud360EstadoMedicoReglasTest extends TestCase
{
    // ------------------------------------------------------------------
    // 422 datos_invalidos
    // ------------------------------------------------------------------

    public function testCuerpoVacioEsDatosInvalidos()
    {
        $r = EstadoMedicoReglas::validar([]);
        $this->assertSame('datos_invalidos', $r['error']);
        // El mensaje orienta a quien llama: nombra el campo que falta.
        $this->assertStringContainsString('activo', $r['mensaje']);
        $this->assertArrayNotHasKey('activo', $r);
    }

    public function testCuerpoSinLaClaveEsDatosInvalidos()
    {
        $r = EstadoMedicoReglas::validar(['otra_cosa' => true, 'tiene_agenda' => false]);
        $this->assertSame('datos_invalidos', $r['error']);
        $this->assertNotEmpty($r['mensaje']);
    }

    /**
     * @dataProvider valoresQueNoSonBooleanoJson
     */
    public function testValorQueNoEsBooleanoEstrictoEsDatosInvalido($valor)
    {
        $r = EstadoMedicoReglas::validar(['activo' => $valor]);
        $this->assertSame('datos_invalidos', $r['error']);
        $this->assertNotEmpty($r['mensaje']);
        $this->assertArrayNotHasKey('activo', $r);
    }

    public function valoresQueNoSonBooleanoJson()
    {
        return [
            'null' => [null],
            'texto true' => ['true'],
            'texto false' => ['false'],
            'texto si' => ['si'],
            'cadena vacía' => [''],
            'entero 1' => [1],
            'entero 0' => [0],
            'entero 2' => [2],
            'entero -1' => [-1],
            'texto 1' => ['1'],
            'texto 0' => ['0'],
            'decimal' => [0.5],
            'lista vacía' => [[]],
            'lista con true' => [[true]],
            'objeto JSON' => [['activo' => true]],
        ];
    }

    // ------------------------------------------------------------------
    // Válidos
    // ------------------------------------------------------------------

    public function testTrueEsValido()
    {
        $r = EstadoMedicoReglas::validar(['activo' => true]);
        $this->assertArrayNotHasKey('error', $r);
        $this->assertTrue($r['activo']);
    }

    public function testFalseEsValido()
    {
        $r = EstadoMedicoReglas::validar(['activo' => false]);
        $this->assertArrayNotHasKey('error', $r);
        $this->assertFalse($r['activo']);
    }

    public function testLoDemasDelCuerpoSeIgnora()
    {
        $r = EstadoMedicoReglas::validar(['activo' => false, 'tiene_agenda' => true, 'otra_cosa' => 'x']);
        $this->assertArrayNotHasKey('error', $r);
        $this->assertSame(['activo' => false], $r);
    }
}
