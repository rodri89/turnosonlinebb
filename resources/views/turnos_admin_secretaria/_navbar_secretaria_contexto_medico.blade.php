{{-- Menú tipo secretaría mientras se editan pantallas de médico (sesión secretaria_context_medico_id). --}}
@php
  $medicoCtx = $medicoCtx ?? (\Illuminate\Support\Facades\DB::table('medicos')->where('id', session('secretaria_context_medico_id'))->first());
@endphp
<nav class="navbar navbar-expand-lg fontNav fixed-top fondoNav flex-column align-items-stretch p-0">
  @if($medicoCtx)
  <div class="w-100 py-2 px-3 d-flex flex-wrap align-items-center justify-content-between small text-white" style="background:#0c5460;">
    <span><strong>Secretaría</strong> — editando <strong>{{ $medicoCtx->apellido }}, {{ $medicoCtx->nombre }}</strong></span>
    <form method="POST" action="{{ route('secretarialimpiarcontextomedico') }}" class="mb-0">
      @csrf
      <button type="submit" class="btn btn-sm btn-light">Volver al panel secretaría</button>
    </form>
  </div>
  @endif
  <div class="container d-flex flex-wrap">
    <a class="textheader navbar-brand text-white" href="{{ url('/homes') }}">Turnos Online</a>
    <button class="navbar-toggler buttonMenuSizeMarco" type="button" data-toggle="collapse" data-target="#navbarSecretariaCtx" aria-controls="navbarSecretariaCtx" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSecretariaCtx">
      <ul class="navbar-nav ml-auto fondoNavMenu">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-toggle="dropdown" v-pre>Paciente <span class="caret"></span></a>
          <div class="dropdown-menu dropdown-menu-right">
            <form method="POST" action="{{ route('nuevopaciente') }}">@csrf<button class="dropdown-item" type="submit">Nuevo</button></form>
            <form method="POST" action="{{ route('actualizarpaciente') }}">@csrf<button class="dropdown-item" type="submit">Actualizar Datos</button></form>
            <form method="POST" action="{{ route('listadopacientes') }}">@csrf<button class="dropdown-item" type="submit">Buscar</button></form>
            <form method="POST" action="{{ route('listadopacienteshistorial') }}">@csrf<button class="dropdown-item" type="submit">Historial</button></form>
            @if(Auth::user()->perfil == 2 || Auth::user()->perfil == 3)
            <form method="POST" action="{{ route('adminpacientes') }}">@csrf<button class="dropdown-item" type="submit">Pendientes</button></form>
            @endif
          </div>
        </li>
        <li class="nav-item">
          <form method="POST" action="{{ route('seleccionarconsultorio') }}">@csrf<input type="hidden" name="option" value="3" /><button class="nav-link sinBackground text-white menuAlineacion" type="submit">Asignar Turnos</button></form>
        </li>
        <li class="nav-item">
          <form method="POST" action="{{ route('seleccionarconsultorio') }}">@csrf<input type="hidden" name="option" value="1" /><button class="nav-link sinBackground text-white menuAlineacion" type="submit">Turnos Asignados</button></form>
        </li>
        <li class="nav-item">
          <form method="POST" action="{{ route('seleccionarconsultorio') }}">@csrf<input type="hidden" name="option" value="2" /><button class="nav-link sinBackground text-white menuAlineacion" type="submit">Sobreturnos</button></form>
        </li>
        <li class="nav-item">
          <form method="POST" action="{{ route('seleccionarconsultorio') }}">@csrf<input type="hidden" name="option" value="6" /><button class="nav-link sinBackground text-white menuAlineacion" type="submit">Bloquear</button></form>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white menuAlineacion" href="{{ route('secretarianuevaobrasocial') }}">Nueva obra social</a>
        </li>
        @if(Auth::user()->perfil == 3 || Auth::user()->perfil == 4)
        <li class="nav-item">
          <form method="POST" action="{{ route('secretariarecetas') }}">@csrf<input type="hidden" name="secretaria_id" value="{{ Auth::user()->id }}" /><button class="nav-link sinBackground text-white menuAlineacion" type="submit">Recetas<div hidden id="seccion_cantidad_recetas_pendientes" class="circulo_rojo_receta float_right"><h2 id="cantidad_recetas_pendientes">+9</h2></div></button></form>
        </li>
        @endif
        <li class="nav-item dropdown" style="list-style:none;">
          <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-toggle="dropdown" v-pre>{{ Auth::user()->name }} <span class="caret"></span></a>
          <div class="dropdown-menu dropdown-menu-right">
            @include('turnos_admin_secretaria._dropdown_gestion_medico')
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form-secretaria-ctx').submit();">{{ __('Cerrar Sesión') }}</a>
            <form id="logout-form-secretaria-ctx" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
          </div>
        </li>
      </ul>
    </div>
  </div>
</nav>
