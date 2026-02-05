
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