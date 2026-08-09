<?php

namespace App\Services;

use App\Http\Controllers\MedicoController;
use DateTime;
use Illuminate\Support\Facades\DB;

class ReporteHorariosMedicosActuales
{
    public const DIAS_NOMBRE = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miercoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sabado',
        7 => 'Domingo',
    ];

    /** Misma lista que el if grande en MedicoController::createJson */
    public const MEDICOS_LOGICA_ESPECIAL = [1, 2, 5, 8, 11, 12, 13, 14, 15, 18, 19, 23, 24, 26, 31, 38];

    public static function siguienteFechaParaDiaSemana($dow)
    {
        $date = new DateTime('today');
        while ((int) $date->format('N') !== (int) $dow) {
            $date->modify('+1 day');
        }

        return $date->format('Y-m-d');
    }

    /**
     * @return array{text_lines: string[], csv_rows: array<int, array<int, string|int>>} csv_rows[0] = cabecera
     */
    public static function generar(MedicoController $medicoController)
    {
        $medicos = DB::table('medicos')
            ->where('activo', 1)
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get(['id', 'apellido', 'nombre', 'consultorio']);

        $textLines = [];
        $textLines[] = 'LISTADO DE HORARIOS ACTUALES (segun createJson)';
        $textLines[] = 'Generado: ' . date('d/m/Y H:i:s');
        $textLines[] = str_repeat('=', 90);

        $csvRows = [];
        $csvRows[] = ['medico_id', 'medico', 'consultorio', 'dia_num', 'dia', 'fecha_evaluada', 'logica_especial', 'cantidad_horarios', 'horarios'];

        foreach ($medicos as $medico) {
            $nombreMedico = trim($medico->apellido . ', ' . $medico->nombre);
            $especial = in_array((int) $medico->id, self::MEDICOS_LOGICA_ESPECIAL, true) ? 'SI' : 'NO';

            $textLines[] = '';
            $textLines[] = "Medico {$medico->id} - {$nombreMedico} (consultorio {$medico->consultorio}) | logica especial: {$especial}";
            $textLines[] = str_repeat('-', 90);

            foreach (self::DIAS_NOMBRE as $diaNum => $diaNom) {
                $fecha = self::siguienteFechaParaDiaSemana($diaNum);

                $json = $medicoController->createJson((int) $medico->id, (int) $medico->consultorio, (int) $diaNum, $fecha, 0);
                $arr = json_decode($json, true);
                if (!is_array($arr)) {
                    $arr = [];
                }

                $horarios = [];
                foreach ($arr as $item) {
                    if (isset($item['horario']) && $item['horario'] !== '') {
                        $horarios[] = $item['horario'];
                    }
                }
                $horarios = array_values(array_unique($horarios));
                sort($horarios);
                $horariosStr = implode(' | ', $horarios);

                $csvRows[] = [
                    $medico->id,
                    $nombreMedico,
                    $medico->consultorio,
                    $diaNum,
                    $diaNom,
                    $fecha,
                    $especial,
                    count($horarios),
                    $horariosStr,
                ];

                $textLines[] = sprintf(
                    '%s (%s) [%s] -> %d horarios%s',
                    $diaNom,
                    $fecha,
                    $especial === 'SI' ? 'especial' : 'normal',
                    count($horarios),
                    $horariosStr !== '' ? ': ' . $horariosStr : ''
                );
            }
        }

        return [
            'text_lines' => $textLines,
            'csv_rows' => $csvRows,
        ];
    }
}
