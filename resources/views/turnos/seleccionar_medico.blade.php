
@extends('turnos/modelo_plantilla')

@section('titulo_header','Seleccionar Médico')

@section('headerContainer')
  <div class="col-md-2 mb-5">
@endsection

@section('descripcion_header','En esta sección podrá elegir el médico especialista que desee.')

@section('body_titulo','Click en el médico para continuar.')

@section('contenedor')

@php
  $especialidad_id = $especialidad_id ?? '';
@endphp

<div class="row">
  @foreach($medicos as $medico)
	<!--<div class="col-md-2 mb-3 ">-->
    @if($medico->castigo_automatico == 1 || !empty($turno_test_mode))
      @php
        $espNombreHidden = isset($especialidad_nombre_flujo_lista) && $especialidad_nombre_flujo_lista !== null && $especialidad_nombre_flujo_lista !== ''
          ? $especialidad_nombre_flujo_lista
          : (($especialidad_txt ?? null) ? $especialidad_txt : ($medico->e_nombre ?? ''));
      @endphp
      <div class="text-center">
      @if($medico->id == 44)
        <form method="POST" action="https://patient.consultoriomovil.net/patient/web/carolinaheinrich/">
      @else
        <form method="POST" action="{{ route('ingresarpaciente') }}">
      @endif
          @csrf
          <input type="hidden" name="medico_id" value="{{$medico->id}}"  /> 
          <input type="hidden" name="especialidad_id" value="{{$especialidad_id}}"  />
          <input type="hidden" name="especialidad_nombre_flujo" value="{{ $espNombreHidden }}" />   		    
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
            @if($medico->id == 44)
            <label class="fontImage" align="center" style="color: {{$medico->color}}"><b>Pediátrica</b></label>
            @endif                
      		</div>


      </form>
    </div>
    @endif
    <!--</div> -->
    @endforeach
</div>

@endsection