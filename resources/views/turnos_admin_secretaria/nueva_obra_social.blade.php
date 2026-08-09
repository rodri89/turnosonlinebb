@extends('turnos_admin_secretaria/turnos_admin_secretaria_plantilla')

@section('body_titulo', 'Nueva obra social')

@section('contenedor')
<div class="row">
  <div class="col-lg-5 mb-4">
    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <p class="mb-3 small text-muted">
      La obra social se registrará en el sistema y se vinculará a <strong>todos</strong> los médicos.
      Quedará <strong>activa</strong> solo para médicos de los consultorios donde trabaja su usuario de secretaría;
      para el resto se creará el vínculo <strong>inactivo</strong> (cada médico puede activarla después desde su panel).
    </p>

    @if($consultorios->isEmpty())
      <div class="alert alert-warning">No tiene consultorios activos asignados. No puede dar de alta obras sociales hasta que un administrador lo habilite.</div>
    @else
      <h5 class="mt-2">Consultorios donde la OS quedará activa</h5>
      <ul class="mb-4 small">
        @foreach($consultorios as $c)
          <li>{{ $c->direccion }} <span class="text-muted">(id {{ $c->id }})</span></li>
        @endforeach
      </ul>

      <h5>Alta</h5>
      <form method="POST" action="{{ route('secretariaaltaobrasocial') }}">
        @csrf
        <div class="form-group">
          <label for="nombre">Nombre de la obra social</label>
          <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre"
                 value="{{ old('nombre') }}" placeholder="Ej: OSDE" required maxlength="191" autocomplete="off">
          @error('nombre')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <button type="submit" class="rodri_button">Registrar OS</button>
      </form>
    @endif
  </div>

  <div class="col-lg-7">
    <h5 class="mb-3">Obras sociales registradas</h5>
    <div class="form-group">
      <label for="buscador_os" class="sr-only">Buscar</label>
      <input type="search" class="form-control" id="buscador_os" placeholder="Buscar por nombre…" autocomplete="off">
    </div>
    <div class="table-responsive border rounded" style="max-height: 420px; overflow-y: auto;">
      <table class="table table-sm table-striped mb-0" id="tabla_obras_sociales">
        <thead class="thead-light sticky-top">
          <tr>
            <th scope="col" style="width:4rem;">#</th>
            <th scope="col">Nombre</th>
            <th scope="col" style="width:6rem;">Estado</th>
          </tr>
        </thead>
        <tbody id="tabla_obras_sociales_body">
          @forelse($obraSociales as $os)
            <tr class="fila-os" data-buscar="{{ \Illuminate\Support\Str::lower($os->nombre) }}">
              <td>{{ $os->id }}</td>
              <td>{{ $os->nombre }}</td>
              <td>
                @if((int) $os->activo === 1)
                  <span class="badge badge-success">Activa</span>
                @else
                  <span class="badge badge-secondary">Inactiva</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="3" class="text-muted text-center py-3">No hay obras sociales cargadas.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <p class="small text-muted mt-2 mb-0">El listado incluye todas las obras sociales del sistema. Tras registrar una nueva, actualice la página para verla aquí.</p>
  </div>
</div>

<script>
  (function () {
    var input = document.getElementById('buscador_os');
    if (!input) return;
    input.addEventListener('input', function () {
      var q = (input.value || '').toLowerCase().trim();
      var filas = document.querySelectorAll('#tabla_obras_sociales_body tr.fila-os');
      filas.forEach(function (tr) {
        var hay = !q || (tr.getAttribute('data-buscar') || '').indexOf(q) !== -1;
        tr.style.display = hay ? '' : 'none';
      });
    });
  })();
</script>
@endsection
