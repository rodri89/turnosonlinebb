@extends('turnos_admin_medico/turnos_admin_medico_plantilla')

@section('contenedor')

<head>        
    <!-- Optional theme -->
    <script src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{asset('js/jquery.min.js')}}"> </script>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
    <!-- Jquery -->
    <script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
    <!-- Datepicker Files -->
    <link rel="stylesheet" href="{{asset('datePicker/css/bootstrap-datepicker3.css')}}">
    <link rel="stylesheet" href="{{asset('datePicker/css/bootstrap-datepicker.standalone.css')}}">
    <script src="{{asset('datePicker/js/bootstrap-datepicker.js')}}"></script>
    <!-- Languaje -->
    <script src="{{asset('datePicker/locales/bootstrap-datepicker.es.min.js')}}"></script>

</head>

<div class="row">
  <div class="col-md-1">
    <img class="card-img-top img_medico_center" src="images/medicos/{{$medico->foto}}" alt="">        
  </div> 
  <h2 class="medicoTituloTextoCel">Asignar turno dia: </h2>  
  
    <div class="col-md-2">
    	<form>
        @csrf                         
          <input type="hidden" name="paciente_dni_mbp" id="paciente_dni_mbp" value="{{$paciente_dni}}" />
          @if($medico->id == 5)
             <input type="hidden" name="dias_deshabilitados" id="dias_deshabilitados" value="0,1,3,4,6" />
          @else
        	   @if($medico->id == 11)
               <input type="hidden" name="dias_deshabilitados" id="dias_deshabilitados" value="0,6" />
             @else
               <input type="hidden" name="dias_deshabilitados" id="dias_deshabilitados" value="{{$dias_deshabilitados}}" />
             @endif
          @endif
          <input type="hidden" id="consultorio" name="consultorio" value="{{$consultorio}}"  />
          <input type="hidden" id="medico_id" name="medico_id" value="{{$medico->id}}"  />
          <input type="hidden" id="especialidad_id" name="especialidad_id" value="{{$medico->especialidad}}"  />
          <input type="hidden" id="moduloPrimerControlDoble" name="moduloPrimerControlDoble" value="{{$moduloPrimerControlDoble}}"  /> 
          <input type="hidden" id="moduloAfiliadoObligatorio" name="moduloAfiliadoObligatorio" value="{{$moduloAfiliadoObligatorio}}">
          <input type="text" id="dia" class="form-control datepicker" name="dia" value="{{$dia}}"
           autocomplete="off" onchange="actualizarListado()">        
          <div class="form-check divMarginCel">
            <input onchange="actualizarListado()" type="checkbox" class="form-check-input" id="primerControlCheck" name="primerControlCheck">
            <label class="form-check-label editText" for="materialUnchecked">Primer Control</label>            
          </div>
  	  </form>                                                                                   
    
</div>

<div class="col-md-2">
  <form method="POST" action="{{ route('medicomostrarsemana') }}">
    @csrf                         
    <input type="hidden" id="consultorio1" name="consultorio" value="{{$consultorio}}"  />
    <input type="hidden" id="medico_id1" name="medico_id" value="{{$medico->id}}"  />
  <button class="rodri_button_calendario divMarginCel"><img class="card-img-top" src="images/iconos/calendario.jpg"/></button>
  </form>
</div>
<label for="text" class="col-sm-0 control-label marginLeft10px editText">Fecha libre mas cercana: <input id="fechaLibreDisponible" class="sinBackground"  value="{{$fechaLibreDisponible}}"></input></label>              
</div>

