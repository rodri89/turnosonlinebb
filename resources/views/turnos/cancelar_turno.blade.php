
@extends('turnos/modelo_plantilla')

@section('title','Consultar Turno')

@section('titulo_header','Consultar/Cancelar Turno')

@section('headerContainer')
<ul>  
  <li class="lead fontColorHeader">Ingrese el número de DNI y click en "Consultar". </li>
  <li class="lead fontColorHeader">Si desea cancelar su turno, debe hacer click en "Cancelar".</li>
  <li class="lead fontColorHeader">Si esta desde un celular presione sobre la tabla y arrastre hacia la izquierda .</li>
</ul>

@endsection

@section('body_titulo','')

@section('contenedor')
<style>
  /* Estilos para el botón Consultar en móviles */
  @media (max-width: 768px) {
    .boton-consultar-mobile {
		height: 30px;
      width: 100% !important;
      margin-left: 10px;
      margin-top: 10px;
    }
  }
</style>

 <div class="row">
 
		<form method="POST" action="{{ route('cancelarturnodni') }}">
		  @csrf   
		  	<h2 class="fontHomeTitulo lead marginLeft10px">DNI del Paciente:</h2>
		  	@if($dni_paciente)
			  <input class="marginLeft10px marginTop20px form-control" type="text" name="dni_paciente" value="{{$dni_paciente}}" />       			    		
			  @else
				<input class="marginLeft10px marginTop20px form-control" type="text" name="dni_paciente" value="" />       			    				 
			  @endif
		  <br>
		  <button type="submit" class="rodri_button boton-consultar-mobile">Consultar</button>                          
		</form>
	
	@if($mensaje)
		<div class="col-md-8">
			<h2 class="text-center fontHomeTitulo lead marginTop50px">{{$mensaje}}</h2>
		</div>
	@else
	@if($dni_paciente)
		<br>
		<div class="col-md-8 marginTop20px">
		@if($turnosRegistrados)
			<h4>Mis Turnos:</h4>
			<div class="table-responsive" style="height:200px; ">
				<table class="table table-condensed" id="tabla_pacientes" name="tabla_pacientes">
			 		<thead>
					    <tr>
					      <th class="editText" scope="col">#</th>
					      <th class="editText" scope="col">Paciente</th>		      
					      <th class="editText" scope="col">Medico</th>
					      <th class="editText" scope="col">Fecha</th>
					      <th class="editText" scope="col">Horario</th>
					      <th class="editText" scope="col">Accion</th>
					    </tr>
			  		</thead>
			  		<tbody id="pacientes-list" name="pacientes-list">
			  			<?php $cont = 1 ?>
			  			@foreach($turnosRegistrados as $tr)
					    <tr>
					      <th class="editText" scope="row">{{$cont++}}</th>
					      <td class="editText">{{$tr["papellido"].', '.$tr["pnombre"]}}</td>		      
					      <td class="editText">{{$tr["apellido"].', '.$tr["nombre"]}}</td>		      
					      <?php $dia_aux = explode('-',$tr["fechaTurno"]);        
		                		$fechaMostrar = $dia_aux[2].'/'.$dia_aux[1].'/'.$dia_aux[0];?>
					      <td class="editText">{{$fechaMostrar}}</td>
					      <td class="editText">{{$tr["horario"]}}</td>
					      <form>
					      	 @csrf   
					  		<input type="hidden" name="turno_id" value="{{$tr['trid']}}" />
					  		<input type="hidden" name="dni_paciente" value="{{$dni_paciente}}" />
					      <td class="editText"><button onclick="cancelarTurno('{{$tr['trid']}}','{{$dni_paciente}}')" type="button" class="rodri_button_cancelar">Cancelar</button></td>
					  	  </form>
					    </tr>
					    @endforeach
					</tbody>
				</table>
			</div>
			<br><br>
		<!--</div> -->		
		@endif
		@if($turnosRegistradosVideollamadas->count()>0)
			<!--	<br>
				<div class="col-md-8 marginTop20px"> -->					
					<h4>Mis Turnos (Videollamada):</h4>
					<div class="table-responsive" style="height:200px; ">
						<table class="table table-condensed" id="tabla_pacientes_videollamada" name="tabla_pacientes_video">
					 		<thead>
							    <tr>
							      <th class="editText" scope="col">#</th>
							      <th class="editText" scope="col">Paciente</th>		      
							      <th class="editText" scope="col">Medico</th>
							      <th class="editText" scope="col">Fecha</th>
							      <th class="editText" scope="col">Horario</th>
							      <th class="editText" scope="col">Cancelar</th>
							      <th class="editText" scope="col">Estado</th>
							    </tr>
					  		</thead>
					  		<tbody id="pacientes-list" name="pacientes-list">
					  			<?php $cont = 1 ?>
					  			@foreach($turnosRegistradosVideollamadas as $tr)
							    	<tr>
							    		<th class="editText" scope="row">{{$cont++}}</th>
							      		<td class="editText">{{$tr->papellido.', '.$tr->pnombre}}</td>		      
							      		<td class="editText">{{$tr->apellido.', '.$tr->nombre}}</td>		      
							      		<?php $dia_aux = explode('-',$tr->fechaTurno);        
				                		$fechaMostrar = $dia_aux[2].'/'.$dia_aux[1].'/'.$dia_aux[0];?>
									    <td class="editText">{{$fechaMostrar}}</td>
									    <td class="editText">{{$tr->horario}}</td>
							      		<form>
							      	 		@csrf   
								  			<input type="hidden" name="turno_id" value="{{$tr->trid}}" />
								  			<input type="hidden" name="dni_paciente" value="{{$dni_paciente}}" />
								      		<td class="editText"><button onclick="cancelarTurnoVideollamada('{{$tr->trid}}','{{$dni_paciente}}')" type="button" class="rodri_button_cancelar">Cancelar</button></td>
							  	  		</form>
							  	  		
							  	  		<form method="POST" action="{{ route('asistirvideollamada') }}">
							      	 		@csrf   
								  			<input type="hidden" name="turno_id" value="{{$tr->trid}}" />
								  			<input type="hidden" name="dni_paciente" value="{{$dni_paciente}}" />
								      		<td class="editText"><button type="submit" class="rodri_button_aceptar">Asistir</button></td>
							  	  		</form>
							  	  		
							   		</tr>
							    @endforeach
							</tbody>
						</table>
					</div>
				
			@endif

			@if($turnosRegistradosPeg->count()>0)
			<!--	<br>
				<div class="col-md-8 marginTop20px"> -->					
					<h4>Mis Turnos (PEG):</h4>
					<div class="table-responsive" style="height:200px; ">
						<table class="table table-condensed" id="tabla_pacientes_videollamada" name="tabla_pacientes_video">
					 		<thead>
							    <tr>
							      <th class="editText" scope="col">#</th>
							      <th class="editText" scope="col">Paciente</th>		      
							      <th class="editText" scope="col">Medico</th>
							      <th class="editText" scope="col">Fecha</th>
							      <th class="editText" scope="col">Horario</th>
							      <th class="editText" scope="col">Cancelar</th>							      
							    </tr>
					  		</thead>
					  		<tbody id="pacientes-list" name="pacientes-list">
					  			<?php $cont = 1 ?>
					  			@foreach($turnosRegistradosPeg as $tr)
							    	<tr>
							    		<th class="editText" scope="row">{{$cont++}}</th>
							      		<td class="editText">{{$tr->papellido.', '.$tr->pnombre}}</td>		      
							      		<td class="editText">{{$tr->apellido.', '.$tr->nombre}}</td>		      
							      		<?php $dia_aux = explode('-',$tr->fechaTurno);        
				                		$fechaMostrar = $dia_aux[2].'/'.$dia_aux[1].'/'.$dia_aux[0];?>
									    <td class="editText">{{$fechaMostrar}}</td>
									    <td class="editText">{{$tr->horario}}</td>
							      		<form>
							      	 		@csrf   
								  			<input type="hidden" name="turno_id" value="{{$tr->trid}}" />
								  			<input type="hidden" name="dni_paciente" value="{{$dni_paciente}}" />
								      		<td class="editText"><button onclick="cancelarTurnoVideollamada('{{$tr->trid}}','{{$dni_paciente}}')" type="button" class="rodri_button_cancelar">Cancelar</button></td>
							  	  		</form>							  	  								  	  						
							   		</tr>
							    @endforeach
							</tbody>
						</table>
					</div>
				
			@endif

			</div>
			@if($turnosRegistradosVideollamadas == false and $turnosRegistrados == false && $turnosRegistradosPeg == false)
			<div class="col-md-8">
			<h2 class="text-center fontHomeTitulo lead marginTop50px">No tiene turnos registrados.</h2>			
			@endif
						
		</div>
	@endif
	@endif
	<br>
	<br>
	<br>
	<br><br><br><br>	
