<?php

namespace App\Http\Controllers\Concerns;

use App\Http\Controllers\SecretariaController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait ResolvesMedicoFromAuth
{
    protected function getMedico()
    {
        $usuarioActual = Auth::user();
        if ($usuarioActual->usuario_tipo == 3) {
            $medicoId = (int) session('secretaria_context_medico_id', 0);
            if ($medicoId && SecretariaController::puedeGestionarMedicoPorId($usuarioActual, $medicoId)) {
                return DB::table('medicos')->where('id', $medicoId)->first();
            }
            return null;
        }

        return DB::table('medicos')
            ->where('medicos.user_id', $usuarioActual->id)
            ->first();
    }

    protected function moduloExiste($medicoId, $moduloId)
    {
        return DB::table('modulo_medicos')
            ->where('medico', $medicoId)
            ->where('modulo', $moduloId)
            ->where('activo', 1)
            ->get();
    }
}
