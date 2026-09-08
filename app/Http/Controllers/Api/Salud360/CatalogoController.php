<?php

namespace App\Http\Controllers\Api\Salud360;

use App\Feriado;
use App\ObraSocialMedico;
use App\Util\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogos compartidos (consultorios, especialidades, obras sociales, feriados, módulos, tipos de turno),
 * obras sociales del médico y mensajes especiales.
 */
class CatalogoController extends Salud360Controller
{
    /**
     * GET /api/salud360/catalogos
     */
    public function index(Request $request)
    {
        $hoy = $this->agenda->hoy();
        $consultorios = DB::table('consultorios')->where('activo', 1)->orderBy('nombre')->get()
            ->map(function ($c) {
                return ['id' => (int) $c->id, 'nombre' => $c->nombre, 'direccion' => $c->direccion, 'telefono' => $c->telefono, 'foto' => $c->foto];
            })->values()->all();
        $especialidades = DB::table('especialidads')->where('activo', 1)->orderBy('nombre')->get()
            ->map(function ($e) {
                return ['id' => (int) $e->id, 'nombre' => $e->nombre, 'color' => isset($e->color) ? $e->color : null];
            })->values()->all();
        $obrasSociales = DB::table('obra_socials')->where('activo', 1)->orderBy('nombre')->get()
            ->map(function ($o) { return ['id' => (int) $o->id, 'nombre' => $o->nombre]; })->values()->all();
        $feriados = DB::table('feriados')
            ->where('fecha', '>=', date('Y-m-d', strtotime('-60 days', strtotime($hoy))))
            ->orderBy('fecha')->get()
            ->map(function ($f) { return ['id' => (int) $f->id, 'fecha' => $f->fecha, 'descripcion' => $f->descripcion]; })->values()->all();
        $modulos = DB::table('modulos')->where('activo', 1)->orderBy('id')->get()
            ->map(function ($m) { return ['id' => (int) $m->id, 'descripcion' => $m->descripcion]; })->values()->all();
        $recetaEstados = Schema::hasTable('receta_estados')
            ? DB::table('receta_estados')->where('activo', 1)->orderBy('id')->get()
                ->map(function ($r) { return ['id' => (int) $r->id, 'descripcion' => isset($r->descripcion) ? $r->descripcion : (isset($r->nombre) ? $r->nombre : null)]; })->values()->all()
            : [];
        $tiposTurno = [];
        foreach ([1, 2, 22, 23, 24, 25] as $codigo) {
            $tiposTurno[] = ['codigo' => $codigo, 'nombre' => Util::getTipoTurno($codigo)];
        }
        $medicos = DB::table('medicos')->where('activo', 1)->orderBy('apellido')->get();
        $listaMedicos = [];
        foreach ($medicos as $m) {
            $listaMedicos[] = $this->formatearMedico($m);
        }

        return $this->ok([
            'hoy' => $hoy,
            'consultorios' => $consultorios,
            'especialidades' => $especialidades,
            'medicos' => $listaMedicos,
            'obras_sociales' => $obrasSociales,
            'feriados' => $feriados,
            'modulos' => $modulos,
            'receta_estados' => $recetaEstados,
            'tipos_turno' => $tiposTurno,
        ]);
    }

    /**
     * GET /api/salud360/obras-sociales?medico_id
     * Obras sociales del médico con estado e importe (tabla obra_social_medicos).
     */
    public function obrasSocialesMedico(Request $request)
    {
        $medico = $this->resolverMedico($request);
        if ($medico === null) {
            return $this->sinPermisoMedico();
        }
        $tieneReserva = Schema::hasColumn('obra_social_medicos', 'importe_reserva');
        $filas = DB::table('obra_social_medicos')
            ->join('obra_socials', 'obra_socials.id', '=', 'obra_social_medicos.obra_social')
            ->select('obra_social_medicos.*', 'obra_socials.nombre')
            ->where('obra_social_medicos.medico', $medico->id)
            ->orderBy('obra_socials.nombre')
            ->get()
            ->map(function ($o) use ($tieneReserva) {
                return [
                    'id' => (int) $o->id,
                    'obra_social_id' => (int) $o->obra_social,
                    'nombre' => $o->nombre,
                    'importe' => isset($o->importe) ? (float) $o->importe : 0,
                    'importe_reserva' => $tieneReserva && $o->importe_reserva !== null ? (float) $o->importe_reserva : null,
                    'activo' => (int) $o->activo,
                ];
            })->values()->all();
        return $this->ok(['obras_sociales' => $filas]);
    }

