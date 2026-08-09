{{-- Opciones equivalentes al menú del médico: siempre piden elegir médico (option 7–11). --}}
@if(Auth::user()->perfil == 2 || Auth::user()->perfil == 5 || Auth::user()->perfil == 6 || Auth::user()->perfil == 7 || Auth::user()->perfil == 8 || Auth::user()->perfil == 9)
<form method="POST" action="{{ route('seleccionarconsultorio') }}" class="d-inline">
  @csrf
  <input type="hidden" name="option" value="7" />
  <button type="submit" class="dropdown-item">{{ __('Configuración') }} (elegir médico)</button>
</form>
@endif
<form method="POST" action="{{ route('seleccionarconsultorio') }}" class="d-inline">
  @csrf
  <input type="hidden" name="option" value="8" />
  <button type="submit" class="dropdown-item">Admin Horarios (elegir médico)</button>
</form>
<form method="POST" action="{{ route('seleccionarconsultorio') }}" class="d-inline">
  @csrf
  <input type="hidden" name="option" value="9" />
  <button type="submit" class="dropdown-item">Admin Horarios Fijos (elegir médico)</button>
</form>
<form method="POST" action="{{ route('seleccionarconsultorio') }}" class="d-inline">
  @csrf
  <input type="hidden" name="option" value="10" />
  <button type="submit" class="dropdown-item">Config Mensajes (elegir médico)</button>
</form>
<form method="POST" action="{{ route('seleccionarconsultorio') }}" class="d-inline">
  @csrf
  <input type="hidden" name="option" value="11" />
  <button type="submit" class="dropdown-item">Obra Social (elegir médico)</button>
</form>
<form method="POST" action="{{ route('seleccionarconsultorio') }}" class="d-inline">
  @csrf
  <input type="hidden" name="option" value="12" />
  <button type="submit" class="dropdown-item">Pagos / Mercado Pago (elegir médico)</button>
</form>
