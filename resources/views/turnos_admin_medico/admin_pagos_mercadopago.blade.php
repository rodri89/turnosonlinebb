@extends('turnos_admin_medico/turnos_admin_medico_plantilla')

@section('title', 'Pagos / Mercado Pago')

@section('body_titulo', 'Pagos / Mercado Pago')

@section('contenedor')
    <p class="text-muted mb-4">Configurá si querés cobrar al reservar turno presencial, definí el importe por obra social y vinculá tu cuenta de Mercado Pago.</p>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(!$moduloActivo)
        <div class="alert alert-warning">
            El módulo <strong>Cobro turnos MercadoPago</strong> no está activo para su perfil. Contacte a administración para habilitarlo.
        </div>
    @else
        <div class="row">
            <div class="col-lg-7">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-light"><strong>Configuración de cobro</strong></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('medicoguardarpagosconfig') }}">
                            @csrf
                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" id="cobro_activo" name="cobro_activo" value="1"
                                    {{ (int) $account->cobro_activo === 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="cobro_activo">Cobrar al reservar turno (presencial)</label>
                            </div>
                            <p class="small text-muted mb-3">
                                El importe se define por obra social en la sección de la derecha. Si una obra social tiene importe $0, la reserva es gratis para esos pacientes.
                            </p>
                            @if($platformSettings)
                                <p class="small text-muted mb-3">
                                    Comisión plataforma: <strong>{{ $platformSettings->platform_commission_percent }}%</strong> (solo lectura).
                                </p>
                            @endif

                            <hr class="my-4">
                            <h6 class="mb-3">Aviso a pacientes</h6>
                            <p class="small text-muted mb-3">
                                Podés avisar a los pacientes que a partir de una fecha empezarás a cobrar la reserva online.
                                El aviso se muestra a los pacientes al pedir turno mientras el cobro esté activo (sin importar la fecha). La fecha de inicio solo define cuándo empieza a aplicarse el cobro online; el pago aplica a turnos con fecha igual o posterior a esa fecha.
                            </p>
                            <div class="form-group">
                                <label for="cobro_desde">Comenzar a cobrar desde (opcional)</label>
                                <input type="date" class="form-control" id="cobro_desde" name="cobro_desde"
                                    value="{{ old('cobro_desde', $account->cobro_desde ? $account->cobro_desde->format('Y-m-d') : '') }}">
                                <small class="form-text text-muted">Si lo dejás vacío, el cobro aplica de inmediato cuando corresponda por obra social.</small>
                            </div>
                            <div class="form-group">
                                <label for="mensaje_aviso_cobro_titulo">Título del aviso</label>
                                <input type="text" class="form-control" id="mensaje_aviso_cobro_titulo" name="mensaje_aviso_cobro_titulo"
                                    maxlength="255"
                                    value="{{ old('mensaje_aviso_cobro_titulo', $account->mensaje_aviso_cobro_titulo ?: 'Aviso sobre reservas de turnos') }}">
                            </div>
                            <div class="form-group">
                                <label for="mensaje_aviso_cobro">Mensaje</label>
                                <textarea class="form-control" id="mensaje_aviso_cobro" name="mensaje_aviso_cobro" rows="4"
                                    placeholder="A partir del [fecha], para confirmar su turno presencial deberá abonar online una reserva que se descuenta del costo de la consulta. Los turnos con fecha anterior a esa no tienen cargo de reserva. El monto depende de su obra social.">{{ old('mensaje_aviso_cobro', $account->mensaje_aviso_cobro) }}</textarea>
                                <small class="form-text text-muted">Podés usar <code>[fecha]</code> para insertar la fecha de inicio. Las bajadas de línea se respetan en el aviso al paciente.</small>
                            </div>

                            <hr class="my-4">
                            <h6 class="mb-3">Reembolso por cancelación</h6>
                            <p class="small text-muted mb-3">
                                Si el paciente cancela con suficiente anticipación, se reembolsa automáticamente la reserva online.
                                Ejemplo: turno el 20/06 con 2 días previos configurados — cancela el 17/06 (3 días antes) se reembolsa;
                                cancela el 19/06 (1 día antes) no se reembolsa.
                            </p>
                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" id="reembolso_cancelacion_activo"
                                    name="reembolso_cancelacion_activo" value="1"
                                    {{ (int) old('reembolso_cancelacion_activo', $account->reembolso_cancelacion_activo) === 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="reembolso_cancelacion_activo">
                                    Reembolsar automáticamente la reserva si el paciente cancela con anticipación
                                </label>
                            </div>
                            <div class="form-group">
                                <label for="reembolso_cancelacion_dias_previos">Días previos mínimos</label>
                                <input type="number" class="form-control" id="reembolso_cancelacion_dias_previos"
                                    name="reembolso_cancelacion_dias_previos" min="1" max="365"
                                    value="{{ old('reembolso_cancelacion_dias_previos', $account->reembolso_cancelacion_dias_previos) }}"
                                    {{ (int) old('reembolso_cancelacion_activo', $account->reembolso_cancelacion_activo) !== 1 ? 'disabled' : '' }}>
                                <small class="form-text text-muted">Cantidad mínima de días de anticipación respecto al día del turno.</small>
                            </div>

                            @if(!empty($esSecretaria))
                            @else
                            <hr class="my-4">
                            <h6 class="mb-3">Permisos de secretaría</h6>
                            <p class="small text-muted mb-3">
                                Definí qué puede hacer la secretaría del consultorio respecto a pagos y reembolsos.
                            </p>
                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" id="secretaria_puede_reembolso"
                                    name="secretaria_puede_reembolso" value="1"
                                    {{ (int) old('secretaria_puede_reembolso', $account->secretaria_puede_reembolso) === 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="secretaria_puede_reembolso">
                                    Secretaria puede dar reembolso
                                </label>
                            </div>
                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" id="secretaria_puede_ver_panel_mp"
                                    name="secretaria_puede_ver_panel_mp" value="1"
                                    {{ (int) old('secretaria_puede_ver_panel_mp', $account->secretaria_puede_ver_panel_mp) === 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="secretaria_puede_ver_panel_mp">
                                    Secretaria puede ver panel de Mercado Pago
                                </label>
                            </div>
                            @endif

                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-light"><strong>Cuenta Mercado Pago</strong></div>
                    <div class="card-body">
                        @if($account->isLinked())
                            <p class="text-success mb-2"><strong>Vinculada</strong></p>
                            @if(empty($esSecretaria))
                            <p class="small mb-1">Collector ID: <code>{{ $account->collector_id }}</code></p>
                            @endif
                            @if($account->mode && $platformSettings && $account->mode !== $platformSettings->mode)
                                <div class="alert alert-warning small py-2 mb-2">
                                    La vinculación es de modo <strong>{{ $account->mode }}</strong> pero la plataforma está en
                                    <strong>{{ $platformSettings->mode }}</strong>.
                                    @if(empty($esSecretaria))
                                    Desconecte y vuelva a conectar Mercado Pago.
                                    @else
                                    El médico debe desconectar y volver a conectar Mercado Pago.
                                    @endif
                                </div>
                            @endif
                            @if($account->linked_at)
                                <p class="small text-muted">Vinculada el {{ $account->linked_at->format('d/m/Y H:i') }}</p>
                            @endif
                            @if(empty($esSecretaria))
                            <p class="small text-muted mb-0">
                                Si el pago falla con error de cuenta inválida, desconecte y vuelva a vincular (especialmente si antes se probó en sandbox y ahora está en producción).
                            </p>
                            <form method="POST" action="{{ route('medicompdisconnect') }}" class="mt-3"
                                onsubmit="return confirm('¿Desvincular Mercado Pago? Los pacientes no podrán pagar hasta reconectar.');">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm">Desconectar</button>
                            </form>
                            @endif
                        @else
                            <p class="text-warning mb-2"><strong>Sin vincular</strong></p>
                            <p class="small text-muted">
                                @if(!empty($esSecretaria))
                                    Los pacientes no podrán pagar turnos hasta que el médico conecte su cuenta de Mercado Pago.
                                @else
                                    Los pacientes no podrán pagar turnos hasta que conectes tu cuenta.
                                @endif
                            </p>
                            @if(empty($esSecretaria))
                            <a href="{{ route('medicompconnect') }}" class="btn btn-success mt-2">Conectar con Mercado Pago</a>
                            @endif
                        @endif
                        @if(empty($esSecretaria))
                        <hr>
                        <p class="small text-muted mb-0">Redirect URI registrada en MP:<br><code>{{ $redirectUri }}</code></p>
                        @endif
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-light"><strong>Cobro por obra social</strong></div>
                    <div class="card-body">
                        @if(!$tieneObrasSociales)
                            <p class="text-muted mb-3">Todavía no tenés obras sociales vinculadas a tu perfil.</p>
                            <form method="POST" action="{{ route('medicovinculartodasobrassocialespagos') }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary">Vincular todas las obras sociales</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('medicoaplicarcobrotodasobrassociales') }}" class="mb-4">
                                @csrf
                                <div class="form-group mb-2">
                                    <label for="importe_todas">Aplicar a todas las activas ($)</label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="importe_todas" name="importe_reserva" required>
                                </div>
                                <button type="submit" class="btn btn-secondary btn-block">Aplicar a todas</button>
                            </form>

                            <div class="form-group mb-3">
                                <label for="buscar_obra_social" class="small text-muted mb-1">Buscar obra social</label>
                                <input type="search" class="form-control form-control-sm" id="buscar_obra_social"
                                    placeholder="Escribí el nombre para filtrar..." autocomplete="off">
                            </div>

                            <div class="table-responsive lista-obras-sociales-scroll">
                                <table class="table table-sm table-striped mb-0" id="tabla_cobro_obras_sociales">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Obra social</th>
                                            <th style="width: 120px;">Importe ($)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($obrasSocialesMedico as $os)
                                        <tr class="fila-obra-social" data-nombre="{{ mb_strtolower($os->nombre, 'UTF-8') }}">
                                            <td class="nombre-obra-social">{{ $os->nombre }}</td>
                                            <td>
                                                <input type="number" step="0.01" min="0" class="form-control form-control-sm importe-reserva-os"
                                                    data-osmid="{{ $os->osmid }}"
                                                    data-nombre="{{ $os->nombre }}"
                                                    value="{{ number_format((float) $os->importe_reserva, 2, '.', '') }}">
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <p id="sin_resultados_obras_sociales" class="small text-muted mt-2 mb-0" style="display: none;">No se encontraron obras sociales con ese criterio.</p>
                            <p class="small text-muted mt-2 mb-0">Los cambios se guardan automáticamente al modificar cada importe.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($puedeProbarReserva))
        <div class="card shadow-sm border-0 mb-4 border-primary">
            <div class="card-header bg-light"><strong>Probar reserva con pago</strong></div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    Para probar Mercado Pago no podés pagarte a vos mismo: generá un link y compartilo con otra persona
                    (familiar, colega, etc.) para que complete el pago con su cuenta. Usá el DNI de un paciente existente
                    cuya obra social tenga importe de reserva configurado; verá la misma pantalla de reserva (calendario, horario y checkout).
                </p>
                <form method="POST" action="{{ route('medicogenerarlinkpruebapago') }}" id="form_prueba_pago">
                    @csrf
                    <div class="form-row align-items-end">
                        <div class="form-group col-md-4">
                            <label for="dni_prueba">DNI del paciente</label>
                            <input type="text" class="form-control" id="dni_prueba" name="dni" required
                                pattern="[0-9]+" placeholder="Ej: 34741602"
                                value="{{ old('dni', session('dni_prueba_pago', '34741602')) }}">
                        </div>
                        <div class="form-group col-md-5">
                            <button type="submit" class="btn btn-primary btn-block">
                                Generar link para compartir
                            </button>
                        </div>
                    </div>
                </form>

                @if(session('link_prueba_pago'))
                <div class="mt-3 p-3 bg-light rounded border">
                    <label class="small font-weight-bold mb-1" for="link_prueba_pago_copia">Link de prueba (válido 72 hs)</label>
                    <div class="input-group">
                        <input type="text" class="form-control form-control-sm" id="link_prueba_pago_copia" readonly
                            value="{{ session('link_prueba_pago') }}">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn_copiar_link_prueba">Copiar</button>
                        </div>
                    </div>
                    <p class="small text-muted mb-0 mt-2">Enviá este link por WhatsApp, mail, etc. Quien lo abra podrá reservar y pagar sin iniciar sesión.</p>
                </div>
                <script>
                (function () {
                    var btn = document.getElementById('btn_copiar_link_prueba');
                    var input = document.getElementById('link_prueba_pago_copia');
                    if (!btn || !input) return;
                    btn.addEventListener('click', function () {
                        input.select();
                        input.setSelectionRange(0, 99999);
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(input.value).then(function () {
                                btn.textContent = 'Copiado';
                                setTimeout(function () { btn.textContent = 'Copiar'; }, 2000);
                            });
                        } else {
                            document.execCommand('copy');
                            btn.textContent = 'Copiado';
                            setTimeout(function () { btn.textContent = 'Copiar'; }, 2000);
                        }
                    });
                })();
                </script>
                @endif

                <hr class="my-3">
                <p class="small text-muted mb-2">Opcional: abrir el flujo vos mismo (solo para ver la pantalla; el pago debe hacerlo otra persona).</p>
                <form method="POST" action="{{ route('medicoprobarreservaturno') }}" target="_blank" class="mb-0"
                    onsubmit="this.querySelector('[name=dni]').value=document.getElementById('dni_prueba').value;">
                    @csrf
                    <input type="hidden" name="dni" value="{{ old('dni', session('dni_prueba_pago', '34741602')) }}">
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Abrir vista paciente (pestaña nueva)</button>
                </form>
            </div>
        </div>
        @elseif($moduloActivo)
        <div class="alert alert-secondary">
            Para probar el flujo del paciente, activá el cobro, definí importes de reserva por obra social y vinculá Mercado Pago.
        </div>
        @endif
    @endif

    <div id="snackbar"><p id="snackbar_text"></p></div>

    @if($moduloActivo)
    <script>
    (function () {
        var check = document.getElementById('reembolso_cancelacion_activo');
        var input = document.getElementById('reembolso_cancelacion_dias_previos');
        if (!check || !input) return;
        check.addEventListener('change', function () {
            input.disabled = !check.checked;
            if (!check.checked) {
                input.value = '';
            }
        });
    })();
    </script>
    @endif

    @if($moduloActivo && $tieneObrasSociales)
    <style>
        .lista-obras-sociales-scroll {
            max-height: 320px;
            overflow-y: auto;
        }
        .lista-obras-sociales-scroll thead th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: #f8f9fa;
            box-shadow: 0 1px 0 #dee2e6;
        }
    </style>
    <script>
    (function () {
        var csrf = '{{ csrf_token() }}';
        var snackbar = document.getElementById('snackbar');
        var snackbarText = document.getElementById('snackbar_text');
        var buscador = document.getElementById('buscar_obra_social');
        var sinResultados = document.getElementById('sin_resultados_obras_sociales');
        var filas = document.querySelectorAll('.fila-obra-social');

        function mostrarToast(mensaje) {
            if (!snackbar || !snackbarText) return;
            snackbarText.textContent = mensaje;
            snackbar.className = 'show';
            setTimeout(function () {
                snackbar.className = snackbar.className.replace('show', '');
            }, 3000);
        }

        function filtrarObrasSociales() {
            var termino = (buscador ? buscador.value : '').trim().toLowerCase();
            var visibles = 0;

            filas.forEach(function (fila) {
                var nombre = fila.getAttribute('data-nombre') || '';
                var coincide = termino === '' || nombre.indexOf(termino) !== -1;
                fila.style.display = coincide ? '' : 'none';
                if (coincide) visibles++;
            });

            if (sinResultados) {
                sinResultados.style.display = (termino !== '' && visibles === 0) ? '' : 'none';
            }
        }

        if (buscador) {
            buscador.addEventListener('input', filtrarObrasSociales);
        }

        document.querySelectorAll('.importe-reserva-os').forEach(function (input) {
            input.addEventListener('change', function () {
                var osmid = this.getAttribute('data-osmid');
                var nombre = this.getAttribute('data-nombre') || 'Obra social';
                var importe = this.value;

                fetch('{{ route('medicoguardarcobroobrasocial') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        obra_social_id: osmid,
                        importe_reserva: importe
                    })
                }).then(function (r) { return r.json(); }).then(function (data) {
                    if (data.ok) {
                        mostrarToast('Importe de reserva guardado: ' + nombre);
                    } else {
                        mostrarToast(data.message || 'No se pudo guardar el importe.');
                    }
                }).catch(function () {
                    mostrarToast('Error al guardar el importe.');
                });
            });
        });
    })();
    </script>
    @endif
@endsection
