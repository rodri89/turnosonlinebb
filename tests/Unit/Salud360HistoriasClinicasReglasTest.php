<?php

namespace Tests\Unit;

use App\Services\Salud360\HistoriaClinicaService;
use App\Services\Salud360\HistoriasClinicasReglas;
use PHPUnit\Framework\TestCase;

/**
 * Reglas de PUT /api/salud360/medicos/{id}/historias-clinicas (sin base de datos).
 *
 * Los casos de la base (403, 404, transacción, idempotencia, filas inactivas) se prueban con curl contra
 * la copia local; ver md/API_SALUD360.md.
 */
class Salud360HistoriasClinicasReglasTest extends TestCase
{
    /** Catálogo de prueba, en el orden en que el servicio informa las habilitadas. */
    private $catalogo = ['pediatria', 'clinica', 'hepatologia', 'gineco'];

    // ------------------------------------------------------------------
    // 422 datos_invalidos: forma del cuerpo
    // ------------------------------------------------------------------

    public function testCuerpoVacioEsDatosInvalidos()
    {
        $r = HistoriasClinicasReglas::validar([], $this->catalogo);
        $this->assertSame('datos_invalidos', $r['error']);
        // El mensaje orienta a quien llama: nombra el campo que falta.
        $this->assertStringContainsString('historias_clinicas', $r['mensaje']);
    }

    public function testCuerpoSinLaClaveEsDatosInvalidos()
    {
        $r = HistoriasClinicasReglas::validar(['otra_cosa' => ['pediatria']], $this->catalogo);
        $this->assertSame('datos_invalidos', $r['error']);
        $this->assertNotEmpty($r['mensaje']);
    }

    /**
     * @dataProvider valoresQueNoSonLista
     */
    public function testValorQueNoEsListaEsDatosInvalido($valor)
    {
        $r = HistoriasClinicasReglas::validar(['historias_clinicas' => $valor], $this->catalogo);
        $this->assertSame('datos_invalidos', $r['error']);
        $this->assertNotEmpty($r['mensaje']);
        $this->assertArrayNotHasKey('historias_clinicas', $r);
    }

    public function valoresQueNoSonLista()
    {
        return [
            'null' => [null],
            'texto' => ['pediatria'],
            'texto con comas' => ['pediatria,clinica'],
            'cadena vacía' => [''],
            'entero' => [1],
            'booleano' => [true],
            'objeto (claves de texto)' => [['uno' => 'pediatria']],
            'objeto (claves numéricas salteadas)' => [[1 => 'pediatria']],
        ];
    }

    /**
     * @dataProvider elementosQueNoSonTexto
     */
    public function testElementoQueNoEsTextoEsDatosInvalido($elemento)
    {
        $r = HistoriasClinicasReglas::validar(['historias_clinicas' => ['pediatria', $elemento]], $this->catalogo);
        $this->assertSame('datos_invalidos', $r['error']);
        $this->assertNotEmpty($r['mensaje']);
        $this->assertArrayNotHasKey('historias_clinicas', $r);
    }

    public function elementosQueNoSonTexto()
    {
        return [
            'null' => [null],
            'entero' => [1],
            'decimal' => [0.5],
            'booleano' => [true],
            'lista' => [['clinica']],
        ];
    }

    public function testElementoQueNoEsTextoGanaSobreCodigoDesconocido()
    {
        $r = HistoriasClinicasReglas::validar(['historias_clinicas' => ['inventada', 7]], $this->catalogo);
        $this->assertSame('datos_invalidos', $r['error']);
        $this->assertStringNotContainsString('inventada', $r['mensaje']);
    }

    // ------------------------------------------------------------------
    // 422 datos_invalidos: códigos fuera del catálogo
    // ------------------------------------------------------------------

    public function testCodigoDesconocidoSeNombraEnElMensaje()
    {
        $r = HistoriasClinicasReglas::validar(['historias_clinicas' => ['pediatria', 'oftalmologia']], $this->catalogo);
        $this->assertSame('datos_invalidos', $r['error']);
        $this->assertStringContainsString('oftalmologia', $r['mensaje']);
        $this->assertArrayNotHasKey('historias_clinicas', $r);
    }

