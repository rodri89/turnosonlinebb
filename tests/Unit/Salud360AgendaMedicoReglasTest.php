<?php

namespace Tests\Unit;

use App\Services\Salud360\AgendaMedicoReglas;
use PHPUnit\Framework\TestCase;

/**
 * Reglas de PUT /api/salud360/medicos/{id}/agenda (sin base de datos).
 *
 * Los casos de la base (403, 404, transacción, turnos futuros) se prueban con curl contra la copia
 * local; ver md/API_SALUD360.md.
 */
class Salud360AgendaMedicoReglasTest extends TestCase
{
    // ------------------------------------------------------------------
    // leerPedido: 422 datos_invalidos
    // ------------------------------------------------------------------

    public function testCuerpoVacioEsDatosInvalidos()
    {
        $r = AgendaMedicoReglas::leerPedido([]);
        $this->assertSame('datos_invalidos', $r['error']);
        $this->assertNotEmpty($r['mensaje']);
    }

    public function testCamposQueNoSonDeAgendaSeIgnoranYSinNingunoEsInvalido()
    {
        $r = AgendaMedicoReglas::leerPedido(['otra_cosa' => true]);
        $this->assertSame('datos_invalidos', $r['error']);
    }

    /**
     * @dataProvider valoresNoBooleanos
     */
    public function testValorQueNoEsBooleanoEsDatosInvalido($valor)
    {
        $this->assertSame('datos_invalidos', AgendaMedicoReglas::leerPedido(['tiene_agenda' => $valor])['error']);
        $this->assertSame('datos_invalidos', AgendaMedicoReglas::leerPedido(['visible_en_turnos' => $valor])['error']);
        // Un campo bueno no salva al malo.
        $this->assertSame('datos_invalidos', AgendaMedicoReglas::leerPedido(['tiene_agenda' => true, 'visible_en_turnos' => $valor])['error']);
    }

    public function valoresNoBooleanos()
    {
        return [
            'null' => [null],
            'texto libre' => ['si'],
            'cadena vacía' => [''],
            'entero 2' => [2],
            'entero -1' => [-1],
            'decimal' => [0.5],
            'lista' => [[true]],
        ];
    }

    /**
     * @dataProvider booleanosValidos
     */
    public function testAceptaLasFormasDeUnBooleano($valor, $esperado)
    {
        $r = AgendaMedicoReglas::leerPedido(['tiene_agenda' => $valor]);
        $this->assertArrayNotHasKey('error', $r);
        $this->assertSame($esperado, $r['pedido']['tiene_agenda']);
        $this->assertNull($r['pedido']['visible_en_turnos']);
    }

    public function booleanosValidos()
    {
        return [
            'true' => [true, true],
            'false' => [false, false],
            'uno' => [1, true],
            'cero' => [0, false],
            'texto true' => ['true', true],
            'texto false' => ['false', false],
            'texto 1' => ['1', true],
            'texto 0' => ['0', false],
        ];
    }

    public function testAlcanzaConUnoSoloDeLosDosCampos()
    {
        $r = AgendaMedicoReglas::leerPedido(['visible_en_turnos' => false]);
        $this->assertNull($r['pedido']['tiene_agenda']);
        $this->assertFalse($r['pedido']['visible_en_turnos']);
    }

    // ------------------------------------------------------------------
    // resolver
    // ------------------------------------------------------------------

    private function pedido($agenda, $visible)
    {
        return ['tiene_agenda' => $agenda, 'visible_en_turnos' => $visible];
    }

    public function testApagarLaAgendaDeUnMedicoVisibleLoDejaNoVisible()
    {
        $r = AgendaMedicoReglas::resolver(true, true, $this->pedido(false, null));
        $this->assertArrayNotHasKey('error', $r);
        $this->assertFalse($r['tiene_agenda']);
        $this->assertFalse($r['visible_en_turnos']);
        $this->assertSame(['tiene_agenda' => 0, 'castigo_automatico' => 0], $r['columnas']);
    }

    public function testApagarLaAgendaYPedirVisibleEnElMismoPedidoEsAgendaRequerida()
    {
        $r = AgendaMedicoReglas::resolver(true, true, $this->pedido(false, true));
        $this->assertSame('agenda_requerida', $r['error']);
        $this->assertArrayNotHasKey('columnas', $r);
    }

    public function testVisibleSinAgendaEsAgendaRequerida()
    {
        $r = AgendaMedicoReglas::resolver(false, false, $this->pedido(null, true));
        $this->assertSame('agenda_requerida', $r['error']);
    }

    public function testPrenderAgendaYVisibleJuntosEnUnMedicoSinAgenda()
    {
        $r = AgendaMedicoReglas::resolver(false, false, $this->pedido(true, true));
        $this->assertArrayNotHasKey('error', $r);
        $this->assertTrue($r['tiene_agenda']);
        $this->assertTrue($r['visible_en_turnos']);
        $this->assertSame(['tiene_agenda' => 1, 'castigo_automatico' => 1], $r['columnas']);
    }

