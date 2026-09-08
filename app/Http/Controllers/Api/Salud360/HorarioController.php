<?php

namespace App\Http\Controllers\Api\Salud360;

use App\FechasAgregada;
use App\HorarioMedico;
use App\HorariosMedicosAgregado;
use App\MedicoConfig;
use App\MedicoPrimerControl;
use App\Services\Salud360\AgendaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Configuración de agenda del médico: horarios fijos (plantilla semanal con vigencia),
 * fechas especiales (fechas_agregadas + horarios_medicos_agregados), cupo de primer control,
 * ventana de días y módulos.
 */
class HorarioController extends Salud360Controller
{
    /**
     * GET /api/salud360/horarios?medico_id[&consultorio_id]
     */
    public function index(Request $request)
    {
        $medico = $this->resolverMedico($request);
        if ($medico === null) {
            return $this->sinPermisoMedico();
        }
        $consultorio = $this->consultorioDe($request, $medico);
        $tieneVigencia = Schema::hasColumn('horario_medicos', 'valido_desde');
        $tieneTipo = Schema::hasColumn('horario_medicos', 'tipo_turno');

        $fijos = DB::table('horario_medicos')
            ->where('medico', $medico->id)
            ->where('consultorio', $consultorio)
            ->where('activo', 1)
            ->orderBy('dia')
            ->orderBy('horario')
            ->get()
            ->map(function ($h) use ($tieneVigencia, $tieneTipo) {
                return [
                    'id' => (int) $h->id,
                    'dia' => (int) $h->dia,
                    'horario' => $h->horario,
                    'doble' => (int) $h->doble,
                    'tipo_turno' => $tieneTipo ? (int) $h->tipo_turno : AgendaService::TIPO_TURNO_CONSULTA,
                    'valido_desde' => $tieneVigencia ? $h->valido_desde : null,
                    'valido_hasta' => $tieneVigencia ? $h->valido_hasta : null,
                ];
            })
            ->values()
            ->all();

        $hoy = $this->agenda->hoy();
        $fechas = DB::table('fechas_agregadas')
            ->where('medico', $medico->id)
            ->where('consultorio', $consultorio)
            ->where('activo', 1)
            ->where('fecha', '>=', $hoy)
            ->orderBy('fecha')
            ->get();
        $especiales = [];
        foreach ($fechas as $f) {
            $horarios = DB::table('horarios_medicos_agregados')
                ->where('fecha_agregada_id', $f->id)
                ->where('activo', 1)
                ->orderBy('horario')
                ->get()
                ->map(function ($h) { return ['id' => (int) $h->id, 'horario' => $h->horario, 'doble' => (int) $h->doble]; })
                ->values()
                ->all();
            $especiales[] = ['id' => (int) $f->id, 'fecha' => $f->fecha, 'dia' => (int) $f->dia, 'horarios' => $horarios];
        }

        return $this->ok([
            'medico_id' => (int) $medico->id,
            'consultorio_id' => $consultorio,
            'horarios_fijos' => $fijos,
            'fechas_especiales' => $especiales,
            'cupo_primer_control' => $this->agenda->cupoPrimerControl($medico->id),
            'ventana_dias' => $this->agenda->ventanaDias($medico->id),
            'modulos' => $this->agenda->modulosActivos($medico->id),
        ]);
    }

    /**
     * POST /api/salud360/horarios  {medico_id, dia 1..7, horario HH:MM, [tipo_turno=1], [valido_desde], [valido_hasta]}
     */
    public function store(Request $request)
    {
        $medico = $this->resolverMedico($request);
        if ($medico === null) {
            return $this->sinPermisoMedico();
        }
        $dia = (int) $request->input('dia');
        $horario = $request->input('horario');
        if ($dia < 1 || $dia > 7 || !$this->horarioValido($horario)) {
            return $this->error('Datos inválidos: dia 1..7 y horario HH:MM.', 422, 'datos');
        }
        $consultorio = $this->consultorioDe($request, $medico);
        $existe = DB::table('horario_medicos')
            ->where('medico', $medico->id)
            ->where('consultorio', $consultorio)
            ->where('dia', $dia)
            ->where('horario', $horario)
            ->where('activo', 1)
            ->exists();
        if ($existe) {
            return $this->error('Ese horario ya está cargado para ese día.', 409, 'duplicado');
        }
        $reg = new HorarioMedico();
        $reg->medico = $medico->id;
        $reg->consultorio = $consultorio;
        $reg->dia = $dia;
        $reg->horario = $horario;
        $reg->doble = 0;
        $reg->activo = 1;
        if (Schema::hasColumn('horario_medicos', 'tipo_turno')) {
            $reg->tipo_turno = (int) $request->input('tipo_turno', AgendaService::TIPO_TURNO_CONSULTA);
        }
        if (Schema::hasColumn('horario_medicos', 'valido_desde')) {
            $reg->valido_desde = $this->agenda->normalizarFecha($request->input('valido_desde'));
            $reg->valido_hasta = $this->agenda->normalizarFecha($request->input('valido_hasta'));
        }
        $reg->save();
        return $this->ok(['id' => (int) $reg->id], 201);
    }

