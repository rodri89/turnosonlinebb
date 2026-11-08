<?php

namespace Tests\Unit;

use App\Services\Salud360\TipoEnReservaReglas;
use PHPUnit\Framework\TestCase;

/**
 * Qué tipo de turno se acepta al cargar un horario fijo (POST horarios) y al reservar un turno
 * (POST turnos, POST turnos/sobreturno), sin base de datos.
 *
 * Los casos de la base (403, el slot que sale de horario_medicos, la fecha especial, lo que queda guardado)
 * se prueban con curl contra la copia local; ver md/API_SALUD360.md.
 */
class Salud360TipoEnReservaReglasTest extends TestCase
{
    // ------------------------------------------------------------------
    // leerTipo: lo que vino en `tipo_turno`
    // ------------------------------------------------------------------

    public function testLeerTipoAusenteONuloOVacioEsNulo()
    {
        $this->assertNull(TipoEnReservaReglas::leerTipo(null));
        $this->assertNull(TipoEnReservaReglas::leerTipo(''));
    }

    public function testLeerTipoEnteroOTextoNumericoEsEntero()
    {
        $this->assertSame(24, TipoEnReservaReglas::leerTipo(24));
        $this->assertSame(24, TipoEnReservaReglas::leerTipo('24'));
        $this->assertSame(1, TipoEnReservaReglas::leerTipo(1));
    }

    public function testLeerTipoBasuraEsCeroYNuncaCoincideConUnTipo()
    {
        $this->assertSame(0, TipoEnReservaReglas::leerTipo('abc'));
        $this->assertSame(0, TipoEnReservaReglas::leerTipo([24]));
        $this->assertSame(0, TipoEnReservaReglas::leerTipo(0));
    }

    // ------------------------------------------------------------------
    // paraHorarioNuevo: el tipo de un horario fijo nuevo
    // ------------------------------------------------------------------

    public function testHorarioNuevoSinTipoEsConsulta()
    {
        $r = TipoEnReservaReglas::paraHorarioNuevo(null, [24, 26]);
        $this->assertSame(['tipo' => 1], $r);
    }

    public function testHorarioNuevoSinTipoYSinHabilitadosEsConsulta()
    {
        $this->assertSame(['tipo' => 1], TipoEnReservaReglas::paraHorarioNuevo(null, []));
    }

    public function testHorarioNuevoConTipoHabilitadoSeAcepta()
    {
        $this->assertSame(['tipo' => 24], TipoEnReservaReglas::paraHorarioNuevo(24, [1, 24, 26]));
    }

    public function testHorarioNuevoConTipoNoHabilitadoEsTipoNoHabilitado()
    {
        $r = TipoEnReservaReglas::paraHorarioNuevo(27, [1, 24, 26]);
        $this->assertSame('tipo_no_habilitado', $r['error']);
        $this->assertArrayNotHasKey('tipo', $r);
        $this->assertSame('El tipo 27 no está habilitado para este médico.', $r['mensaje']);
    }

    public function testHorarioNuevoMensajeLlevaElNombreSiSeConoce()
    {
        $r = TipoEnReservaReglas::paraHorarioNuevo(27, [1], [27 => 'Consulta y evaluación']);
        $this->assertSame('tipo_no_habilitado', $r['error']);
        $this->assertSame('El tipo Consulta y evaluación (27) no está habilitado para este médico.', $r['mensaje']);
    }

    public function testMedicoSinHabilitadosSoloAceptaConsulta()
    {
        $this->assertSame(['tipo' => 1], TipoEnReservaReglas::paraHorarioNuevo(1, []));
        $r = TipoEnReservaReglas::paraHorarioNuevo(24, []);
        $this->assertSame('tipo_no_habilitado', $r['error']);
    }

    public function testMedicoConUnSoloTipoHabilitadoDistintoDeUnoAceptaElUno()
    {
        // La app publicada manda 1 por omisión y la web lo grababa en silencio: con un solo tipo, se acepta.
        $this->assertSame(['tipo' => 1], TipoEnReservaReglas::paraHorarioNuevo(1, [24]));
        $this->assertSame(['tipo' => 1], TipoEnReservaReglas::paraHorarioNuevo(1, ['24']));
    }

    public function testMedicoConDosOMasTiposHabilitadosSinElUnoNoAceptaElUno()
    {
        $r = TipoEnReservaReglas::paraHorarioNuevo(1, [24, 26]);
        $this->assertSame('tipo_no_habilitado', $r['error']);
        $this->assertArrayNotHasKey('tipo', $r);
        $this->assertSame('tipo_no_habilitado', TipoEnReservaReglas::paraHorarioNuevo(1, [24, 26, 28])['error']);
    }

    public function testMedicoConUnSoloTipoHabilitadoSigueValidandoLosDemasTipos()
    {
        $this->assertSame(['tipo' => 24], TipoEnReservaReglas::paraHorarioNuevo(24, [24]));
        $this->assertSame('tipo_no_habilitado', TipoEnReservaReglas::paraHorarioNuevo(27, [24])['error']);
        $this->assertSame('tipo_no_habilitado', TipoEnReservaReglas::paraHorarioNuevo(0, [24])['error']);
    }

