
@extends('turnos/modelo_plantilla_timer')

@section('title','Consultar Turno')

@section('titulo_header','Sección Videollamadas')

@section('descripcion_header','')
@section('headerContainer')
<ul>  
  <li class="lead fontColorHeader">Cuando el paciente y el profesional esten disponibles, un link aparecerá y podrá realizar su consulta.</li>
  <li class="lead fontColorHeader">Si usted está conectado desde un teléfono celular, para realizar la videollamada es necesario tener instalada la aplicación Hangout y tener un correo electrónico. Para verificarlo <a data-target="#modalAyudaVideollamada" data-toggle="modal" class="MainNavText letrasblancas" id="MainNavHelp" 
       href="#modalAyudaVideollamada">Click aquí.</a></li>    
  <li class="lead fontColorHeader"><h5 class="letrasrojo">IMPORTANTE:</h5><i>Instalar la aplicación puede tomar unos minutos, realice la prueba con anticipación previa, a su horario de consulta.</i></li>
</ul>

@endsection


@section('contenedor')

<div class="row">
	<div class="col-md-6">	    
     	<h2 class="fontHomeTitulo lead">Información del turno</h2>	  	
		 <h6 class="fontColorHeader">Paciente: {{$paciente->apellido.', '.$paciente->nombre}}</h6>		 		
		 <h6 class="fontColorHeader">Medico: {{$medico->apellido.', '.$medico->nombre}}</h6>		 		
		 <h6 class="fontColorHeader">Fecha: {{$fechaTurno}}</h6>		 	
		 <h6 class="fontColorHeader">Horario: {{$horario}}</h6>	
		 <input hidden id="turno_id" name="turno_id" value="{{$turnoRegistrado->id}}"></input>	 			
		 <input hidden id="videollamada_key" value="{{$videollamada->key}}"></input>	 					 		 
	</div>
	<!-- 
	1) tres opciones: solo obra social y sino tiene obra social mercado pago.
	2) solo mercado pago.
	3) obra social mas diferencial, deberia haber un diferencial para cada obra social.
	 -->
	@if($videollamada->perfil == 1)
		@if(($tieneObraSocialCompleta == 1) || ($turnoRegistrado->pago == 1 || $moduloMercadoPago == 0)) 
			<div class="col-md-6">	    
		     	 <b><h4 class="letraAzul lead">Indique al profesional que esta listo para asistir y espere que el profesional este disponible.</h4></b>
		     	 <div id="seccion_disponible">     	
		     	 <h6 class="fontColorHeader">Click en disponible para indicar que esta listo: <button onclick="actualizarEstadoPaciente('{{$paciente->id}}','{{$turnoRegistrado->id}}', 1)" type="button" class="rodri_button_aceptar">Disponible</button></h6>	     
		     	</div>
		     	<div hidden id="seccion_no_disponible">
		     	 <h6 class="fontColorHeader">Click en no disponible para indicar que no esta listo: <button onclick="actualizarEstadoPaciente('{{$paciente->id}}','{{$turnoRegistrado->id}}', 0)" type="button" class="rodri_button_cancelar">No Disponible</button></h6>
		     	</div>   
				 <h6 class="fontColorHeader">Estado del paciente: <input disabled class="sinBackgroundMedioAncho letraAzul" id="estado_paciente" name="estado_paciente"></input></h6>
				 <h6 class="fontColorHeader">Estado del profesional: <input disabled class="sinBackgroundMedioAncho letraAzul" id="estado_profesional" name="estado_profesional"></input></h6>
			</div>	
		@else
			@if($paciente->dni == 34741602)	<!-- -->
				@if($turnoRegistrado->pago_ticket == null)
					<div class="col-md-6">
						<b><h4 class="letraAzul lead">Para realizar la videollamada deberá abonar el turno.</h4></b>		
						
						<a mp-mode="dftl" href="{{$videollamada->link_pago}}" name="MP-payButton" class='blue-ar-l-rn-none'>Pagar</a>
							<script type="text/javascript">
							(function(){function $MPC_load(){window.$MPC_loaded !== true && (function(){var s = document.createElement("script");s.type = "text/javascript";s.async = true;s.src = document.location.protocol+"//secure.mlstatic.com/mptools/render.js";var x = document.getElementsByTagName('script')[0];x.parentNode.insertBefore(s, x);window.$MPC_loaded = true;})();}window.$MPC_loaded !== true ? (window.attachEvent ?window.attachEvent('onload', $MPC_load) : window.addEventListener('load', $MPC_load, false)) : null;})();
							</script>

						<b><h4 class="letraAzul lead">Si ya realizo el pago ingrese el número de operación.</h4></b>	
						<div class="row">
							<div class="col-md-4">				
			          			<input type="text" class="form-control editText" id="numero_operacion" name="numero_operacion" placeholder="N° Operacion" />
			          		</div>
			          		<div>
			          		<button onclick="guardarNumeroOperacion('{{$turnoRegistrado->id}}')" type="button" class="rodri_button_aceptar">Guardar</button>
			          		</div>
		          		</div>
							<small>NOTA: El número de operación fue enviado por mercado pago a su correo.</small>		
					</div>
				@else
					<div class="col-md-6">
						<h2 class="fontHomeTitulo lead">Información del pago</h2>	  	
						<b><h4 class="letraAzul lead">El pago debe ser validado por el profesional.</h4></b>		
						<b><h4 class="letraAzul lead">Una vez que sea validado podrá acceder a la videollamada.</h4></b>		
					</div>
				@endif
			@endif
		@endif
	@endif <!-- fin perfil 1-->
	<!-- Siempre paga la consulta no importa si tiene obra social-->
	@if($videollamada->perfil == 2)		
		@if($turnoRegistrado->pago == 1)
			<div class="col-md-6">	    
	     	 <b><h4 class="letraAzul lead">Indique al profesional que esta listo para asistir y espere que el profesional este disponible.</h4></b>
	     	 <div id="seccion_disponible">     	
	     	 <h6 class="fontColorHeader">Click en disponible para indicar que esta listo: <button onclick="actualizarEstadoPaciente('{{$paciente->id}}','{{$turnoRegistrado->id}}', 1)" type="button" class="rodri_button_aceptar">Disponible</button></h6>	     
	     	</div>
	     	<div hidden id="seccion_no_disponible">
	     	 <h6 class="fontColorHeader">Click en no disponible para indicar que no esta listo: <button onclick="actualizarEstadoPaciente('{{$paciente->id}}','{{$turnoRegistrado->id}}', 0)" type="button" class="rodri_button_cancelar">No Disponible</button></h6>
	     	</div>   
			 <h6 class="fontColorHeader">Estado del paciente: <input disabled class="sinBackgroundMedioAncho letraAzul" id="estado_paciente" name="estado_paciente"></input></h6>
			 <h6 class="fontColorHeader">Estado del profesional: <input disabled class="sinBackgroundMedioAncho letraAzul" id="estado_profesional" name="estado_profesional"></input></h6>
		</div>
		@else
			@if($turnoRegistrado->pago_ticket == null)
					<div class="col-md-6">
						<b><h4 class="letraAzul lead">Para realizar la videollamada deberá abonar el turno.</h4></b>		
						
						<a mp-mode="dftl" href="{{$videollamada->link_pago}}" name="MP-payButton" class='blue-ar-l-rn-none'>Pagar</a>
							<script type="text/javascript">
							(function(){function $MPC_load(){window.$MPC_loaded !== true && (function(){var s = document.createElement("script");s.type = "text/javascript";s.async = true;s.src = document.location.protocol+"//secure.mlstatic.com/mptools/render.js";var x = document.getElementsByTagName('script')[0];x.parentNode.insertBefore(s, x);window.$MPC_loaded = true;})();}window.$MPC_loaded !== true ? (window.attachEvent ?window.attachEvent('onload', $MPC_load) : window.addEventListener('load', $MPC_load, false)) : null;})();
							</script>

						<b><h4 class="letraAzul lead">Si ya realizo el pago ingrese el número de operación.</h4></b>	
						<div class="row">
							<div class="col-md-4">				
			          			<input type="text" class="form-control editText" id="numero_operacion" name="numero_operacion" placeholder="N° Operacion" />
			          		</div>
			          		<div>
			          		<button onclick="guardarNumeroOperacion('{{$turnoRegistrado->id}}')" type="button" class="rodri_button_aceptar">Guardar</button>
			          		</div>
		          		</div>
							<small>NOTA: El número de operación fue enviado por mercado pago a su correo.</small>		
					</div>
				@else
					<div class="col-md-6">
						<h2 class="fontHomeTitulo lead">Información del pago</h2>	  	
						<b><h4 class="letraAzul lead">El pago debe ser validado por el profesional.</h4></b>		
						<b><h4 class="letraAzul lead">Una vez que sea validado podrá acceder a la videollamada.</h4></b>		
					</div>
				@endif
		@endif

	@endif	 <!-- fin perfil 2-->
