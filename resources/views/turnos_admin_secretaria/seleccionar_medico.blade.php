@extends('turnos_admin_secretaria/turnos_admin_secretaria_plantilla')

<input type="hidden" id="seleccionar_medico_screen">

@section('contenedor')
<input id="seleccionar_medico_cantidad" type="hidden" value="{{ $medicos->count() }}">
<?php $cont = 0; ?>
<div class="row">
  @foreach($medicos as $medico)
  <div id="seleccionar_medico_style_id_{{ $cont }}" class="col-md-2 mb-3 text-center" style="height:450px">
    <?php $cont++; ?>
    @php
      $op = (int) $option;
      $destinosGestion = [7 => 'config', 8 => 'horarios', 9 => 'horarios_fijos', 10 => 'mensajes', 11 => 'obra_social', 12 => 'pagos'];
      $formAction = '#';
      $hiddens = [];
      if (in_array($op, [7, 8, 9, 10, 11, 12], true)) {
        $formAction = route('secretariaestablecercontextomedico');
        $hiddens = [
          'medico_id' => $medico->id,
          'consultorio' => $consultorio,
          'destino' => $destinosGestion[$op],
        ];
      } elseif ($op === 1) {
        $formAction = route('turnosasignadosdia');
        $hiddens = ['medico_id' => $medico->id, 'consultorio' => $consultorio, 'option' => $option];
      } elseif ($op === 2) {
        $formAction = route('secretariaadminsobreturnos');
        $hiddens = ['medico_id' => $medico->id, 'consultorio' => $consultorio, 'option' => $option];
      } elseif ($op === 3) {
        $formAction = route('secretariaasignarturnos');
        $hiddens = ['medico_id' => $medico->id, 'consultorio' => $consultorio, 'option' => $option];
      } elseif ($op === 4) {
        $formAction = route('secretariaasignarturnos');
        $hiddens = ['medico_id' => $medico->id, 'consultorio' => $consultorio, 'option' => $option, 'paciente_dni' => $paciente_dni];
      } elseif ($op === 5) {
        $formAction = route('secretariaadminsobreturnos');
        $hiddens = ['medico_id' => $medico->id, 'consultorio' => $consultorio, 'option' => $option, 'paciente_dni' => $paciente_dni];
      } elseif ($op === 6) {
        $formAction = route('secretariabloquearturnos');
        $hiddens = ['medico_id' => $medico->id, 'consultorio' => $consultorio, 'option' => $option];
      }
    @endphp

    <form method="POST" action="{{ $formAction }}" @if($formAction === '#') onsubmit="return false;" @endif>
      @csrf
      @foreach($hiddens as $name => $val)
        <input type="hidden" name="{{ $name }}" value="{{ $val }}" />
      @endforeach
      <button class="btn btn-primary-outline img-responsive img_home" type="submit">
        <img class="card-img-top" src="images/medicos/{{ $medico->foto }}" alt="">
      </button>

      <div class="card-body">
        <h6 align="center">{{ $medico->apellido }}, <br> {{ $medico->nombre }}</h6>
      </div>
    </form>
  </div>
  @endforeach
</div>

<script type="text/javascript">
  function checkResolution(){
    var w = window.innerWidth;
    var cantidad = document.getElementById("seleccionar_medico_cantidad").value;

    if(w < 420){
      for (var i = 0; i < cantidad; i++) {
        var div = document.getElementById("seleccionar_medico_style_id_"+i);
        if(div != null)
          div.setAttribute('style', 'style="height:450px');
      }
    }
  }
</script>

@endsection
