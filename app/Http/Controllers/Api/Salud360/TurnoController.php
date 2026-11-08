<?php

namespace App\Http\Controllers\Api\Salud360;

use App\Services\Salud360\AgendaService;
use App\Services\Salud360\DisponibilidadReglas;
use App\Services\Salud360\TipoEnReservaReglas;
use App\Services\TipoTurnoService;
use App\TurnoRegistrado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Alta, cancelación y actualización de turnos desde Salud 360.
 * Replica las validaciones de MedicoController (registrarAsignarTurno, registrarSobreturno,
 * borrarTurnoAgendaSemanal, bloquarDiaAgendaSemanal, registrarAsistencia, updateCaja, updateComentario).
 */
class TurnoController extends Salud360Controller
{
    /**
     * GET /api/salud360/turnos?medico_id[&desde][&hasta][&actualizado_desde][&incluir_cancelados]
     * Listado para sincronización. Por defecto: turnos activos desde hoy.
     */
    public function index(Request $request)
    {
        $medico = $this->resolverMedico($request);
        if ($medico === null) {
            return $this->sinPermisoMedico();
        }
        $consultorio = $this->consultorioDe($request, $medico);
        $q = $this->agenda->consultaTurnos()
            ->where('turno_registrados.medico', $medico->id)
            ->where('turno_registrados.consultorio', $consultorio);

        $desde = $this->fecha($request, 'desde');
        $hasta = $this->fecha($request, 'hasta');
        $actualizadoDesde = $request->input('actualizado_desde');

        if ($actualizadoDesde) {
            $q->where('turno_registrados.updated_at', '>=', $actualizadoDesde);
        } else {
            if ($desde === null) {
                $desde = $this->agenda->hoy();
            }
            $q->where('turno_registrados.fechaTurno', '>=', $desde);
        }
        if ($hasta !== null) {
            $q->where('turno_registrados.fechaTurno', '<=', $hasta);
        }
        if ((int) $request->input('incluir_cancelados', $actualizadoDesde ? 1 : 0) !== 1) {
            $q->where('turno_registrados.activo', 1);
        }
        $limite = max(1, min(2000, (int) $request->input('limite', 1000)));
        $turnos = $q->orderBy('turno_registrados.fechaTurno')
            ->orderBy('turno_registrados.horario')
            ->limit($limite)
            ->get()
            ->map(function ($t) { return $this->agenda->formatearTurno($t); })
            ->values()
            ->all();
        return $this->ok(['turnos' => $turnos, 'cantidad' => count($turnos)]);
    }

    /**
     * GET /api/salud360/turnos/{id}
     */
    public function show(Request $request, $id)
    {
        $t = $this->agenda->consultaTurnos()->where('turno_registrados.id', (int) $id)->first();
        if ($t === null) {
            return $this->error('Turno no encontrado.', 404, 'no_encontrado');
        }
        if (!$this->puedeGestionarMedico($request->user(), $t->medico)) {
            return $this->sinPermisoMedico();
        }
        return $this->ok(['turno' => $this->agenda->formatearTurno($t)]);
    }

    /**
     * GET /api/salud360/turnos/paciente/{pacienteId}?medico_id
     * Historial de turnos de un paciente con el médico.
     */
    public function porPaciente(Request $request, $pacienteId)
    {
        $medico = $this->resolverMedico($request);
        if ($medico === null) {
            return $this->sinPermisoMedico();
        }
        $turnos = $this->agenda->consultaTurnos()
            ->where('turno_registrados.medico', $medico->id)
            ->where('turno_registrados.paciente', (int) $pacienteId)
            ->orderBy('turno_registrados.fechaTurno', 'desc')
            ->orderBy('turno_registrados.horario', 'desc')
            ->limit(200)
            ->get()
            ->map(function ($t) { return $this->agenda->formatearTurno($t); })
            ->values()
            ->all();
        return $this->ok(['turnos' => $turnos]);
    }

