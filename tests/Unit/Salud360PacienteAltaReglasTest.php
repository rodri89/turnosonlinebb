<?php

namespace Tests\Unit;

use App\Services\Salud360\PacienteAltaReglas;
use PHPUnit\Framework\TestCase;

/**
 * Regla de POST /api/salud360/pacientes: ¿se pueden escribir los datos enviados en la ficha? (sin base de datos).
 *
 * Que el paciente «esté vinculado al médico» (fila en `medico_pacientes` o algún turno con él) lo resuelve el
 * controlador con la base; los casos de punta a punta se prueban con curl contra la copia local
 * (ver md/API_SALUD360.md).
 */
class Salud360PacienteAltaReglasTest extends TestCase
{
    public function testDniNuevoSeCreaConLosDatosEnviados()
    {
        $this->assertTrue(PacienteAltaReglas::debeActualizarDatos(false, false));
    }

    public function testDniExistenteYVinculadoAlMedicoSeActualiza()
    {
        $this->assertTrue(PacienteAltaReglas::debeActualizarDatos(true, true));
    }

    public function testDniExistenteSinVinculoConElMedicoNoSePisa()
    {
        $this->assertFalse(PacienteAltaReglas::debeActualizarDatos(true, false));
    }

    public function testElVinculoNoCambiaLaReglaDeUnPacienteNuevo()
    {
        // Un paciente que no existe no puede estar vinculado; si llegara a venir marcado igual, se crea.
        $this->assertTrue(PacienteAltaReglas::debeActualizarDatos(false, true));
    }

    public function testAceptaLosValoresQueDevuelveLaBase()
    {
        // El controlador pasa el resultado de exists(): booleanos. Cualquier otra cosa se lee como verdadero/falso de PHP.
        $this->assertFalse(PacienteAltaReglas::debeActualizarDatos(1, 0));
        $this->assertTrue(PacienteAltaReglas::debeActualizarDatos(1, 1));
        $this->assertTrue(PacienteAltaReglas::debeActualizarDatos(0, 0));
    }

    public function testElResultadoSiempreEsBooleano()
    {
        foreach ([[true, true], [true, false], [false, true], [false, false]] as $caso) {
            $this->assertInternalType('bool', PacienteAltaReglas::debeActualizarDatos($caso[0], $caso[1]));
        }
    }
}
