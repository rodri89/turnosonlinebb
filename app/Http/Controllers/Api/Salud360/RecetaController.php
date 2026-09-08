<?php

namespace App\Http\Controllers\Api\Salud360;

use App\Receta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pedidos de recetas que los pacientes cargan desde la app y el consultorio gestiona.
 */
class RecetaController extends Salud360Controller
{
    /**
     * GET /api/salud360/recetas?medico_id[&estado][&solo_pendientes=1][&actualizado_desde][&limite]
     * Recetas del consultorio del médico (misma vista que la web: por consultorio).
     */
    public function index(Request $request)
    {
        $medico = $this->resolverMedico($request);
        if ($medico === null) {
            return $this->sinPermisoMedico();
        }
        $consultorio = $this->consultorioDe($request, $medico);
        $q = DB::table('recetas')
            ->join('pacientes', 'pacientes.id', '=', 'recetas.paciente')
            ->leftJoin('receta_estados', 'recetas.estado', '=', 'receta_estados.id')
            ->select(
                'recetas.*',
                'pacientes.nombre as paciente_nombre',
                'pacientes.apellido as paciente_apellido',
                'pacientes.dni as paciente_dni',
                'pacientes.telefono as paciente_telefono',
                'pacientes.mail as paciente_mail',
                'receta_estados.descripcion as estado_descripcion'
            )
            ->where('recetas.consultorio', $consultorio)
            ->where('recetas.activo', 1);
        if ($request->has('estado')) {
            $q->where('recetas.estado', (int) $request->input('estado'));
        } elseif ((int) $request->input('solo_pendientes', 0) === 1) {
            $q->where('recetas.estado', '!=', 5);
        }
        if ($request->input('actualizado_desde')) {
            $q->where('recetas.updated_at', '>=', $request->input('actualizado_desde'));
        }
        $limite = max(1, min(1000, (int) $request->input('limite', 300)));
        $filas = $q->orderBy('recetas.created_at', 'desc')->limit($limite)->get();
        $recetas = [];
        foreach ($filas as $r) {
            $recetas[] = $this->formatearReceta($r);
        }
        return $this->ok(['recetas' => $recetas]);
    }

    /**
     * GET /api/salud360/recetas/{id}
     */
    public function show(Request $request, $id)
    {
        $r = DB::table('recetas')
            ->join('pacientes', 'pacientes.id', '=', 'recetas.paciente')
            ->leftJoin('receta_estados', 'recetas.estado', '=', 'receta_estados.id')
            ->select('recetas.*', 'pacientes.nombre as paciente_nombre', 'pacientes.apellido as paciente_apellido',
                'pacientes.dni as paciente_dni', 'pacientes.telefono as paciente_telefono', 'pacientes.mail as paciente_mail',
                'receta_estados.descripcion as estado_descripcion')
            ->where('recetas.id', (int) $id)
            ->first();
        if ($r === null) {
            return $this->error('Receta no encontrada.', 404, 'no_encontrado');
        }
        if (!$this->puedeGestionarMedico($request->user(), $r->medico)) {
            return $this->sinPermisoMedico();
        }
        $item = $this->formatearReceta($r);
        $item['fotos'] = $this->fotos($r->id);
        return $this->ok(['receta' => $item]);
    }

    /**
     * POST /api/salud360/recetas/{id}/estado  {estado_id, [comentario], [motivo_rechazo]}
     * Misma regla que RecetaController@actualizarEstadoReceta.
     */
    public function estado(Request $request, $id)
    {
        $receta = Receta::find((int) $id);
        if ($receta === null) {
            return $this->error('Receta no encontrada.', 404, 'no_encontrado');
        }
        if (!$this->puedeGestionarMedico($request->user(), $receta->medico)) {
            return $this->sinPermisoMedico();
        }
        $estadoId = (int) $request->input('estado_id');
        if ($estadoId < 1) {
            return $this->error('estado_id inválido.', 422, 'datos');
        }
        $comentario = $request->input('comentario');
        $receta->estado = $estadoId;
        $receta->comentario = ($comentario === null) ? '' : (string) $comentario;
        if ($estadoId === 4) { // rechazada
            $motivo = (string) $request->input('motivo_rechazo', '');
            $receta->motivo = $receta->motivo . '||Motivo Rechazo:|' . $motivo;
        }
        $receta->save();
        return $this->show($request, $receta->id);
    }

    private function fotos($recetaId)
    {
        if (!Schema::hasTable('paciente_recetas')) {
            return [];
        }
        return DB::table('paciente_recetas')
            ->where('receta', $recetaId)
            ->where('activo', 1)
            ->get()
            ->map(function ($f) {
                $ruta = (string) $f->foto;
                $url = (strpos($ruta, 'http') === 0) ? $ruta : asset('storage/' . ltrim($ruta, '/'));
                return ['id' => (int) $f->id, 'url' => $url];
            })->values()->all();
    }

    private function formatearReceta($r)
    {
        return [
            'id' => (int) $r->id,
            'paciente_id' => (int) $r->paciente,
            'medico_id' => (int) $r->medico,
            'consultorio_id' => (int) $r->consultorio,
            'motivo' => $r->motivo,
            'estado' => (int) $r->estado,
            'estado_descripcion' => isset($r->estado_descripcion) ? $r->estado_descripcion : null,
            'retira_consultorio' => (int) $r->retira_consultorio,
            'comentario' => (string) $r->comentario,
            'foto' => $r->foto,
            'paciente' => [
                'id' => (int) $r->paciente,
                'nombre' => $r->paciente_nombre,
                'apellido' => $r->paciente_apellido,
                'dni' => $r->paciente_dni,
                'telefono' => $r->paciente_telefono,
                'mail' => $r->paciente_mail,
            ],
            'created_at' => $r->created_at,
            'updated_at' => $r->updated_at,
        ];
    }
}