</div>

<br><br>
<div class="row contenedor3" id="seccion_link" hidden>	
	<h2 class="fontHomeTitulo lead contenido3"> <a href="{{$videollamada->link}}" target="_blank">Clik aqui para iniciar la videollamada</a></h2>	
</div>

<div class="modal fade" id="modalAyudaVideollamada" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title" 
        id="favoritesModalLabel">Verificar Hangout</h4>
      </div>
      <div class="modal-body"> 
          <h6>Para verificar si tiene instalada la aplicacion haga click en el siguiente link.</h6><br>
          <h6><a href="https://hangouts.google.com/call/-58qHK5XlrI5hxiz29DBAEEE" target="_blank" id="a_link_existe">Click aquí para probar la videollamada.</a></h6>
          <h6>Si no tiene instalada la aplicación, podrá hacerlo desde ese link.</h6><br>
          <h6>Si ya tiene instalada la aplicacion click en el link y luego CLICK en el botón UNIRME.</h6><br>
          <h6>Esto es solo una prueba. Para realizar la consulta con el médico deberá esperar que se habilite el link en el horario programado.</h6><br>
          
      </div>
      <div class="modal-footer">
        <button type="button" 
           class="rodri_button_aceptar" 
           data-dismiss="modal">Aceptar</button>                                      
      </div>                                      
      </div>
    </div>
