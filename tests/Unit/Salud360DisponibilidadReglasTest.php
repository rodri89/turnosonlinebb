<?php

namespace Tests\Unit;

use App\Services\Salud360\DisponibilidadReglas;
use PHPUnit\Framework\TestCase;

/**
 * Reglas de disponibilidad (sin base de datos): qué turnos ocupan un horario en la lista de «libres»
 * y qué horarios bloquea `POST /api/salud360/turnos/bloquear-dia`.
 *
 * Los casos con base (lista, validación, 401/403) se prueban con curl contra la copia local;
 * ver md/API_SALUD360.md.
 */
class Salud360DisponibilidadReglasTest extends TestCase
{
    private function turno($horario, $tipo, $dni)
    {
        return (object) ['horario' => $horario, 'tipo_turno' => $tipo, 'paciente_dni' => $dni];
    }

    private function horarios(array $ocupados)
    {
        return array_map(function ($t) { return $t->horario; }, $ocupados);
    }

    // ------------------------------------------------------------------
    // esDniBloqueo
    // ------------------------------------------------------------------

    public function testElDni99999EsElPacienteDeBloqueo()
    {
        $this->assertTrue(DisponibilidadReglas::esDniBloqueo(99999));
        $this->assertTrue(DisponibilidadReglas::esDniBloqueo('99999'));
    }

    public function testOtrosDniNoSonBloqueo()
    {
        $this->assertFalse(DisponibilidadReglas::esDniBloqueo(12345678));
        $this->assertFalse(DisponibilidadReglas::esDniBloqueo('99999a'));
        $this->assertFalse(DisponibilidadReglas::esDniBloqueo(''));
        $this->assertFalse(DisponibilidadReglas::esDniBloqueo(null));
    }

    // ------------------------------------------------------------------
    // ocupaHorario: el bloqueo ocupa sin importar el tipo
    // ------------------------------------------------------------------

    public function testUnBloqueoOcupaElHorarioAunqueSeaDeOtroTipo()
    {
        // El bug: bloqueo guardado con tipo 1 y paciente eligiendo el tipo 24.
        $this->assertTrue(DisponibilidadReglas::ocupaHorario(1, true, 24, false));
        $this->assertTrue(DisponibilidadReglas::ocupaHorario('1', true, '24', false));
    }

    public function testUnBloqueoOcupaTambienConOpcionesOConTurnosDobles()
    {
        $this->assertTrue(DisponibilidadReglas::ocupaHorario(1, true, 24, true));
        $this->assertTrue(DisponibilidadReglas::ocupaHorario(1, true, 24, false, true));
    }

    public function testUnTurnoRealSigueFiltrandoPorTipo()
    {
        $this->assertTrue(DisponibilidadReglas::ocupaHorario(24, false, 24, false));
        $this->assertTrue(DisponibilidadReglas::ocupaHorario('24', false, 24, false));
        $this->assertFalse(DisponibilidadReglas::ocupaHorario(1, false, 24, false));
        $this->assertFalse(DisponibilidadReglas::ocupaHorario(null, false, 24, false));
    }

    public function testUnTurnoRealConOpcionesOcupaSinMirarElTipo()
    {
        // Con opciones, el tipo del turno pudo cambiar a la opción elegida tras el insert.
        $this->assertTrue(DisponibilidadReglas::ocupaHorario(31, false, 24, true));
    }

    public function testUnTurnoRealConOpcionesPeroSiempreFiltrarSigueFiltrando()
    {
        // Turnos dobles: siempre se filtra por tipo.
        $this->assertFalse(DisponibilidadReglas::ocupaHorario(31, false, 24, true, true));
        $this->assertTrue(DisponibilidadReglas::ocupaHorario(24, false, 24, true, true));
    }

    // ------------------------------------------------------------------
    // turnosQueOcupan
    // ------------------------------------------------------------------

