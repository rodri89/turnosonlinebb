@extends('turnos_admin_medico/turnos_admin_medico_plantilla')

@section('contenedor')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <h4 class="mb-1">Mis horarios fijos</h4>
            <p class="text-muted mb-0 small">Configurá los horarios de atención de tu semana. Estos son los turnos que se ofrecen de forma recurrente cada semana. Podés agregar o quitar horarios por día.</p>
            @if(isset($tieneVigencia) && $tieneVigencia)
                <p class="text-muted small mt-1">Podés indicar <strong>válido desde</strong> (nuevos horarios a partir de una fecha) y <strong>válido hasta</strong> (dejar de usar un horario desde cierta fecha). Si no ponés ninguna fecha, el horario es vigente <strong>para siempre</strong>.</p>
            @endif
            @if(isset($consultorio) && $consultorio)
                <p class="text-muted small mt-1"><strong>Consultorio:</strong> {{ $consultorio->nombre }}</p>
            @endif
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div id="mensaje-ajax" class="alert d-none" role="alert"></div>

    <div class="row">
        @foreach($horariosPorDia as $numDia => $datos)
            <div class="col-12 col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header bg-light py-2">
                        <h6 class="mb-0 font-weight-bold">{{ $datos['nombre'] }}</h6>
                    </div>
                    <div class="card-body py-3">
                        <ul class="list-group list-group-flush list-horarios-dia" data-dia="{{ $numDia }}">
                            @forelse($datos['horarios'] as $h)
                                <li class="list-group-item px-2 py-2" data-id="{{ $h->id }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="horario-texto font-weight-bold">{{ $h->horario }}</span>
                                            @if(isset($tieneVigencia) && $tieneVigencia && (!empty($h->valido_desde) || !empty($h->valido_hasta)))
                                                <div class="small text-muted mt-1">
                                                    @if(!empty($h->valido_desde))
                                                        Desde: {{ \Carbon\Carbon::parse($h->valido_desde)->format('d/m/Y') }}
                                                    @endif
                                                    @if(!empty($h->valido_hasta))
                                                        <span class="ml-1">Hasta: {{ \Carbon\Carbon::parse($h->valido_hasta)->format('d/m/Y') }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        <div class="d-flex align-items-center">
                                            @if(isset($tieneVigencia) && $tieneVigencia)
                                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 mr-1 vigencia-horario" data-id="{{ $h->id }}" data-valido-desde="{{ $h->valido_desde ?? '' }}" data-valido-hasta="{{ $h->valido_hasta ?? '' }}" title="Vigencia">Vig.</button>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 eliminar-horario" data-id="{{ $h->id }}" title="Quitar este horario">✕</button>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="list-group-item text-muted small py-2">Sin horarios cargados</li>
                            @endforelse
                        </ul>
                        <div class="mt-2 pt-2 border-top">
                            @if(isset($tieneVigencia) && $tieneVigencia)
                                <div class="form-row small mb-1">
                                    <div class="col-6">
                                        <label class="mb-0">Válido desde (opc.) — fecha</label>
                                        <input type="date" class="form-control form-control-sm input-valido-desde" data-dia="{{ $numDia }}" placeholder="dd/mm/aaaa" title="Vacío = para siempre">
                                    </div>
                                    <div class="col-6">
                                        <label class="mb-0">Válido hasta (opc.) — fecha</label>
                                        <input type="date" class="form-control form-control-sm input-valido-hasta" data-dia="{{ $numDia }}" placeholder="dd/mm/aaaa" title="Vacío = para siempre">
                                    </div>
                                </div>
                                <p class="small text-muted mb-1">Dejar vacío = vigente para siempre. Ambos son fechas (elegí día, mes y año).</p>
                            @endif
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control form-control-sm input-horario-dia" data-dia="{{ $numDia }}" placeholder="Ej: 09:00, 09:10, 09:20, 14:30" maxlength="5" title="Formato HH:MM (ej. cada 10, 15, 20, 30 o 40 min)">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-sm btn-primary agregar-horario" data-dia="{{ $numDia }}">Agregar</button>
                                </div>
                            </div>
                            <p class="small text-muted mb-0 mt-1">Ingresá la hora en formato HH:MM (ej. 08:10, 09:15, 14:20, 17:40).</p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@if(isset($tieneVigencia) && $tieneVigencia)
<!-- Modal vigencia -->
<div class="modal fade" id="modalVigencia" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title">Vigencia del horario</h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body py-2">
                <input type="hidden" id="vigencia-horario-id">
                <div class="form-group small mb-2">
                    <label>Válido desde (fecha)</label>
                    <input type="date" class="form-control form-control-sm" id="vigencia-input-desde" placeholder="dd/mm/aaaa">
                </div>
                <div class="form-group small mb-0">
                    <label>Válido hasta (fecha) <span class="text-muted">(dejar de usar desde)</span></label>
                    <input type="date" class="form-control form-control-sm" id="vigencia-input-hasta" placeholder="dd/mm/aaaa">
                </div>
                <p class="small text-muted mb-0">Ambos son fechas: elegí día, mes y año.</p>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-sm btn-primary" id="vigencia-guardar">Guardar</button>
            </div>
        </div>
    </div>
</div>
@endif

<script>
(function() {
    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var urlGuardar = '{{ route("medicoguardarhorariofijo") }}';
    var urlEliminar = '{{ route("medicoeliminarhorariofijo") }}';
    var urlVigencia = '{{ isset($tieneVigencia) && $tieneVigencia ? route("medicoactualizarvigenciahorariofijo") : "" }}';
    var tieneVigencia = {{ isset($tieneVigencia) && $tieneVigencia ? 'true' : 'false' }};

    function mostrarMensaje(texto, esError) {
        var el = document.getElementById('mensaje-ajax');
        el.textContent = texto;
        el.classList.remove('alert-success', 'alert-danger', 'd-none');
        el.classList.add(esError ? 'alert-danger' : 'alert-success');
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        setTimeout(function() { el.classList.add('d-none'); }, 4000);
    }

    function getListaDia(dia) {
        return document.querySelector('.list-horarios-dia[data-dia="' + dia + '"]');
    }

    function getInputHorarioDia(dia) {
        return document.querySelector('.input-horario-dia[data-dia="' + dia + '"]');
    }

    /** Normaliza y valida hora en formato HH:MM. Devuelve "HH:MM" o null si inválido. */
    function normalizarHora(val) {
        if (!val || typeof val !== 'string') return null;
        val = val.trim().replace(/\s/g, '');
        var m = val.match(/^(\d{1,2}):(\d{1,2})$/);
        if (!m) return null;
        var h = parseInt(m[1], 10);
        var min = parseInt(m[2], 10);
        if (h < 0 || h > 23 || min < 0 || min > 59) return null;
        return (h < 10 ? '0' : '') + h + ':' + (min < 10 ? '0' : '') + min;
    }

    function fechaParaInput(d) {
        if (!d) return '';
        var m = d.match(/^(\d{4})-(\d{2})-(\d{2})/);
        return m ? m[0] : '';
    }

    document.querySelectorAll('.agregar-horario').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var dia = this.getAttribute('data-dia');
            var input = getInputHorarioDia(dia);
            var horario = normalizarHora(input ? input.value : '');
            if (!horario) {
                mostrarMensaje('Ingresá una hora en formato HH:MM (ej. 09:00, 09:10, 14:30).', true);
                return;
            }
            var payload = { dia: dia, horario: horario, _token: csrf };
            if (tieneVigencia) {
                var inputDesde = document.querySelector('.input-valido-desde[data-dia="' + dia + '"]');
                var inputHasta = document.querySelector('.input-valido-hasta[data-dia="' + dia + '"]');
                if (inputDesde && inputDesde.value) payload.valido_desde = inputDesde.value;
                if (inputHasta && inputHasta.value) payload.valido_hasta = inputHasta.value;
            }
            btn.disabled = true;
            fetch(urlGuardar, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                btn.disabled = false;
                if (data.ok) {
                    var list = getListaDia(dia);
                    var placeholder = list.querySelector('.list-group-item.text-muted');
                    if (placeholder) placeholder.remove();
                    var vigenciaHtml = '';
                    if (tieneVigencia && (data.valido_desde || data.valido_hasta)) {
                        var desde = data.valido_desde ? (data.valido_desde.split('-')[2] + '/' + data.valido_desde.split('-')[1] + '/' + data.valido_desde.split('-')[0]) : '';
                        var hasta = data.valido_hasta ? (data.valido_hasta.split('-')[2] + '/' + data.valido_hasta.split('-')[1] + '/' + data.valido_hasta.split('-')[0]) : '';
                        vigenciaHtml = '<div class="small text-muted mt-1">' + (desde ? 'Desde: ' + desde : '') + (hasta ? ' Hasta: ' + hasta : '') + '</div>';
                    }
                    var li = document.createElement('li');
                    li.className = 'list-group-item px-2 py-2';
                    li.setAttribute('data-id', data.id);
                    li.innerHTML = '<div class="d-flex justify-content-between align-items-start"><div><span class="horario-texto font-weight-bold">' + data.horario + '</span>' + vigenciaHtml + '</div><div class="d-flex align-items-center">' +
                        (tieneVigencia ? '<button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 mr-1 vigencia-horario" data-id="' + data.id + '" title="Vigencia">Vig.</button>' : '') +
                        '<button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 eliminar-horario" data-id="' + data.id + '" title="Quitar este horario">✕</button></div></div>';
                    list.appendChild(li);
                    li.querySelector('.eliminar-horario').addEventListener('click', eliminarClick);
                    if (tieneVigencia && li.querySelector('.vigencia-horario')) li.querySelector('.vigencia-horario').addEventListener('click', vigenciaClick);
                    if (input) input.value = '';
                    if (tieneVigencia) {
                        var id = document.querySelector('.input-valido-desde[data-dia="' + dia + '"]');
                        var ih = document.querySelector('.input-valido-hasta[data-dia="' + dia + '"]');
                        if (id) id.value = ''; if (ih) ih.value = '';
                    }
                    mostrarMensaje('Horario agregado correctamente.');
                } else {
                    mostrarMensaje(data.mensaje || 'No se pudo agregar.', true);
                }
            })
            .catch(function() {
                btn.disabled = false;
                mostrarMensaje('Error de conexión. Intentá de nuevo.', true);
            });
        });
    });

    function vigenciaClick() {
        var id = this.getAttribute('data-id');
        var desde = this.getAttribute('data-valido-desde') || '';
        var hasta = this.getAttribute('data-valido-hasta') || '';
        document.getElementById('vigencia-horario-id').value = id;
        document.getElementById('vigencia-input-desde').value = fechaParaInput(desde);
        document.getElementById('vigencia-input-hasta').value = fechaParaInput(hasta);
        $('#modalVigencia').modal('show');
    }

    if (tieneVigencia && urlVigencia) {
        document.getElementById('vigencia-guardar').addEventListener('click', function() {
            var id = document.getElementById('vigencia-horario-id').value;
            var desde = document.getElementById('vigencia-input-desde').value;
            var hasta = document.getElementById('vigencia-input-hasta').value;
            var payload = { id: id, _token: csrf };
            if (desde) payload.valido_desde = desde;
            if (hasta) payload.valido_hasta = hasta;
            fetch(urlVigencia, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.ok) {
                    var li = document.querySelector('.list-group-item[data-id="' + id + '"]');
                    if (li) {
                        var vigDiv = li.querySelector('.small.text-muted');
                        var desdeStr = data.valido_desde ? (data.valido_desde.split('-')[2] + '/' + data.valido_desde.split('-')[1] + '/' + data.valido_desde.split('-')[0]) : '';
                        var hastaStr = data.valido_hasta ? (data.valido_hasta.split('-')[2] + '/' + data.valido_hasta.split('-')[1] + '/' + data.valido_hasta.split('-')[0]) : '';
                        var btn = li.querySelector('.vigencia-horario');
                        if (btn) {
                            btn.setAttribute('data-valido-desde', data.valido_desde || '');
                            btn.setAttribute('data-valido-hasta', data.valido_hasta || '');
                        }
                        if (vigDiv) vigDiv.innerHTML = (desdeStr ? 'Desde: ' + desdeStr : '') + (hastaStr ? ' Hasta: ' + hastaStr : '');
                        else if (desdeStr || hastaStr) {
                            var div = document.createElement('div');
                            div.className = 'small text-muted mt-1';
                            div.textContent = (desdeStr ? 'Desde: ' + desdeStr : '') + (hastaStr ? ' Hasta: ' + hastaStr : '');
                            li.querySelector('div > div').appendChild(div);
                        }
                    }
                    $('#modalVigencia').modal('hide');
                    mostrarMensaje('Vigencia actualizada.');
                } else {
                    mostrarMensaje(data.mensaje || 'No se pudo actualizar.', true);
                }
            })
            .catch(function() {
                mostrarMensaje('Error de conexión.', true);
            });
        });
        document.querySelectorAll('.vigencia-horario').forEach(function(btn) {
            btn.addEventListener('click', vigenciaClick);
        });
    }

    function eliminarClick() {
        var id = this.getAttribute('data-id');
        var li = this.closest('li');
        var dia = this.closest('.list-horarios-dia').getAttribute('data-dia');
        this.disabled = true;
        fetch(urlEliminar, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ id: id, _token: csrf })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.ok) {
                li.remove();
                var list = getListaDia(dia);
                if (list && list.querySelectorAll('li').length === 0) {
                    var empty = document.createElement('li');
                    empty.className = 'list-group-item text-muted small py-2';
                    empty.textContent = 'Sin horarios cargados';
                    list.appendChild(empty);
                }
                mostrarMensaje('Horario eliminado.');
            } else {
                mostrarMensaje(data.mensaje || 'No se pudo eliminar.', true);
                li.querySelector('.eliminar-horario').disabled = false;
            }
        })
        .catch(function() {
            mostrarMensaje('Error de conexión.', true);
            li.querySelector('.eliminar-horario').disabled = false;
        });
    }

    document.querySelectorAll('.eliminar-horario').forEach(function(btn) {
        btn.addEventListener('click', eliminarClick);
    });
})();
</script>
@endsection