    public function testTodosLosCodigosDesconocidosSeNombranUnaSolaVez()
    {
        $r = HistoriasClinicasReglas::validar(
            ['historias_clinicas' => ['uno', 'clinica', 'dos', 'uno']],
            $this->catalogo
        );
        $this->assertSame('datos_invalidos', $r['error']);
        $this->assertStringContainsString('uno', $r['mensaje']);
        $this->assertStringContainsString('dos', $r['mensaje']);
        $this->assertSame(1, substr_count($r['mensaje'], '«uno»'));
        // Lo que sí es válido no se nombra como desconocido.
        $this->assertStringNotContainsString('«clinica»', $r['mensaje']);
    }

    public function testElMensajeListaLosCodigosValidos()
    {
        $r = HistoriasClinicasReglas::validar(['historias_clinicas' => ['x']], $this->catalogo);
        foreach ($this->catalogo as $codigo) {
            $this->assertStringContainsString($codigo, $r['mensaje']);
        }
    }

    public function testElCodigoSeCompararExacto()
    {
        foreach (['Pediatria', ' pediatria', 'pediatria ', ''] as $codigo) {
            $r = HistoriasClinicasReglas::validar(['historias_clinicas' => [$codigo]], $this->catalogo);
            $this->assertSame('datos_invalidos', $r['error'], 'Código: «' . $codigo . '»');
        }
    }

    public function testUnCodigoDesconocidoNoSalvaAlResto()
    {
        $r = HistoriasClinicasReglas::validar(['historias_clinicas' => ['clinica', 'pediatria', 'nada']], $this->catalogo);
        $this->assertSame('datos_invalidos', $r['error']);
    }

    // ------------------------------------------------------------------
    // Válidos
    // ------------------------------------------------------------------

    public function testListaVaciaEsValida()
    {
        $r = HistoriasClinicasReglas::validar(['historias_clinicas' => []], $this->catalogo);
        $this->assertArrayNotHasKey('error', $r);
        $this->assertSame([], $r['historias_clinicas']);
    }

    public function testUnCodigoValido()
    {
        $r = HistoriasClinicasReglas::validar(['historias_clinicas' => ['clinica']], $this->catalogo);
        $this->assertArrayNotHasKey('error', $r);
        $this->assertSame(['clinica'], $r['historias_clinicas']);
    }

    public function testLosDuplicadosSeNormalizan()
    {
        $r = HistoriasClinicasReglas::validar(
            ['historias_clinicas' => ['clinica', 'pediatria', 'clinica', 'clinica']],
            $this->catalogo
        );
        $this->assertArrayNotHasKey('error', $r);
        $this->assertSame(['pediatria', 'clinica'], $r['historias_clinicas']);
    }

    public function testLaListaNormalizadaSigueElOrdenDelCatalogo()
    {
        $r = HistoriasClinicasReglas::validar(
            ['historias_clinicas' => ['gineco', 'hepatologia', 'pediatria']],
            $this->catalogo
        );
        $this->assertSame(['pediatria', 'hepatologia', 'gineco'], $r['historias_clinicas']);
    }

    public function testTodasLasDelCatalogo()
    {
        $r = HistoriasClinicasReglas::validar(['historias_clinicas' => array_reverse($this->catalogo)], $this->catalogo);
        $this->assertSame($this->catalogo, $r['historias_clinicas']);
    }

    public function testLasClavesSobrantesDelCuerpoSeIgnoran()
    {
        $r = HistoriasClinicasReglas::validar(
            ['historias_clinicas' => ['clinica'], 'medico_id' => 99, 'x' => null],
            $this->catalogo
        );
        $this->assertSame(['clinica'], $r['historias_clinicas']);
    }

    // ------------------------------------------------------------------
    // Catálogo real del servicio
    // ------------------------------------------------------------------

    /** Sin `odontologia` en el catálogo, guardar las HC de un médico desde la app se la borraba (spec 102 de Salud 360). */
    public function testOdontologiaEsValidaYSeConservaAlGuardar()
    {
        $catalogo = array_keys(HistoriaClinicaService::CATALOGO);
        $r = HistoriasClinicasReglas::validar(['historias_clinicas' => ['odontologia', 'pediatria']], $catalogo);
        $this->assertArrayNotHasKey('error', $r);
        $this->assertSame(['pediatria', 'odontologia'], $r['historias_clinicas']);
    }

    public function testLaListaNormalizadaEsUnaListaConIndicesCorridos()
    {
        $r = HistoriasClinicasReglas::validar(['historias_clinicas' => ['gineco', 'pediatria']], $this->catalogo);
        $this->assertSame([0, 1], array_keys($r['historias_clinicas']));
    }
}
