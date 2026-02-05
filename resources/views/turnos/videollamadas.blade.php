
@extends('turnos/modelo_plantilla_timer')

@section('title','Consultar Turno')

@section('titulo_header','Sección Videollamadas')

@section('descripcion_header','')
@section('headerContainer')
<ul>  
  <li class="lead fontColorHeader">Cuando el paciente y el profesional esten disponibles un link aparecerá y podrá realizar su consulta.</li>
  <li class="lead fontColorHeader">Si esta desde un telefono celular, para realizar la videollamada es necesario tener instalada la aplicacion Hangout y tener un correo electrónico, para verificarlo <a data-target="#modalAyudaVideollamada" data-toggle="modal" class="MainNavText letrasblancas" id="MainNavHelp" 
       href="#modalAyudaVideollamada">Click aquí.</a></li>  
  <li class="lead fontColorHeader">Importante: instalar la aplicación puede tomar unos minutos, realice la prueba con anticipación previa a su horario de consulta.</li>
</ul>

@endsection
@section('body_titulo','')

@section('contenedor')

<div class="row">
	<div class="col-md-6">	    
     	<h2 class="fontVideollamadaTitulo lead">Información del turno</h2>	  	
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
	@if($moduloMercadoPago == 0) 
		@include('components.videollamada_asistir')
	@else
		@if($turnoRegistrado->pago == 1)
			@include('components.videollamada_asistir')
		@else
			@if($medicoTrabajaConObraSocial == 1)
				@if($tieneObraSocialCompleta == 1)
					@include('components.videollamada_asistir')		
				@else
          @if($paciente->obra_social === 'PARTICULAR')
            @include('components.mercadopago_pagar')  
          @else
    				<div class="col-md-6">
    					<label class="letraAzul">Los datos de la obra social estan incompletos es necesario cargar su foto.</label>	
    					<button onclick="mostrarModalCargarFoto()" type="submit" class="rodri_button_aceptar">Cargar Foto</button>
    				</div>
          @endif
				@endif	
			@else
				<div class="col-md-6">	 
					<label class="letraAzul">El profesional no trabaja con su obra social</label>				
					@include('components.mercadopago_pagar')	
				</div>
			@endif
		@endif
	@endif
@endif

@if($videollamada->perfil == 2)
	@if($turnoRegistrado->pago == 1) 
		@include('components.videollamada_asistir')	
	@else
		<div class="col-md-6">	 	 
			@include('components.mercadopago_pagar')		
		</div>
	@endif
@endif
</div>

<br><br>
<div class="row contenedor3" id="seccion_link" hidden>		
	<h2 class="fontHomeTitulo lead contenido3 videollamada_margin_left_50px"> <a href="{{$videollamada->link}}" target="_blank"><span class="luz" id="blink"><b>Click aqui para iniciar la videollamada</b></span></a></h2>
	<h2 hidden id="link_fijo"  class="fontHomeTitulo lead contenido3"> <a href="{{$videollamada->link}}" target="_blank">Click aqui para iniciar la videollamada</a></h2>
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
@include('modal.modal_cargar_foto_obra_social')
@include('modal.snackbar')
@include('modal.modal_instructivo_videollamadas')

<script>
(function() {

setInterval(function(){
  var el = document.getElementById('blink');
  if(el.className == 'luz'){
      el.className = 'luz on';
  }else{
      el.className = 'luz';
  }
},500);

})();
</script>

<style>
.luz.on{
  color: #0099ff;/*color del texto al cambiar*/
  text-shadow:
     1px  1px rgba(255, 255, 255, .1),
    -1px -1px rgba(0, 0, 0, .88),
     0px  0px 20px #0099ff;/*color de la luz del texto*/
}
.luz{
  font-size:40px;/*tamaño de la fuente*/
  color: #000000;
  text-shadow:
     1px  1px rgba(255, 255, 255, .1),
    -1px -1px rgba(0, 0, 0, .88);
}
</style>

<div id="snackbar"><p><input class="input_snackbar_220" id="snackbar_msj" name="snackbar_msj"></input></p></div>
<script type="text/javascript">

function actualizarEstadoPaciente(paciente_id, turnosRegistrado_id, estado){ 	
    if(estado == 1){
				//document.getElementById("estado_paciente").value = 'Disponible';
				var ep = document.getElementById("estado_paciente");//.value = 'Disponible';
    			ep.value = 'Disponible'
    			ep.setAttribute('class', 'sinBackgroundMedioAncho letrasVerde');    	
				document.getElementById("seccion_disponible").hidden = true;
				document.getElementById("seccion_no_disponible").hidden = false;
			} else {
				//document.getElementById("estado_paciente").value = 'No Disponible';
				var ep = document.getElementById("estado_paciente");//.value = 'Disponible';
    			ep.value = 'No Disponible'
    			ep.setAttribute('class', 'sinBackgroundMedioAncho letrasrojo');
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
    			var ep = document.getElementById("estado_profesional"); //.value = 'Disponible';
    			ep.value = 'Disponible'
    			ep.setAttribute('class', 'sinBackgroundMedioAncho letrasVerde');    		
    		} else {
    			var ep = document.getElementById("estado_profesional");
    			ep.value = 'No Disponible'
    			ep.setAttribute('class', 'sinBackgroundMedioAncho letrasrojo');
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
    	}
    });  	
  }

  function mostrarModalCargarFoto(){
  	$('#modalCargarFotoObraSocial').modal();
  }


</script>

@endsection