
@extends('turnos/modelo_plantilla')

@section('titulo_header','Seleccionar Especialidad')

@section('headerContainer')
  <div class="col-md-2 mb-5">
@endsection

@section('descripcion_header','En esta sección podrá elegir la especialidad que desees')

@section('body_titulo','Click en la especialidad para continuar.')

@section('contenedor')

<div class="row">
  @foreach($especialidades as $especialidad)
  <!--<div class="col-md-2 mb-3 ">-->
    @if($especialidad->activo == 1)
      <form method="POST" action="{{ route('seleccionarmedicoespecialidad') }}">
          @csrf
          <input type="hidden" name="especialidad_id" value="{{$especialidad->id}}"  />           
          <button class="btn btn-primary-outline img-responsive img_home">
            <img class="card-img-top " src="images/especialidad/{{$especialidad->foto}}" alt="">
          </button>
          <div class="card-body">
            <h6 class="fontImage" align="center" style="color: {{$especialidad->color}}; font-size: 20px; width: 100px; margin-left: 20px">
              {{$especialidad->nombre}}
            </h6>            
          </div>        
      </form>
    @endif
    <!--</div> -->
    @endforeach
</div>

<!-- Botón flotante "Mis Turnos" solo para móviles -->
<div class="boton-flotante-mis-turnos">
  <a href="/mis_turnos" class="flotante-mis-turnos rodri_button">Mis Turnos</a>
</div>

<style>
  /* Botón flotante solo visible en móviles */
  .boton-flotante-mis-turnos {
    display: none;
  }
  
  @media (max-width: 768px) {
    .boton-flotante-mis-turnos {
      display: block;
    }
    
    .flotante-mis-turnos {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 35px;
      font-size: 18px;          
      width: 120px;
      position: fixed;
      bottom: 25px;
      right: 25px;
      z-index: 1000;
      text-decoration: none;
      text-align: center;
      line-height: 30px;
    }
    
    .flotante-mis-turnos:hover {
      text-decoration: none;
      color: #FFF;
    }
  }
</style>

@endsection