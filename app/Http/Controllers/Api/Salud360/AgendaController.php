<?php

namespace App\Http\Controllers\Api\Salud360;

use App\Services\Salud360\AgendaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Lectura de agenda: turnos del día, disponibilidad, semana y próximas fechas.
 */
class AgendaController extends Salud360Controller
{
    /**
     * GET /api/salud360/agenda/dia?medico_id&fecha[&consultorio_id][&tipo_turno]
     * Turnos activos del día + disponibilidad de horarios.
     */
    public function dia(Request $request)
    {
        $medico = $this->resolverMedico($request);
        if ($medico === null) {
            return $this->sinPermisoMedico();
        }
        $fecha = $this->fecha($request);
        if ($fecha === null) {
            return $this->error('Fecha inválida (usar AAAA-MM-DD).', 422, 'fecha');
        }
        $consultorio = $this->consultorioDe($request, $medico);
        $tipoTurno = (int) $request->input('tipo_turno', AgendaService::TIPO_TURNO_CONSULTA);
        $dia = $this->agenda->diaSemana($fecha);

        return $this->ok([
            'fecha' => $fecha,
            'dia' => $dia,
            'medico_id' => (int) $medico->id,
            'consultorio_id' => $consultorio,
            'es_feriado' => $this->agenda->esFeriado($fecha),
            'turnos' => $this->agenda->turnosDelDia($medico->id, $consultorio, $fecha),
            'slots' => $this->agenda->slots($medico->id, $consultorio, $dia, $fecha, $tipoTurno),
            'cantidad_sobreturnos' => $this->agenda->cantidadSobreturnos($medico->id, $consultorio, $fecha),
            'modulo_caja_comentario' => $this->agenda->moduloActivo($medico->id, AgendaService::MODULO_CAJA_COMENTARIO),
        ]);
    }

    /**
     * GET /api/salud360/agenda/disponibilidad?medico_id&fecha[&tipo_turno][&primer_control]
     * Horarios libres/ocupados del día. Con primer_control=1 devuelve pares consecutivos.
     */
    public function disponibilidad(Request $request)
    {
        $medico = $this->resolverMedico($request);
        if ($medico === null) {
            return $this->sinPermisoMedico();
        }
        $fecha = $this->fecha($request);
        if ($fecha === null) {
            return $this->error('Fecha inválida (usar AAAA-MM-DD).', 422, 'fecha');
        }
        $consultorio = $this->consultorioDe($request, $medico);
        $tipoTurno = (int) $request->input('tipo_turno', AgendaService::TIPO_TURNO_CONSULTA);
        $primerControl = (int) $request->input('primer_control', 0) === 1;
        $dia = $this->agenda->diaSemana($fecha);

        $moduloDoble = $this->agenda->moduloActivo($medico->id, AgendaService::MODULO_PRIMER_CONTROL_DOBLE) === 1;
        $usaDobles = $primerControl && $moduloDoble;

        $slots = $usaDobles
            ? $this->agenda->slotsDobles($medico->id, $consultorio, $dia, $fecha, $tipoTurno)
            : $this->agenda->slots($medico->id, $consultorio, $dia, $fecha, $tipoTurno);

        return $this->ok([
            'fecha' => $fecha,
            'dia' => $dia,
            'es_feriado' => $this->agenda->esFeriado($fecha),
            'primer_control_doble' => $usaDobles,
            'cupo_primer_control_disponible' => $primerControl
                ? $this->agenda->controlarCantidadPrimerControl($medico->id, $dia, $consultorio, $fecha)
                : null,
            'slots' => $slots,
        ]);
    }

