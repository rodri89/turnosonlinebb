<?php

namespace App\Http\Controllers\Api\Salud360;

use App\Services\Salud360\TiposTurnoReglas;
use App\Services\TipoTurnoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tipos de turno que ofrece cada médico, desde el panel de administración de la app.
 *
 * Es lo mismo que la web hace con `POST admin_tipo_turno_medico` y `POST admin_tipo_turno_opciones_medico`
 * (tablas `medico_tipo_turnos` y `tipo_turno_opciones`, a cargo de TipoTurnoService), pero con token y en
 * un solo pedido. Las reglas del cuerpo están en TiposTurnoReglas.
 *
 * Cambiarlos es solo del administrador; leerlos (GET) también pueden el médico dueño y la secretaria con
 * permiso sobre ese médico. Lo que se quita queda inactivo (`activo = 0`), no se borra la fila, y las
 * opciones guardadas de un tipo que se deshabilita se conservan. No toca horarios, turnos, agenda ni
 * historias clínicas: si a un tipo deshabilitado le quedan horarios activos, solo se avisa.
 *
 * Compatible con PHP 7.1 / Laravel 5.8.
 */
class MedicoTiposTurnoController extends Salud360Controller
{
    /**
     * GET medicos/{id}/tipos-turno
     *
     * Solo los tipos habilitados del médico (con la condición `primer_control`) y sus opciones activas.
     *
     * Lo puede leer el administrador (cualquier médico), el médico sobre sí mismo y la secretaria sobre los
     * médicos de sus consultorios (los mismos permisos que el resto de la API, `puedeGestionarMedico`): la app
     * los necesita para ofrecer solo tipos habilitados al cargar horarios y para elegir la opción de un turno.
     * Cambiarlos sigue siendo solo del administrador (PUT).
     */
    public function ver(Request $request, $id)
    {
        $user = $request->user();
        $medicoId = (int) $id;
        if (!$this->esAdmin($user) && !$this->puedeGestionarMedico($user, $medicoId)) {
            return $this->error('No tiene permiso para ver los tipos de turno de ese médico.', 403, 'sin_permiso');
        }
        if ($medicoId < 1 || !DB::table('medicos')->where('id', $medicoId)->exists()) {
            return $this->error('Médico no encontrado.', 404, 'no_encontrado');
        }

        return $this->ok($this->estado($medicoId));
    }

    /**
     * PUT medicos/{id}/tipos-turno  {tipos: [{id, [primer_control], [opciones]}, ...]}
     *
     * Deja habilitados exactamente esos tipos (`[]` = ninguno). Si un tipo trae `opciones`, deja esas
     * opciones exactamente; si no, las de ese tipo no se tocan. Responde con el estado final real, igual
     * que el GET, más `avisos`.
     */
    public function guardar(Request $request, $id)
    {
        if (!$this->esAdmin($request->user())) {
            return $this->error('Solo el administrador puede cambiar los tipos de turno de un médico.', 403, 'sin_permiso');
        }
        $medicoId = (int) $id;
        if ($medicoId < 1 || !DB::table('medicos')->where('id', $medicoId)->exists()) {
            return $this->error('Médico no encontrado.', 404, 'no_encontrado');
        }

        // JSON estricto, tal como llegó: el middleware ConvertEmptyStringsToNull convertiría un
        // `"primer_control": ""` en `null` («siempre») y no hay que aceptarlo.
        $cuerpo = json_decode($request->getContent(), true);
        if (!is_array($cuerpo)) {
            $cuerpo = [];
        }
        $activos = DB::table('tipo_turnos')->where('activo', 1)->pluck('id')->map(function ($tipoId) {
            return (int) $tipoId;
        })->all();
        $leido = TiposTurnoReglas::validarConfiguracion($cuerpo, $activos);
        if (isset($leido['error'])) {
            return $this->error($leido['mensaje'], 422, $leido['error']);
        }

        $servicio = app(TipoTurnoService::class);

        // Todo junto y con la fila del médico bloqueada: dos administradores a la vez no se pisan a medias.
        $resultado = DB::transaction(function () use ($medicoId, $leido, $servicio) {
            $medico = DB::table('medicos')->where('id', $medicoId)->lockForUpdate()->first();
            if ($medico === null) {
                return null;
            }
            $items = array_map(function ($tipo) {
                return ['id' => $tipo['id'], 'primer_control' => $tipo['primer_control']];
            }, $leido['tipos']);
            $servicio->guardar($medicoId, $items);
            foreach ($leido['tipos'] as $tipo) {
                if ($tipo['opciones'] !== null) {
                    $servicio->guardarOpciones($medicoId, $tipo['id'], $tipo['opciones']);
                }
            }
            $estado = $this->estado($medicoId);
            $estado['avisos'] = $this->avisos($medicoId, array_column($estado['tipos'], 'id'));
            return $estado;
        });

        if ($resultado === null) {
            return $this->error('Médico no encontrado.', 404, 'no_encontrado');
        }

        return $this->ok($resultado);
    }

    /**
     * Estado real del médico leído de la base: los tipos habilitados que siguen activos en el catálogo (los
     * mismos que muestra la web de administración), por id, cada uno con sus opciones activas.
     */
    private function estado($medicoId)
    {
        $servicio = app(TipoTurnoService::class);
        $nombres = $servicio->nombresPorId();
        $condiciones = DB::table('medico_tipo_turnos')
            ->where('medico', $medicoId)
            ->where('activo', 1)
            ->pluck('primer_control', 'tipo_turno')
            ->all();
        $activosEnCatalogo = DB::table('tipo_turnos')->where('activo', 1)->pluck('id')->map(function ($tipoId) {
            return (int) $tipoId;
        })->all();

        $tipos = [];
        foreach ($servicio->habilitados($medicoId) as $tipoId) {
            if (!in_array($tipoId, $activosEnCatalogo, true)) {
                continue;
            }
            $condicion = $condiciones[$tipoId] ?? null;
            $tipos[] = [
                'id' => $tipoId,
                'nombre' => $nombres[$tipoId] ?? '',
                'primer_control' => $condicion === null ? null : (int) $condicion,
                'opciones' => $servicio->opciones($medicoId, $tipoId),
            ];
        }

        return ['medico_id' => $medicoId, 'tipos' => $tipos];
    }

    /**
     * Avisos de lo que la web también permite pero conviene saber: tipos NO habilitados que todavía tienen
     * horarios fijos activos del médico (`horario_medicos`). Solo números y nombres de tipo.
     *
     * @param int[] $habilitados ids que quedaron habilitados
     * @return string[]
     */
    private function avisos($medicoId, array $habilitados)
    {
        if (!Schema::hasColumn('horario_medicos', 'tipo_turno')) {
            return [];
        }
        $cantidades = DB::table('horario_medicos')
            ->where('medico', $medicoId)
            ->where('activo', 1)
            ->whereNotIn('tipo_turno', $habilitados)
            ->groupBy('tipo_turno')
            ->orderBy('tipo_turno')
            ->selectRaw('tipo_turno, COUNT(*) as cantidad')
            ->get();

        $nombres = app(TipoTurnoService::class)->nombresPorId();
        $avisos = [];
        foreach ($cantidades as $fila) {
            $tipoId = (int) $fila->tipo_turno;
            $n = (int) $fila->cantidad;
            $nombre = $nombres[$tipoId] ?? ('con id ' . $tipoId);
            $avisos[] = 'El tipo ' . $nombre . ' sigue teniendo ' . $n . ($n === 1 ? ' horario activo.' : ' horarios activos.');
        }
        return $avisos;
    }
}
