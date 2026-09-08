<?php

namespace Tests\Unit;

use App\Services\Salud360\AgendaService;
use PHPUnit\Framework\TestCase;

/**
 * Pruebas de los métodos puros (sin base de datos) del servicio de agenda de Salud 360.
 */
class Salud360AgendaServiceTest extends TestCase
{
    /** @var AgendaService */
    private $agenda;

    protected function setUp(): void
    {
        parent::setUp();
        $this->agenda = new AgendaService();
    }

    public function testNormalizarFechaAceptaLosFormatosDeLaWeb()
    {
        $this->assertSame('2026-09-08', $this->agenda->normalizarFecha('2026-09-08'));
        $this->assertSame('2026-09-08', $this->agenda->normalizarFecha('2026/09/08'));
        $this->assertSame('2026-09-08', $this->agenda->normalizarFecha('08/09/2026'));
        $this->assertSame('2026-09-08', $this->agenda->normalizarFecha('8/9/2026'));
        $this->assertNull($this->agenda->normalizarFecha('2026-13-40'));
        $this->assertNull($this->agenda->normalizarFecha('hoy'));
        $this->assertNull($this->agenda->normalizarFecha(''));
        $this->assertNull($this->agenda->normalizarFecha(null));
    }

    public function testDiaSemanaUsaLaConvencionDeLaBase()
    {
        $this->assertSame(1, $this->agenda->diaSemana('2026-09-07')); // lunes
        $this->assertSame(7, $this->agenda->diaSemana('2026-09-13')); // domingo
    }

    public function testFechaMostrar()
    {
        $this->assertSame('08/09/2026', $this->agenda->fechaMostrar('2026-09-08'));
        $this->assertSame('08/09/2026', $this->agenda->fechaMostrar('2026/09/08'));
    }

    public function testMarcarLibresReplicaCreateJson()
    {
        $turnos = [(object) ['horario' => '09:00'], (object) ['horario' => '09:20'], (object) ['horario' => '09:40']];
        $registrados = collect([(object) ['horario' => '09:20']]);

        $this->assertSame([
            ['horario' => '09:00', 'libre' => 1],
            ['horario' => '09:20', 'libre' => 0],
            ['horario' => '09:40', 'libre' => 1],
        ], $this->agenda->marcarLibres($turnos, $registrados));
    }

    public function testMarcarLibresDoblesReplicaCreateJsonTurnosDobles()
    {
        $turnos = [
            (object) ['horario' => '09:00', 'doble' => 0],
            (object) ['horario' => '09:20', 'doble' => 0],
            (object) ['horario' => '09:40', 'doble' => 0],
            (object) ['horario' => '10:00', 'doble' => 0],
        ];
        $registrados = collect([(object) ['horario' => '09:40']]);

        $this->assertSame([
            ['horario' => '09:00', 'horario2' => '09:20', 'libre' => 1],
            ['horario' => '09:20', 'horario2' => '09:20', 'libre' => 0], // el siguiente está ocupado
            ['horario' => '09:40', 'horario2' => '09:40', 'libre' => 0], // ocupado
            ['horario' => '10:00', 'horario2' => '10:00', 'libre' => 0], // no tiene siguiente
        ], $this->agenda->marcarLibresDobles($turnos, $registrados));
    }

    public function testMarcarLibresDoblesIgnoraHorariosMarcadosComoDobles()
    {
        $turnos = [
            (object) ['horario' => '09:00', 'doble' => 1],
            (object) ['horario' => '09:20', 'doble' => 0],
        ];
        $resultado = $this->agenda->marcarLibresDobles($turnos, collect());
        $this->assertSame(0, $resultado[0]['libre']);
    }
}
