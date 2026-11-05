<?php

namespace App\Http\Controllers\Api\Salud360;

use App\Services\Salud360\HistoriaClinicaService;
use App\Services\Salud360\HistoriasClinicasReglas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Historias clínicas habilitadas a cada médico, desde el panel de administración de la app.
 *
 * Es lo mismo que la web hace con `POST admin_hc_medico` (tabla `salud360_medico_hc`, a cargo de
 * HistoriaClinicaService), pero con token. Las reglas del cuerpo están en HistoriasClinicasReglas.
 *
 * Solo para el administrador. Lo que se quita queda inactivo (`activo = 0`), no se borra la fila.
 * No toca agenda, licencias, horarios ni ninguna otra tabla.
 *
 * Compatible con PHP 7.1 / Laravel 5.8.
 */
class MedicoHistoriasClinicasController extends Salud360Controller
{
    /**
     * PUT medicos/{id}/historias-clinicas  {historias_clinicas: [codigo, ...]}
     *
     * Deja habilitadas exactamente esas (`[]` = ninguna) y responde con la lista final real.
     */
    public function guardar(Request $request, $id)
    {
        if (!$this->esAdmin($request->user())) {
            return $this->error('Solo el administrador puede cambiar las historias clínicas de un médico.', 403, 'sin_permiso');
        }
        $medicoId = (int) $id;
        if ($medicoId < 1 || !DB::table('medicos')->where('id', $medicoId)->exists()) {
            return $this->error('Médico no encontrado.', 404, 'no_encontrado');
        }
        $leido = HistoriasClinicasReglas::validar($request->all(), array_keys(HistoriaClinicaService::CATALOGO));
        if (isset($leido['error'])) {
            return $this->error($leido['mensaje'], 422, $leido['error']);
        }

        $servicio = app(HistoriaClinicaService::class);
        // La tabla se crea sola si falta; un CREATE TABLE cierra la transacción en MySQL, así que va antes.
        $servicio->asegurarTabla();

        // Todo junto y con la fila del médico bloqueada: dos administradores a la vez no se pisan a medias.
        $resultado = DB::transaction(function () use ($medicoId, $leido, $servicio) {
            $medico = DB::table('medicos')->where('id', $medicoId)->lockForUpdate()->first();
            if ($medico === null) {
                return null;
            }
            $servicio->guardar($medicoId, $leido['historias_clinicas']);
            return $servicio->habilitadas($medicoId);
        });

        if ($resultado === null) {
            return $this->error('Médico no encontrado.', 404, 'no_encontrado');
        }

        return $this->ok(['medico_id' => $medicoId, 'historias_clinicas' => $resultado]);
    }
}
