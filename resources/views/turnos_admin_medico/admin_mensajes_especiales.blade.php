@extends('turnos_admin_medico/turnos_admin_medico_plantilla')

@section('title', 'Config mensajes')

@section('contenedor')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h4 class="mb-1">Config mensajes</h4>
            <p class="text-muted mb-0 small">Los mensajes se muestran a los pacientes al pedir turno con vos. Podés definir vigencia por fechas (opcional); si las dejás vacías, el aviso aplica siempre mientras esté activo. Las bajadas de línea en el texto se respetan en el aviso.</p>
        </div>
    </div>

    @if(!empty($tablaInexistente) && $tablaInexistente)
        <div class="alert alert-warning">La tabla <code>medico_mensajes_especiales</code> no existe en esta base. Ejecutá <code>php artisan migrate</code> o el script SQL correspondiente.</div>
    @else
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <strong>{{ $mensajeEdit ? 'Editar mensaje' : 'Nuevo mensaje' }}</strong>
                    </div>
                    <div class="card-body">
                        @if($mensajeEdit)
                            <form method="POST" action="{{ route('medicoactualizarmensajespecial') }}">
                                @csrf
                                <input type="hidden" name="id" value="{{ $mensajeEdit->id }}">

                                <div class="form-group">
                                    <label for="titulo_e">Título</label>
                                    <input type="text" class="form-control" id="titulo_e" name="titulo" required maxlength="255" value="{{ old('titulo', $mensajeEdit->titulo) }}">
                                </div>
                                <div class="form-group">
                                    <label for="descripcion_e">Descripción</label>
                                    <textarea class="form-control" id="descripcion_e" name="descripcion" rows="4" required>{{ old('descripcion', $mensajeEdit->descripcion) }}</textarea>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="vd_e">Válido desde (opc.)</label>
                                        <input type="date" class="form-control" id="vd_e" name="valido_desde" value="{{ old('valido_desde', $mensajeEdit->valido_desde) }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="vh_e">Válido hasta (opc.)</label>
                                        <input type="date" class="form-control" id="vh_e" name="valido_hasta" value="{{ old('valido_hasta', $mensajeEdit->valido_hasta) }}">
                                    </div>
                                </div>
                                <div class="form-group form-check">
                                    <input type="checkbox" class="form-check-input" id="activo_e" name="activo" value="1" {{ (int) old('activo', $mensajeEdit->activo) === 1 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="activo_e">Activo (visible para pacientes)</label>
                                </div>
                                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                                <a href="{{ route('medicoconfigmensajes') }}" class="btn btn-outline-secondary ml-2">Cancelar edición</a>
                            </form>
                        @else
                            <form method="POST" action="{{ route('medicoguardarmensajespecial') }}">
                                @csrf
                                <div class="form-group">
                                    <label for="titulo_n">Título</label>
                                    <input type="text" class="form-control" id="titulo_n" name="titulo" required maxlength="255" value="{{ old('titulo') }}">
                                </div>
                                <div class="form-group">
                                    <label for="descripcion_n">Descripción</label>
                                    <textarea class="form-control" id="descripcion_n" name="descripcion" rows="4" required>{{ old('descripcion') }}</textarea>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="vd_n">Válido desde (opc.)</label>
                                        <input type="date" class="form-control" id="vd_n" name="valido_desde" value="{{ old('valido_desde') }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="vh_n">Válido hasta (opc.)</label>
                                        <input type="date" class="form-control" id="vh_n" name="valido_hasta" value="{{ old('valido_hasta') }}">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Agregar mensaje</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <strong>Mis mensajes</strong>
                    </div>
                    <div class="card-body p-0">
                        @if($mensajes->isEmpty())
                            <p class="text-muted p-3 mb-0">Todavía no cargaste mensajes.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Título</th>
                                            <th>Estado</th>
                                            <th>Vigencia</th>
                                            <th class="text-right">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($mensajes as $m)
                                            <tr>
                                                <td>
                                                    <span class="font-weight-bold">{{ \Illuminate\Support\Str::limit($m->titulo, 40) }}</span>
                                                    <div class="small text-muted mt-1" style="white-space: pre-line; max-height: 7rem; overflow-y: auto;">{{ $m->descripcion }}</div>
                                                </td>
                                                <td>
                                                    @if($m->activo)
                                                        <span class="badge badge-success">Activo</span>
                                                    @else
                                                        <span class="badge badge-secondary">Inactivo</span>
                                                    @endif
                                                </td>
                                                <td class="small">
                                                    @if($m->valido_desde || $m->valido_hasta)
                                                        @if($m->valido_desde){{ \Carbon\Carbon::parse($m->valido_desde)->format('d/m/Y') }}@else…@endif
                                                        —
                                                        @if($m->valido_hasta){{ \Carbon\Carbon::parse($m->valido_hasta)->format('d/m/Y') }}@else…@endif
                                                    @else
                                                        <span class="text-muted">Siempre</span>
                                                    @endif
                                                </td>
                                                <td class="text-right text-nowrap">
                                                    <a href="{{ route('medicoconfigmensajes', ['edit' => $m->id]) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                                    @if($m->activo)
                                                        <form method="POST" action="{{ route('medicotoggleactivomensajespecial') }}" class="d-inline" onsubmit="return confirm('¿Desactivar este mensaje?');">
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $m->id }}">
                                                            <input type="hidden" name="activo" value="0">
                                                            <button type="submit" class="btn btn-sm btn-outline-warning">Desactivar</button>
                                                        </form>
                                                    @else
                                                        <form method="POST" action="{{ route('medicotoggleactivomensajespecial') }}" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $m->id }}">
                                                            <input type="hidden" name="activo" value="1">
                                                            <button type="submit" class="btn btn-sm btn-outline-success">Activar</button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
