<style>
.mercadopago_collapse_250px{
 width: 250px; 
}

.mercadopago_collapse_70px{
 width: 70px; 
}

.mercadopago_collapse_150px{
 width: 150px; 
}

.mercadopago_collapse_100px{
 width: 100px;  
}

.mercadopago_collapse_30px{
 width: 30px; 
}

.sinBackgroundMp{
  background-color: transparent;
  border: none;
  width: 92px;
}

.mercadoPago_img{
	
}

@media (max-width:360px){
.sinBackgroundMp{
  background-color: transparent;
  border: none;
  width: 92px;
}

.mercadopago_collapse_250px{
 width: 300px; 
}

.mercadopago_collapse_70px{
 width: 70px; 
}

.mercadopago_collapse_150px{
 width: 130px; 
}

.mercadopago_collapse_100px{
 width: 100px;  
}

.mercadopago_collapse_30px{
 width: 10px; 
}

.fontVideollamadaTitulo{
  font-size: 1.4rem;
  color:#303F9F;
}

.videollamada_margin_left_50px{  
  text-align: center;
}
}
</style>
<div id="error_tarjeta" hidden>
	<label class="mt-3 letrasrojo">El pago no ha sido procesado, hay algun dato incorrecto. </label> <br>
</div>
<label class="mt-3 letraAzul"> Detalles de tarjeta: </label>

<div class="form-group form-row">
	<div class="col">
		<small clsss="letraAzul">Numero de tarjeta</small>
		<input class="form-control mercadopago_collapse_250px" type="text" id="cardNumber" data-checkout="cardNumber" placeholder="Numero tarjeta">
	</div>	

	<div class="col">
		<small clsss="letraAzul">CVC</small>
		<input class="form-control mercadopago_collapse_70px" type="text" data-checkout="securityCode" placeholder="CVC">
	</div>
	
	<div class="mercadopago_collapse_30px"></div>
	
	<div class="col">
		<small clsss="letraAzul">MM</small>
		<input class="form-control mercadopago_collapse_70px" type="text" data-checkout="cardExpirationMonth" placeholder="MM">
	</div>

	<div class="col">
		<small clsss="letraAzul">YY</small>
		<input class="form-control mercadopago_collapse_70px" type="text"  data-checkout="cardExpirationYear" placeholder="YY">
	</div>
	
</div>

<div class="form-group form-row">
	<div class="col">
		<small clsss="letraAzul">Nombre</small>
		<input class="form-control mercadopago_collapse_250px" type="text" data-checkout="cardholderName" placeholder="Nombre">
	</div>

	<div class="col">
		<small clsss="letraAzul">Email</small>
		<input class="form-control mercadopago_collapse_250px" type="email" data-checkout="cardholderEmail" placeholder="email@example.com" name="email">
	</div>
</div>

<div class="form-group form-row">
	<div class="col">
		<small clsss="letraAzul">Tipo</small>
		<select class="form-control mercadopago_collapse_100px" id="docType" data-checkout="docType"></select>
		<!--<select class="form-control mercadopago_collapse_100px" data-checkout="docType"></select>-->
	</div>

	<div class="col">
		<small clsss="letraAzul">Numero</small>
		<input class="form-control mercadopago_collapse_150px" type="text" data-checkout="docNumber" placeholder="Número">
	</div>
</div>

<div class="form-group form-row">
	<div class="col">
		<small class="form-text text-muter" role="alert">Su pago será convertido a peso Argentino.</small>
	</div>
</div>

<div class="form-group form-row">
	<div class="col">
		<small class="form-text text-danger" id="paymentErrors" role="alert"></small>
	</div>
</div>

<div class="text-center mt-3">
	<button type="submit" id="payButton" class="rodri_button">Pagar</button>
</div>
<br>
<div class="text-center">
<img class="mercadoPago_img"  src="https://imgmp.mlstatic.com/org-img/banners/ar/medios/468X60.jpg" 
title="Mercado Pago - Medios de pago" alt="Mercado Pago - Medios de pago" 
width="468" height="60"/>
</div>

<input type="hidden" id="cardNetwork" name="card_network">
<input type="hidden" id="cardToken" name="card_token">

<script src="https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js"></script>

<script>
	var videollamadaKey = document.getElementById("videollamada_key").value;
	const mercadoPago = window.Mercadopago;
	mercadoPago.setPublishableKey(videollamadaKey);
	mercadoPago.getIdentificationTypes();
</script>

<script>
	function setCardNetwork(){	
		const cardNumber = document.getElementById("cardNumber");		
		mercadoPago.getPaymentMethod(
			{ "bin": cardNumber.value.substring(0,6) }, 
			function(status, response){
				const cardNetwork = document.getElementById("cardNetwork");

				cardNetwork.value = response[0].id;
				//alert(status);
			});
	}	
</script>

<script>
	const mercadoPagoForm = document.getElementById("paymentForm");

	mercadoPagoForm.addEventListener('submit', function(e){
		e.preventDefault();
		//console.log(mercadoPagoForm.innerHTML);
		//alert(mercadoPagoForm.innerHTML);
		mercadoPago.createToken(mercadoPagoForm, function(status, response){
			/*var array = Object.entries(response);
			alert(array[0]); // error
			alert(array[1]); // message 
			alert(array[2]); // cause */			
			//alert(Object.entries(response));
			console.log(Object.entries(response));
			if(status != 200 && status != 201 ) {
				//alert(status);				
				document.getElementById("error_tarjeta").hidden = false;
				const error = document.getElementById("paymentErrors");
				errors.textContent = response.cause[0].description;
			} else {
				document.getElementById("error_tarjeta").hidden = true;
				const cardToken = document.getElementById("cardToken");
				setCardNetwork();
				cardToken.value = response.id;

				mercadoPagoForm.submit();
			}
		});

	});
</script>