    public function testTurnosQueOcupanMezclaBloqueosYTurnosReales()
    {
        $turnos = [
            $this->turno('09:00', 1, 99999),     // bloqueo tipo 1
            $this->turno('09:20', 1, 30111222),  // real de otro tipo: no ocupa para el tipo 24
            $this->turno('09:40', 24, 30111222), // real del mismo tipo
            $this->turno('10:00', 24, 99999),    // bloqueo del mismo tipo
        ];
        $ocupados = DisponibilidadReglas::turnosQueOcupan($turnos, 24, false, false);
        $this->assertSame(['09:00', '09:40', '10:00'], $this->horarios($ocupados));
    }

    public function testTurnosQueOcupanConOpcionesCuentaTodosLosReales()
    {
        $turnos = [
            $this->turno('09:20', 1, 30111222),
            $this->turno('09:40', 31, 30111222),
        ];
        $ocupados = DisponibilidadReglas::turnosQueOcupan($turnos, 24, true, false);
        $this->assertSame(['09:20', '09:40'], $this->horarios($ocupados));
    }

    public function testTurnosQueOcupanSinDniDelPacienteNoEsBloqueo()
    {
        // Si por algún motivo no se pudo unir el paciente, se comporta como un turno real.
        $turnos = [(object) ['horario' => '09:00', 'tipo_turno' => 1]];
        $this->assertSame([], DisponibilidadReglas::turnosQueOcupan($turnos, 24, false, false));
    }

    public function testTurnosQueOcupanAceptaColecciones()
    {
        $turnos = collect([$this->turno('09:00', 1, 99999), $this->turno('09:20', 1, 30111222)]);
        $ocupados = DisponibilidadReglas::turnosQueOcupan($turnos, 24, false, false);
        $this->assertSame(['09:00'], $this->horarios($ocupados));
    }

    // ------------------------------------------------------------------
    // horariosParaBloquear (bloquear-dia): todos los tipos, sin duplicar horarios
    // ------------------------------------------------------------------

    public function testBloquearDiaIncluyeLosHorariosDeTodosLosTipos()
    {
        $horarios = [
            (object) ['horario' => '09:00', 'tipo_turno' => 1],
            (object) ['horario' => '09:20', 'tipo_turno' => 1],
            (object) ['horario' => '10:00', 'tipo_turno' => 24],
        ];
        $r = DisponibilidadReglas::horariosParaBloquear($horarios, 1);
        $this->assertSame([
            ['horario' => '09:00', 'tipo_turno' => 1],
            ['horario' => '09:20', 'tipo_turno' => 1],
            ['horario' => '10:00', 'tipo_turno' => 24],
        ], $r);
    }

    public function testBloquearDiaNoDuplicaUnHorarioQueEstaEnDosTipos()
    {
        $horarios = [
            (object) ['horario' => '09:00', 'tipo_turno' => 24],
            (object) ['horario' => '09:00', 'tipo_turno' => 1],
            (object) ['horario' => '09:20', 'tipo_turno' => 24],
        ];
        $r = DisponibilidadReglas::horariosParaBloquear($horarios, 1);
        $this->assertSame([
            ['horario' => '09:00', 'tipo_turno' => 1],
            ['horario' => '09:20', 'tipo_turno' => 24],
        ], $r);
    }

    public function testBloquearDiaUsaElTipoPorDefectoSiElHorarioNoLoTiene()
    {
        // Horarios de fechas agregadas o de videollamada: no tienen columna tipo_turno.
        $horarios = [(object) ['horario' => '09:00'], (object) ['horario' => '09:20', 'tipo_turno' => 0]];
        $r = DisponibilidadReglas::horariosParaBloquear($horarios, 1);
        $this->assertSame([
            ['horario' => '09:00', 'tipo_turno' => 1],
            ['horario' => '09:20', 'tipo_turno' => 1],
        ], $r);
    }

    public function testBloquearDiaOrdenaPorHorarioYSinHorariosDevuelveVacio()
    {
        $horarios = [
            (object) ['horario' => '10:00', 'tipo_turno' => 24],
            (object) ['horario' => '09:00', 'tipo_turno' => 1],
        ];
        $r = DisponibilidadReglas::horariosParaBloquear($horarios, 1);
        $this->assertSame(['09:00', '10:00'], array_column($r, 'horario'));
        $this->assertSame([], DisponibilidadReglas::horariosParaBloquear([], 1));
        $this->assertSame([], DisponibilidadReglas::horariosParaBloquear(collect(), 1));
    }