    public function testPrenderSoloLaAgendaNoTocaLaVisibilidad()
    {
        $visible = AgendaMedicoReglas::resolver(false, true, $this->pedido(true, null));
        $this->assertTrue($visible['visible_en_turnos']);
        $this->assertSame(['tiene_agenda' => 1], $visible['columnas']);

        $interna = AgendaMedicoReglas::resolver(false, false, $this->pedido(true, null));
        $this->assertFalse($interna['visible_en_turnos']);
        $this->assertSame(['tiene_agenda' => 1], $interna['columnas']);
    }

    public function testVisibleConAgendaYaPrendidaSeGuarda()
    {
        $r = AgendaMedicoReglas::resolver(true, false, $this->pedido(null, true));
        $this->assertTrue($r['tiene_agenda']);
        $this->assertTrue($r['visible_en_turnos']);
        $this->assertSame(['castigo_automatico' => 1], $r['columnas']);
    }

    public function testApagarSoloVisibleSiempreSePuede()
    {
        // Con agenda.
        $con = AgendaMedicoReglas::resolver(true, true, $this->pedido(null, false));
        $this->assertTrue($con['tiene_agenda']);
        $this->assertFalse($con['visible_en_turnos']);
        $this->assertSame(['castigo_automatico' => 0], $con['columnas']);

        // Sin agenda y visible (combinación anómala que ya existe en la base): se puede limpiar.
        $anomalo = AgendaMedicoReglas::resolver(false, true, $this->pedido(null, false));
        $this->assertArrayNotHasKey('error', $anomalo);
        $this->assertFalse($anomalo['tiene_agenda']);
        $this->assertFalse($anomalo['visible_en_turnos']);
    }

    public function testApagarLaAgendaLimpiaElMedicoVisibleSinAgenda()
    {
        $r = AgendaMedicoReglas::resolver(false, true, $this->pedido(false, null));
        $this->assertArrayNotHasKey('error', $r);
        $this->assertFalse($r['visible_en_turnos']);
        $this->assertSame(0, $r['columnas']['castigo_automatico']);
    }

    public function testNoSeInventaUnCambioSiLaAgendaYaEstabaApagada()
    {
        // El estado final es el mismo y el mismo pedido repetido da el mismo resultado.
        $primera = AgendaMedicoReglas::resolver(true, true, $this->pedido(false, null));
        $segunda = AgendaMedicoReglas::resolver(false, false, $this->pedido(false, null));
        $this->assertSame($primera['tiene_agenda'], $segunda['tiene_agenda']);
        $this->assertSame($primera['visible_en_turnos'], $segunda['visible_en_turnos']);
        $this->assertSame($primera['columnas'], $segunda['columnas']);
    }

    public function testVisibleSinAgendaNoGuardaNada()
    {
        $r = AgendaMedicoReglas::resolver(false, false, $this->pedido(null, true));
        $this->assertArrayNotHasKey('columnas', $r);
    }

    // ------------------------------------------------------------------
    // resolverAlta: POST usuarios (rol médico)
    // ------------------------------------------------------------------

    public function testAltaSinNingunoQuedaSinAgendaYNoVisible()
    {
        $r = AgendaMedicoReglas::resolverAlta([]);
        $this->assertArrayNotHasKey('error', $r);
        $this->assertFalse($r['tiene_agenda']);
        $this->assertFalse($r['visible_en_turnos']);
        $this->assertSame(['tiene_agenda' => 0, 'castigo_automatico' => 0], $r['columnas']);
    }

    public function testAltaDeLaAppViejaMostrarEnTurnosSinTieneAgendaDejaAgendaYVisible()
    {
        $r = AgendaMedicoReglas::resolverAlta(['mostrar_en_turnos' => true]);
        $this->assertArrayNotHasKey('error', $r);
        $this->assertTrue($r['tiene_agenda']);
        $this->assertTrue($r['visible_en_turnos']);
        $this->assertSame(['tiene_agenda' => 1, 'castigo_automatico' => 1], $r['columnas']);
    }

    public function testAltaDeLaAppViejaMostrarEnTurnosFalseSinTieneAgendaQuedaSinAgenda()
    {
        $r = AgendaMedicoReglas::resolverAlta(['mostrar_en_turnos' => false]);
        $this->assertFalse($r['tiene_agenda']);
        $this->assertFalse($r['visible_en_turnos']);
        $this->assertSame(['tiene_agenda' => 0, 'castigo_automatico' => 0], $r['columnas']);
    }