    /**
     * POST /api/salud360/turnos
     * {medico_id, paciente_id, fecha, horario, [horario2], [tipo_turno=1], [primer_control=0], [consultorio_id], [comentario]}
     *
     * Códigos de rechazo (HTTP 409): `ocupado`, `mismo_dia`, `cupo_primer_control`, `horario_invalido`.
     *
     * Si viene `tipo_turno` y el horario es de la plantilla semanal, el tipo tiene que ser el del horario o una
     * opción vigente de él; si no, 422 `tipo_invalido`. El turno se guarda con el tipo recibido (la opción).
     * Excepción: si el médico tiene a lo sumo un tipo habilitado, un `tipo_turno` = 1 (el valor por omisión de
     * la app publicada) se acepta y el turno se guarda con el tipo T del horario, no con 1.
     * Sin `tipo_turno`, o en un horario sin plantilla (fecha especial), no se valida. Ver tipoEnSlot.
     */
    public function store(Request $request)
    {
        $medico = $this->resolverMedico($request);
        if ($medico === null) {
            return $this->sinPermisoMedico();
        }
        $fecha = $this->fecha($request);
        $horario = $request->input('horario');
        $horario2 = $request->input('horario2');
        $pacienteId = (int) $request->input('paciente_id');
        if ($fecha === null || !$this->horarioValido($horario) || $pacienteId < 1) {
            return $this->error('Datos inválidos: se requiere paciente_id, fecha (AAAA-MM-DD) y horario (HH:MM).', 422, 'datos');
        }
        $paciente = DB::table('pacientes')->where('id', $pacienteId)->first();
        if ($paciente === null) {
            return $this->error('Paciente no encontrado.', 404, 'paciente');
        }
        $consultorio = $this->consultorioDe($request, $medico);
        $tipoTurno = (int) $request->input('tipo_turno', AgendaService::TIPO_TURNO_CONSULTA);
        $dia = $this->agenda->diaSemana($fecha);

        // Primer control "SI" solo cuando el médico tiene el módulo de primer control doble.
        $moduloDoble = $this->agenda->moduloActivo($medico->id, AgendaService::MODULO_PRIMER_CONTROL_DOBLE) === 1;
        $primerControl = $moduloDoble && (int) $request->input('primer_control', 0) === 1;

        // El paciente "bloqueo" (DNI 99999) puede ocupar varios horarios el mismo día, como en la web (SecretariaController).
        $esBloqueo = (int) $paciente->dni === 99999;
        if (!$esBloqueo && $this->agenda->validarTurnoMismoDia($pacienteId, $medico->id, $consultorio, $fecha)) {
            return $this->error('El paciente ya tiene un turno con este médico ese día.', 409, 'mismo_dia');
        }
        if ($this->agenda->validarTurnoLibre($medico->id, $consultorio, $dia, $horario, $fecha)->count() > 0) {
            return $this->error('El horario ya está ocupado.', 409, 'ocupado');
        }
        // Va después de `ocupado` y `mismo_dia`: un pedido que ya fallaba por eso sigue fallando igual.
        $tipoPedido = TipoEnReservaReglas::leerTipo($request->input('tipo_turno'));
        $enSlot = $this->tipoEnSlot($tipoPedido, $medico->id, $consultorio, $dia, $horario, $fecha);
        if (isset($enSlot['error'])) {
            return $this->error($enSlot['mensaje'], 422, $enSlot['error']);
        }
        $tipoParaSlots = $enSlot['padre'] !== null ? $enSlot['padre'] : $tipoTurno;
        // Un 1 aceptado por tener el médico un solo tipo habilitado se guarda con el tipo del horario.
        $tipoGuardado = isset($enSlot['tipo']) ? $enSlot['tipo'] : $tipoTurno;

        $base = [
            'paciente_id' => $pacienteId,
            'medico_id' => $medico->id,
            'consultorio_id' => $consultorio,
            'dia' => $dia,
            'fecha' => $fecha,
            'tipo_turno' => $tipoGuardado,
            'primer_control' => $primerControl,
            'sobreturno' => 0,
            'comentario' => (string) $request->input('comentario', ''),
            'otorgado_por' => $request->user()->email,
        ];

        $creados = [];
        if ($primerControl && $horario2 !== null && $horario2 !== '') {
            if (!$this->horarioValido($horario2)) {
                return $this->error('horario2 inválido (HH:MM).', 422, 'horario_invalido');
            }
            if ($this->agenda->validarTurnoLibre($medico->id, $consultorio, $dia, $horario2, $fecha)->count() > 0) {
                return $this->error('El segundo horario ya está ocupado.', 409, 'ocupado');
            }
            $enSlot2 = $this->tipoEnSlot($tipoPedido, $medico->id, $consultorio, $dia, $horario2, $fecha);
            if (isset($enSlot2['error'])) {
                return $this->error($enSlot2['mensaje'], 422, $enSlot2['error']);
            }
            if (!$this->agenda->controlarCantidadPrimerControl($medico->id, $dia, $consultorio, $fecha)) {
                return $this->error('No quedan cupos de primer control para ese día.', 409, 'cupo_primer_control');
            }
            $creados[] = $this->agenda->registrarTurno(array_merge($base, ['horario' => $horario]));
            $tipoGuardado2 = isset($enSlot2['tipo']) ? $enSlot2['tipo'] : $tipoTurno;
            $creados[] = $this->agenda->registrarTurno(array_merge($base, ['horario' => $horario2, 'tipo_turno' => $tipoGuardado2]));
        } else {
            $creados[] = $this->agenda->registrarTurno(array_merge($base, ['horario' => $horario]));
        }

        $ids = array_map(function ($t) { return (int) $t->id; }, $creados);
        $turnos = $this->agenda->consultaTurnos()
            ->whereIn('turno_registrados.id', $ids)
            ->get()
            ->map(function ($t) { return $this->agenda->formatearTurno($t); })
            ->values()
            ->all();

        return $this->ok([
            'turnos' => $turnos,
            'slots' => $this->agenda->slots($medico->id, $consultorio, $dia, $fecha, $tipoParaSlots),
        ], 201);
    }