    // ------------------------------------------------------------------
    // turnoDestinoLibre / destinosLibres (reserva interna de la secretaria):
    // cualquier turno activo en el mismo horario ocupa, sin importar el tipo.
    // ------------------------------------------------------------------

    private function enSlot($id, $tipo, $activo, $dni = 30111222)
    {
        return (object) ['id' => $id, 'tipo_turno' => $tipo, 'activo' => $activo, 'paciente_dni' => $dni];
    }

    public function testDestinoSinTurnosEstaLibre()
    {
        $this->assertTrue(DisponibilidadReglas::turnoDestinoLibre([]));
        $this->assertTrue(DisponibilidadReglas::turnoDestinoLibre(collect()));
    }

    public function testUnBloqueoDeOtroTipoOcupaElDestino()
    {
        // El bug: bloqueo tipo 1 (DNI 99999) y la secretaria reserva con el tipo del horario (24).
        $slot = [$this->enSlot(10, 1, 1, 99999)];
        $this->assertFalse(DisponibilidadReglas::turnoDestinoLibre($slot));
    }

    public function testUnTurnoRealDeOtroTipoOcupaElDestino()
    {
        $slot = [$this->enSlot(11, 1, 1)];
        $this->assertFalse(DisponibilidadReglas::turnoDestinoLibre($slot));
    }

    public function testUnTurnoDelMismoTipoOcupaElDestino()
    {
        $slot = [$this->enSlot(12, 24, 1)];
        $this->assertFalse(DisponibilidadReglas::turnoDestinoLibre($slot));
    }

    public function testUnTurnoCanceladoNoOcupaElDestino()
    {
        $slot = [$this->enSlot(13, 1, 0), $this->enSlot(14, 24, '0')];
        $this->assertTrue(DisponibilidadReglas::turnoDestinoLibre($slot));
    }

    public function testUnTurnoCanceladoJuntoAUnoActivoSigueOcupado()
    {
        $slot = [$this->enSlot(15, 1, 0), $this->enSlot(16, 24, '1')];
        $this->assertFalse(DisponibilidadReglas::turnoDestinoLibre($slot));
    }

    public function testElTurnoQueSeMueveNoOcupaSuPropioDestino()
    {
        // Mover un turno dentro de su mismo horario (p. ej. cambiar el tipo) no choca consigo mismo.
        $slot = [$this->enSlot(17, 1, 1)];
        $this->assertTrue(DisponibilidadReglas::turnoDestinoLibre($slot, 17));
        $this->assertTrue(DisponibilidadReglas::turnoDestinoLibre($slot, '17'));
        $this->assertFalse(DisponibilidadReglas::turnoDestinoLibre($slot, 99));
        $this->assertFalse(DisponibilidadReglas::turnoDestinoLibre($slot, 0));
        $this->assertFalse(DisponibilidadReglas::turnoDestinoLibre($slot, null));
    }

    public function testDestinoDobleConUnSoloHorarioOcupadoNoEstaLibre()
    {
        $libre = [];
        $ocupado = [$this->enSlot(18, 1, 1, 99999)];
        $this->assertTrue(DisponibilidadReglas::destinosLibres([$libre, $libre]));
        $this->assertFalse(DisponibilidadReglas::destinosLibres([$ocupado, $libre]));
        $this->assertFalse(DisponibilidadReglas::destinosLibres([$libre, $ocupado]));
        $this->assertFalse(DisponibilidadReglas::destinosLibres([$ocupado, $ocupado]));
    }

    public function testDestinoDobleConLosDosHorariosCanceladosEstaLibre()
    {
        $cancelado = [$this->enSlot(19, 24, 0)];
        $this->assertTrue(DisponibilidadReglas::destinosLibres([$cancelado, $cancelado]));
    }

    public function testDestinosLibresSinHorariosEsFalsoPorSeguridad()
    {
        // Sin horarios que comprobar no hay nada que reservar.
        $this->assertFalse(DisponibilidadReglas::destinosLibres([]));
    }
}
