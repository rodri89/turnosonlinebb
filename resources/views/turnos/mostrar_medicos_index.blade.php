
@extends('turnos/modelo_plantilla')

@section('titulo_header', '')

@section('descripcion_header', 'En esta sección se encuentran todos los especialistas que utilizan el sitio.')

@section('body_titulo','Listado de médicos')

@section('contenedor')

<div class="row">
  @foreach($medicos as $medico)
  <!--<div class="col-md-2 mb-3 ">-->
    @if($medico->castigo_automatico == 1)
     <form method="POST" action="{{ route('ingresarpaciente') }}">
          @csrf
          <input type="hidden" name="medico_id" value="{{$medico->id}}"  />
          <input type="hidden" name="especialidad_id" value="{{ $medico->especialidad }}" />
          <input type="hidden" name="especialidad_nombre_flujo" value="{{ $medico->e_nombre ?? '' }}" />
          <button class="btn btn-primary-outline img-responsive img_home">
            <img class="card-img-top " src="images/medicos/{{$medico->foto}}" alt="">
          </button>
          <div class="card-body">
            <h6 class="fontImage" align="center">{{$medico->m_apellido}}, <br> {{$medico->m_nombre}}</h6>      
            <h6 class="fontImage" align="center" style="color: {{$medico->color}}"><b>{{$medico->e_nombre}}</b></h6>                  
          </div>        
      </form>
    @endif
    <!--</div> -->
    @endforeach
</div>

@endsection