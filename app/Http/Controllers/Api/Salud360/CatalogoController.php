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
     * GET /api/salud360/medicos/{id}/foto
     * Devuelve la foto del médico (public/images/medicos). Sin token: es la misma imagen pública de la web de turnos,
     * pero servida por Laravel para que la app web pueda pedirla con CORS. 404 si no tiene foto.
     */
    public function fotoMedico($id)
    {
        $m = DB::table('medicos')->where('id', (int) $id)->first();
        $foto = $m !== null ? trim((string) $m->foto) : '';
        if ($foto === '' || $foto === 'medico_sin_foto.png' || strpos($foto, '..') !== false || strpos($foto, '/') !== false) {
            return $this->error('El médico no tiene foto.', 404, 'sin_foto');
        }
        // En el hosting la app vive en TurnosImage/ y las imágenes en el document root (public_html), así que
        // public_path() no alcanza: se prueban varias carpetas y, si no está en disco, se trae por su URL pública.
        $relativa = 'images/medicos/' . $foto;
        $candidatas = [public_path($relativa)];
        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            $candidatas[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . $relativa;
        }
        $candidatas[] = base_path('../public_html/' . $relativa);
        foreach ($candidatas as $ruta) {
            if (is_file($ruta)) {
                return response()->file($ruta, ['Cache-Control' => 'public, max-age=86400']);
            }
        }
        $contenido = @file_get_contents(asset($relativa));
        if ($contenido === false || $contenido === '') {
            return $this->error('El médico no tiene foto.', 404, 'sin_foto');
        }
        $tipo = 'image/jpeg';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $detectado = $finfo ? finfo_buffer($finfo, $contenido) : false;
            if ($detectado) {
                $tipo = $detectado;
            }
        } elseif (preg_match('/\.png$/i', $foto)) {
            $tipo = 'image/png';
        }
        return response($contenido, 200, ['Content-Type' => $tipo, 'Cache-Control' => 'public, max-age=86400']);
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
     * POST /api/salud360/mensajes  {medico_id, titulo, descripcion, [valido_desde], [valido_hasta], [activo=1]}
     * Crea un mensaje especial del médico (misma tabla que la pantalla "mensajes" de la web).
     */
    public function guardarMensaje(Request $request)
    {
        $medico = $this->resolverMedico($request);
        if ($medico === null) {
            return $this->sinPermisoMedico();
        }
        if (!Schema::hasTable('medico_mensajes_especiales')) {
            return $this->error('Los mensajes especiales no están disponibles en esta instalación.', 422, 'no_disponible');
        }
        $titulo = trim((string) $request->input('titulo'));
        $descripcion = trim((string) $request->input('descripcion'));
        if ($titulo === '' || mb_strlen($titulo) > 255 || $descripcion === '') {
            return $this->error('Indicá titulo (hasta 255 caracteres) y descripcion.', 422, 'datos');
        }
        $desde = $this->agenda->normalizarFecha($request->input('valido_desde'));
        $hasta = $this->agenda->normalizarFecha($request->input('valido_hasta'));
        if ($desde !== null && $hasta !== null && $hasta < $desde) {
            return $this->error('La fecha "válido hasta" no puede ser anterior a "válido desde".', 422, 'datos');
        }
        $id = DB::table('medico_mensajes_especiales')->insertGetId([
            'medico_id' => $medico->id,
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'valido_desde' => $desde,
            'valido_hasta' => $hasta,
            'activo' => $request->has('activo') ? ((int) $request->input('activo') === 1 ? 1 : 0) : 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return $this->ok(['id' => (int) $id], 201);
    }

    /**
     * PUT /api/salud360/mensajes/{id}  {[titulo], [descripcion], [valido_desde], [valido_hasta], [activo]}
     * Actualiza solo los campos enviados (valido_desde/valido_hasta en null o "" borran la fecha).
     */
    public function actualizarMensaje(Request $request, $id)
    {
        if (!Schema::hasTable('medico_mensajes_especiales')) {
            return $this->error('Los mensajes especiales no están disponibles en esta instalación.', 422, 'no_disponible');
        }
        $reg = DB::table('medico_mensajes_especiales')->where('id', (int) $id)->first();
        if ($reg === null) {
            return $this->error('Mensaje no encontrado.', 404, 'no_encontrado');
        }
        if (!$this->puedeGestionarMedico($request->user(), $reg->medico_id)) {
            return $this->sinPermisoMedico();
        }
        $upd = [];
        if ($request->has('titulo')) {
            $titulo = trim((string) $request->input('titulo'));
            if ($titulo === '' || mb_strlen($titulo) > 255) {
                return $this->error('titulo no puede estar vacío (hasta 255 caracteres).', 422, 'datos');
            }
            $upd['titulo'] = $titulo;
        }
        if ($request->has('descripcion')) {
            $descripcion = trim((string) $request->input('descripcion'));
            if ($descripcion === '') {
                return $this->error('descripcion no puede estar vacía.', 422, 'datos');
            }
            $upd['descripcion'] = $descripcion;
        }
        if ($request->has('valido_desde')) {
            $upd['valido_desde'] = $this->agenda->normalizarFecha($request->input('valido_desde'));
        }
        if ($request->has('valido_hasta')) {
            $upd['valido_hasta'] = $this->agenda->normalizarFecha($request->input('valido_hasta'));
        }
        if ($request->has('activo')) {
            $upd['activo'] = (int) $request->input('activo') === 1 ? 1 : 0;
        }
        if (count($upd) === 0) {
            return $this->error('Indicá al menos un campo para actualizar.', 422, 'datos');
        }
        $desde = array_key_exists('valido_desde', $upd) ? $upd['valido_desde'] : $reg->valido_desde;
        $hasta = array_key_exists('valido_hasta', $upd) ? $upd['valido_hasta'] : $reg->valido_hasta;
        if ($desde !== null && $hasta !== null && $hasta < $desde) {
            return $this->error('La fecha "válido hasta" no puede ser anterior a "válido desde".', 422, 'datos');
        }
        $upd['updated_at'] = now();
        DB::table('medico_mensajes_especiales')->where('id', $reg->id)->update($upd);
        return $this->ok([]);
    }

    /**
     * DELETE /api/salud360/mensajes/{id}
     */
    public function borrarMensaje(Request $request, $id)
    {
        if (!Schema::hasTable('medico_mensajes_especiales')) {
            return $this->error('Los mensajes especiales no están disponibles en esta instalación.', 422, 'no_disponible');
        }
        $reg = DB::table('medico_mensajes_especiales')->where('id', (int) $id)->first();
        if ($reg === null) {
            return $this->error('Mensaje no encontrado.', 404, 'no_encontrado');
        }
        if (!$this->puedeGestionarMedico($request->user(), $reg->medico_id)) {
            return $this->sinPermisoMedico();
        }
        DB::table('medico_mensajes_especiales')->where('id', $reg->id)->delete();
        return $this->ok([]);
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