    public function testMedicoConElUnoComoUnicoTipoHabilitadoAceptaElUno()
    {
        $this->assertSame(['tipo' => 1], TipoEnReservaReglas::paraHorarioNuevo(1, [1]));
    }

    public function testMedicoConDosTiposIncluyendoElUnoAceptaElUno()
    {
        $this->assertSame(['tipo' => 1], TipoEnReservaReglas::paraHorarioNuevo(1, [1, 24]));
    }

    public function testHorarioNuevoConTipoCeroOBasuraEsTipoNoHabilitado()
    {
        $this->assertSame('tipo_no_habilitado', TipoEnReservaReglas::paraHorarioNuevo(0, [])['error']);
        $this->assertSame('tipo_no_habilitado', TipoEnReservaReglas::paraHorarioNuevo(0, [1, 24])['error']);
    }

    public function testHorarioNuevoCompararEsPorEnteroEstricto()
    {
        // Los habilitados pueden llegar como texto desde la base.
        $this->assertSame(['tipo' => 24], TipoEnReservaReglas::paraHorarioNuevo(24, ['24', '26']));
    }

    // ------------------------------------------------------------------
    // paraTurno: el tipo de un turno sobre un horario de plantilla
    // ------------------------------------------------------------------

    public function testTurnoSinTipoNoSeValida()
    {
        $this->assertSame(['padre' => null], TipoEnReservaReglas::paraTurno(null, [24], [24 => [28]]));
    }

    public function testTurnoSobreSlotSinPlantillaNoSeValidaConCualquierTipo()
    {
        // Fecha agregada o sobreturno a mano: no hay horario de plantilla.
        $this->assertSame(['padre' => null], TipoEnReservaReglas::paraTurno(999, [], []));
        $this->assertSame(['padre' => null], TipoEnReservaReglas::paraTurno(0, [], []));
    }

    public function testTurnoConElMismoTipoDelHorarioSeAcepta()
    {
        $this->assertSame(['padre' => null], TipoEnReservaReglas::paraTurno(24, [24], [24 => [28, 34]]));
    }

    public function testTurnoConOpcionValidaDelPadreSeAceptaYInformaElPadre()
    {
        $this->assertSame(['padre' => 24], TipoEnReservaReglas::paraTurno(28, [24], [24 => [28, 34]]));
    }

    public function testTurnoConOpcionDeOtroPadreEsTipoInvalido()
    {
        $r = TipoEnReservaReglas::paraTurno(35, [24], [24 => [28, 34], 29 => [35]]);
        $this->assertSame('tipo_invalido', $r['error']);
        $this->assertArrayNotHasKey('padre', $r);
        $this->assertSame('El tipo 35 no corresponde a ese horario: es de tipo 24 y 35 no es una de sus opciones.', $r['mensaje']);
    }

    public function testTurnoConTipoCualquieraEsTipoInvalido()
    {
        $r = TipoEnReservaReglas::paraTurno(27, [1], []);
        $this->assertSame('tipo_invalido', $r['error']);
        $this->assertSame('El tipo 27 no corresponde a ese horario: es de tipo 1 y 27 no es una de sus opciones.', $r['mensaje']);
    }

    public function testTurnoMensajeLlevaLosNombresSiSeConocen()
    {
        $r = TipoEnReservaReglas::paraTurno(27, [24], [], [24 => 'Deportología', 27 => 'Consulta y evaluación']);
        $this->assertSame('El tipo Consulta y evaluación (27) no corresponde a ese horario: es de tipo Deportología (24) y Consulta y evaluación (27) no es una de sus opciones.', $r['mensaje']);
    }

    public function testTurnoConTipoCeroEsTipoInvalidoSiHayPlantilla()
    {
        $this->assertSame('tipo_invalido', TipoEnReservaReglas::paraTurno(0, [1], [])['error']);
    }

    public function testTurnoPadreSinOpcionesSoloAceptaSuPropioTipo()
    {
        $this->assertSame('tipo_invalido', TipoEnReservaReglas::paraTurno(28, [24], [24 => []])['error']);
        $this->assertSame(['padre' => null], TipoEnReservaReglas::paraTurno(24, [24], [24 => []]));
    }

    public function testTurnoSlotConDosTiposEnLaPlantillaAceptaCualquieraDeLosDos()
    {
        // La web lo impide al crear, pero puede haber filas cargadas a mano con la misma hora en dos tipos.
        $opciones = [1 => [], 24 => [28]];
        $this->assertSame(['padre' => null], TipoEnReservaReglas::paraTurno(24, [1, 24], $opciones));
        $this->assertSame(['padre' => null], TipoEnReservaReglas::paraTurno(1, [1, 24], $opciones));
        $this->assertSame(['padre' => 24], TipoEnReservaReglas::paraTurno(28, [1, 24], $opciones));
        $this->assertSame('tipo_invalido', TipoEnReservaReglas::paraTurno(30, [1, 24], $opciones)['error']);
    }

