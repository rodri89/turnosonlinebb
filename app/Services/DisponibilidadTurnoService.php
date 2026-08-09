<?php

namespace App\Services;

use App\Http\Controllers\PacienteController;
use Illuminate\Support\Facades\DB;

class DisponibilidadTurnoService
{
    /**
     * Busca las próximas N fechas con al menos un turno libre en un solo recorrido,
     * precargando feriados del rango para evitar una query por día.
     *
     * @return array{fechas: string[], fechas_raw: string[]}
     */
    public function buscarProximasFechasLibres(
        int $medicoId,
        $primerControl,
        int $esVideollamada,
        string $diaInicio,
        int $cantidad = 3,
        int $maxDiasBusqueda = 180
    ): array {
        $pacienteController = app(PacienteController::class);
        $medico = \App\Medico::find($medicoId);
        if (!$medico) {
            return ['fechas' => [], 'fechas_raw' => []];
        }
        $consultorio = DB::table('consultorios')
            ->where('consultorios.id', $medico->consultorio)
            ->first();

        if ($esVideollamada == 0) {
            $moduloPrimerControlDoble = DB::table('modulo_medicos')
                ->where('modulo_medicos.medico', $medicoId)
                ->where('modulo_medicos.modulo', 3)
                ->where('modulo_medicos.activo', 1)
                ->exists();
            if (!$moduloPrimerControlDoble) {
                $primerControl = 0;
            }
        }

        $fechaFinBusqueda = date('Y-m-d', strtotime("+{$maxDiasBusqueda} days", strtotime($diaInicio)));
        $feriadosSet = DB::table('feriados')
            ->whereBetween('fecha', [$diaInicio, $fechaFinBusqueda])
            ->pluck('fecha')
            ->flip()
            ->all();

        $fechasEncontradas = [];
        $fechasRaw = [];
        $dia = $diaInicio;
        $diasRecorridos = 0;

        while (count($fechasEncontradas) < $cantidad && $diasRecorridos < $maxDiasBusqueda) {
            if (!isset($feriadosSet[$dia])) {
                $hayTurnoLibre = $pacienteController->checkTurnoLibre(
                    $medicoId,
                    $dia,
                    $primerControl,
                    $esVideollamada,
                    $medico,
                    $consultorio
                );
                if ($hayTurnoLibre == 1) {
                    $fechasRaw[] = $dia;
                    $fechasEncontradas[] = $this->convertirFechaMostrar($dia);
                    $dia = date('Y-m-d', strtotime('+1 day', strtotime($dia)));
                    $diasRecorridos++;
                    continue;
                }
            }
            $dia = date('Y-m-d', strtotime('+1 day', strtotime($dia)));
            $diasRecorridos++;
        }

        return [
            'fechas' => $fechasEncontradas,
            'fechas_raw' => $fechasRaw,
        ];
    }

    /**
     * Devuelve la primera fecha libre a partir de $diaInicio (equivalente a turnoLibreMasCercano).
     */
    public function buscarPrimeraFechaLibre(
        int $medicoId,
        $primerControl,
        int $esVideollamada,
        string $diaInicio,
        int $maxDiasBusqueda = 180
    ): string {
        $result = $this->buscarProximasFechasLibres(
            $medicoId,
            $primerControl,
            $esVideollamada,
            $diaInicio,
            1,
            $maxDiasBusqueda
        );

        if (!empty($result['fechas_raw'])) {
            return $result['fechas_raw'][0];
        }

        return date('Y-m-d', strtotime("+{$maxDiasBusqueda} days", strtotime($diaInicio)));
    }

    private function convertirFechaMostrar(string $fecha): string
    {
        $fecha_aux = explode('-', $fecha);

        return $fecha_aux[2] . '/' . $fecha_aux[1] . '/' . $fecha_aux[0];
    }
}
