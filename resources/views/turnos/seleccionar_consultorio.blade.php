
@extends('turnos/modelo_plantilla')

@section('titulo_header','Seleccionar Consultorio')

@section('headerContainer')
  <div class="col-md-2 mb-5">
    <img class="img-fluid" src="images/iconos/paula_clinica.png" alt="Consultorio" style="max-height: 120px;">
  </div>
@endsection

@section('descripcion_header','En esta sección podrá elegir el consultorio que desee.')

@section('body_titulo')
<span class="fontColorHeader">Seleccione el médico en este consultorio.</span>
@endsection

@section('contenedor')

<div class="row">
	@foreach($consultorios as $consultorio)  
	<div class="col-md-2 mb-3">
   		<div>   			        
        <form method="POST" action="{{ route('consultorioseleccionarmedico') }}">
          @csrf
          <input type="hidden" name="consultorio_id" value="{{$consultorio->id}}"  />      
          
      	   <button class="btn btn-primary-outline"><img class="card-img-top" src="images/consultorios/{{$consultorio->foto}}" alt=""></button>
      		<div class="card-body">
        		<h4 align="center">{{$consultorio->nombre}}</h4>      
      		</div>    	  
        </form>
    	</div>
    </div>
    @endforeach
</div>

@endsection