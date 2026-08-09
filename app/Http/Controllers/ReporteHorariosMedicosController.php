<?php

namespace App\Http\Controllers;

use App\Services\ReporteHorariosMedicosActuales;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteHorariosMedicosController extends Controller
{
    /**
     * HTML: reporte de horarios según createJson (solo admin).
     */
    public function mostrarHtml(Request $request)
    {
        $medicoController = app(MedicoController::class);
        $data = ReporteHorariosMedicosActuales::generar($medicoController);

        return response()->view('turnos_admin.reporte_horarios_medicos', [
            'titulo' => 'Reporte horarios médicos (createJson)',
            'contenido' => implode("\n", $data['text_lines']),
            'generado' => date('d/m/Y H:i:s'),
        ]);
    }

    /**
     * Descarga CSV del mismo reporte.
     */
    public function descargarCsv()
    {
        $medicoController = app(MedicoController::class);
        $data = ReporteHorariosMedicosActuales::generar($medicoController);

        $filename = 'horarios_medicos_actuales_' . date('Ymd_His') . '.csv';

        return new StreamedResponse(function () use ($data) {
            $out = fopen('php://output', 'w');
            foreach ($data['csv_rows'] as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