    /**
     * POST /api/salud360/turnos/sobreturno  {medico_id, paciente_id, fecha, horario, [tipo_turno], [comentario]}
     *
     * `tipo_turno` se valida igual que en `store` (422 `tipo_invalido`), solo si el horario es de la plantilla;
     * también vale la excepción del 1 con a lo sumo un tipo habilitado (se guarda el tipo del horario).
     */
    public function sobreturno(Request $request)
    {
        $medico = $this->resolverMedico($request);
        if ($medico === null) {
            return $this->sinPermisoMedico();
        }
        $fecha = $this->fecha($request);
        $horario = $request->input('horario');
        $pacienteId = (int) $request->input('paciente_id');
        if ($fecha === null || !$this->horarioValido($horario) || $pacienteId < 1) {
            return $this->error('Datos inválidos: se requiere paciente_id, fecha (AAAA-MM-DD) y horario (HH:MM).', 422, 'datos');
        }
        if (!DB::table('pacientes')->where('id', $pacienteId)->exists()) {
            return $this->error('Paciente no encontrado.', 404, 'paciente');
        }
        $consultorio = $this->consultorioDe($request, $medico);
        $tipoTurno = (int) $request->input('tipo_turno', AgendaService::TIPO_TURNO_CONSULTA);
        $dia = $this->agenda->diaSemana($fecha);

        if ($this->agenda->validarTurnoLibre($medico->id, $consultorio, $dia, $horario, $fecha)->count() > 0) {
            return $this->error('El horario ya está ocupado.', 409, 'ocupado');
        }
        $enSlot = $this->tipoEnSlot(TipoEnReservaReglas::leerTipo($request->input('tipo_turno')), $medico->id, $consultorio, $dia, $horario, $fecha);
        if (isset($enSlot['error'])) {
            return $this->error($enSlot['mensaje'], 422, $enSlot['error']);
        }
        $t = $this->agenda->registrarTurno([
            'paciente_id' => $pacienteId,
            'medico_id' => $medico->id,
            'consultorio_id' => $consultorio,
            'dia' => $dia,
            'horario' => $horario,
            'fecha' => $fecha,
            'tipo_turno' => isset($enSlot['tipo']) ? $enSlot['tipo'] : $tipoTurno,
            'primer_control' => false,
            'sobreturno' => 1,
            'comentario' => (string) $request->input('comentario', ''),
            'otorgado_por' => $request->user()->email,
        ]);
        $creado = $this->agenda->consultaTurnos()->where('turno_registrados.id', $t->id)->first();
        return $this->ok([
            'turno' => $this->agenda->formatearTurno($creado),
            'cantidad_sobreturnos' => $this->agenda->cantidadSobreturnos($medico->id, $consultorio, $fecha),
        ], 201);
    }

