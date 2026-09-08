<?php

namespace App\Http\Controllers\Api\Salud360;

use App\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Pacientes del consultorio / del médico. Misma tabla `pacientes` que usa la web y la app de pacientes.
 */
class PacienteController extends Salud360Controller
{
    /**
     * GET /api/salud360/pacientes?medico_id[&actualizado_desde][&desde_id][&limite]
     * Pacientes vinculados al médico (medico_pacientes) o al consultorio (paciente_secretarias).
     * Paginado por id ascendente para sincronización.
     */
    public function index(Request $request)
    {
        $medico = $this->resolverMedico($request);
        if ($medico === null) {
            return $this->sinPermisoMedico();
        }
        $consultorio = $this->consultorioDe($request, $medico);
        $limite = max(1, min(1000, (int) $request->input('limite', 500)));
        $desdeId = (int) $request->input('desde_id', 0);
        $actualizadoDesde = $request->input('actualizado_desde');

        $q = DB::table('pacientes')
            ->where('pacientes.id', '>', $desdeId)
            ->where(function ($w) use ($medico, $consultorio) {
                $w->whereExists(function ($s) use ($medico) {
                    $s->select(DB::raw(1))->from('medico_pacientes')
                        ->whereRaw('medico_pacientes.paciente = pacientes.id')
                        ->where('medico_pacientes.medico', $medico->id);
                })->orWhereExists(function ($s) use ($consultorio) {
                    $s->select(DB::raw(1))->from('paciente_secretarias')
                        ->whereRaw('paciente_secretarias.paciente = pacientes.id')
                        ->where('paciente_secretarias.consultorio', $consultorio)
                        ->where('paciente_secretarias.activo', 1);
                });
            });
        if ($actualizadoDesde) {
            $q->where('pacientes.updated_at', '>=', $actualizadoDesde);
        }
        $filas = $q->orderBy('pacientes.id')->limit($limite)->get();
        $pacientes = [];
        foreach ($filas as $p) {
            $pacientes[] = $this->formatearPaciente($p);
        }
        $ultimoId = count($filas) > 0 ? (int) $filas->last()->id : $desdeId;
        return $this->ok(['pacientes' => $pacientes, 'ultimo_id' => $ultimoId, 'hay_mas' => count($filas) === $limite]);
    }

    /**
     * GET /api/salud360/pacientes/buscar?q=  (DNI, apellido o nombre)
     */
    public function buscar(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        if (strlen($q) < 2) {
            return $this->error('Ingresá al menos 2 caracteres.', 422, 'datos');
        }
        $consulta = DB::table('pacientes');
        if (ctype_digit($q)) {
            $consulta->where('dni', 'like', $q . '%');
        } else {
            $consulta->where(function ($w) use ($q) {
                $w->where('apellido', 'like', '%' . $q . '%')
                    ->orWhere('nombre', 'like', '%' . $q . '%')
                    ->orWhere('mail', 'like', '%' . $q . '%');
            });
        }
        $filas = $consulta->orderBy('apellido')->orderBy('nombre')->limit(50)->get();
        $pacientes = [];
        foreach ($filas as $p) {
            $pacientes[] = $this->formatearPaciente($p);
        }
        return $this->ok(['pacientes' => $pacientes]);
    }

    /**
     * GET /api/salud360/pacientes/{id}
     */
    public function show(Request $request, $id)
    {
        $p = DB::table('pacientes')->where('id', (int) $id)->first();
        if ($p === null) {
            return $this->error('Paciente no encontrado.', 404, 'no_encontrado');
        }
        $medicos = DB::table('medico_pacientes')
            ->where('paciente', $p->id)
            ->get()
            ->map(function ($mp) { return ['medico_id' => (int) $mp->medico, 'bloqueado' => (int) $mp->bloqueado]; })
            ->values()
            ->all();
        return $this->ok(['paciente' => $this->formatearPaciente($p), 'medicos' => $medicos]);
    }