    public function testTurnoVideollamadaTieneSuPropioCircuitoYNoSeValida()
    {
        // Sus horarios están en horario_medico_videollamadas, no en la plantilla: se acepta como hasta ahora.
        $this->assertSame(['padre' => null], TipoEnReservaReglas::paraTurno(4, [1], []));
    }

    public function testTurnoOpcionesComoTextoSeComparanPorEntero()
    {
        $this->assertSame(['padre' => 24], TipoEnReservaReglas::paraTurno(28, ['24'], [24 => ['28']]));
    }

    // ------------------------------------------------------------------
    // paraTurno con la lista de habilitados: el 1 de la app publicada con un solo tipo habilitado
    // ------------------------------------------------------------------

    public function testTurnoConUnoYUnSoloTipoHabilitadoSeAceptaYSeGuardaConElTipoDelHorario()
    {
        $r = TipoEnReservaReglas::paraTurno(1, [24], [24 => [28]], [], [24]);
        $this->assertSame(['padre' => 24, 'tipo' => 24], $r);
    }

    public function testTurnoConUnoYSinTiposHabilitadosSeAceptaConElTipoDelHorario()
    {
        $this->assertSame(['padre' => 24, 'tipo' => 24], TipoEnReservaReglas::paraTurno(1, [24], [], [], []));
    }

    public function testTurnoConUnoYDosOMasTiposHabilitadosEsTipoInvalido()
    {
        $r = TipoEnReservaReglas::paraTurno(1, [24], [24 => []], [], [24, 26]);
        $this->assertSame('tipo_invalido', $r['error']);
        $this->assertArrayNotHasKey('tipo', $r);
        $this->assertSame('El tipo 1 no corresponde a ese horario: es de tipo 24 y 1 no es una de sus opciones.', $r['mensaje']);
    }

    public function testTurnoConTipoNoHabilitadoYUnSoloTipoHabilitadoEsTipoInvalido()
    {
        $r = TipoEnReservaReglas::paraTurno(27, [24], [24 => [28]], [], [24]);
        $this->assertSame('tipo_invalido', $r['error']);
    }

    public function testTurnoConOpcionValidaYUnSoloTipoHabilitadoSeAceptaComoSiempre()
    {
        // La opción es la que queda guardada: no se agrega 'tipo'.
        $this->assertSame(['padre' => 24], TipoEnReservaReglas::paraTurno(28, [24], [24 => [28]], [], [24]));
    }

    public function testTurnoConElMismoTipoDelHorarioYUnSoloTipoHabilitadoSeAceptaComoSiempre()
    {
        $this->assertSame(['padre' => null], TipoEnReservaReglas::paraTurno(24, [24], [24 => []], [], [24]));
    }

    public function testTurnoConUnoSobreHorarioDeTipoUnoNoCambiaNada()
    {
        $this->assertSame(['padre' => null], TipoEnReservaReglas::paraTurno(1, [1], [], [], [1]));
        $this->assertSame(['padre' => null], TipoEnReservaReglas::paraTurno(1, [1], [], [], [24]));
    }

    public function testTurnoConUnoYHorarioConUnoEntreSusTiposNoSeNormaliza()
    {
        // Si el 1 es uno de los tipos del slot, ya coincide: se guarda 1 como siempre.
        $this->assertSame(['padre' => null], TipoEnReservaReglas::paraTurno(1, [1, 24], [], [], [24]));
    }

    public function testTurnoSinTipoNoCambiaConLaListaDeHabilitados()
    {
        $this->assertSame(['padre' => null], TipoEnReservaReglas::paraTurno(null, [24], [], [], [24]));
        $this->assertSame(['padre' => null], TipoEnReservaReglas::paraTurno(null, [24], [], [], [24, 26]));
    }

    public function testTurnoSobreSlotSinPlantillaNoCambiaConLaListaDeHabilitados()
    {
        $this->assertSame(['padre' => null], TipoEnReservaReglas::paraTurno(1, [], [], [], [24]));
    }

    public function testTurnoConUnoYSlotConDosTiposDistintosDeUnoEsAmbiguoYSeRechaza()
    {
        // No hay un único tipo T al que llevar el 1.
        $r = TipoEnReservaReglas::paraTurno(1, [24, 26], [], [], [24]);
        $this->assertSame('tipo_invalido', $r['error']);
    }

    public function testTurnoConUnoYVideollamadaNoCambia()
    {
        $this->assertSame(['padre' => null], TipoEnReservaReglas::paraTurno(4, [24], [], [], [24]));
    }

    public function testTurnoSinListaDeHabilitadosConservaLaReglaDeSiempre()
    {
        // Sin la lista (llamada vieja) no se sabe cuántos tipos tiene el médico: el 1 sobre un horario 24 no pasa.
        $this->assertSame('tipo_invalido', TipoEnReservaReglas::paraTurno(1, [24], [])['error']);
    }

    public function testTurnoHabilitadosComoTextoSeContanPorEntero()
    {
        $this->assertSame(['padre' => 24, 'tipo' => 24], TipoEnReservaReglas::paraTurno(1, ['24'], [], [], ['24']));
        $this->assertSame('tipo_invalido', TipoEnReservaReglas::paraTurno(1, ['24'], [], [], ['24', '26'])['error']);
    }
}