<div class="row">	
    <div class="col-md-9">
      <br>          
        <div id="seccion_feriado" hidden>
          @if($esFeriado!=null)
          <input class="sinBackground letrasrojo" id="feriadoDescripcion" name="feriadoDescripcion" value="{{$esFeriado->descripcion}}"></input>          
          @endif
        </div>
        <div id="seccion_no_feriado">
      <!-- <div class="table-responsive" style="height:600px; overflow-y: scroll;"> -->
      	<div class="table-responsive" style="height:400px; overflow-y: scroll;">

	        <table class="table table-condensed" id="tabla_pacientes" name="tabla_pacientes">
		       <thead>
		          <tr>
		            <th class="editText" scope="col">#</th>		 
		            <th class="editText" scope="col">Horario</th>           
		            <th class="editText" scope="col">DNI</th>
		            <th class="editText" scope="col">Paciente</th>
                @if($medico->especialidad == 2)
                  <th scope="col">Consulta</th>
                  <th scope="col">Comentario</th>
                @endif	
                <th class="editText" scope="col">Cancelar</th>  	                                                                
		          </tr>
		        </thead>
		        <tbody id="pacientes-list" name="pacientes-list">
		          <?php $cont = 0; $indice = 1; ?>
		          @foreach($turnos as $turno)   
              @if((strcmp ($primerControl , 'SI' ) == 0))                         
                <tr>
                <th class="editText" scope="row">{{$indice++}}</th>
                <td class="editText">{{$turno["horario"]}}</td>  
                @if($turno["dni"] == 99999)               
                  <td></td>               
                @else
                  <td class="editText">{{$turno["dni"]}}</td>              
                @endif                 
                @if($turno["libre"] == 0)                  
                  <td class="editText"><button onclick="reservarTurnoPrimerControl('{{$turno['horario']}}','{{$turno['horario2']}}')" class="rodri_button_aceptar">RESERVAR</button></td>                
                @else
                  <td> {{$turno["nombre"]}}</td>
                   @if($medico->especialidad == 2)
                        <td> {{$turno["tipo_turno"]}}</td>
                        <td class="editText"><input type="text" onchange="guardarComentario('{{$turno['trid']}}')" id="comentario{{$turno['trid']}}" name="comentario" value="{{$turno['comentario']}}"/></td> 
                      @endif
                  @if($turno["dni"] == 99999)               
                    <td></td>               
                  @else
                    <td><button onclick="modalCancelarTurno('{{$turno['horario']}}')" class="rodri_button_cancelar_no">X</button></td>               
                  @endif  
                @endif
              </tr>
              @else  		          
		          <tr>
		            <th class="editText" scope="row">{{$indice++}}</th>
		            <td class="editText">{{$turno["horario"]}}</td>
                 @if($turno["dni"] == 99999)               
                    <td></td>               
                  @else
                    <td class="editText">{{$turno["dni"]}}</td>   
                  @endif    		                  
		            @if($turno["libre"] == 0)                  
		              <td class="editText"><button onclick="reservarTurno('{{$turno['horario']}}')" class="rodri_button_aceptar">RESERVAR</button></td>		            
		        	  @else
		              <td class="editText"> {{$turno["nombre"]}}</td> 
                   @if($medico->especialidad == 2)
                        <td> {{$turno["tipo_turno"]}}</td> 
                        <td> {{$turno["comentario"]}}</td>
                      @endif
                  @if($turno["dni"] == 99999)               
                    <td></td>               
                  @else
                    <td><button onclick="modalCancelarTurno('{{$turno['horario']}}')" class="rodri_button_cancelar_no">X</button></td>               
                  @endif 
                @endif
		          </tr>
              @endif                
		          @endforeach
		      </tbody>
	    	</table>
    	</div>
      </div>
    </div>
    
    <div class="col-md-3">
      @if($medico->especialidad == 2)
      
      <form method="POST" action="{{ route('medicoasignarturnospeg') }}">
       @csrf                         
        <input type="hidden" id="tipo_estudio" name="tipo_estudio" value="{{$tipo_estudio}}"  />
        <input type="hidden" id="consultorio3" name="consultorio" value="{{$consultorio}}"  />
        <input type="hidden" id="medico_id3" name="medico_id" value="{{$medico->id}}"  />
      <button class="sinBackground"><h4><u>PEG</u></h4></button>
      </form>
      

      <input type="hidden" id="estudio_seleccionado" value="0">
      <h4>Tipo Turno:</h4>
      <select class="form-control mercadopago_collapse_250px" id="tipo_turno_select" name="tipo_turno_select">
              <option>Consulta y ECG</option>    
              <option>Ecocardiograma Doppler Color</option>    
              <option>Ecodoppler de Vasos de Cuello</option>              
      </select>
      @endif

    	<h4 class="editText">Horario Reservado:</h4>
    	<input class="editText" disabled type="text" id="horario_seleccionado" name="horario_seleccionado" value="" placeholder="" />
      <input disabled type="hidden" id="horario_seleccionado2" name="horario_seleccionado2" value="" placeholder="" />
      		      	
			<h4 class="editText">Ingresar paciente:</h4>		  		  
			<input class="editText" type="text" id="dni_paciente" name="dni_paciente" value="" placeholder="Ingrese el DNI" onchange="validarPacienteExiste()" />
      <button class="rodri_button_calendario divMarginCel" type="button" onclick="mostrarModalBuscar()"><img class="card-img-top" src="images/iconos/buscar.png"/></button>								 
			<!--<a onclick="validarPacienteExiste()" type="button" class="btn rodri_button_a">Consultar</a>						 -->
	    <label hidden id="msj_no_asistio" class="letrasrojo">El paciente no asistio la consulta anterior</label>
		<table>			
			<tbody id="pacientes-list2" name="pacientes-list2">		          
		          <tr>		            
		            <td class="editText">Paciente: </td>
		            <td class="editText" id="paciente"></td>
		          </tr>
		          <td class="editText">DNI:</td>      
		            <td class="editText" id="dni"></td>
		          <tr>		            
		            <td class="editText">Mail:</td>      
		            <td class="editText" id="mail"></td>
		          </tr>
		          <tr>		                           
		            <td class="editText">Telefono:</td>      
		            <td class="editText" id="telefono"></td>
		          </tr>
		          <tr>		                  
		            <td class="editText">Obra social:</td>            
		            <td class="editText" id="obrasocial"></td>
		          </tr>		          
		          <tr hidden>
		          	<td id="paciente_id"></td>
		          </tr>
		      </tbody>			
		</table>
		<br>
		<div id="pacienteNoExiste" hidden>
			<h5 class="editText">El paciente no existe en el sistema.</h5>			
			<button onclick="showModalRegistrarPaciente()" class="rodri_button_large"  type="button">Desea dar de alta el paciente?</button>
		</div>			
		<br>
		<div id="altaTurno" hidden>
			<h5></h5>						
			<button onclick="registrarTurno()" class="rodri_button editText">Registrar</button>
		</div>			
		<br>	
		<div id="debeSeleccionarHorario" hidden>
			<h5 class="editText">Debe seleccionar un horario. Presione REGISTRAR en los botones de la tabla en el horario que desee.</h5>								
		</div>			
		<br>		
	</div>    	
</div>