</div>

<div class="modal fade" id="modalCancelarTurno" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Turno Cancelado</h4>
      </div>      
       	<div class="modal-body">
        	<p id="modalCancelarTurnoMensaje">Su turno ha sido cancelado.</p>
        	<p id="modalReembolsoMensaje" class="text-muted small mb-0" style="display: none;"></p>
       	</div>
      	<div class="modal-footer">
        	<button class="rodri_button_aceptar" data-dismiss="modal">Finalizar</button>            
      	</div>
  
    </div>
  </div>
</div>

<script type="text/javascript">

$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});


function cancelarTurno(turno_id, dni){ 	
  $.ajax({
   type:'POST',
   dataType:'JSON',
   url:'/cancelar_turno_paciente',
   data:{dni_paciente: dni, turno_id:turno_id, _token: '{{csrf_token()}}'},
   success:function(data){              
    //alert(data.turnosPaciente.length);              
      //alert(data.turnosPaciente[0].nombrep);
      var contador = 0;
        $("#tabla_pacientes").find("tr:gt(0)").remove();
        for (i = 0; i < data.turnosRegistrados.length; i++){
            contador++;
            var fechaMostrar_array = data.turnosRegistrados[i].fechaTurno.split('-');
            var fechaMostrar = fechaMostrar_array[2]+"/"+fechaMostrar_array[1]+"/"+fechaMostrar_array[0]; 
            //alert(fechaMostrar);
            var paciente = "<tr><td>"+contador+"</td><td>"+data.turnosRegistrados[i].papellido+", "+data.turnosRegistrados[i].pnombre+"</td><td>"+data.turnosRegistrados[i].apellido+", "+data.turnosRegistrados[i].nombre+"</td><td>"+fechaMostrar+"</td><td>"+data.turnosRegistrados[i].horario+"<td><button class='rodri_button_cancelar' onclick='cancelarTurno("+data.turnosRegistrados[i].trid+","+data.dni_paciente+")'>Cancelar</button></td></tr>";                                
            $('#pacientes-list').append(paciente); 
        }
        var reembolsoMsg = document.getElementById('modalReembolsoMensaje');
        if (reembolsoMsg) {
            if (data.reembolso_mensaje) {
                reembolsoMsg.textContent = data.reembolso_mensaje;
                reembolsoMsg.style.display = '';
            } else {
                reembolsoMsg.textContent = '';
                reembolsoMsg.style.display = 'none';
            }
        }
        $('#modalCancelarTurno').modal();
   }
  });
}

