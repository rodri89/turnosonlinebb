<?php

namespace App\Http\Controllers\Api\Salud360;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SecretariaController;
use App\Services\Salud360\AgendaService;
use App\Services\Salud360\HistoriaClinicaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Base de los controladores de la API consumida por la app Salud 360.
 *
 * Resuelve el rol del usuario autenticado (Passport, guard `api`) y sobre qué médico
 * puede operar: el médico sobre sí mismo, la secretaria sobre los médicos de sus
 * consultorios y el administrador sobre cualquiera.
 */
abstract class Salud360Controller extends Controller
{
    const TIPO_ADMIN = 1;
    const TIPO_MEDICO = 2;
    const TIPO_SECRETARIA = 3;

    /** @var AgendaService */
    protected $agenda;

    public function __construct(AgendaService $agenda)
    {
        $this->agenda = $agenda;
    }

    // ------------------------------------------------------------------
    // Respuestas
    // ------------------------------------------------------------------

    protected function ok(array $data = [], $status = 200)
    {
        return response()->json(array_merge(['ok' => true], $data), $status);
    }

    protected function error($mensaje, $status = 422, $codigo = null, array $extra = [])
    {
        $body = ['ok' => false, 'mensaje' => $mensaje];
        if ($codigo !== null) {
            $body['codigo'] = $codigo;
        }
        return response()->json(array_merge($body, $extra), $status);
    }

    // ------------------------------------------------------------------
    // Usuario y permisos
    // ------------------------------------------------------------------

    protected function rol($user)
    {
        switch ((int) $user->usuario_tipo) {
            case self::TIPO_ADMIN:
                return 'admin';
            case self::TIPO_MEDICO:
                return 'medico';
            case self::TIPO_SECRETARIA:
                return 'secretaria';
            default:
                return 'desconocido';
        }
    }

    protected function esAdmin($user)
    {
        return (int) $user->usuario_tipo === self::TIPO_ADMIN;
    }

    /** Médico asociado al usuario (solo para usuario_tipo 2). */
    protected function medicoPropio($user)
    {
        return DB::table('medicos')->where('user_id', $user->id)->where('activo', 1)->first();
    }

    /** Secretaria asociada al usuario (solo para usuario_tipo 3). */
    protected function secretariaPropia($user)
    {
        return DB::table('secretarias')->where('user_id', $user->id)->first();
    }

    /** Consultorios activos de la secretaria. */
    protected function consultoriosDeSecretaria($secretariaId)
    {
        return DB::table('secretaria_consultorios')
            ->join('consultorios', 'consultorios.id', '=', 'secretaria_consultorios.consultorio_id')
            ->select('consultorios.id', 'consultorios.nombre', 'consultorios.direccion', 'consultorios.telefono')
            ->where('secretaria_consultorios.secretaria_id', $secretariaId)
            ->where('secretaria_consultorios.activo', 1)
            ->where('consultorios.activo', 1)
            ->get();
    }

    /**
     * ¿El usuario puede operar sobre ese médico?
     */
    protected function puedeGestionarMedico($user, $medicoId)
    {
        $medicoId = (int) $medicoId;
        if ($medicoId < 1) {
            return false;
        }
        switch ((int) $user->usuario_tipo) {
            case self::TIPO_ADMIN:
                return DB::table('medicos')->where('id', $medicoId)->where('activo', 1)->exists();
            case self::TIPO_MEDICO:
                $propio = $this->medicoPropio($user);
                return $propio !== null && (int) $propio->id === $medicoId;
            case self::TIPO_SECRETARIA:
                return SecretariaController::puedeGestionarMedicoPorId($user, $medicoId);
            default:
                return false;
        }
    }