    /**
     * PUT /api/salud360/horarios/{id}  {[valido_desde], [valido_hasta]}  (null o "" borra la fecha)
     */
    public function update(Request $request, $id)
    {
        $reg = DB::table('horario_medicos')->where('id', (int) $id)->where('activo', 1)->first();
        if ($reg === null) {
            return $this->error('Horario no encontrado.', 404, 'no_encontrado');
        }
        if (!$this->puedeGestionarMedico($request->user(), $reg->medico)) {
            return $this->sinPermisoMedico();
        }
        if (!Schema::hasColumn('horario_medicos', 'valido_desde')) {
            return $this->error('La vigencia no está disponible en esta instalación.', 422, 'no_disponible');
        }
        $upd = [];
        if ($request->has('valido_desde')) {
            $upd['valido_desde'] = $this->agenda->normalizarFecha($request->input('valido_desde'));
        }
        if ($request->has('valido_hasta')) {
            $upd['valido_hasta'] = $this->agenda->normalizarFecha($request->input('valido_hasta'));
        }
        if (count($upd) === 0) {
            return $this->error('Indicá valido_desde y/o valido_hasta.', 422, 'datos');
        }
        DB::table('horario_medicos')->where('id', $reg->id)->update($upd);
        return $this->ok([]);
    }

    /**
     * DELETE /api/salud360/horarios/{id}
     */
    public function destroy(Request $request, $id)
    {
        $reg = DB::table('horario_medicos')->where('id', (int) $id)->where('activo', 1)->first();
        if ($reg === null) {
            return $this->error('Horario no encontrado.', 404, 'no_encontrado');
        }
        if (!$this->puedeGestionarMedico($request->user(), $reg->medico)) {
            return $this->sinPermisoMedico();
        }
        DB::table('horario_medicos')->where('id', $reg->id)->update(['activo' => 0]);
        return $this->ok([]);
    }

    /**
     * POST /api/salud360/horarios/fecha-especial  {medico_id, fecha, horarios: ["09:00", ...]}
     * Crea (o completa) una fecha especial que reemplaza la plantilla semanal ese día.
     */
    public function guardarFechaEspecial(Request $request)
    {
        $medico = $this->resolverMedico($request);
        if ($medico === null) {
            return $this->sinPermisoMedico();
        }
        $fecha = $this->fecha($request);
        $horarios = $request->input('horarios', []);
        if ($fecha === null || !is_array($horarios) || count($horarios) === 0) {
            return $this->error('Se requiere fecha (AAAA-MM-DD) y una lista de horarios HH:MM.', 422, 'datos');
        }
        foreach ($horarios as $h) {
            if (!$this->horarioValido($h)) {
                return $this->error('Horario inválido: ' . $h, 422, 'datos');
            }
        }
        $consultorio = $this->consultorioDe($request, $medico);
        $dia = $this->agenda->diaSemana($fecha);

        $existente = DB::table('fechas_agregadas')
            ->where('fecha', $fecha)->where('medico', $medico->id)->where('consultorio', $consultorio)->where('activo', 1)
            ->first();
        if ($existente !== null) {
            $fa = FechasAgregada::find($existente->id);
        } else {
            $fa = new FechasAgregada();
            $fa->activo = 1;
            $fa->fecha = $fecha;
            $fa->medico = $medico->id;
            $fa->consultorio = $consultorio;
            $fa->dia = $dia;
            $fa->save();
        }
        $creados = [];
        foreach ($horarios as $h) {
            $ya = DB::table('horarios_medicos_agregados')
                ->where('fecha_agregada_id', $fa->id)->where('medico', $medico->id)->where('consultorio', $consultorio)
                ->where('horario', $h)->where('activo', 1)
                ->first();
            if ($ya !== null) {
                continue;
            }
            $ha = new HorariosMedicosAgregado();
            $ha->activo = 1;
            $ha->doble = 0;
            $ha->medico = $medico->id;
            $ha->consultorio = $consultorio;
            $ha->horario = $h;
            $ha->fecha_agregada_id = $fa->id;
            $ha->dia = $dia;
            $ha->save();
            $creados[] = ['id' => (int) $ha->id, 'horario' => $h];
        }
        return $this->ok(['fecha_especial_id' => (int) $fa->id, 'creados' => $creados], 201);
    }

