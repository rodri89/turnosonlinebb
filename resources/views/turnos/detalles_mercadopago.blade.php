
@extends('turnos/modelo_plantilla_timer')

@section('title','Mercado Pago Detalles')

@section('titulo_header','Mercado Pago Detalles de Pago')

@section('descripcion_header','Su pago ha sido exitoso.')

@section('body_titulo','')

@section('contenedor')

<div class="row">
	<div class="col-md-6">	    
     	<h2 class="fontHomeTitulo lead">Click en continuar para volver a mis turnos.</h2>	  			 		 
		 <button type="submit" id="payButton" class="rodri_button">Continuar</button>
	</div>
</div>

@endsection