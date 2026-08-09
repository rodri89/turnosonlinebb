<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class TurnoTestMedicoHelper
{
    public static function resolverMedicoFlujoPaciente(int $medicoId): ?object
    {
        if ((int) session('turno_test_medico_id') === 1 && $medicoId === 1) {
            $rows = DB::select('select * from medicos where id = ?', [$medicoId]);

            return $rows[0] ?? null;
        }

        $rows = DB::select('select * from medicos where id = ? and activo=1', [$medicoId]);

        return $rows[0] ?? null;
    }
}