    /**
     * GET /api/salud360/agenda/semana?medico_id&desde[&cantidad]
     * Próximos N días con horarios cargados y su disponibilidad (equivalente a la agenda semanal).
     */
    public function semana(Request $request)
    {
        $medico = $this->resolverMedico($request);
        if ($medico === null) {
            return $this->sinPermisoMedico();
        }
        $desde = $this->fecha($request, 'desde');
        if ($desde === null) {
            $desde = $this->agenda->hoy();
        }
        $cantidad = max(1, min(14, (int) $request->input('cantidad', 5)));
        $consultorio = $this->consultorioDe($request, $medico);
        $tipoTurno = (int) $request->input('tipo_turno', AgendaService::TIPO_TURNO_CONSULTA);

        $dias = [];
        foreach ($this->agenda->proximasFechasConHorario($medico, $desde, $cantidad) as $fecha) {
            $dia = $this->agenda->diaSemana($fecha);
            $dias[] = [
                'fecha' => $fecha,
                'dia' => $dia,
                'es_feriado' => $this->agenda->esFeriado($fecha),
                'slots' => $this->agenda->slots($medico->id, $consultorio, $dia, $fecha, $tipoTurno),
                'turnos' => $this->agenda->turnosDelDia($medico->id, $consultorio, $fecha),
            ];
        }
        return $this->ok(['desde' => $desde, 'dias' => $dias]);
    }

    /**
     * GET /api/salud360/agenda/proximas-fechas?medico_id[&primer_control][&cantidad][&desde]
     * Próximas fechas con al menos un turno libre.
     */
    public function proximasFechas(Request $request)
    {
        $medico = $this->resolverMedico($request);
        if ($medico === null) {
            return $this->sinPermisoMedico();
        }
        $desde = $this->fecha($request, 'desde');
        if ($desde === null) {
            $desde = $this->agenda->hoy();
        }
        $cantidad = max(1, min(10, (int) $request->input('cantidad', 3)));
        $primerControl = (int) $request->input('primer_control', 0);
        $consultorio = $this->consultorioDe($request, $medico);
        $tipoTurno = (int) $request->input('tipo_turno', AgendaService::TIPO_TURNO_CONSULTA);
        $usaDobles = $primerControl === 1
            && $this->agenda->moduloActivo($medico->id, AgendaService::MODULO_PRIMER_CONTROL_DOBLE) === 1;

        $fechas = [];
        $fecha = $desde;
        $recorridos = 0;
        $maximo = $this->agenda->ventanaDias($medico->id);
        while (count($fechas) < $cantidad && $recorridos < $maximo) {
            if (!$this->agenda->esFeriado($fecha)) {
                $dia = $this->agenda->diaSemana($fecha);
                if ($usaDobles) {
                    $hay = $this->agenda->controlarCantidadPrimerControl($medico->id, $dia, $consultorio, $fecha)
                        && $this->hayLibre($this->agenda->slotsDobles($medico->id, $consultorio, $dia, $fecha, $tipoTurno));
                } else {
                    $hay = $this->hayLibre($this->agenda->slots($medico->id, $consultorio, $dia, $fecha, $tipoTurno));
                }
                if ($hay) {
                    $fechas[] = $fecha;
                }
            }
            $fecha = date('Y-m-d', strtotime('+1 day', strtotime($fecha)));
            $recorridos++;
        }
        return $this->ok(['fechas' => $fechas]);
    }

    /**
     * GET /api/salud360/agenda/dias-atencion?medico_id
     * Días de la semana con horarios cargados (plantilla) y fechas especiales futuras.
     */
    public function diasAtencion(Request $request)
    {
        $medico = $this->resolverMedico($request);
        if ($medico === null) {
            return $this->sinPermisoMedico();
        }
        $consultorio = $this->consultorioDe($request, $medico);
        $dias = DB::table('horario_medicos')
            ->where('medico', $medico->id)
            ->where('consultorio', $consultorio)
            ->where('activo', 1)
            ->distinct()
            ->orderBy('dia')
            ->pluck('dia')
            ->map(function ($d) { return (int) $d; })
            ->values()
            ->all();
        $fechas = DB::table('fechas_agregadas')
            ->where('medico', $medico->id)
            ->where('consultorio', $consultorio)
            ->where('activo', 1)
            ->where('fecha', '>=', $this->agenda->hoy())
            ->orderBy('fecha')
            ->pluck('fecha')
            ->values()
            ->all();
        return $this->ok(['dias_semana' => $dias, 'fechas_especiales' => $fechas]);
    }

    private function hayLibre(array $slots)
    {
        foreach ($slots as $s) {
            if ((int) $s['libre'] === 1) {
                return true;
            }
        }
        return false;
    }
}
