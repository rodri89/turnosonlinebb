@extends('turnos_admin_secretaria/turnos_admin_secretaria_plantilla')

<input type="hidden" id="seleccionar_medico_screen">

@section('contenedor')
<input id="seleccionar_medico_cantidad" type="hidden" value="{{$medicos->count()}}">
<?php $cont = 0; ?>
<div class="row">
  @foreach($medicos as $medico)  
  <div id="seleccionar_medico_style_id_{{$cont}}" class="col-md-2 mb-3 text-center" style="height:450px">
    <?php $cont++; ?>

    @if($option == 1) 
    <form method="POST" action="{{ route('turnosasignadosdia') }}">
    @else
      @if($option == 2) 
      <form method="POST" action="{{ route('secretariaadminsobreturnos') }}">
      @else
        @if($option == 3)       
        <form method="POST" action="{{ route('secretariaasignarturnos') }}">
        @else
          @if($option == 4)       
          <form method="POST" action="{{ route('secretariaasignarturnos') }}">
            <input type="hidden" name="paciente_dni" value="{{$paciente_dni}}"  />           
          @else
            @if($option == 5)       
              <form method="POST" action="{{ route('secretariaadminsobreturnos') }}">
                <input type="hidden" name="paciente_dni" value="{{$paciente_dni}}"  />           
            @else
              @if($option == 6)
              <form method="POST" action="{{ route('secretariabloquearturnos') }}">
              @endif
            @endif
          @endif
        @endif
      @endif
    @endif
          @csrf
          <input type="hidden" name="medico_id" value="{{$medico->id}}"  />           
          <input type="hidden" name="consultorio" value="{{$consultorio}}"  />           
          <input type="hidden" name="option" value="{{$option}}"  />           
          <button class="btn btn-primary-outline img-responsive img_home">
            <img class="card-img-top" src="images/medicos/{{$medico->foto}}" alt="">
          </button>
        
          <div class="card-body">
            <h6 align="center">{{$medico->apellido}}, <br> {{$medico->nombre}}</h6>      
          </div>        
      </form>
    </div>
    @endforeach
</div>

<script type="text/javascript">
  

  function checkResolution(){
    var w = window.innerWidth;
    var h = window.innerHeight;
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