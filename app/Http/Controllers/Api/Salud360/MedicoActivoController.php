<?php

namespace App\Http\Controllers\Api\Salud360;

use App\Services\Salud360\EstadoMedicoReglas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Alta y baja de acceso de un médico, desde el panel de administración de la app.
 *
 * Escribe `medicos.activo` (1/0). Un médico con `activo = 0` queda dado de baja en turnos: no figura
 * en `catalogos`, su perfil no trae la ficha y la API ya no opera sobre él (`medicoPropio` busca con
 * `activo = 1`). Las reglas del cuerpo están en EstadoMedicoReglas.
 *
 * Solo para el administrador. No toca agenda (`tiene_agenda`, `castigo_automatico`), historias
 * clínicas, licencias, horarios, turnos ni tokens. Las secretarias y el administrador no tienen
 * «activo»: no hay dónde guardarlo.
 *
 * Compatible con PHP 7.1 / Laravel 5.8.
 */
class MedicoActivoController extends Salud360Controller
{
    /**
     * PUT medicos/{id}/activo  {activo: true|false}
     *
     * Idempotente. Responde con el estado final real, leído de la base.
     */
    public function guardar(Request $request, $id)
    {
        if (!$this->esAdmin($request->user())) {
            return $this->error('Solo el administrador puede activar o desactivar a un médico.', 403, 'sin_permiso');
        }
        $medicoId = (int) $id;
        if ($medicoId < 1 || !DB::table('medicos')->where('id', $medicoId)->exists()) {
            return $this->error('Médico no encontrado.', 404, 'no_encontrado');
        }
        $leido = EstadoMedicoReglas::validar($request->all());
        if (isset($leido['error'])) {
            return $this->error($leido['mensaje'], 422, $leido['error']);
        }

        // Todo junto y con la fila bloqueada: dos administradores a la vez no se pisan a medias.
        $activo = DB::transaction(function () use ($medicoId, $leido) {
            $medico = DB::table('medicos')->where('id', $medicoId)->lockForUpdate()->first();
            if ($medico === null) {
                return null;
            }
            $nuevo = $leido['activo'] ? 1 : 0;
            // Solo se escribe si cambia: repetir el mismo pedido no toca la fila.
            if ((int) $medico->activo !== $nuevo) {
                DB::table('medicos')->where('id', $medicoId)->update(['activo' => $nuevo, 'updated_at' => Carbon::now()]);
            }
            return (int) DB::table('medicos')->where('id', $medicoId)->value('activo') === 1;
        });

        if ($activo === null) {
            return $this->error('Médico no encontrado.', 404, 'no_encontrado');
        }

        return $this->ok(['medico_id' => $medicoId, 'activo' => $activo]);
    }
}
