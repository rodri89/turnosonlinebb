   					
	<form method="POST" action="{{ route('pay') }}" id="paymentForm">		
		@csrf
		<input hidden id="medico_id" name="medico_id" value="{{$medico->id}}"></input>	 			
		<input hidden id="turno_id" name="turno_id" value="{{$turnoRegistrado->id}}"></input>	 			
		@if($payment != null)
			<b><h4 class="letrasrojo lead">Ha ocurrido un error la transacción no fue realizada.</h4></b>			
		@endif
		<b><h4 class="letraAzul lead marginTopCel_15px">Para realizar la videollamada deberá abonar el turno.</h4></b>		

		<div class="form-group form-row">				
			<input  type="text" class="form-control sinBackgroundMp letraAzul" value="Importe $">
			<div class="col">		
				<input disabled type="number" min="5" step="0.01" class="form-control sinBackgroundMp" name="value" value="{{$videollamada->importe}}">
			</div>
		</div>
		<div class="row mt-3">
			<div class="col">
				<label class="letraAzul">Click sobre el Metodo de pago</label>
				<div class="form-group" id="toggler"> <!-- Para que funcione el collapse hay que agregar un id padre en este caso toggler -->
					<div class="btn-group btn-group-toggle" data-toggle="buttons">
						<!-- aca iria un for each si tenes mas un metodo de pago 
						  el label tiene que tener data-target= con un id que despues utilizamos abajo en el div y el data-toggle collapse para decir que va a colapsar -->
						<label class="btn btn-outline-secondary rounded m-2 p-1" data-target="#mercadopagoCollapse" data-toggle="collapse">
						<input type="radio" name="payment_platform" value="MercadoPago" required>
						<img class="img_turno_apl" src="{{asset('images/iconos/mercadopago.png')}}">
					</div>
					<div id="mercadopagoCollapse" class="collapse" data-parent="#toggler"> <!-- data-parent toggler referencia al de arriba y la clase collapse me indica que va a colapsar y el id -->
					@include('components.mercadopago_collapse')
					</div>
				</div>
			</div>
		</div>					
	</form> 			