<div class="modal fade" id="altaPacienteModal" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title" 
        id="favoritesModalLabel">Datos del paciente:</h4>
      </div>
      <div class="modal-body">
        <label for="text" class="col-sm-0 control-label">DNI</label>      
          <input type="text" class="form-control editText" id="modal_dni" placeholder="DNI"/>          

          <label for="text" class="col-sm-0 control-label">Nombre</label>      
          <input type="text" class="form-control editText" id="modal_nombre"  placeholder="Nombre Paciente" />

          <label for="text" class="col-sm-0 control-label">Apellido</label>      
          <input type="text" class="form-control editText" id="modal_apellido"  placeholder="Apellido Paciente"  />

          <label for="text" class="col-sm-0 control-label editText">* Fecha Nacimiento</label><br>
          <div class="row">
             <div class="fechaNacEditText">
              <input type="text" maxlength="2" class="form-control editText" id="modal_fecha_nacimiento_dia" name="fecha_nacimiento"  placeholder="dd" />
            </div>
             <label for="text" class="col-sm-0 control-label editText margin5">/</label>
            <div class="fechaNacEditText">
              <input type="text" maxlength="2" class="form-control editText" id="modal_fecha_nacimiento_mes" name="fecha_nacimiento"  placeholder="mm" />
            </div>
            <label for="text" class="col-sm-0 control-label margin5">/</label>
            <div class="fechaNacAnioEditText">   
              <input type="text" maxlength="4" class="form-control editText" id="modal_fecha_nacimiento_anio" name="fecha_nacimiento"  placeholder="YYYY" />
            </div>
            <input type="hidden" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento"  />
         </div>        

          <label for="text" class="col-sm-0 control-label">Telefono</label>      
          <input type="text" class="form-control editText" id="modal_telefono"  placeholder="Telefono solo numeros (EJ: 2915050050)" />

          <label for="text" class="col-sm-0 control-label">Localidad</label>      
          <input type="text" class="form-control editText" id="modal_localidad"  placeholder="Localidad" />
          
          <label for="text" class="col-sm-0 control-label">Domicilio</label>      
          <input type="text" class="form-control editText" id="modal_domicilio"  placeholder="Domicilio" />

          <label for="text" class="col-sm-0 control-label">Mail</label>      
          <input type="text" class="form-control editText" id="modal_mail" placeholder="Mail"  />

          
          @if($moduloAfiliadoObligatorio == 1)                                  
            <label for="text" class="col-sm-0 control-label editText">¿Es afiliado obligatorio?</label>      <br>                  
            <div class="form-check">                        
              <label class="form-check-label editText" for="materialUnchecked">Si</label>
              <input type="radio" id="check_afiliado_obligatorio_si" name="radio1" value="1" required>
                          
              <label class="form-check-label editText" for="materialChecked">No</label>
              <input type="radio" id="check_afiliado_obligatorio_no" name="radio1" value="0" required>
            </div>              
          @endif

          <label for="text" class="col-sm-0 control-label">Obra Social</label>
          <select class="form-control" id="modal_obra_social" name="obra_social">            
            <option>N/A</option>            
            @foreach($obraSociales as $os)
            <option>{{$os->nombre}}</option>            
            @endforeach
          </select>      
          <!--<input type="text" class="form-control editText" id="modal_obra_social" name="obra_social" placeholder="Obra Social"  />-->

          <label for="text" class="col-sm-0 control-label">N° Afiliado</label>      
          <input type="text" class="form-control editText" id="modal_numero_afiliado" name="numero_afiliado" placeholder="N° Afiliado"  />
          
          <label for="text" class="col-sm-0 control-label">Plan</label>      
          <input type="text" class="form-control editText" id="modal_plan_obra_social" name="plan_obra_social" placeholder="Plan Obra Social"  />

          <br>
          <label hidden id="error_mensaje_registrar" for="text" class="col-sm-0 control-label letrasrojo">Para registrar "Debe ingresar un numero de teléfono válido"</label> 
      </div>
      <div class="modal-footer">
        <button type="button" 
           class="rodri_button_cancelar" 
           data-dismiss="modal">Cancelar</button>
        <span class="pull-right">
        	<button onclick="registrarPaciente()" class="rodri_button_aceptar">Registrar</button>                                
        </span>
      </div>
    </div>
  </div>
</div>
 
<div class="modal fade" id="modalTurnoOk" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">      	
        <h4 class="modal-title"         
        id="modalTitleMensaje">Turno Registrado</h4>
      </div>      
       <div class="modal-body">
       	 <label for="text" class="col-sm-0" id="modal_texto_ok">El turno ha sido registrado correctamente.</label>                   
       </div>
      <div class="modal-footer">
      <button type="button" 
           class="rodri_button_aceptar" 
           data-dismiss="modal">Aceptar</button>        
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalTurnoFail" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">      	
        <h4 class="modal-title"         
        id="modalTitleMensaje">Turno no Registrado</h4>
      </div>      
       <div class="modal-body">
       	<label for="text" class="col-sm-0" id="modal_texto_fail">Lo siento, el turno no ha sido registrado.</label>           
       </div>
      <div class="modal-footer">
    <button type="button" 
           class="rodri_button_cancelar" 
           data-dismiss="modal">Aceptar</button>        
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalEstaSeguro" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title" 
        id="favoritesModalLabel">Eliminar Turno:</h4>
      </div>
      <div class="modal-body">       
          <label for="text" class="col-sm-0" id="modal_texto_dia_fail">¿Esta seguro que desea eliminar el turno de las <input class="modal_input_horario" type="text" id="modal_turno_horario"></input> hs ?</label>
      </div>
      <div class="modal-footer">
        <button type="button" 
           class="rodri_button_cancelar" 
           data-dismiss="modal">Cancelar</button>  
        <button type="button" 
           onclick="accionCancelarTurno()" 
           class="rodri_button_aceptar" 
           data-dismiss="modal">Aceptar</button>                                      
      </div>
      </div>
  </div>
