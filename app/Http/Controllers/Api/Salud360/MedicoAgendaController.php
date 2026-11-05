<?php

namespace App\Http\Controllers\Api\Salud360;

use App\Services\Salud360\AgendaMedicoReglas;
use App\Services\Salud360\AgendaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Agenda por médico, desde el panel de administración de la app (spec 005 de Salud 360).
 *
 * Dos datos independientes del médico:
 *  - «tiene agenda»: `medicos.tiene_agenda` (el médico maneja turnos);
 *  - «visible para pacientes»: `medicos.castigo_automatico` («Mostrar Médico», 1 = se muestra).
 * Las reglas están en AgendaMedicoReglas.
 *
 * Solo para el administrador. No cancela ni modifica turnos, y no toca horarios, licencias ni las
 * historias clínicas (`salud360_medico_hc`): apagar la agenda no borra lo cargado.
 *
 * Compatible con PHP 7.1 / Laravel 5.8.
 */
class MedicoAgendaController extends Salud360Controller
{
    /**
     * PUT medicos/{id}/agenda  {[tiene_agenda], [visible_en_turnos]}  — al menos uno, booleanos.
     *
     * Responde siempre con el estado final de los dos y con `turnos_futuros`: los turnos vigentes
     * (no cancelados) del médico de hoy en adelante. Solo se informa; no se toca ninguno.
     */
    public function guardar(Request $request, $id)
    {
        if (!$this->esAdmin($request->user())) {
            return $this->error('Solo el administrador puede cambiar la agenda de un médico.', 403, 'sin_permiso');
        }
        $medicoId = (int) $id;
        if ($medicoId < 1 || !DB::table('medicos')->where('id', $medicoId)->exists()) {
            return $this->error('Médico no encontrado.', 404, 'no_encontrado');
        }
        $leido = AgendaMedicoReglas::leerPedido($request->all());
        if (isset($leido['error'])) {
            return $this->error($leido['mensaje'], 422, $leido['error']);
        }
        if (!Schema::hasColumn('medicos', 'tiene_agenda')) {
            return $this->error('La agenda por médico no está disponible en esta instalación (falta la columna tiene_agenda).', 422, 'no_disponible');
        }

        // Todo junto y con la fila bloqueada: dos administradores a la vez no se pisan a medias.
        $resultado = DB::transaction(function () use ($medicoId, $leido) {
            $medico = DB::table('medicos')->where('id', $medicoId)->lockForUpdate()->first();
            if ($medico === null) {
                return ['error' => 'no_encontrado', 'mensaje' => 'Médico no encontrado.'];
            }
            $r = AgendaMedicoReglas::resolver(
                (int) $medico->tiene_agenda === 1,
                (int) $medico->castigo_automatico === 1,
                $leido['pedido']
            );
            if (isset($r['error'])) {
                return $r;
            }
            // Solo se escribe lo que cambia: repetir el mismo pedido no toca la fila.
            $cambios = [];
            foreach ($r['columnas'] as $columna => $valor) {
                if ((int) $medico->{$columna} !== $valor) {
                    $cambios[$columna] = $valor;
                }
            }
            if (count($cambios) > 0) {
                $cambios['updated_at'] = Carbon::now();
                DB::table('medicos')->where('id', $medicoId)->update($cambios);
            }
            return $r;
        });

        if (isset($resultado['error'])) {
            $status = $resultado['error'] === 'no_encontrado' ? 404 : 422;
            return $this->error($resultado['mensaje'], $status, $resultado['error']);
        }

        return $this->ok([
            'medico_id' => $medicoId,
            'tiene_agenda' => $resultado['tiene_agenda'],
            'visible_en_turnos' => $resultado['visible_en_turnos'],
            'turnos_futuros' => $this->turnosFuturos($medicoId),
        ]);
    }

    /**
     * Turnos vigentes del médico con fecha de hoy en adelante, en todos sus consultorios. «Vigente» es
     * lo mismo que ocupa un horario en la agenda (`turno_registrados.activo` en 1 o 2): lo cancelado
     * tiene `activo = 0`.
     */
    private function turnosFuturos($medicoId)
    {
        return (int) DB::table('turno_registrados')
            ->where('medico', $medicoId)
            ->whereIn('activo', AgendaService::ACTIVOS_QUE_OCUPAN)
            ->where('fechaTurno', '>=', $this->agenda->hoy())
            ->count();
    }
}