    /**
     * POST /api/salud360/obras-sociales  {medico_id, obra_social_id, [activo], [importe]}
     * Crea o actualiza la relación médico–obra social.
     */
    public function guardarObraSocialMedico(Request $request)
    {
        $medico = $this->resolverMedico($request);
        if ($medico === null) {
            return $this->sinPermisoMedico();
        }
        $osId = (int) $request->input('obra_social_id');
        if (!DB::table('obra_socials')->where('id', $osId)->exists()) {
            return $this->error('Obra social no encontrada.', 404, 'no_encontrado');
        }
        $existe = DB::table('obra_social_medicos')->where('medico', $medico->id)->where('obra_social', $osId)->first();
        if ($existe !== null) {
            $osm = ObraSocialMedico::find($existe->id);
        } else {
            $osm = new ObraSocialMedico();
            $osm->medico = $medico->id;
            $osm->obra_social = $osId;
            $osm->importe = 0;
            $osm->activo = 1;
        }
        if ($request->has('activo')) {
            $osm->activo = (int) $request->input('activo') === 1 ? 1 : 0;
        }
        if ($request->has('importe')) {
            $osm->importe = floatval($request->input('importe'));
        }
        $osm->save();
        return $this->ok(['id' => (int) $osm->id]);
    }

    /**
     * GET /api/salud360/mensajes?medico_id  — mensajes especiales vigentes del médico.
     */
    public function mensajes(Request $request)
    {
        $medico = $this->resolverMedico($request);
        if ($medico === null) {
            return $this->sinPermisoMedico();
        }
        if (!Schema::hasTable('medico_mensajes_especiales')) {
            return $this->ok(['mensajes' => []]);
        }
        $mensajes = DB::table('medico_mensajes_especiales')
            ->where('medico_id', $medico->id)
            ->orderByDesc('activo')
            ->orderByDesc('id')
            ->get()
            ->map(function ($m) {
                return [
                    'id' => (int) $m->id,
                    'titulo' => $m->titulo,
                    'descripcion' => $m->descripcion,
                    'valido_desde' => $m->valido_desde,
                    'valido_hasta' => $m->valido_hasta,
                    'activo' => (int) $m->activo,
                ];
            })->values()->all();
        return $this->ok(['mensajes' => $mensajes]);
    }

    /**
     * POST /api/salud360/feriados  {fecha, descripcion}  — solo administrador.
     */
    public function guardarFeriado(Request $request)
    {
        if (!$this->esAdmin($request->user())) {
            return $this->error('Solo el administrador puede cargar feriados.', 403, 'sin_permiso');
        }
        $fecha = $this->fecha($request);
        if ($fecha === null) {
            return $this->error('Fecha inválida (usar AAAA-MM-DD).', 422, 'fecha');
        }
        if (DB::table('feriados')->where('fecha', $fecha)->exists()) {
            return $this->error('Ya existe un feriado en esa fecha.', 409, 'duplicado');
        }
        $f = new Feriado();
        $f->fecha = $fecha;
        $f->descripcion = (string) $request->input('descripcion', '');
        $f->save();
        return $this->ok(['id' => (int) $f->id], 201);
    }

    /**
     * DELETE /api/salud360/feriados/{id}  — solo administrador.
     */
    public function borrarFeriado(Request $request, $id)
    {
        if (!$this->esAdmin($request->user())) {
            return $this->error('Solo el administrador puede borrar feriados.', 403, 'sin_permiso');
        }
        $borrados = DB::table('feriados')->where('id', (int) $id)->delete();
        if ($borrados === 0) {
            return $this->error('Feriado no encontrado.', 404, 'no_encontrado');
        }
        return $this->ok([]);
    }
}