    /**
     * POST /api/salud360/pacientes
     * {medico_id, dni, nombre, apellido, [telefono], [mail], [fecha_nacimiento], [domicilio], [localidad],
     *  [obra_social], [numero_afiliado], [obra_social_plan], [afiliado_obligatorio]}
     * Si el DNI ya existe, actualiza los datos enviados (misma regla que la web) y lo vincula al médico/consultorio.
     */
    public function store(Request $request)
    {
        $medico = $this->resolverMedico($request);
        if ($medico === null) {
            return $this->sinPermisoMedico();
        }
        $dni = trim((string) $request->input('dni', ''));
        if ($dni === '' || !ctype_digit($dni)) {
            return $this->error('DNI inválido.', 422, 'datos');
        }
        $existente = DB::table('pacientes')->where('dni', $dni)->first();
        $paciente = $existente === null ? new Paciente() : Paciente::find($existente->id);
        $nuevo = $existente === null;

        if ($nuevo) {
            $paciente->dni = $dni;
            $paciente->terminos_condiciones = 0;
            $paciente->fecha_castigo = '2000-01-01 00:00:00';
            $paciente->obra_social_foto = '';
            $paciente->activo = 1;
            foreach (['nombre', 'apellido', 'telefono', 'domicilio', 'localidad', 'mail', 'obra_social', 'numero_afiliado', 'obra_social_plan'] as $campo) {
                $paciente->$campo = '';
            }
            $paciente->fecha_nacimiento = '1000-01-01 00:00:00';
            $paciente->afiliado_obligatorio = 0;
            foreach (['one_signal_id', 'fcm_token', 'google_calendar_access_token', 'google_calendar_refresh_token'] as $campo) {
                if (\Schema::hasColumn('pacientes', $campo)) {
                    $paciente->$campo = '';
                }
            }
        }
        $this->aplicarCampos($paciente, $request);
        if ($nuevo && (trim((string) $paciente->nombre) === '' || trim((string) $paciente->apellido) === '')) {
            return $this->error('Nombre y apellido son obligatorios.', 422, 'datos');
        }
        $paciente->save();

        $consultorio = $this->consultorioDe($request, $medico);
        $this->agenda->vincularMedicoPaciente($medico->id, $paciente->id);
        $this->agenda->vincularSecretariaPaciente($paciente->id, $consultorio);

        $p = DB::table('pacientes')->where('id', $paciente->id)->first();
        return $this->ok(['paciente' => $this->formatearPaciente($p), 'creado' => $nuevo], $nuevo ? 201 : 200);
    }

    /**
     * PUT /api/salud360/pacientes/{id}
     */
    public function update(Request $request, $id)
    {
        $paciente = Paciente::find((int) $id);
        if ($paciente === null) {
            return $this->error('Paciente no encontrado.', 404, 'no_encontrado');
        }
        if ($request->has('dni')) {
            $dni = trim((string) $request->input('dni'));
            if ($dni === '' || !ctype_digit($dni)) {
                return $this->error('DNI inválido.', 422, 'datos');
            }
            $otro = DB::table('pacientes')->where('dni', $dni)->where('id', '!=', $paciente->id)->exists();
            if ($otro) {
                return $this->error('Ya existe otro paciente con ese DNI.', 409, 'dni_duplicado');
            }
            $paciente->dni = $dni;
        }
        $this->aplicarCampos($paciente, $request);
        $paciente->save();
        $p = DB::table('pacientes')->where('id', $paciente->id)->first();
        return $this->ok(['paciente' => $this->formatearPaciente($p)]);
    }