</div>

@include('modal.snackbar')
@include('modal.modal_instructivo_videollamadas')
<div id="snackbar"><p><input class="input_snackbar_220" id="snackbar_msj" name="snackbar_msj"></input></p></div>
<script type="text/javascript">

function actualizarEstadoPaciente(paciente_id, turnosRegistrado_id, estado){ 	
    if(estado == 1){
				document.getElementById("estado_paciente").value = 'Disponible';
				document.getElementById("seccion_disponible").hidden = true;
				document.getElementById("seccion_no_disponible").hidden = false;
			} else {
				document.getElementById("estado_paciente").value = 'No Disponible';
				document.getElementById("seccion_disponible").hidden = false;
				document.getElementById("seccion_no_disponible").hidden = true;
			}
    $.ajax({
	    type:'POST',
	    dataType:'JSON',
	    url:'/actualizar_estado_paciente_videollamada',
	    data:{paciente_id: paciente_id, turno_id:turnosRegistrado_id, estado:estado, _token: '{{csrf_token()}}'},
	    success:function(data){    	    	    
    			
    	}
    }); 
}

function checkEstadoMedico(){
	var turno_id = document.getElementById("turno_id").value;			   
	$.ajax({
	    type:'POST',
	    dataType:'JSON',
	    url:'/check_estado_medico_videollamada',
	    data:{turno_id: turno_id, _token: '{{csrf_token()}}'},
	    success:function(data){    	    	    	
    		if(data.turno.disponible_medico == 1){
    			document.getElementById("estado_profesional").value = 'Disponible';
    		} else {
    			document.getElementById("estado_profesional").value = 'No Disponible';
    		}	
    		if((data.turno.disponible_medico == 1) && (data.turno.disponible == 1)){
    			document.getElementById("seccion_link").hidden = false;
    		} else {
    			document.getElementById("seccion_link").hidden = true;
    		}
    	}
    });	
}

function timerFunction(){
	checkEstadoMedico();
}

 /*window.onload=function() {
 	timerFunction();
 	$("#modalInstructivoVideollamadas").modal();      
  }
  */

  function mostrarSnackbar(text) {    
    document.getElementById("snackbar_msj").value = text;
    var x = document.getElementById("snackbar");
    x.className = "show";
    setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
  }

  function guardarNumeroOperacion(turno_id){
	var numero_operacion = document.getElementById("numero_operacion").value;	  		
  	$.ajax({
	    type:'POST',
	    dataType:'JSON',
	    url:'/guardar_numero_operacion',
	    data:{turno_id: turno_id, numero_operacion:numero_operacion, _token: '{{csrf_token()}}'},
	    success:function(data){    	    	    	
    		mostrarSnackbar("Guardado");
    		location.reload();
    	}
    });  	
  }

</script>

@endsection