</div>

<div class="modal fade" id="modalTurnoMsj" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Turno no Registrado</h4>
      </div>      
       <div class="modal-body">
        <label hidden for="text" class="col-sm-0" id="modal_texto_msj1">El paciente ya tiene un turno registrado para esa fecha.</label>           
        <label hidden for="text" class="col-sm-0" id="modal_texto_msj2">La cantidad de primeros controles de esta fecha esta completa. </label>           
       </div>
      <div class="modal-footer">
    <button type="button" 
           class="rodri_button_cancelar" 
           data-dismiss="modal">Aceptar</button>        
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalTurnoMsjPeg" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Turno no Registrado</h4>
      </div>      
       <div class="modal-body">
        <label for="text" class="col-sm-0" id="modal_texto_msj3">El paciente ya tiene un turno registrado para realizar un PEG en ese horario.</label>           
                
       </div>
      <div class="modal-footer">
    <button type="button" 
           class="rodri_button_cancelar" 
           data-dismiss="modal">Aceptar</button>        
      </div>
    </div>
  </div>
</div>

<div id="snackbar"><p id="snackbar_text"></p></div>

@include('modal.modal_es_feriado')
@include('modal.snackbar')
@include('modal.modal_buscar_paciente')

<script type="text/javascript">
var dias_deshabilitados=document.getElementById("dias_deshabilitados").value;  
  $('.datepicker').datepicker({
    weekStart: 0,
    startDate: '-60d',
    language: "es",
    keyboardNavigation: false,
    forceParse: false,
    autoclose: true,
    daysOfWeekHighlighted: diasHabilitados(),
    daysOfWeekDisabled: dias_deshabilitados    
  }).attr('readonly','readonly');    
  
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  function diasHabilitados() {      
    var myArr = document.getElementById("dias_deshabilitados").value;
    myArr = myArr.split(",");
          
    var diasHabilitados = "";
    if(!myArr.includes('0'))
      diasHabilitados+='0,';
    if(!myArr.includes('1'))
      diasHabilitados+='1,';
    if(!myArr.includes('2'))
      diasHabilitados+='2,';
    if(!myArr.includes('3'))
      diasHabilitados+='3,';
    if(!myArr.includes('4'))
      diasHabilitados+='4,';
    if(!myArr.includes('5'))
      diasHabilitados+='5,';

      diasHabilitados = diasHabilitados.substring(0, diasHabilitados.length - 1);          
      return diasHabilitados;
  }
 