    /**
     * Devuelve el médico sobre el que opera el pedido (`medico_id`), o el propio si el
     * usuario es médico y no lo indicó. Devuelve null si no tiene permiso.
     */
    protected function resolverMedico(Request $request, $medicoId = null)
    {
        $user = $request->user();
        if ($medicoId === null) {
            $medicoId = $request->input('medico_id');
        }
        if (($medicoId === null || $medicoId === '') && (int) $user->usuario_tipo === self::TIPO_MEDICO) {
            return $this->medicoPropio($user);
        }
        if (!$this->puedeGestionarMedico($user, $medicoId)) {
            return null;
        }
        return DB::table('medicos')->where('id', (int) $medicoId)->where('activo', 1)->first();
    }

    /** Respuesta estándar cuando `resolverMedico` devuelve null. */
    protected function sinPermisoMedico()
    {
        return $this->error('No tiene permiso para operar sobre ese médico.', 403, 'sin_permiso');
    }

    /** Consultorio del pedido: `consultorio_id` o el del médico. */
    protected function consultorioDe(Request $request, $medico)
    {
        $c = $request->input('consultorio_id');
        return ($c === null || $c === '') ? (int) $medico->consultorio : (int) $c;
    }

    /** Nombre con el que se firma un cambio (comentarios de cancelación, otorgado_por). */
    protected function firmaUsuario($user)
    {
        if ((int) $user->usuario_tipo === self::TIPO_MEDICO) {
            $m = $this->medicoPropio($user);
            if ($m !== null) {
                return $m->apellido . ', ' . $m->nombre;
            }
        }
        if ((int) $user->usuario_tipo === self::TIPO_SECRETARIA) {
            $s = $this->secretariaPropia($user);
            if ($s !== null) {
                return $s->apellido . ', ' . $s->nombre;
            }
        }
        return (string) $user->name;
    }

    /** Fecha del pedido normalizada a Y-m-d, o null si es inválida. */
    protected function fecha(Request $request, $campo = 'fecha')
    {
        return $this->agenda->normalizarFecha($request->input($campo));
    }

    protected function horarioValido($horario)
    {
        return is_string($horario) && preg_match('/^\d{2}:\d{2}$/', $horario) === 1;
    }

    protected function formatearMedico($m)
    {
        $especialidad = DB::table('especialidads')->where('id', $m->especialidad)->first();
        return [
            'id' => (int) $m->id,
            'nombre' => $m->nombre,
            'apellido' => $m->apellido,
            'mail' => $m->mail,
            'telefono' => $m->telefono,
            'sexo' => isset($m->sexo) ? $m->sexo : null,
            'foto' => $m->foto,
            'especialidad_id' => (int) $m->especialidad,
            'especialidad' => $especialidad !== null ? $especialidad->nombre : null,
            'consultorio_id' => (int) $m->consultorio,
            'activo' => (int) $m->activo,
            // Historias clínicas de Salud 360 habilitadas por el administrador (tabla salud360_medico_hc).
            'historias_clinicas' => app(HistoriaClinicaService::class)->habilitadas($m->id),
        ];
    }

    protected function formatearPaciente($p)
    {
        return [
            'id' => (int) $p->id,
            'nombre' => $p->nombre,
            'apellido' => $p->apellido,
            'dni' => $p->dni,
            'telefono' => $p->telefono,
            'domicilio' => $p->domicilio,
            'localidad' => $p->localidad,
            'mail' => $p->mail,
            'nota' => $p->nota,
            'fecha_nacimiento' => ($p->fecha_nacimiento && substr((string) $p->fecha_nacimiento, 0, 4) !== '1000') ? substr((string) $p->fecha_nacimiento, 0, 10) : null,
            'obra_social' => $p->obra_social,
            'numero_afiliado' => $p->numero_afiliado,
            'obra_social_plan' => $p->obra_social_plan,
            'afiliado_obligatorio' => (int) $p->afiliado_obligatorio,
            'activo' => (int) $p->activo,
            'created_at' => $p->created_at,
            'updated_at' => $p->updated_at,
        ];
    }
}
