
@extends('turnos/modelo_plantilla')

@section('titulo_header','Seleccionar Médico')

@section('headerContainer')
  <div class="col-md-2 mb-5">
@endsection

@section('descripcion_header','En esta sección podrá elegir el médico especialista que desee.')

@section('body_titulo','Click en el médico para continuar.')

@section('contenedor')

<div class="row">
  @foreach($medicos as $medico)
	<!--<div class="col-md-2 mb-3 ">-->
    @if($medico->castigo_automatico == 1)
      <div class="text-center">
      <form method="POST" action="{{ route('ingresarpaciente') }}">
          @csrf
          <input type="hidden" name="medico_id" value="{{$medico->id}}"  /> 
          <input type="hidden" name="especialidad_id" value="{{$especialidad_id}}"  />   		    
      		<button class="btn btn-primary-outline img-responsive img_home">
            <img class="card-img-top " src="images/medicos/{{$medico->foto}}" alt="">
          </button>
      		<div class="card-body">
        		<h6 class="fontImage" align="center">{{$medico->m_apellido}}, <br> {{$medico->m_nombre}}</h6>      
            <h6 class="fontImage" align="center" style="color: {{$medico->color}}"><b>@if($especialidad_txt != null){{$especialidad_txt}}@else{{$medico->e_nombre}}@endif</b></h6>  
            @if($medico->id == 31)
            <label class="fontImage" align="center">Atención de trastornos deglutorios</label>
            @endif
            @if($medico->id == 33)
            <label class="fontImage" align="center"><b>INFANTIL</b></label>
            @endif
            @if($medico->id == 34)
            <label class="fontImage" align="center"><b>ADULTOS</b></label>
            @endif                
      		</div>


      </form>
    </div>
    @endif
    <!--</div> -->
    @endforeach
</div>

@endsection