function mostrarSnackbar(cs) {    
    document.getElementById("snackbar_text").innerHTML = cs;
    var x = document.getElementById("snackbar");
    x.className = "show";
    setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
  }

 function guardarComentario(comentario_id){
  //  alert(comentario_id);
  //  
    var turnoRegistradoId = comentario_id;
    var comentario = document.getElementById("comentario"+comentario_id).value;
    var medico = document.getElementById("medico_id").value;    
    var consul = document.getElementById("consultorio").value;
    var dia = document.getElementById("dia").value;    
     $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/update_comentario_listado_pacientes',
           data:{medico_id: medico, consultorio:consul, fecha :dia, comentario:comentario, turnoRegistradoId: turnoRegistradoId, _token: '{{csrf_token()}}'},
           success:function(data){              
              actualizarListado();  
              mostrarSnackbar("Comentario Actualizado");
           }
        }); 
  }

 function reservarTurno(horario){  	  
  	var horarioSeleccionado = document.getElementById("horario_seleccionado");  
  	horarioSeleccionado.value= horario;
  	var paciente = document.getElementById("paciente").innerHTML;	 	
   	if(paciente.localeCompare('')!=0){
   		document.getElementById("altaTurno").hidden=false;	
   		document.getElementById("debeSeleccionarHorario").hidden=true;	 		
   	}
  }

  function reservarTurnoPrimerControl(horario, horario2){    
    var horarioSeleccionado = document.getElementById("horario_seleccionado");  
    var horarioSeleccionado2 = document.getElementById("horario_seleccionado2");  
    horarioSeleccionado.value= horario;
    horarioSeleccionado2.value= horario2;
    
    var paciente = document.getElementById("paciente").innerHTML;   
    if(paciente.localeCompare('')!=0){
      document.getElementById("altaTurno").hidden=false;  
      document.getElementById("debeSeleccionarHorario").hidden=true;      
    }  
  }

  function actualizarListado() {
    var valor = document.getElementById("dia").value;
    var medico = document.getElementById("medico_id").value;    
    var consul = document.getElementById("consultorio").value;    
    var moduloPrimerControlDoble = document.getElementById("moduloPrimerControlDoble").value;    
    var primerControl = document.getElementById("primerControlCheck").checked;
    var primerControlNum = 0;
    if((primerControl) && (moduloPrimerControlDoble == 1))
      primerControlNum = 1;
  	document.getElementById("horario_seleccionado").value = "";
    document.getElementById("horario_seleccionado2").value = "";
    
  	document.getElementById("altaTurno").hidden = true;        
     $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/medico_actualizar_asignar_turnos',
           data:{medico_id: medico, consultorio:consul, fecha :valor, primerControl:primerControlNum, _token: '{{csrf_token()}}'},
           success:function(data){          
              var especialidad_id = document.getElementById("especialidad_id").value;
                if(especialidad_id == 2){
                  generarTablaCardiologo(data);                
                } else {
                  generarTabla(data);                
                }      
               document.getElementById("fechaLibreDisponible").value = data.fechaLibreDisponible;         
           }
        });
  }

  function registrarPaciente(){
  		var dni = document.getElementById("modal_dni").value;
  		var nombre = document.getElementById("modal_nombre").value;
  		var apellido = document.getElementById("modal_apellido").value;
  		var obrasocial = document.getElementById("modal_obra_social").value;
  		var telefono = document.getElementById("modal_telefono").value;
      var domicilio = document.getElementById("modal_domicilio").value;
      var localidad = document.getElementById("modal_localidad").value;
  		var mail = document.getElementById("modal_mail").value; 
      var numero_afiliado = document.getElementById("modal_numero_afiliado").value; 
      var plan = document.getElementById("modal_plan_obra_social").value;         
      var consultorio = document.getElementById("consultorio").value; 
      var fecha_nacimiento_dia = document.getElementById("modal_fecha_nacimiento_dia").value;     
      var fecha_nacimiento_mes = document.getElementById("modal_fecha_nacimiento_mes").value;     
      var fecha_nacimiento_anio = document.getElementById("modal_fecha_nacimiento_anio").value;
      var fecha_nacimiento = null;
      if((fecha_nacimiento_dia!=null)&&(fecha_nacimiento_dia.localeCompare('')!=0)){
        var fecha_nacimiento = fecha_nacimiento_anio+"/"+fecha_nacimiento_mes+"/"+fecha_nacimiento_dia;
      }
      var afiliado_obligatorio = 2;
      if(document.getElementById("moduloAfiliadoObligatorio").value == 1){
        if(document.getElementById("check_afiliado_obligatorio_si").checked)
          afiliado_obligatorio = 1;
        if(document.getElementById("check_afiliado_obligatorio_no").checked)
          afiliado_obligatorio = 0;
      }  

  		if(isNaN(dni)||(isNaN(telefono))){
  			alert("El DNI y el telefono deben estar compuestos solo por numeros.");
  		} else {  			

        if(!validarTelefono()){       
            document.getElementById("error_mensaje_registrar").hidden = false;
        } else {
    			$.ajax({
  		       type:'POST',
  		       dataType:'JSON',
  		       url:'/alta_paciente_medico_secretaria',
  		      data:{dni :dni,nombre:nombre,apellido:apellido,fecha_nacimiento:fecha_nacimiento,telefono:telefono,mail:mail,obra_social:obrasocial,numero_afiliado:numero_afiliado,plan:plan, consultorio:consultorio, domicilio:domicilio, afiliado_obligatorio:afiliado_obligatorio, localidad:localidad, _token: '{{csrf_token()}}'},
  		       success:function(data){      
  		       		var dni_paciente = document.getElementById("dni_paciente");
  			       	var paciente = document.getElementById("paciente");
  			   		  var dni = document.getElementById("dni");
  			   	    var telefono = document.getElementById("telefono");
  			   	  	var mail = document.getElementById("mail");
  			   	   	var obrasocial = document.getElementById("obrasocial");
  			       if(data.paciente != null){        			       	
  			       		paciente.innerHTML = data.paciente.apellido+", "+data.paciente.nombre;
  			       		dni.innerHTML = data.paciente.dni;
      						telefono.innerHTML = data.paciente.telefono;
      						mail.innerHTML = data.paciente.mail;
      						obrasocial.innerHTML = data.paciente.obra_social;
      						paciente_id.innerHTML = data.paciente.id;
      			      document.getElementById("dni_paciente").value=data.paciente.dni;
                  document.getElementById("horario_seleccionado").innerHTML="";
                  document.getElementById("pacienteNoExiste").hidden=true;
                  //document.getElementById("horarioIncorrecto").hidden=true;
                  vaciarCamposModal();
                  document.getElementById("altaTurno").hidden = false;    
                   $('#altaPacienteModal').modal('hide');               
  			       	} else {
  			       		alert("El paciente no ha sido registrado");           			       		
  					 }	              
  			    }
      		});  		
  		}  		
    }
  }

  function validarTelefono(){
    var telefono = document.getElementById("modal_telefono");
    var consultorio = document.getElementById("consultorio").value;
    if(consultorio == 6) {
      if(telefono.value != null && telefono.value.length < 4)
        return false;
    } 
    return true;
  }

  function validarAsistioUltimaVes(paciente_id) {
    var medico_id = document.getElementById("medico_id").value;    
    var consultorio = document.getElementById("consultorio").value;          
    if(consultorio == 6){
      $.ajax({
         type:'POST',
         dataType:'JSON',
         url:'/validar_asistio_ultima_ves',
         data:{paciente_id :paciente_id, medico_id:medico_id, _token: '{{csrf_token()}}'},
         success:function(data){                    
              if(data.turno.asistio == 2){
                document.getElementById("msj_no_asistio").hidden = false;    
              } else {
                document.getElementById("msj_no_asistio").hidden = true;    
              }
           }
      });   
    }
  }

  function validarPacienteExiste() {
  	var dni = document.getElementById("dni_paciente").value;      		
    var consultorio = document.getElementById("consultorio").value;
		$.ajax({
       type:'POST',
       dataType:'JSON',
       url:'/paciente_consultar',
       data:{dni_paciente :dni, consultorio:consultorio, _token: '{{csrf_token()}}'},
       success:function(data){           		
	       	var paciente = document.getElementById("paciente");	       	
  	   		var dni = document.getElementById("dni");
  	   		var telefono = document.getElementById("telefono");
  	   		var mail = document.getElementById("mail");
  	   		var obrasocial = document.getElementById("obrasocial");
  	   		var horarioSeleccionado = document.getElementById("horario_seleccionado").value;
          document.getElementById("msj_no_asistio").hidden = true;
	        if (data.paciente != null) {
            validarAsistioUltimaVes(data.paciente.id);        	       	
	       		document.getElementById("pacienteNoExiste").hidden=true;	       			       					       		
	       		paciente.innerHTML = data.paciente.apellido+", "+data.paciente.nombre;	       			       		
	       		dni.innerHTML = data.paciente.dni;
				    telefono.innerHTML = data.paciente.telefono;
    				mail.innerHTML = data.paciente.mail;
    				obrasocial.innerHTML = data.paciente.obra_social;
    				paciente_id.innerHTML = data.paciente.id;	
								
    				if (horarioSeleccionado.localeCompare('') == 0) {					
    					document.getElementById("debeSeleccionarHorario").hidden=false;									
    				} else {					
    					document.getElementById("altaTurno").hidden=false;
    					document.getElementById("debeSeleccionarHorario").hidden=true;					
    				}				
  	       } else {       
  	       		document.getElementById("debeSeleccionarHorario").hidden=false;	    
  	       		document.getElementById("pacienteNoExiste").hidden=false;          				
  	       		document.getElementById("debeSeleccionarHorario").hidden=true;					
  	       		document.getElementById("altaTurno").hidden=true;	     	       		  	
  	       		paciente.innerHTML = "";
  	       		dni.innerHTML = "";
      				telefono.innerHTML = "";
      				mail.innerHTML = "";
      				obrasocial.innerHTML = "";
      				paciente_id.innerHTML = "";
      			}	              
	       }
    });  	
  }

  function vaciarCampos(){
		document.getElementById("altaTurno").hidden=true;
	  	document.getElementById("horario_seleccionado").value ="";
      document.getElementById("horario_seleccionado2").value ="";
	  	document.getElementById("paciente").innerHTML="";	       	
	   	document.getElementById("dni").innerHTML="";
	   	document.getElementById("telefono").innerHTML="";
	   	document.getElementById("mail").innerHTML="";
	   	document.getElementById("obrasocial").innerHTML="";
	   	document.getElementById("dni_paciente").value ="";
      vaciarCamposModal();	   	
  }

  function vaciarCamposModal(){
    $('#modal_dni').val("");
    $('#modal_nombre').val("");
    $('#modal_apellido').val("");
    $('#modal_obra_social').val("N/A");
    $('#modal_telefono').val("");
    $('#modal_domicilio').val("");  
    $('#modal_localidad').val("");  
    $('#modal_mail').val("");
    $('#modal_numero_afiliado').val("");
    $('#modal_plan_obra_social').val("");
    $('#modal_fecha_nacimiento_dia').val("");
    $('#modal_fecha_nacimiento_mes').val("");
    $('#modal_fecha_nacimiento_anio').val("");
    if(document.getElementById("moduloAfiliadoObligatorio").value == 1){
      document.getElementById("check_afiliado_obligatorio_si").checked = false;
      document.getElementById("check_afiliado_obligatorio_no").checked = false;
    }
  }

  function showModalRegistrarPaciente(){
    var dni = document.getElementById("dni_paciente").value;
     $('#modal_dni').val(dni);
     $("#altaPacienteModal").modal();  
  }

  function registrarTurno(){  			
		var paciente_id = document.getElementById("paciente_id").innerHTML;      		
		var medico_id = document.getElementById("medico_id").value;
		var consultorio = document.getElementById("consultorio").value;
		var fechaTurno = document.getElementById("dia").value;
		var horario = document.getElementById("horario_seleccionado").value;
    var horario2 = document.getElementById("horario_seleccionado2").value;		
    var moduloPrimerControlDoble = document.getElementById("moduloPrimerControlDoble").value;  
    var especialidad_id = document.getElementById("especialidad_id").value;
    document.getElementById("msj_no_asistio").hidden = true;
    var tipo_turno = 0;
    if(especialidad_id == 2){
      var select = document.getElementById("tipo_turno_select").value;
      if(select.localeCompare("Consulta y ECG")==0)
        tipo_turno = 1;
      if(select.localeCompare("Ecocardiograma Doppler Color")==0)
        tipo_turno = 2;
      if(select.localeCompare("Ecodoppler de Vasos de Cuello")==0)
        tipo_turno = 3;

    }
    //alert(tipo_turno);
    var primerControl = document.getElementById("primerControlCheck").checked;
    var primerControlNum = 0;
    if(primerControl)
      primerControlNum=1;		

    if(horario2.localeCompare('')==0)
      horario2 = "0";
        
	  vaciarCampos();

   if((primerControl) && (moduloPrimerControlDoble == 1)){
	   $.ajax({
	       type:'POST',
	       dataType:'JSON',
	       url:'/medico_registrar_asignar_turno_doble',
	       data:{paciente_id :paciente_id, medico_id:medico_id, consultorio:consultorio, fechaTurno:fechaTurno, horario:horario, horario2:horario2, primerControl:primerControlNum, tipo_turno:tipo_turno, _token: '{{csrf_token()}}'},
	       success:function(data){                    
		       if(data.turnoRegistrado == 1){
              document.getElementById("msj_no_asistio").hidden = true;        
		       		$("#modalTurnoOk").modal();		       			       			       				     	       		  
		          generarTabla(data);
		       	} else {           		
		       		$("#modalTurnoFail").modal();
				    }	              
	       	}
    	}); 
   } else {
       $.ajax({
       type:'POST',
       dataType:'JSON',
       url:'/medico_registrar_asignar_turno',
       data:{paciente_id :paciente_id, medico_id:medico_id, consultorio:consultorio, fechaTurno:fechaTurno, horario:horario, primerControl:primerControlNum, tipo_turno:tipo_turno, _token: '{{csrf_token()}}'},
       success:function(data){           

         if(data.turnoRegistrado == 0){        
              $("#modalTurnoFail").modal();
           }
           if(data.turnoRegistrado == 1){              
              $("#modalTurnoOk").modal();                                                           
              var especialidad_id = document.getElementById("especialidad_id").value;
                if(especialidad_id == 2){
                  generarTablaCardiologo(data);                
                } else {
                  generarTabla(data);                
                }
            }
           if(data.turnoRegistrado == 2){  
              document.getElementById("modal_texto_msj1").hidden=false;      
              document.getElementById("modal_texto_msj2").hidden=true;

              $("#modalTurnoMsj").modal();
           }
           if(data.turnoRegistrado == 3){
             var especialidad_id = document.getElementById("especialidad_id").value;             
              if(especialidad_id == 2){
                $("#modalTurnoMsjPeg").modal();  
              } else {
                document.getElementById("modal_texto_msj2").hidden=false;
                document.getElementById("modal_texto_msj1").hidden=true;
                $("#modalTurnoMsj").modal();
              }
           }               
        }
      });
   }
}

  function generarTabla(data){
    var contador = 0;
    $("#tabla_pacientes").find("tr:gt(0)").remove();     
    var count = Object.keys(data.turnosPaciente).length;
    if(data.esFeriado != null){      
      $("#modal_input_texto").val(data.esFeriado.descripcion);
      $("#modalEsFeriado").modal();
    } else {                 
      for (i = 0; i < count; i++){
         contador = i + 1;        
        if(data.turnosPaciente[i].dni == 99999){
          var paciente = '<tr><th scope="row">'+contador+'</th><td>'+data.turnosPaciente[i].horario+'</td><td></td><td>Cancelar</td><td></td></tr>';
         } else {
           if(data.primerControl == 1){                       
            //alert(data.turnosPaciente[i].libre+" - "+data.turnosPaciente[i].horario);
            if(data.turnosPaciente[i].libre == 0){  
                var paciente = '<tr><th class="editText" scope="row">'+contador+'</th><td class="editText">'+data.turnosPaciente[i].horario+'</td><td class="editText">'+data.turnosPaciente[i].dni+'</td><td class="editText"><button onClick=reservarTurnoPrimerControl("'+data.turnosPaciente[i].horario+'","'+data.turnosPaciente[i].horario2+'") class="rodri_button_aceptar editText">REGISTRAR</button></td><td></td></tr>'                                                                  
              } else {
                var paciente = '<tr><td class="editText">'+contador+'</td><td class="editText">'+data.turnosPaciente[i].horario+'</td><td class="editText">'+data.turnosPaciente[i].dni+'</td><td class="editText">'+data.turnosPaciente[i].nombre+'</td><td><button onClick=modalCancelarTurno("'+data.turnosPaciente[i].horario+'") class="rodri_button_cancelar_no">X</button></td></tr>';                                                          
              }
          }
          else{                      
            if(data.turnosPaciente[i].libre == 0){                      
              var paciente = '<tr><th class="editText" scope="row">'+contador+'</th><td class="editText">'+data.turnosPaciente[i].horario+'</td><td class="editText">'+data.turnosPaciente[i].dni+'</td><td class="editText"><button onClick=reservarTurno("'+data.turnosPaciente[i].horario+'") class="rodri_button_aceptar editText">REGISTRAR</button></td><td></td></tr>';                                                                  
            } else {
              var paciente = '<tr><td class="editText">'+contador+'</td><td class="editText">'+data.turnosPaciente[i].horario+'</td><td class="editText">'+data.turnosPaciente[i].dni+'</td><td class="editText">'+data.turnosPaciente[i].nombre+'</td><td><button onClick=modalCancelarTurno("'+data.turnosPaciente[i].horario+'") class="rodri_button_cancelar_no">X</button></td></tr>';                                                          
            }
          }
        }
          $('#pacientes-list').append(paciente);                    
      } 
    }
  }

  function generarTablaCardiologo(data){
    var contador = 0;
    $("#tabla_pacientes").find("tr:gt(0)").remove(); 
    var count = Object.keys(data.turnosPaciente).length;
    if(data.esFeriado != null){            
      $("#modal_input_texto").val(data.esFeriado.descripcion);
      $("#modalEsFeriado").modal();
    } else {             
      for (i = 0; i < count; i++) {
         contador = i + 1;        
         var comentario_id = "comentario"+data.turnosPaciente[i].trid;
         if(data.turnosPaciente[i].dni == 99999){
            var paciente = '<tr><th scope="row">'+contador+'</th><td>'+data.turnosPaciente[i].horario+'</td><td></td><td>Cancelar</td><td></td><td></td></tr>';
         } else {
           if(data.primerControl == 1){                       
            //alert(data.turnosPaciente[i].libre+" - "+data.turnosPaciente[i].horario);
            if(data.turnosPaciente[i].libre == 0){  
                var paciente = '<tr><th scope="row">'+contador+'</th><td>'+data.turnosPaciente[i].horario+'</td><td>'+data.turnosPaciente[i].dni+'</td><td><button onClick=reservarTurnoPrimerControl("'+data.turnosPaciente[i].horario+'","'+data.turnosPaciente[i].horario2+'") class="rodri_button_aceptar">REGISTRAR</button></td><td></td><td></td></tr>';                                                                  
              } else {
                var paciente = '<tr><td>'+contador+'</td><td>'+data.turnosPaciente[i].horario+'</td><td>'+data.turnosPaciente[i].dni+'</td><td>'+data.turnosPaciente[i].nombre+'</td><td>'+data.turnosPaciente[i].tipo_turno+'</td><td><input type="text" onchange=guardarComentario('+data.turnosPaciente[i].trid+') id='+comentario_id+' name="comentario" value="'+data.turnosPaciente[i].comentario+'"></td><td>'+data.turnosPaciente[i].comentario+'</td><td><button onClick=modalCancelarTurno("'+data.turnosPaciente[i].horario+'") class="rodri_button_cancelar_no">X</button></td></tr>';                                                      
              }
          }
          else{                      
            if(data.turnosPaciente[i].libre == 0){                      
              var paciente = '<tr><th scope="row">'+contador+'</th><td>'+data.turnosPaciente[i].horario+'</td><td>'+data.turnosPaciente[i].dni+'</td><td><button onClick=reservarTurno("'+data.turnosPaciente[i].horario+'") class="rodri_button_aceptar">REGISTRAR</button></td><td></td><td></td></tr>';                                                                  
            } else {
              var paciente = '<tr><td>'+contador+'</td><td>'+data.turnosPaciente[i].horario+'</td><td>'+data.turnosPaciente[i].dni+'</td><td>'+data.turnosPaciente[i].nombre+'</td><td>'+data.turnosPaciente[i].tipo_turno+'</td><td><input type="text" onchange=guardarComentario('+data.turnosPaciente[i].trid+') id='+comentario_id+' name="comentario" value="'+data.turnosPaciente[i].comentario+'"></td><td><button onClick=modalCancelarTurno("'+data.turnosPaciente[i].horario+'") class="rodri_button_cancelar_no">X</button></td></tr>';                                                          
            }
          }
        }
        $('#pacientes-list').append(paciente);                    
      }
    }
   }

   function accionCancelarTurno(){
    var horario = document.getElementById("modal_turno_horario").value;
    var valor = document.getElementById("dia").value;
    var medico = document.getElementById("medico_id").value;    
    var consul = document.getElementById("consultorio").value;    
    var moduloPrimerControlDoble = document.getElementById("moduloPrimerControlDoble").value;    
    var primerControl = document.getElementById("primerControlCheck").checked;
    var primerControlNum = 0;
    if((primerControl) && (moduloPrimerControlDoble == 1))
      primerControlNum = 1;
    document.getElementById("horario_seleccionado").value = "";
    document.getElementById("horario_seleccionado2").value = "";
    var tipo_estudio = 0;
    document.getElementById("altaTurno").hidden = true;        
     $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/medico_cancelar_asignar_turnos',
           data:{medico_id: medico, consultorio:consul, fecha :valor, primerControl:primerControlNum, horario:horario, tipo_estudio:tipo_estudio, _token: '{{csrf_token()}}'},
           success:function(data){          
              var especialidad_id = document.getElementById("especialidad_id").value;
                if(especialidad_id == 2){
                  generarTablaCardiologo(data);                
                } else {
                  generarTabla(data);                
                }      
              document.getElementById("fechaLibreDisponible").value = data.fechaLibreDisponible;         
           }
        });
    }

  function modalCancelarTurno(horario){
     $('#modal_turno_horario').val(horario);
     $("#modalEstaSeguro").modal();
  }  

  function mostrarModalBuscar(){
    $("#modalBuscarPaciente").modal();  
  }

  function seleccionarPaciente(dni){
    document.getElementById("dni_paciente").value = dni;
    $("#modalBuscarPaciente").modal('hide');                
    validarPacienteExiste();
  }

  window.onload=function() {          
    checkRecetasPendientes();

    var dni_existe = document.getElementById("paciente_dni_mbp").value;
    if(dni_existe.localeCompare("")!=0){
      document.getElementById("dni_paciente").value = dni_existe;
      validarPacienteExiste();
    }
    if(document.getElementById("feriadoDescripcion") != null){
      var feriado = document.getElementById("feriadoDescripcion").value;      
        $("#modal_input_texto").val(feriado);
        $("#modalEsFeriado").modal();      
    }
  }
  
</script>

@endsection