    /**
     * GET /api/salud360/pacientes/pendientes?medico_id
     * Pacientes que se registraron desde la app y esperan activación (activo = 2) en el consultorio.
     */
    public function pendientes(Request $request)
    {
        $medico = $this->resolverMedico($request);
        if ($medico === null) {
            return $this->sinPermisoMedico();
        }
        $consultorio = $this->consultorioDe($request, $medico);
        $filas = DB::table('paciente_secretarias')
            ->join('pacientes', 'pacientes.id', '=', 'paciente_secretarias.paciente')
            ->select('pacientes.*', 'paciente_secretarias.id as paciente_secretaria_id')
            ->where('paciente_secretarias.consultorio', $consultorio)
            ->where('paciente_secretarias.activo', 0)
            ->where('pacientes.activo', 2)
            ->orderBy('pacientes.apellido')
            ->get();
        $pacientes = [];
        foreach ($filas as $p) {
            $item = $this->formatearPaciente($p);
            $item['paciente_secretaria_id'] = (int) $p->paciente_secretaria_id;
            $pacientes[] = $item;
        }
        return $this->ok(['pacientes' => $pacientes]);
    }

    /**
     * POST /api/salud360/pacientes/{id}/activar  {medico_id}
     */
    public function activar(Request $request, $id)
    {
        $medico = $this->resolverMedico($request);
        if ($medico === null) {
            return $this->sinPermisoMedico();
        }
        $paciente = Paciente::find((int) $id);
        if ($paciente === null) {
            return $this->error('Paciente no encontrado.', 404, 'no_encontrado');
        }
        $consultorio = $this->consultorioDe($request, $medico);
        $paciente->activo = 1;
        $paciente->save();
        DB::table('paciente_secretarias')
            ->where('paciente', $paciente->id)
            ->where('consultorio', $consultorio)
            ->update(['activo' => 1]);
        $this->agenda->vincularMedicoPaciente($medico->id, $paciente->id);
        $this->agenda->vincularSecretariaPaciente($paciente->id, $consultorio);

        if ($paciente->mail) {
            try {
                \Mail::to($paciente->mail)->queue(new \App\Mail\ActivarPacienteMailable(['nombre' => $paciente->nombre, 'apellido' => $paciente->apellido]));
            } catch (\Exception $e) {
                \Log::warning('Salud360: no se pudo enviar el mail de activación: ' . $e->getMessage());
            }
        }
        $p = DB::table('pacientes')->where('id', $paciente->id)->first();
        return $this->ok(['paciente' => $this->formatearPaciente($p)]);
    }

    /**
     * POST /api/salud360/pacientes/{id}/bloqueo  {medico_id, bloqueado: 0|1}
     */
    public function bloqueo(Request $request, $id)
    {
        $medico = $this->resolverMedico($request);
        if ($medico === null) {
            return $this->sinPermisoMedico();
        }
        $pacienteId = (int) $id;
        if (!DB::table('pacientes')->where('id', $pacienteId)->exists()) {
            return $this->error('Paciente no encontrado.', 404, 'no_encontrado');
        }
        $this->agenda->vincularMedicoPaciente($medico->id, $pacienteId);
        DB::table('medico_pacientes')
            ->where('medico', $medico->id)
            ->where('paciente', $pacienteId)
            ->update(['bloqueado' => (int) $request->input('bloqueado', 0) === 1 ? 1 : 0]);
        return $this->ok([]);
    }

    private function aplicarCampos(Paciente $paciente, Request $request)
    {
        $texto = ['nombre', 'apellido', 'telefono', 'domicilio', 'localidad', 'mail', 'obra_social', 'numero_afiliado', 'obra_social_plan'];
        foreach ($texto as $campo) {
            if ($request->has($campo)) {
                $v = $request->input($campo);
                $paciente->$campo = $v === null ? '' : trim((string) $v);
            }
        }
        if ($request->has('fecha_nacimiento')) {
            $f = $this->agenda->normalizarFecha($request->input('fecha_nacimiento'));
            $paciente->fecha_nacimiento = $f === null ? '1000-01-01 00:00:00' : $f;
        }
        if ($request->has('afiliado_obligatorio')) {
            $paciente->afiliado_obligatorio = (int) $request->input('afiliado_obligatorio') === 1 ? 1 : 0;
        }
    }
}
