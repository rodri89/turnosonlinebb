<?php

/**
 * Genera listado de horarios según MedicoController::createJson (misma lógica que la app).
 *
 * Uso (desde la raíz del proyecto):
 *   php tools/report_horarios_medicos_actuales.php
 *
 * Salida: carpeta reports/ en la raíz del proyecto.
 * En el navegador (usuario admin): /admin/reporte/horarios-medicos
 */

use Illuminate\Contracts\Console\Kernel;
use App\Http\Controllers\MedicoController;
use App\Services\ReporteHorariosMedicosActuales;

$basePath = dirname(__DIR__);

date_default_timezone_set('America/Argentina/Buenos_Aires');

require $basePath . '/vendor/autoload.php';
$app = require_once $basePath . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$controller = $app->make(MedicoController::class);
$data = ReporteHorariosMedicosActuales::generar($controller);

$timestamp = date('Ymd_His');
$dir = $basePath . '/reports';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$csvPath = "{$dir}/horarios_medicos_actuales_{$timestamp}.csv";
$txtPath = "{$dir}/horarios_medicos_actuales_{$timestamp}.txt";

$csv = fopen($csvPath, 'w');
foreach ($data['csv_rows'] as $row) {
    fputcsv($csv, $row);
}
fclose($csv);

file_put_contents($txtPath, implode(PHP_EOL, $data['text_lines']) . PHP_EOL);

echo "Reporte generado en la raiz del proyecto:\n";
echo "- {$csvPath}\n";
echo "- {$txtPath}\n";