function cancelarTurnoVideollamada(turno_id, dni){ 	
  $.ajax({
   type:'POST',
   dataType:'JSON',
   url:'/cancelar_turno_paciente_videollamada',
   data:{dni_paciente: dni, turno_id:turno_id, _token: '{{csrf_token()}}'},
   success:function(data){              
   	location.reload();
	/*    
      var contador = 0;
        $("#tabla_pacientes_videollamada").find("tr:gt(0)").remove();
        for (i = 0; i < data.turnosRegistrados.length; i++){
            contador++;
            var fechaMostrar_array = data.turnosRegistrados[i].fechaTurno.split('-');
            var fechaMostrar = fechaMostrar_array[2]+"/"+fechaMostrar_array[1]+"/"+fechaMostrar_array[0]; 
            //alert(fechaMostrar);
            var paciente = "<tr><td>"+contador+"</td><td>"+data.turnosRegistrados[i].papellido+", "+data.turnosRegistrados[i].pnombre+"</td><td>"+data.turnosRegistrados[i].apellido+", "+data.turnosRegistrados[i].nombre+"</td><td>"+fechaMostrar+"</td><td>"+data.turnosRegistrados[i].horario+"<td><button class='rodri_button_cancelar' onclick='cancelarTurnoVideollamada("+data.turnosRegistrados[i].trid+","+data.dni_paciente+")'>Cancelar</button></td><td><button class='rodri_button_cancelar' onclick='cancelarTurnoVideollamada("+data.turnosRegistrados[i].trid+","+data.dni_paciente+")'>Cancelar</button></td></tr>";                                
            $('#pacientes-list').append(paciente); 
        }
        $('#modalCancelarTurno').modal();*/
   }
  });
}

</script>

@endsection