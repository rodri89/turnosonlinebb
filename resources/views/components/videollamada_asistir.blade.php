<div class="col-md-6 marginTopCel_25px">	    
	 <b><h4 class="letraAzul lead">Indique al profesional que esta listo para asistir y espere que el profesional este disponible.</h4></b>
	 <div id="seccion_disponible">     	
	 <h6 class="fontColorHeader marginTopCel_15px">Click en disponible para indicar que esta listo: <button onclick="actualizarEstadoPaciente('{{$paciente->id}}','{{$turnoRegistrado->id}}', 1)" type="button" class="rodri_button_aceptar marginTopCel_15px">Disponible</button></h6>	     
	</div>
	<div hidden id="seccion_no_disponible">
	 <h6 class="fontColorHeader marginTopCel_15px">Click en no disponible para indicar que no esta listo: <button onclick="actualizarEstadoPaciente('{{$paciente->id}}','{{$turnoRegistrado->id}}', 0)" type="button" class="rodri_button_cancelar marginTopCel_15px">No Disponible</button></h6>
	</div>   
	 <h6 class="fontColorHeader">Estado del paciente: <input disabled class="sinBackgroundMedioAncho letraAzul" id="estado_paciente" name="estado_paciente"></input></h6>
	 <h6 class="fontColorHeader">Estado del profesional: <input disabled class="sinBackgroundMedioAncho letraAzul" id="estado_profesional" name="estado_profesional"></input></h6>
</div>	