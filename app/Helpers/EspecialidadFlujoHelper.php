<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class EspecialidadFlujoHelper
{
    /**
     * Nombre de especialidad tal como la eligió el paciente en el flujo (texto para turno_registrados.especialidad).
     */
    public static function nombreParaTurno(
        ?string $desdeFormulario,
        ?string $especialidadTxtLegacy,
        $especialidadIdRequest,
        $medicoEspecialidadFk
    ): ?string {
        $t = function ($v) {
            if ($v === null) {
                return '';
            }

            return trim((string) $v);
        };
        if ($t($desdeFormulario) !== '') {
            return $t($desdeFormulario);
        }
        if ($t($especialidadTxtLegacy) !== '') {
            return $t($especialidadTxtLegacy);
        }
        if ($especialidadIdRequest !== null && $especialidadIdRequest !== '') {
            $idInt = (int) $especialidadIdRequest;
            if ($idInt === 12) {
                return 'Infectología';
            }
            if ($idInt === 18) {
                return 'Deportologia';
            }
            if ($idInt === 15) {
                return 'Nefrología Infantil';
            }
            $row = DB::table('especialidads')->where('id', $idInt)->first();
            if ($row && ! empty($row->nombre)) {
                return $row->nombre;
            }
        }
        if ($medicoEspecialidadFk !== null && $medicoEspecialidadFk !== '') {
            $row = DB::table('especialidads')->where('id', (int) $medicoEspecialidadFk)->first();
            if ($row && ! empty($row->nombre)) {
                return rtrim($row->nombre);
            }
        }

        return null;
    }
}