    /**
     * POST /api/salud360/turnos/bloquear-dia  {medico_id, fecha, paciente_id, [tipo_turno]}
     * Ocupa todos los horarios del día con el paciente indicado (misma mecánica que la web:
     * el consultorio usa un paciente "bloqueo"), sin importar el tipo de turno de cada horario: cada
     * bloqueo se guarda con el tipo de su propio horario. `tipo_turno` solo hace falta para videollamada (4)
     * o como tipo de los horarios que no traen el suyo. Falla si el día ya tiene turnos.
     */
    public function bloquearDia(Request $request)
    {
        $medico = $this->resolverMedico($request);
        if ($medico === null) {
            return $this->sinPermisoMedico();
        }
        $fecha = $this->fecha($request);
        $pacienteId = (int) $request->input('paciente_id');
        if ($fecha === null || $pacienteId < 1) {
            return $this->error('Se requiere fecha (AAAA-MM-DD) y paciente_id.', 422, 'datos');
        }
        if (!DB::table('pacientes')->where('id', $pacienteId)->exists()) {
            return $this->error('Paciente no encontrado.', 404, 'paciente');
        }
        $consultorio = $this->consultorioDe($request, $medico);
        $tipoTurno = (int) $request->input('tipo_turno', AgendaService::TIPO_TURNO_CONSULTA);
        $dia = $this->agenda->diaSemana($fecha);

        $ocupados = DB::table('turno_registrados')
            ->where('medico', $medico->id)
            ->where('consultorio', $consultorio)
            ->where('fechaTurno', $fecha)
            ->where('dia', $dia)
            ->where('activo', 1)
            ->count();
        if ($ocupados > 0) {
            return $this->error('El día ya tiene turnos registrados; cancelalos antes de bloquearlo.', 409, 'dia_con_turnos');
        }
        // Se bloquean los horarios de todos los tipos de turno (un horario libre de otro tipo seguiría
        // ofreciéndose); videollamada tiene sus propios horarios y se pide con tipo_turno = 4.
        $horarios = $tipoTurno === AgendaService::TIPO_TURNO_VIDEOLLAMADA
            ? $this->agenda->horariosDelDia($medico->id, $consultorio, $dia, $fecha, $tipoTurno)
            : $this->agenda->horariosDelDiaTodosLosTipos($medico->id, $consultorio, $dia, $fecha);
        $creados = 0;
        foreach (DisponibilidadReglas::horariosParaBloquear($horarios, $tipoTurno) as $h) {
            $this->agenda->registrarTurno([
                'paciente_id' => $pacienteId,
                'medico_id' => $medico->id,
                'consultorio_id' => $consultorio,
                'dia' => $dia,
                'horario' => $h['horario'],
                'fecha' => $fecha,
                'tipo_turno' => $h['tipo_turno'],
                'primer_control' => false,
                'sobreturno' => 0,
                'comentario' => 'Bloqueado desde Salud 360',
                'otorgado_por' => $request->user()->email,
            ]);
            $creados++;
        }
        return $this->ok([
            'bloqueados' => $creados,
            'turnos' => $this->agenda->turnosDelDia($medico->id, $consultorio, $fecha),
        ]);
    }

    /**
     * POST /api/salud360/turnos/{id}/cancelar  [{motivo}]
     */
    public function cancelar(Request $request, $id)
    {
        $turno = TurnoRegistrado::find((int) $id);
        if ($turno === null) {
            return $this->error('Turno no encontrado.', 404, 'no_encontrado');
        }
        if (!$this->puedeGestionarMedico($request->user(), $turno->medico)) {
            return $this->sinPermisoMedico();
        }
        if ((int) $turno->activo === 0) {
            return $this->ok(['turno' => $this->formatearPorId($turno->id), 'ya_cancelado' => true]);
        }
        $firma = $this->firmaUsuario($request->user());
        $turno->activo = 0;
        $motivo = trim((string) $request->input('motivo', ''));
        $turno->comentario = 'Cancelado por :' . $firma . ($motivo !== '' ? ' - ' . $motivo : '');
        if (Schema::hasColumn('turno_registrados', 'cancelado_por')) {
            $turno->cancelado_por = $request->user()->email;
        }
        $turno->save();

        return $this->ok(['turno' => $this->formatearPorId($turno->id)]);
    }

