<?php

namespace App\Services\Salud360;

use App\MedicoPaciente;
use App\PacienteSecretaria;
use App\TurnoRegistrado;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lógica de agenda compartida entre la web (TurnoController) y la API de Salud 360.
 *
 * Concentra el cálculo de disponibilidad (antes `createJson` / `createJsonTurnosDobles`)
 * y las validaciones de registro de turnos, sin depender de la sesión web.
 *
 * Compatible con PHP 7.1 / Laravel 5.8.
 */
class AgendaService
{
    const MODULO_ACTIVAR_PACIENTES = 1;
    const MODULO_CAJA_COMENTARIO = 2;
    const MODULO_PRIMER_CONTROL_DOBLE = 3;
    const MODULO_AFILIADO_OBLIGATORIO = 8;
    const MODULO_VENTANA_DIAS = 9;
    const MODULO_MOSTRAR_DOS = 11;
    const MODULO_COBRO_TURNOS_MP = 12;

    const TIPO_TURNO_CONSULTA = 1;
    const TIPO_TURNO_VIDEOLLAMADA = 4;

    /** Estados de `turno_registrados.activo` que ocupan un horario. */
    const ACTIVOS_QUE_OCUPAN = [1, 2];

    // ------------------------------------------------------------------
    // Fechas
    // ------------------------------------------------------------------