    /**
     * DELETE /api/salud360/horarios/fecha-especial/{id}  — desactiva la fecha y todos sus horarios.
     */
    public function borrarFechaEspecial(Request $request, $id)
    {
        $fa = DB::table('fechas_agregadas')->where('id', (int) $id)->where('activo', 1)->first();
        if ($fa === null) {
            return $this->error('Fecha especial no encontrada.', 404, 'no_encontrado');
        }
        if (!$this->puedeGestionarMedico($request->user(), $fa->medico)) {
            return $this->sinPermisoMedico();
        }
        DB::table('horarios_medicos_agregados')->where('fecha_agregada_id', $fa->id)->update(['activo' => 0]);
        DB::table('fechas_agregadas')->where('id', $fa->id)->update(['activo' => 0]);
        return $this->ok([]);
    }

    /**
     * DELETE /api/salud360/horarios/fecha-especial/horario/{id}  — desactiva un horario puntual de una fecha especial.
     */
    public function borrarHorarioEspecial(Request $request, $id)
    {
        $ha = DB::table('horarios_medicos_agregados')->where('id', (int) $id)->where('activo', 1)->first();
        if ($ha === null) {
            return $this->error('Horario no encontrado.', 404, 'no_encontrado');
        }
        if (!$this->puedeGestionarMedico($request->user(), $ha->medico)) {
            return $this->sinPermisoMedico();
        }
        DB::table('horarios_medicos_agregados')->where('id', $ha->id)->update(['activo' => 0]);
        return $this->ok([]);
    }

    /**
     * PUT /api/salud360/config/primer-control  {medico_id, dias: [{dia, cantidad}]}
     */
    public function primerControl(Request $request)
    {
        $medico = $this->resolverMedico($request);
        if ($medico === null) {
            return $this->sinPermisoMedico();
        }
        $dias = $request->input('dias', []);
        if (!is_array($dias)) {
            return $this->error('dias debe ser una lista de {dia, cantidad}.', 422, 'datos');
        }
        $consultorio = $this->consultorioDe($request, $medico);
        foreach ($dias as $item) {
            $dia = isset($item['dia']) ? (int) $item['dia'] : 0;
            $cantidad = isset($item['cantidad']) ? (int) $item['cantidad'] : 0;
            if ($dia < 1 || $dia > 7 || $cantidad < 0) {
                return $this->error('Día o cantidad inválidos.', 422, 'datos');
            }
            $existe = DB::table('medico_primer_controls')
                ->where('medico', $medico->id)->where('dia', $dia)->where('consultorio', $consultorio)->where('activo', 1)
                ->first();
            if ($existe !== null) {
                $reg = MedicoPrimerControl::find($existe->id);
            } else {
                $reg = new MedicoPrimerControl();
                $reg->medico = $medico->id;
                $reg->dia = $dia;
                $reg->consultorio = $consultorio;
                $reg->activo = 1;
            }
            $reg->cantidadPrimerControl = $cantidad;
            $reg->save();
        }
        return $this->ok(['cupo_primer_control' => $this->agenda->cupoPrimerControl($medico->id)]);
    }

    /**
     * PUT /api/salud360/config/ventana-dias  {medico_id, dias}
     */
    public function ventanaDias(Request $request)
    {
        $medico = $this->resolverMedico($request);
        if ($medico === null) {
            return $this->sinPermisoMedico();
        }
        $dias = (int) $request->input('dias');
        if ($dias < 1 || $dias > 730) {
            return $this->error('dias debe estar entre 1 y 730.', 422, 'datos');
        }
        $existe = DB::table('medico_configs')
            ->where('medico', $medico->id)->where('modulo', AgendaService::MODULO_VENTANA_DIAS)->where('activo', 1)
            ->first();
        if ($existe !== null) {
            $cfg = MedicoConfig::find($existe->id);
        } else {
            $cfg = new MedicoConfig();
            $cfg->medico = $medico->id;
            $cfg->modulo = AgendaService::MODULO_VENTANA_DIAS;
            if (Schema::hasColumn('medico_configs', 'valor_consulta')) {
                $cfg->valor_consulta = 0;
            }
            $cfg->activo = 1;
        }
        $cfg->valor_string = (string) $dias;
        $cfg->valor_integer = $dias;
        $cfg->save();
        return $this->ok(['ventana_dias' => $dias]);
    }
}