    /**
     * POST /api/salud360/turnos/{id}/asistencia  {asistio: 0|1|2}
     */
    public function asistencia(Request $request, $id)
    {
        $turno = TurnoRegistrado::find((int) $id);
        if ($turno === null) {
            return $this->error('Turno no encontrado.', 404, 'no_encontrado');
        }
        if (!$this->puedeGestionarMedico($request->user(), $turno->medico)) {
            return $this->sinPermisoMedico();
        }
        $turno->asistio = (int) $request->input('asistio', 0);
        $turno->save();
        return $this->ok(['turno' => $this->formatearPorId($turno->id)]);
    }

    /**
     * POST /api/salud360/turnos/{id}/caja  {caja}
     */
    public function caja(Request $request, $id)
    {
        $turno = TurnoRegistrado::find((int) $id);
        if ($turno === null) {
            return $this->error('Turno no encontrado.', 404, 'no_encontrado');
        }
        if (!$this->puedeGestionarMedico($request->user(), $turno->medico)) {
            return $this->sinPermisoMedico();
        }
        $turno->caja = floatval($request->input('caja', 0));
        $turno->save();
        return $this->ok(['turno' => $this->formatearPorId($turno->id)]);
    }

    /**
     * POST /api/salud360/turnos/{id}/comentario  {comentario}
     */
    public function comentario(Request $request, $id)
    {
        $turno = TurnoRegistrado::find((int) $id);
        if ($turno === null) {
            return $this->error('Turno no encontrado.', 404, 'no_encontrado');
        }
        if (!$this->puedeGestionarMedico($request->user(), $turno->medico)) {
            return $this->sinPermisoMedico();
        }
        $c = $request->input('comentario');
        $turno->comentario = $c === null ? '' : (string) $c;
        $turno->save();
        return $this->ok(['turno' => $this->formatearPorId($turno->id)]);
    }

    /**
     * Valida el `tipo_turno` explícito de una reserva contra el horario de la plantilla semanal de ese slot
     * (mismo médico, consultorio, día y hora, activo y vigente en la fecha). Si la fecha tiene fecha especial
     * (`fechas_agregadas`), la plantilla no rige ese día y no hay nada que validar. Sin tipo explícito no
     * consulta nada. Las reglas están en TipoEnReservaReglas::paraTurno (con los tipos habilitados del médico, para
     * el 1 de la app publicada).
     *
     * @param int|null $tipoPedido TipoEnReservaReglas::leerTipo del cuerpo
     * @return array ['padre' => null|int, 'tipo' => int (solo si hay que guardar otro tipo que el recibido)]
     *               o ['error' => ..., 'mensaje' => ...]
     */
    private function tipoEnSlot($tipoPedido, $medicoId, $consultorio, $dia, $horario, $fecha)
    {
        if ($tipoPedido === null || !Schema::hasColumn('horario_medicos', 'tipo_turno')) {
            return ['padre' => null];
        }
        if ($this->agenda->checkFechaAgregada($medicoId, $consultorio, $fecha) !== -1) {
            return ['padre' => null];
        }
        $filas = DB::table('horario_medicos')
            ->where('medico', $medicoId)
            ->where('consultorio', $consultorio)
            ->where('dia', $dia)
            ->where('horario', $horario)
            ->where('activo', 1)
            ->get();
        $tiposDelHorario = $this->agenda->filtrarVigencia($filas, $fecha)
            ->pluck('tipo_turno')
            ->map(function ($t) { return (int) $t; })
            ->unique()
            ->values()
            ->all();
        if (count($tiposDelHorario) === 0) {
            return ['padre' => null];
        }

        $servicio = app(TipoTurnoService::class);
        $opcionesPorPadre = [];
        foreach ($tiposDelHorario as $padre) {
            $opcionesPorPadre[$padre] = array_column($servicio->opciones($medicoId, $padre), 'id');
        }
        return TipoEnReservaReglas::paraTurno($tipoPedido, $tiposDelHorario, $opcionesPorPadre, $servicio->nombresPorId(), $servicio->habilitados($medicoId));
    }

    private function formatearPorId($id)
    {
        $t = $this->agenda->consultaTurnos()->where('turno_registrados.id', (int) $id)->first();
        return $t === null ? null : $this->agenda->formatearTurno($t);
    }
}