    /**
     * Acepta `Y-m-d`, `Y/m/d` o `d/m/Y` y devuelve `Y-m-d` (o null si es inválida).
     */
    public function normalizarFecha($fecha)
    {
        if ($fecha === null) {
            return null;
        }
        $fecha = trim((string) $fecha);
        if ($fecha === '') {
            return null;
        }
        if (preg_match('/^(\d{4})[-\/](\d{2})[-\/](\d{2})$/', $fecha, $m)) {
            $y = $m[1]; $mo = $m[2]; $d = $m[3];
        } elseif (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $fecha, $m)) {
            $y = $m[3]; $mo = str_pad($m[2], 2, '0', STR_PAD_LEFT); $d = str_pad($m[1], 2, '0', STR_PAD_LEFT);
        } else {
            return null;
        }
        if (!checkdate((int) $mo, (int) $d, (int) $y)) {
            return null;
        }
        return $y . '-' . $mo . '-' . $d;
    }

    /** Día de la semana como en la base: 1 = lunes ... 7 = domingo. */
    public function diaSemana($fecha)
    {
        return (int) date('N', strtotime($fecha));
    }

    /** `Y-m-d` -> `d/m/Y`. */
    public function fechaMostrar($fecha)
    {
        $p = explode('-', str_replace('/', '-', (string) $fecha));
        if (count($p) !== 3) {
            return (string) $fecha;
        }
        return $p[2] . '/' . $p[1] . '/' . $p[0];
    }

    public function hoy()
    {
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        return date('Y-m-d');
    }

    public function esFeriado($fecha)
    {
        return DB::table('feriados')->where('fecha', $fecha)->exists();
    }

    // ------------------------------------------------------------------
    // Módulos y configuración del médico
    // ------------------------------------------------------------------

    /** 1 si el módulo está activo para el médico, 0 si no. */
    public function moduloActivo($medicoId, $moduloId)
    {
        $activo = DB::table('modulo_medicos')
            ->where('medico', $medicoId)
            ->where('modulo', $moduloId)
            ->where('activo', 1)
            ->exists();
        return $activo ? 1 : 0;
    }

    /** Ids de módulos activos del médico. */
    public function modulosActivos($medicoId)
    {
        return DB::table('modulo_medicos')
            ->where('medico', $medicoId)
            ->where('activo', 1)
            ->pluck('modulo')
            ->map(function ($m) { return (int) $m; })
            ->values()
            ->all();
    }

    /** Ventana de días hacia adelante en la que se pueden sacar turnos (módulo 9). */
    public function ventanaDias($medicoId, $porDefecto = 180)
    {
        $cfg = DB::table('medico_configs')
            ->where('medico', $medicoId)
            ->where('modulo', self::MODULO_VENTANA_DIAS)
            ->where('activo', 1)
            ->first();
        if ($cfg === null || $cfg->valor_integer === null) {
            return (int) $porDefecto;
        }
        return (int) $cfg->valor_integer;
    }

    /** Cupo de primeros controles por día de la semana: [dia => cantidad]. */
    public function cupoPrimerControl($medicoId)
    {
        $filas = DB::table('medico_primer_controls')
            ->where('medico', $medicoId)
            ->where('activo', 1)
            ->get();
        $out = [];
        foreach ($filas as $f) {
            $out[] = [
                'id' => (int) $f->id,
                'dia' => (int) $f->dia,
                'consultorio' => (int) $f->consultorio,
                'cantidad' => (int) $f->cantidadPrimerControl,
            ];
        }
        return $out;
    }

    // ------------------------------------------------------------------
    // Horarios del médico
    // ------------------------------------------------------------------

    /** Id de `fechas_agregadas` para esa fecha o -1 si no hay fecha especial. */
    public function checkFechaAgregada($medicoId, $consultorioId, $fecha)
    {
        $f = DB::table('fechas_agregadas')
            ->where('medico', $medicoId)
            ->where('consultorio', $consultorioId)
            ->where('fecha', $fecha)
            ->where('activo', 1)
            ->first();
        return $f === null ? -1 : (int) $f->id;
    }

    /**
     * Filtra horarios semanales por vigencia (valido_desde / valido_hasta).
     * Si la tabla no tiene las columnas, no filtra.
     */
    public function filtrarVigencia($turnos, $fecha)
    {
        if ($turnos === null) {
            return collect();
        }
        $col = ($turnos instanceof Collection) ? $turnos : collect($turnos);
        if (!Schema::hasColumn('horario_medicos', 'valido_desde')) {
            return $col->values();
        }
        $fechaNorm = str_replace('/', '-', (string) $fecha);
        return $col->filter(function ($t) use ($fechaNorm) {
            $desde = isset($t->valido_desde) ? $t->valido_desde : null;
            $hasta = isset($t->valido_hasta) ? $t->valido_hasta : null;
            if (!empty($desde) && $desde > $fechaNorm) {
                return false;
            }
            if (!empty($hasta) && $hasta < $fechaNorm) {
                return false;
            }
            return true;
        })->values();
    }

    /**
     * Horarios que ofrece el médico para una fecha concreta:
     * plantilla semanal vigente (o videollamada si tipo 4), reemplazada por la fecha agregada si existe.
     */
    public function horariosDelDia($medicoId, $consultorioId, $dia, $fecha, $tipoTurno)
    {
        if ((int) $tipoTurno === self::TIPO_TURNO_VIDEOLLAMADA) {
            $turnos = DB::table('horario_medico_videollamadas')
                ->where('medico', $medicoId)
                ->where('consultorio', $consultorioId)
                ->where('dia', $dia)
                ->where('activo', 1)
                ->orderBy('horario')
                ->get();
        } else {
            $q = DB::table('horario_medicos')
                ->where('medico', $medicoId)
                ->where('consultorio', $consultorioId)
                ->where('dia', $dia)
                ->where('activo', 1);
            if (Schema::hasColumn('horario_medicos', 'tipo_turno')) {
                $q->where('tipo_turno', $tipoTurno);
            }
            $turnos = $this->filtrarVigencia($q->orderBy('horario')->get(), $fecha);
        }

        $fechaId = $this->checkFechaAgregada($medicoId, $consultorioId, $fecha);
        if ($fechaId !== -1) {
            $turnos = DB::table('horarios_medicos_agregados')
                ->where('fecha_agregada_id', $fechaId)
                ->where('medico', $medicoId)
                ->where('consultorio', $consultorioId)
                ->where('dia', $dia)
                ->where('activo', 1)
                ->orderBy('horario')
                ->get();
        }
        return $turnos;
    }

    /**
     * Turnos que ocupan horario ese día (activo 1 o 2) más los horarios con pago MercadoPago pendiente.
     */
    public function turnosRegistradosDelDia($medicoId, $consultorioId, $dia, $fecha, $tipoTurno, $siempreFiltrarTipo = false)
    {
        $q = DB::table('turno_registrados')
            ->where('medico', $medicoId)
            ->where('consultorio', $consultorioId)
            ->where('dia', $dia)
            ->where('fechaTurno', $fecha)
            ->whereIn('activo', self::ACTIVOS_QUE_OCUPAN);
        // Comportamiento histórico de createJson: el médico 13 ocupa el horario con cualquier tipo de turno.
        // Para los turnos dobles (createJsonTurnosDobles) siempre se filtra por tipo.
        if ($siempreFiltrarTipo || (int) $medicoId !== 13) {
            $q->where('tipo_turno', $tipoTurno);
        }
        $registrados = $q->orderBy('horario')->get();

        $pendientes = [];
        try {
            $intentService = new \App\Services\MercadoPago\TurnoPagoIntentService();
            $pendientes = $intentService->getHorariosBloqueadosPorPagoPendiente($medicoId, $consultorioId, $dia, $fecha, $tipoTurno);
        } catch (\Exception $e) {
            \Log::warning('Salud360: no se pudieron leer los pagos pendientes: ' . $e->getMessage());
        }
        foreach ($pendientes as $horarioPendiente) {
            $yaListado = false;
            foreach ($registrados as $tr) {
                if ($tr->horario === $horarioPendiente) {
                    $yaListado = true;
                    break;
                }
            }
            if (!$yaListado) {
                $registrados->push((object) ['horario' => $horarioPendiente]);
            }
        }
        return $registrados;
    }

    /** true si el horario figura en la colección de turnos registrados. */
    public function horarioOcupado($horario, $registrados)
    {
        foreach ($registrados as $r) {
            if (strcmp((string) $horario, (string) $r->horario) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Disponibilidad de un día: [{horario, libre}] (equivalente a `createJson`).
     */
    public function slots($medicoId, $consultorioId, $dia, $fecha, $tipoTurno)
    {
        $turnos = $this->horariosDelDia($medicoId, $consultorioId, $dia, $fecha, $tipoTurno);
        $registrados = $this->turnosRegistradosDelDia($medicoId, $consultorioId, $dia, $fecha, $tipoTurno);
        return $this->marcarLibres($turnos, $registrados);
    }

    /** Marca cada horario con libre = 1/0 según los registrados. Método puro (sin base). */
    public function marcarLibres($turnos, $registrados)
    {
        $data = [];
        foreach ($turnos as $turno) {
            $data[] = [
                'horario' => $turno->horario,
                'libre' => $this->horarioOcupado($turno->horario, $registrados) ? 0 : 1,
            ];
        }
        return $data;
    }

    /**
     * Disponibilidad para primer control doble: pares de horarios consecutivos
     * [{horario, horario2, libre}] (equivalente a `createJsonTurnosDobles`).
     */
    public function slotsDobles($medicoId, $consultorioId, $dia, $fecha, $tipoTurno)
    {
        $q = DB::table('horario_medicos')
            ->where('medico', $medicoId)
            ->where('consultorio', $consultorioId)
            ->where('dia', $dia)
            ->where('activo', 1);
        if (Schema::hasColumn('horario_medicos', 'tipo_turno')) {
            $q->where('tipo_turno', $tipoTurno);
        }
        $turnos = $this->filtrarVigencia($q->orderBy('horario', 'asc')->get(), $fecha);

        $fechaId = $this->checkFechaAgregada($medicoId, $consultorioId, $fecha);
        if ($fechaId !== -1) {
            $turnos = DB::table('horarios_medicos_agregados')
                ->where('fecha_agregada_id', $fechaId)
                ->where('medico', $medicoId)
                ->where('consultorio', $consultorioId)
                ->where('dia', $dia)
                ->where('activo', 1)
                ->orderBy('horario')
                ->get();
        }
        $registrados = $this->turnosRegistradosDelDia($medicoId, $consultorioId, $dia, $fecha, $tipoTurno, true);
        return $this->marcarLibresDobles($turnos, $registrados);
    }

    /** Método puro: arma los pares consecutivos libres. */
    public function marcarLibresDobles($turnos, $registrados)
    {
        $turnos = ($turnos instanceof Collection) ? $turnos->values()->all() : array_values((array) $turnos);
        $data = [];
        $n = count($turnos);
        for ($i = 0; $i < $n; $i++) {
            $libre = 0;
            $actualOcupado = $this->horarioOcupado($turnos[$i]->horario, $registrados);
            $doble = isset($turnos[$i]->doble) ? (int) $turnos[$i]->doble : 0;
            if ($doble === 0 && !$actualOcupado) {
                $j = $i + 1;
                if ($j < $n && !$this->horarioOcupado($turnos[$j]->horario, $registrados)) {
                    $data[] = ['horario' => $turnos[$i]->horario, 'horario2' => $turnos[$j]->horario, 'libre' => 1];
                    $libre = 1;
                }
            }
            if ($libre === 0) {
                $data[] = ['horario' => $turnos[$i]->horario, 'horario2' => $turnos[$i]->horario, 'libre' => 0];
            }
        }
        return $data;
    }

    /**
     * Próximas fechas (a partir de $desde) en las que el médico tiene horarios cargados.
     * Generaliza `obtener5Dias`.
     */
    public function proximasFechasConHorario($medico, $desde, $cantidad = 5, $maxDias = 120)
    {
        $consultorio = $medico->consultorio;
        $diasSemana = DB::table('horario_medicos')
            ->where('medico', $medico->id)
            ->where('consultorio', $consultorio)
            ->where('activo', 1)
            ->distinct()
            ->pluck('dia')
            ->map(function ($d) { return (int) $d; });
        $fechasAgregadas = DB::table('fechas_agregadas')
            ->where('medico', $medico->id)
            ->where('consultorio', $consultorio)
            ->where('activo', 1)
            ->where('fecha', '>=', $desde)
            ->pluck('fecha')
            ->flip()
            ->all();

        $out = [];
        $fecha = $desde;
        $recorridos = 0;
        while (count($out) < $cantidad && $recorridos < $maxDias) {
            $dia = $this->diaSemana($fecha);
            if (isset($fechasAgregadas[$fecha])) {
                $out[] = $fecha;
            } elseif ($diasSemana->contains($dia)) {
                $horarios = $this->horariosDelDia($medico->id, $consultorio, $dia, $fecha, self::TIPO_TURNO_CONSULTA);
                if (count($horarios) > 0) {
                    $out[] = $fecha;
                }
            }
            $fecha = date('Y-m-d', strtotime('+1 day', strtotime($fecha)));
            $recorridos++;
        }
        return $out;
    }

    // ------------------------------------------------------------------
    // Validaciones
    // ------------------------------------------------------------------

    /** Colección con los turnos activos que ya ocupan ese horario (vacía si está libre). */
    public function validarTurnoLibre($medicoId, $consultorioId, $dia, $horario, $fecha)
    {
        $check = DB::table('turno_registrados')
            ->where('medico', $medicoId)
            ->where('consultorio', $consultorioId)
            ->where('dia', $dia)
            ->where('horario', $horario)
            ->where('fechaTurno', $fecha)
            ->whereIn('activo', self::ACTIVOS_QUE_OCUPAN)
            ->get();
        if ($check->count() > 0) {
            return $check;
        }
        try {
            $intentService = new \App\Services\MercadoPago\TurnoPagoIntentService();
            if ($intentService->slotTienePagoPendiente($medicoId, $consultorioId, $dia, $horario, $fecha)) {
                return collect([(object) ['id' => 0, 'pending_mp' => true]]);
            }
        } catch (\Exception $e) {
            \Log::warning('Salud360: no se pudo verificar pago pendiente: ' . $e->getMessage());
        }
        return $check;
    }

    /** true si el paciente ya tiene un turno activo con ese médico ese día. */
    public function validarTurnoMismoDia($pacienteId, $medicoId, $consultorioId, $fecha)
    {
        if ((int) $pacienteId === 14043) { // paciente prenatal, no se valida
            return false;
        }
        return DB::table('turno_registrados')
            ->where('medico', $medicoId)
            ->where('consultorio', $consultorioId)
            ->where('paciente', $pacienteId)
            ->where('fechaTurno', $fecha)
            ->where('activo', 1)
            ->exists();
    }

    /** Los médicos pueden limitar la cantidad de primeros controles por día. */
    public function controlarCantidadPrimerControl($medicoId, $dia, $consultorioId, $fecha)
    {
        $cfg = DB::table('medico_primer_controls')
            ->where('medico', $medicoId)
            ->where('dia', $dia)
            ->where('consultorio', $consultorioId)
            ->where('activo', 1)
            ->first();
        if ($cfg === null) {
            return false;
        }
        $actual = DB::table('turno_registrados')
            ->where('medico', $medicoId)
            ->where('consultorio', $consultorioId)
            ->where('fechaTurno', $fecha)
            ->where('activo', 1)
            ->where('primerControl', 'SI')
            ->count();
        // Cada primer control ocupa dos turnos.
        return ((int) $cfg->cantidadPrimerControl * 2) > $actual;
    }

    public function cantidadTurnosPacienteMes($medicoId, $consultorioId, $pacienteId, $fecha)
    {
        $p = explode('-', $fecha);
        return DB::table('turno_registrados')
            ->where('paciente', $pacienteId)
            ->where('medico', $medicoId)
            ->where('consultorio', $consultorioId)
            ->whereMonth('fechaTurno', $p[1])
            ->whereYear('fechaTurno', $p[0])
            ->where('activo', 1)
            ->count();
    }

    public function cantidadSobreturnos($medicoId, $consultorioId, $fecha)
    {
        return DB::table('turno_registrados')
            ->where('medico', $medicoId)
            ->where('consultorio', $consultorioId)
            ->where('fechaTurno', $fecha)
            ->where('sobreturno', 1)
            ->where('activo', 1)
            ->count();
    }

    // ------------------------------------------------------------------
    // Registro
    // ------------------------------------------------------------------

    /**
     * Inserta un turno (mismo formato que MedicoController::registrarTurno) y vincula paciente↔médico/consultorio.
     */
    public function registrarTurno(array $d)
    {
        $t = new TurnoRegistrado();
        $t->paciente = $d['paciente_id'];
        $t->medico = $d['medico_id'];
        $t->consultorio = $d['consultorio_id'];
        $t->dia = $d['dia'];
        $t->horario = $d['horario'];
        $t->fechaTurno = $d['fecha'];
        $t->asistio = 0;
        $t->sobreturno = isset($d['sobreturno']) ? (int) $d['sobreturno'] : 0;
        $t->primerControl = (isset($d['primer_control']) && $d['primer_control']) ? 'SI' : 'NO';
        $t->caja = 0;
        $t->comentario = isset($d['comentario']) ? (string) $d['comentario'] : '';
        $t->tipo_turno = isset($d['tipo_turno']) ? (int) $d['tipo_turno'] : self::TIPO_TURNO_CONSULTA;
        $t->especialidad = isset($d['especialidad']) ? $d['especialidad'] : '';
        if (Schema::hasColumn('turno_registrados', 'cancelado_por')) {
            $t->cancelado_por = '';
        }
        $t->otorgado_por = isset($d['otorgado_por']) ? $d['otorgado_por'] : 'Salud360';
        $t->msj_enviado = 0;
        $t->activo = 1;
        if (Schema::hasColumn('turno_registrados', 'google_calendar_event_id')) {
            $t->google_calendar_event_id = '';
        }
        $t->save();

        $this->vincularMedicoPaciente($d['medico_id'], $d['paciente_id']);
        $this->vincularSecretariaPaciente($d['paciente_id'], $d['consultorio_id']);
        return $t;
    }

    public function vincularMedicoPaciente($medicoId, $pacienteId)
    {
        $existe = DB::table('medico_pacientes')
            ->where('medico', $medicoId)
            ->where('paciente', $pacienteId)
            ->exists();
        if (!$existe) {
            $mp = new MedicoPaciente();
            $mp->medico = $medicoId;
            $mp->paciente = $pacienteId;
            $mp->bloqueado = 0;
            $mp->save();
        }
    }

    public function vincularSecretariaPaciente($pacienteId, $consultorioId)
    {
        $existe = DB::table('paciente_secretarias')
            ->where('paciente', $pacienteId)
            ->where('consultorio', $consultorioId)
            ->where('activo', 1)
            ->exists();
        if (!$existe) {
            $ps = new PacienteSecretaria();
            $ps->paciente = $pacienteId;
            $ps->consultorio = $consultorioId;
            $ps->activo = 1;
            $ps->save();
        }
    }

    // ------------------------------------------------------------------
    // Consultas de agenda
    // ------------------------------------------------------------------

    /** Turnos activos del día con los datos del paciente (equivalente a getTurnosPaciente). */
    public function turnosDelDia($medicoId, $consultorioId, $fecha)
    {
        return $this->consultaTurnos()
            ->where('turno_registrados.medico', $medicoId)
            ->where('turno_registrados.consultorio', $consultorioId)
            ->where('turno_registrados.fechaTurno', $fecha)
            ->where('turno_registrados.activo', 1)
            ->orderBy('turno_registrados.horario')
            ->get()
            ->map(function ($t) { return $this->formatearTurno($t); })
            ->values()
            ->all();
    }

    /** Query base de turnos con join a pacientes. */
    public function consultaTurnos()
    {
        return DB::table('turno_registrados')
            ->join('pacientes', 'pacientes.id', '=', 'turno_registrados.paciente')
            ->select(
                'turno_registrados.*',
                'pacientes.nombre as paciente_nombre',
                'pacientes.apellido as paciente_apellido',
                'pacientes.dni as paciente_dni',
                'pacientes.telefono as paciente_telefono',
                'pacientes.mail as paciente_mail',
                'pacientes.obra_social as paciente_obra_social',
                'pacientes.numero_afiliado as paciente_numero_afiliado'
            );
    }

    /** Estructura uniforme de un turno para la API. */
    public function formatearTurno($t)
    {
        return [
            'id' => (int) $t->id,
            'paciente_id' => (int) $t->paciente,
            'medico_id' => (int) $t->medico,
            'consultorio_id' => (int) $t->consultorio,
            'dia' => (int) $t->dia,
            'horario' => $t->horario,
            'fecha' => str_replace('/', '-', (string) $t->fechaTurno),
            'asistio' => (int) $t->asistio,
            'sobreturno' => (int) $t->sobreturno,
            'primer_control' => ($t->primerControl === 'SI'),
            'caja' => (float) $t->caja,
            'comentario' => (string) $t->comentario,
            'tipo_turno' => (int) $t->tipo_turno,
            'especialidad' => isset($t->especialidad) ? $t->especialidad : null,
            'otorgado_por' => $t->otorgado_por,
            'cancelado_por' => isset($t->cancelado_por) ? $t->cancelado_por : null,
            'activo' => (int) $t->activo,
            'pago' => isset($t->pago) ? (int) $t->pago : 0,
            'pago_estado' => isset($t->pago_estado) ? $t->pago_estado : null,
            'importe_reserva' => isset($t->importe_reserva) ? $t->importe_reserva : null,
            'paciente' => [
                'id' => (int) $t->paciente,
                'nombre' => isset($t->paciente_nombre) ? $t->paciente_nombre : null,
                'apellido' => isset($t->paciente_apellido) ? $t->paciente_apellido : null,
                'dni' => isset($t->paciente_dni) ? $t->paciente_dni : null,
                'telefono' => isset($t->paciente_telefono) ? $t->paciente_telefono : null,
                'mail' => isset($t->paciente_mail) ? $t->paciente_mail : null,
                'obra_social' => isset($t->paciente_obra_social) ? $t->paciente_obra_social : null,
                'numero_afiliado' => isset($t->paciente_numero_afiliado) ? $t->paciente_numero_afiliado : null,
            ],
            'created_at' => $t->created_at,
            'updated_at' => $t->updated_at,
        ];
    }
}