    public function testAltaConAgendaInternaNoVisible()
    {
        $r = AgendaMedicoReglas::resolverAlta(['tiene_agenda' => true, 'mostrar_en_turnos' => false]);
        $this->assertTrue($r['tiene_agenda']);
        $this->assertFalse($r['visible_en_turnos']);
        $this->assertSame(['tiene_agenda' => 1, 'castigo_automatico' => 0], $r['columnas']);
    }

    public function testAltaConAgendaSinDecirMostrarEnTurnosEsInterna()
    {
        $r = AgendaMedicoReglas::resolverAlta(['tiene_agenda' => true]);
        $this->assertSame(['tiene_agenda' => 1, 'castigo_automatico' => 0], $r['columnas']);
    }

    public function testAltaConAgendaPublica()
    {
        $r = AgendaMedicoReglas::resolverAlta(['tiene_agenda' => true, 'mostrar_en_turnos' => true]);
        $this->assertTrue($r['tiene_agenda']);
        $this->assertTrue($r['visible_en_turnos']);
        $this->assertSame(['tiene_agenda' => 1, 'castigo_automatico' => 1], $r['columnas']);
    }

    public function testAltaSinAgendaExplicitaYNoVisible()
    {
        $r = AgendaMedicoReglas::resolverAlta(['tiene_agenda' => false, 'mostrar_en_turnos' => false]);
        $this->assertArrayNotHasKey('error', $r);
        $this->assertSame(['tiene_agenda' => 0, 'castigo_automatico' => 0], $r['columnas']);
    }

    public function testAltaSinAgendaExplicitaSinDecirMostrarEnTurnos()
    {
        $r = AgendaMedicoReglas::resolverAlta(['tiene_agenda' => false]);
        $this->assertArrayNotHasKey('error', $r);
        $this->assertSame(['tiene_agenda' => 0, 'castigo_automatico' => 0], $r['columnas']);
    }

    public function testAltaVisibleSinAgendaEsAgendaRequerida()
    {
        $r = AgendaMedicoReglas::resolverAlta(['tiene_agenda' => false, 'mostrar_en_turnos' => true]);
        $this->assertSame('agenda_requerida', $r['error']);
        $this->assertNotEmpty($r['mensaje']);
        $this->assertArrayNotHasKey('columnas', $r);
    }

    /**
     * @dataProvider valoresNoBooleanos
     */
    public function testAltaConTieneAgendaNoBooleanoEsDatosInvalidos($valor)
    {
        $r = AgendaMedicoReglas::resolverAlta(['tiene_agenda' => $valor, 'mostrar_en_turnos' => false]);
        $this->assertSame('datos_invalidos', $r['error']);
        $this->assertNotEmpty($r['mensaje']);
        $this->assertArrayNotHasKey('columnas', $r);
    }

    /**
     * @dataProvider booleanosValidos
     */
    public function testAltaAceptaLasFormasDeUnBooleanoEnTieneAgenda($valor, $esperado)
    {
        $r = AgendaMedicoReglas::resolverAlta(['tiene_agenda' => $valor]);
        $this->assertArrayNotHasKey('error', $r);
        $this->assertSame($esperado, $r['tiene_agenda']);
    }

    public function testAltaDatosInvalidosGanaSobreAgendaRequerida()
    {
        $r = AgendaMedicoReglas::resolverAlta(['tiene_agenda' => 'si', 'mostrar_en_turnos' => true]);
        $this->assertSame('datos_invalidos', $r['error']);
    }

    public function testAltaMostrarEnTurnosSigueLaVerdadDeSiempre()
    {
        // Hoy el alta usa `$request->input('mostrar_en_turnos') ? 1 : 0`; no cambia cómo se lee.
        $this->assertTrue(AgendaMedicoReglas::resolverAlta(['mostrar_en_turnos' => 1])['visible_en_turnos']);
        $this->assertTrue(AgendaMedicoReglas::resolverAlta(['mostrar_en_turnos' => 'true'])['visible_en_turnos']);
        $this->assertFalse(AgendaMedicoReglas::resolverAlta(['mostrar_en_turnos' => 0])['visible_en_turnos']);
        $this->assertFalse(AgendaMedicoReglas::resolverAlta(['mostrar_en_turnos' => null])['visible_en_turnos']);
        $this->assertFalse(AgendaMedicoReglas::resolverAlta(['mostrar_en_turnos' => ''])['visible_en_turnos']);
    }

    public function testAltaSiempreDevuelveLasDosColumnas()
    {
        $casos = [
            [], ['mostrar_en_turnos' => true], ['tiene_agenda' => true], ['tiene_agenda' => false],
            ['tiene_agenda' => true, 'mostrar_en_turnos' => true],
        ];
        foreach ($casos as $cuerpo) {
            $r = AgendaMedicoReglas::resolverAlta($cuerpo);
            $this->assertSame(['tiene_agenda', 'castigo_automatico'], array_keys($r['columnas']));
        }
    }
}
