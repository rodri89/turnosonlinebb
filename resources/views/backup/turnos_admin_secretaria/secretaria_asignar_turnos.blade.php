@extends('turnos_admin_secretaria/turnos_admin_secretaria_plantilla')

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

<input type="hidden" id="secretaria_asignar_turno_screen">

<div class="row">
  <div class="col-md-1">
    <img class="card-img-top img_medico_center" src="images/medicos/{{$medico->foto}}" alt="">        
  </div> 
  <h2>Asignar turno dia: </h2>  
  
    <div class="col-md-2">
    	<form>
        @csrf                         
          <input type="hidden" name="paciente_id_lpa" id="paciente_id_lpa" value="{{$paciente_dni}}" />
        	@if($medico->id == 5)
            <input type="hidden" name="dias_deshabilitados" id="dias_deshabilitados" value="0,1,3,4,6" />
          @else
            <input type="hidden" name="dias_deshabilitados" id="dias_deshabilitados" value="{{$dias_deshabilitados}}" />
          @endif
          <input type="hidden" id="consultorio2" name="consultorio" value="{{$consultorio}}"  />
          <input type="hidden" id="medico_id2" name="medico_id" value="{{$medico->id}}"  />
          <input type="hidden" id="especialidad_id" name="especialidad_id" value="{{$medico->especialidad}}"  />
          <input type="text" id="dia" class="form-control datepicker" name="dia" value="{{$dia}}"
           autocomplete="off" onchange="actualizarListado()">    
          <input type="hidden" id="moduloPrimerControlDoble" name="moduloPrimerControlDoble" value="{{$moduloPrimerControlDoble}}"  />
          <input type="hidden" id="moduloAfiliadoObligatorio" name="moduloAfiliadoObligatorio" value="{{$moduloAfiliadoObligatorio}}">   
          <div class="form-check">
            <input onchange="actualizarListado()" type="checkbox" class="form-check-input" id="primerControlCheck" name="primerControlCheck">
            <label class="form-check-label" for="materialUnchecked">Primer Control</label>            
          </div>
  	  </form>                                                                                   
    
</div>

<div class="col-md-2">
  <form method="POST" action="{{ route('mostrarsemana') }}">
    @csrf                         
    <input type="hidden" id="consultorio" name="consultorio" value="{{$consultorio}}"  />
    <input type="hidden" id="medico_id" name="medico_id" value="{{$medico->id}}"  />
  <button class="rodri_button_calendario"><img class="card-img-top" src="images/iconos/calendario.jpg"/></button>
  </form>
</div>
<label for="text" class="col-sm-0 control-label">Fecha libre mas cercana: 
  <input id="fechaLibreDisponible1" class="sinBackground"  value="{{$fechaLibreDisponible1}}"></input><br>
  <input id="fechaLibreDisponible2" class="sinBackground marginLeft_184px" value="{{$fechaLibreDisponible2}}"></input><br>
  <input id="fechaLibreDisponible3" class="sinBackground marginLeft_184px"  value="{{$fechaLibreDisponible3}}"></input></label>
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
      	
        
        <label hidden id="modificar_turno_texto" class="letrasrojo">Modificar Turno</label>
        <button hidden id="modificar_turno_btn" class="rodri_button_cancelar_no" onclick="cancelarModificarTurno()">X</button>  
        <label hidden id="modificar_turno_id" class="letrasrojo">0</label>  
        <label hidden id="modificar_turno_dni" class="letrasrojo"></label>  

        <div class="table-responsive" style="height:400px; overflow-y: scroll;">
	        <table class="table table-condensed" id="tabla_pacientes" name="tabla_pacientes">
		       <thead>
		          <tr>
		            <th scope="col">#</th>		 
                <th scope="col">Tipo Turno</th>
		            <th scope="col">Horario</th>           
		            <th scope="col">DNI</th>
		            <th scope="col">Paciente</th>
                @if($medico->especialidad == 2)
                  <th scope="col">Consulta</th>
                  <th scope="col">Comentario</th>
                @endif		    
                <th scope="col">Cancelar</th>                                                                           
                <th scope="col">Modificar</th>                                                                           
		          </tr>
		        </thead>
		        <tbody id="pacientes-list" name="pacientes-list">
		          <?php $cont = 0; $indice = 1; ?>
		          @foreach($turnos as $turno)   
              @if((strcmp ($primerControl , 'SI' ) == 0))                         
                <tr>
                    <th scope="row">{{$indice++}}</th>
                    <td>{{$turno["tipo_turno_text"]}}</td> 
                    <td>{{$turno["horario"]}}</td> 
                    @if($turno["dni"] == 99999)               
                      <td></td>               
                    @else
                      <td>{{$turno["dni"]}}</td>               
                    @endif
                    @if($turno["libre"] == 0)                  
                      <td><button onclick="reservarTurnoPrimerControl('{{$turno['horario']}}','{{$turno['horario2']}}')" class="rodri_button_aceptar">RESERVAR</button></td>                
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
                        <td><button onclick="modalModificarTurno('{{$turno['horario']}}', '{{$turno['trid']}}', '{{$turno['dni']}}')" class="rodri_button_aceptar_si">M</button></td>
                      @endif 
                    @endif
              </tr>
              @else  		          
		          <tr>
		            <th scope="row">{{$indice++}}</th>
                <td>{{$turno["tipo_turno_text"]}}</td> 
		            <td>{{$turno["horario"]}}</td>		            
		             @if($turno["dni"] == 99999)               
                  <td></td>               
                @else
                  <td>{{$turno["dni"]}}</td>   
                @endif		            
		            @if($turno["libre"] == 0)                  
		              <td><button onclick="reservarTurno('{{$turno['horario']}}')" class="rodri_button_aceptar">RESERVAR</button></td>	<td><button hidden class="rodri_button_cancelar_no">X</button></td>	   
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
                    <td><button onclick="modalModificarTurno('{{$turno['horario']}}', '{{$turno['trid']}}', , '{{$turno['dni']}}')" class="rodri_button_aceptar_si">M</button></td>
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
      
      <form method="POST" action="{{ route('secretariaasignarturnospeg') }}">
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
    	
      <h4>Horario Reservado:</h4>
    	<input disabled type="hidden" id="horario_seleccionado_tipo_turno" name="horario_seleccionado_tipo_turno" value="" placeholder="" />
      <input disabled type="text" id="horario_seleccionado" name="horario_seleccionado" value="" placeholder="" />
      <input disabled type="hidden" id="horario_seleccionado2" name="horario_seleccionado2" value="" placeholder="" />
    			      	
			<h4>Ingresar paciente:</h4>      
			<input type="text" id="dni_paciente" name="dni_paciente" value="" placeholder="Ingrese el DNI" onchange="validarPacienteExiste()" />
      <button class="rodri_button_calendario divMarginCel" type="button" onclick="mostrarModalBuscar()"><img class="card-img-top" src="images/iconos/buscar.png"/></button>
			<!--<a onclick="validarPacienteExiste()" type="button" class="btn rodri_button_a">Consultar</a>						 -->
		  <label hidden id="msj_no_asistio" class="letrasrojo">El paciente no asistio la consulta anterior</label>
		<table>			
			<tbody id="pacientes-list2" name="pacientes-list2">		          
		          <tr>		            
		            <td>Paciente: </td>
		            <td id="paciente"></td>
		          </tr>
		          <td>DNI:</td>      
		            <td id="dni"></td>
		          <tr>		            
		            <td>Mail:</td>      
		            <td id="mail"></td>
		          </tr>
		          <tr>		                           
		            <td>Telefono:</td>      
		            <td id="telefono"></td>
		          </tr>
		          <tr>		                  
		            <td>Obra social:</td>            
		            <td id="obrasocial"></td>
		          </tr>		          
		          <tr hidden>
		          	<td id="paciente_id"></td>
		          </tr>
		      </tbody>			
		</table>
		<br>
		<div id="pacienteNoExiste" hidden>
			<h5>El paciente no existe en el sistema.</h5>			
			<button onclick="showModalRegistrarPaciente()" class="rodri_button_large"  type="button">Desea dar de alta el paciente?</button>
		</div>			
		<br>
		<div id="altaTurno" hidden>						
			<button onclick="registrarTurno()" class="rodri_button">Registrar</button>
		</div>			
		<br>	
		<div id="debeSeleccionarHorario" hidden>
			<h5>Debe seleccionar un horario. Presione REGISTRAR en los botones de la tabla en el horario que desee.</h5>								
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
        <label id="label_dni" for="text" class="col-sm-0 control-label">DNI</label>      
          <input type="text" class="form-control" id="modal_dni" placeholder="DNI"/>          

          <label id="label_nombre" for="text" class="col-sm-0 control-label">Nombre</label>      
          <input type="text" class="form-control" id="modal_nombre"  placeholder="Nombre Paciente" />

          <label id="label_apellido" for="text" class="col-sm-0 control-label">Apellido</label>      
          <input type="text" class="form-control" id="modal_apellido"  placeholder="Apellido Paciente"  />

          <label for="text" class="col-sm-0 control-label">Fecha Nacimiento</label><br>
          <div class="row">
             <div class="col-md-2">
              <input type="text" maxlength="2" class="form-control" id="modal_fecha_nacimiento_dia" name="fecha_nacimiento"  placeholder="dd" required />
            </div>
             <label for="text" class="col-sm-0 control-label">/</label>
            <div class="col-md-2">
              <input type="text" maxlength="2" class="form-control" id="modal_fecha_nacimiento_mes" name="fecha_nacimiento"  placeholder="mm" required />
            </div>
            <label for="text" class="col-sm-0 control-label">/</label>
            <div class="col-md-3">   
              <input type="text" maxlength="4" class="form-control" id="modal_fecha_nacimiento_anio" name="fecha_nacimiento"  placeholder="YYYY" required />
            </div>
        </div>
          <label id="label_telefono" for="text" class="col-sm-0 control-label">Telefono</label>      
          <input type="text" class="form-control" id="modal_telefono"  placeholder="Telefono solo numeros (EJ: 2915050050)" />

          <label for="text" class="col-sm-0 control-label">Localidad</label>      
          <input type="text" class="form-control" id="modal_localidad"  placeholder="Localidad" />

          <label for="text" class="col-sm-0 control-label">Domicilio</label>      
          <input type="text" class="form-control" id="modal_domicilio"  placeholder="Domicilio" />

          <label for="text" class="col-sm-0 control-label">Mail</label>      
          <input type="text" class="form-control" id="modal_mail" placeholder="Mail"  />

          <input type="hidden" id="moduloAfiliadoObligatorioid" value="{{$moduloAfiliadoObligatorio}}">
          @if($moduloAfiliadoObligatorio == 1)                                  
            <label id="label_afiliado_obligatorio" for="text" class="col-sm-0 control-label editText">¿Es afiliado obligatorio?</label>      <br>                  
            <div class="form-check">                        
              <label class="form-check-label editText" for="materialUnchecked">Si</label>
              <input type="radio" id="check_afiliado_obligatorio_si" name="radio1" value="1" required>
                          
              <label class="form-check-label editText" for="materialChecked">No</label>
              <input type="radio" id="check_afiliado_obligatorio_no" name="radio1" value="0" required>
            </div>              
          @endif

          <label id="label_obra_social" for="text" class="col-sm-0 control-label">Obra Social</label>      
          <select class="form-control" id="modal_obra_social" name="obra_social">            
            <option>N/A</option>            
            @foreach($obraSociales as $os)
            <option>{{$os->nombre}}</option>            
            @endforeach
          </select>
          <!--<input type="text" class="form-control" id="modal_obra_social" name="obra_social" placeholder="Obra Social"  />-->

          <label id="label_numero_afiliado" for="text" class="col-sm-0 control-label">N° Afiliado</label>      
          <input type="text" class="form-control" id="modal_numero_afiliado" name="numero_afiliado" placeholder="N° Afiliado"  />
          
          <label id="label_plan" for="text" class="col-sm-0 control-label">Plan</label>      
          <input type="text" class="form-control" id="modal_plan_obra_social" name="plan_obra_social" placeholder="Plan Obra Social"  />
          <br>
          <label hidden id="error_mensaje_registrar" for="text" class="col-sm-0 control-label letrasrojo"></label>                
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

<div class="modal fade" id="modalEstaSeguroModificar" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title" 
        id="favoritesModalLabel">Modificar Turno:</h4>
      </div>
      <div class="modal-body">       
          <label class="col-sm-0">¿Esta seguro que desea modificar el turno de las </label><label id="modal_turno_horario_modificar"></label><label>hs ?</label>
      </div>
      <div class="modal-footer">
        <button type="button" 
           onclick="cancelarModificarTurno()" 
           class="rodri_button_cancelar" 
           data-dismiss="modal">Cancelar</button>  
        <button type="button" 
           onclick="guardarModificarTurno()" 
           class="rodri_button_aceptar" 
           data-dismiss="modal">Aceptar</button>                                      
      </div>
      </div>
  </div>
</div>

<div id="snackbar"><p id="snackbar_text"></p></div>

@include('modal.modal_es_feriado')
@include('modal.snackbar')
@include('modal.modal_buscar_paciente_secretaria')

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
  });    
  
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  function validarCamposRequired(){
    var value = true;
    var msj = "Faltan completar campos";
        
    if(document.getElementById("modal_dni").value.localeCompare("") == 0){
      value = false;      
      document.getElementById("label_dni").setAttribute('class', 'letrasrojo');
    } else {
      document.getElementById("label_dni").setAttribute('class', '');
    }

    if(document.getElementById("modal_nombre").value.localeCompare("")==0){
      value = false;
      document.getElementById("label_nombre").setAttribute('class', 'letrasrojo');
    } else {
      document.getElementById("label_nombre").setAttribute('class', '');
    }

    if(document.getElementById("modal_apellido").value.localeCompare("")==0){
      value = false;
      document.getElementById("label_apellido").setAttribute('class', 'letrasrojo');
    } else {
      document.getElementById("label_apellido").setAttribute('class', '');
    }

    if(document.getElementById("modal_telefono").value.localeCompare("")==0){
      value = false;
      document.getElementById("label_telefono").setAttribute('class', 'letrasrojo');
    } else {
      document.getElementById("label_telefono").setAttribute('class', '');
    }

    if(document.getElementById("modal_obra_social").value.localeCompare("PARTICULAR")!=0 && document.getElementById("modal_numero_afiliado").value.localeCompare("") == 0){
      value = false;
      document.getElementById("label_numero_afiliado").setAttribute('class', 'letrasrojo');
    } else {
      document.getElementById("label_numero_afiliado").setAttribute('class', '');
    }

    if(document.getElementById("moduloAfiliadoObligatorioid").value == 1){
      if(document.getElementById("check_afiliado_obligatorio_si").checked == false && document.getElementById("check_afiliado_obligatorio_no").checked == false){
        value = false;
        document.getElementById("label_afiliado_obligatorio").setAttribute('class', 'letrasrojo');
      } else {
        document.getElementById("label_afiliado_obligatorio").setAttribute('class', '');
      }
      if(document.getElementById("modal_obra_social").value.localeCompare("PARTICULAR") != 0 && document.getElementById("modal_plan_obra_social").value.localeCompare("") == 0){
        value = false;
        document.getElementById("label_plan").setAttribute('class', 'letrasrojo');
      } else {
        document.getElementById("label_plan").setAttribute('class', '');
      }
    }

    if(value == false){
      document.getElementById("error_mensaje_registrar").hidden = false;
      document.getElementById("error_mensaje_registrar").innerHTML = msj;
      //mostrarSnackbar(msj);
    } else {
      document.getElementById("error_mensaje_registrar").hidden = true;
    }

    return value;
  }

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

    function guardarComentario(comentario_id) {
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
           url:'/update_comentario_listado_pacientes_secretaria',
           data:{medico_id: medico, consultorio:consul, fecha :dia, comentario:comentario, turnoRegistradoId: turnoRegistradoId, _token: '{{csrf_token()}}'},
           success:function(data){              
              actualizarListado();
              mostrarSnackbar("Comentario Actualizado");
           }
        }); 
  }
 
 function reservarTurno(horario, tipoTurno) {  	  
  	var horarioSeleccionado = document.getElementById("horario_seleccionado");  
  	horarioSeleccionado.value = horario;
    document.getElementById("horario_seleccionado_tipo_turno").value = tipoTurno;
  	var paciente = document.getElementById("paciente").innerHTML;	 	
   	if(paciente.localeCompare('')!=0){
   		document.getElementById("altaTurno").hidden=false;	
   		document.getElementById("debeSeleccionarHorario").hidden=true;	 		
   	}
  }

  function reservarTurnoPrimerControl(horario, horario2) {    
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

    document.getElementById("horario_seleccionado_tipo_turno").value = "";
    document.getElementById("horario_seleccionado").value = "";
    document.getElementById("horario_seleccionado2").value = "";
    
  	document.getElementById("altaTurno").hidden=true;     
    var tipo_estudio = 0;      
     $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/actualizar_asignar_turnos',
           data:{medico_id: medico, consultorio:consul, fecha :valor, primerControl:primerControlNum, _token: '{{csrf_token()}}'},
           success:function(data){          
              var contador = 0;
                $("#tabla_pacientes").find("tr:gt(0)").remove();  
                var especialidad_id = document.getElementById("especialidad_id").value;
                if(especialidad_id == 2){
                  generarTablaCardiologo(data);                
                } else {
                  generarTabla(data);                
                }            
                document.getElementById("fechaLibreDisponible1").value = data.fechaLibreMasCercana1;
                if(data.fechaLibreMasCercana2 != null)
                  document.getElementById("fechaLibreDisponible2").value = data.fechaLibreMasCercana2;
                if(data.fechaLibreMasCercana3 != null)
                  document.getElementById("fechaLibreDisponible3").value = data.fechaLibreMasCercana3;
                // checkTurnosVirtuales();
           }
        });
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

  function registrarPaciente() {
      if(validarCamposRequired()){
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
              document.getElementById("error_mensaje_registrar").innerHTML = 'Para registrar "Debe ingresar un numero de teléfono válido"';
          } else {
         			$.ajax({
    		       type:'POST',
    		       dataType:'JSON',
    		       url:'/alta_paciente_medico_secretaria',
               data:{dni :dni,nombre:nombre,apellido:apellido,fecha_nacimiento:fecha_nacimiento,telefono:telefono,mail:mail,obra_social:obrasocial,numero_afiliado:numero_afiliado,plan:plan, consultorio:consultorio, domicilio:domicilio, localidad:localidad, afiliado_obligatorio:afiliado_obligatorio, _token: '{{csrf_token()}}'},
    		       success:function(data){      
    		       		document.getElementById("error_mensaje_registrar").hidden = true;
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
                    document.getElementById("horario_seleccionado_tipo_turno").innerHTML="";
        						document.getElementById("pacienteNoExiste").hidden=true;
        						//document.getElementById("horarioIncorrecto").hidden=true;
                    vaciarCamposModal();
                    document.getElementById("altaTurno").hidden=false;
                    $('#altaPacienteModal').modal('hide');                
    			       	} else {
    			       		alert("El paciente no ha sido registrado");           			       		
    					}	              
    			    }
        		});  		
    		  }  		
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

  function validarPacienteExiste(){  	  	
		var dni = document.getElementById("dni_paciente").value;      		
    var consultorio = document.getElementById("consultorio").value;
		$.ajax({
       type:'POST',
       dataType:'JSON',
       url:'/consultar_paciente',
       data:{dni_paciente :dni, consultorio:consultorio, _token: '{{csrf_token()}}'},
       success:function(data){           		
	       	var paciente = document.getElementById("paciente");	       	
  	   		var dni = document.getElementById("dni");
  	   		var telefono = document.getElementById("telefono");
  	   		var mail = document.getElementById("mail");
  	   		var obrasocial = document.getElementById("obrasocial");
  	   		var horarioSeleccionado = document.getElementById("horario_seleccionado").value;          
          document.getElementById("msj_no_asistio").hidden = true;
  	       if(data.paciente != null){
              validarAsistioUltimaVes(data.paciente.id);        	       	
  	       		document.getElementById("pacienteNoExiste").hidden=true;	       			       					       		
  	       		paciente.innerHTML = data.paciente.apellido+", "+data.paciente.nombre;	       			       		
  	       		dni.innerHTML = data.paciente.dni;
      				telefono.innerHTML = data.paciente.telefono;
      				mail.innerHTML = data.paciente.mail;
      				obrasocial.innerHTML = data.paciente.obra_social;
      				paciente_id.innerHTML = data.paciente.id;	
  								
    				if(horarioSeleccionado.localeCompare('')==0){					
    					document.getElementById("debeSeleccionarHorario").hidden=false;									
    				} else {					
    					document.getElementById("altaTurno").hidden=false;
    					document.getElementById("debeSeleccionarHorario").hidden=true;					
    				}				
    	       	}
    	       	else{       
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
    document.getElementById("horario_seleccionado_tipo_turno").value ="";
    document.getElementById("horario_seleccionado2").value ="";
  	document.getElementById("paciente").innerHTML="";	       	
   	document.getElementById("dni").innerHTML="";
   	document.getElementById("telefono").innerHTML="";
   	document.getElementById("mail").innerHTML="";
   	document.getElementById("obrasocial").innerHTML="";
   	document.getElementById("dni_paciente").value ="";
    vaciarCamposModal();	   	
  }

  function registrarTurno() {  			
		var paciente_id = document.getElementById("paciente_id").innerHTML;      		
		var medico_id = document.getElementById("medico_id").value;
		var consultorio = document.getElementById("consultorio").value;
		var fechaTurno = document.getElementById("dia").value;
		var horario = document.getElementById("horario_seleccionado").value;
    var tipo_turno = document.getElementById("horario_seleccionado_tipo_turno").value;
    var horario2 = document.getElementById("horario_seleccionado2").value;		
    var moduloPrimerControlDoble = document.getElementById("moduloPrimerControlDoble").value;  
    var especialidad_id = document.getElementById("especialidad_id").value;
    document.getElementById("msj_no_asistio").hidden = true; 
    //var tipo_turno = 0;
    if(especialidad_id == 2){
      var select = document.getElementById("tipo_turno_select").value;
      if(select.localeCompare("Consulta y ECG") == 0)
        tipo_turno = 1;
      if(select.localeCompare("Ecocardiograma Doppler Color") == 0)
        tipo_turno = 2;
      if(select.localeCompare("Ecodoppler de Vasos de Cuello") == 0)
        tipo_turno = 3;
    }

    var primerControl = document.getElementById("primerControlCheck").checked;
    var primerControlNum = 0;
    if(primerControl)
      primerControlNum = 1;

    if(horario2.localeCompare('') == 0)
      horario2 = "0";
    
    var cancelar_modificar_turno_id = document.getElementById("modificar_turno_id").innerHTML;

	  vaciarCampos();
    
    if((primerControl) && (moduloPrimerControlDoble == 1)) {
      $.ajax({
         type:'POST',
         dataType:'JSON',
         url:'/registrar_asignar_turno_doble',
         data:{paciente_id :paciente_id, medico_id:medico_id, consultorio:consultorio, fechaTurno:fechaTurno, horario:horario, horario2:horario2, primerControl:primerControlNum, tipo_turno:tipo_turno, cancelar_modificar_turno_id:cancelar_modificar_turno_id, _token: '{{csrf_token()}}'},
         success:function(data){
            //alert(data.dat_pac);

           if(data.turnoRegistrado == 0){        
              $("#modalTurnoFail").modal();
           }
           if(data.turnoRegistrado == 1){ 
              document.getElementById('primerControlCheck').checked = false;       
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
              document.getElementById("modal_texto_msj2").hidden=false;
              document.getElementById("modal_texto_msj1").hidden=true;
              $("#modalTurnoMsj").modal();
           }                            
           cancelarModificarTurno();
          }
      });
    } else {
	   $.ajax({
	       type:'POST',
	       dataType:'JSON',
	       url:'/registrar_asignar_turno',
	       data:{paciente_id :paciente_id, medico_id:medico_id, consultorio:consultorio, fechaTurno:fechaTurno, horario:horario, primerControl:primerControlNum, tipo_turno:tipo_turno, cancelar_modificar_turno_id: cancelar_modificar_turno_id, _token: '{{csrf_token()}}'},
	       success:function(data){
            //alert(data.dat_pac);
		       if(data.turnoRegistrado == 1) {        
		       		$("#modalTurnoOk").modal();
              document.getElementById('primerControlCheck').checked = false;
              var especialidad_id = document.getElementById("especialidad_id").value;
                if(especialidad_id == 2){
                  generarTablaCardiologo(data);                
                } else {
                  generarTabla(data);                
                }
		       	}
            if(data.turnoRegistrado == 0) {        
		       		$("#modalTurnoFail").modal();
				    }	 
            if(data.turnoRegistrado == 2) {  
              document.getElementById("modal_texto_msj1").hidden=false;      
              document.getElementById("modal_texto_msj2").hidden=true;
              document.getElementById("modal_texto_msj1").innerHTML = "El paciente ya tiene un turno registrado para esa fecha.";
              $("#modalTurnoMsj").modal();
           }    
           if(data.turnoRegistrado == 3) {  
              document.getElementById("modal_texto_msj1").hidden=false;      
              document.getElementById("modal_texto_msj2").hidden=true;  
              document.getElementById("modal_texto_msj1").innerHTML = "El paciente ya tiene un turno registrado para realizar un PEG.";  
              $("#modalTurnoMsj").modal();
           }
           cancelarModificarTurno();          
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
      for (i = 0; i < count; i++) {    
         var tipoTurno = data.turnosPaciente[i].tipo_turno;            
         var tipoTurnoText = data.turnosPaciente[i].tipo_turno_text;            
         contador = i + 1;        
         if(data.turnosPaciente[i].dni == 99999){
            var paciente = '<tr><th scope="row">'+contador+'</th><td>'+data.turnosPaciente[i].horario+'</td><td></td><td>Cancelar</td><td></td><td></td></tr>';
         } else {
           if(data.primerControl == 1){                       
            //alert(data.turnosPaciente[i].libre+" - "+data.turnosPaciente[i].horario);
            if(data.turnosPaciente[i].libre == 0){  
                var paciente = '<tr><th scope="row">'+contador+'</th><td>'+data.turnosPaciente[i].tipo_turno_text+'</td><td>'+data.turnosPaciente[i].horario+'</td><td>'+data.turnosPaciente[i].dni+'</td><td><button onClick=reservarTurnoPrimerControl("'+data.turnosPaciente[i].horario+'","'+data.turnosPaciente[i].horario2+'") class="rodri_button_aceptar">REGISTRAR</button></td><td></td><td></td></tr>';                                                                  
              } else {
                var paciente = '<tr><td>'+contador+'</td><td>'+data.turnosPaciente[i].tipo_turno_text+'</td><td>'+data.turnosPaciente[i].horario+'</td><td>'+data.turnosPaciente[i].dni+'</td><td>'+data.turnosPaciente[i].nombre+'</td><td><button onClick=modalCancelarTurno("'+data.turnosPaciente[i].horario+'") class="rodri_button_cancelar_no">X</button></td><td><button onClick=modalModificarTurno("'+data.turnosPaciente[i].horario+'","'+data.turnosPaciente[i].trid+'","'+data.turnosPaciente[i].dni+'") class="rodri_button_aceptar_si">M</button></td></tr>';                                                      
              }
          }
          else{                      
            if(data.turnosPaciente[i].libre == 0){                      
             var paciente = '<tr><th scope="row">' + contador + '</th><td>'+data.turnosPaciente[i].tipo_turno_text+'</td><td>' + data.turnosPaciente[i].horario + '</td><td>' + data.turnosPaciente[i].dni + '</td><td><button onClick="reservarTurno(\'' + data.turnosPaciente[i].horario + '\', \'' + tipoTurno + '\')" class="rodri_button_aceptar">REGISTRAR</button></td><td></td><td></td></tr>';
            } else {
              var paciente = '<tr><td>'+contador+'</td><td>'+data.turnosPaciente[i].tipo_turno_text+'</td><td>'+data.turnosPaciente[i].horario+'</td><td>'+data.turnosPaciente[i].dni+'</td><td>'+data.turnosPaciente[i].nombre+'</td><td><button onClick=modalCancelarTurno("'+data.turnosPaciente[i].horario+'") class="rodri_button_cancelar_no">X</button></td><td><button onClick=modalModificarTurno("'+data.turnosPaciente[i].horario+'","'+data.turnosPaciente[i].trid+'","'+data.turnosPaciente[i].dni+'") class="rodri_button_aceptar_si">M</button></td></tr>';                                                          
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
                var paciente = '<tr><td>'+contador+'</td><td>'+data.turnosPaciente[i].horario+'</td><td>'+data.turnosPaciente[i].dni+'</td><td>'+data.turnosPaciente[i].nombre+'</td><td>'+data.turnosPaciente[i].tipo_turno+'</td><td><input type="text" onchange=guardarComentario('+data.turnosPaciente[i].trid+') id='+comentario_id+' name="comentario" value="'+data.turnosPaciente[i].comentario+'"></td><td><button onClick=modalCancelarTurno("'+data.turnosPaciente[i].horario+'") class="rodri_button_cancelar_no">X</button></td></tr>';                                                      
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
    var canceladoPor = "Cancelado por Secretaria";
    var primerControlNum = 0;
    if((primerControl) && (moduloPrimerControlDoble == 1))
      primerControlNum = 1;

    document.getElementById("horario_seleccionado").value="";
    document.getElementById("horario_seleccionado2").value="";
    
    document.getElementById("altaTurno").hidden=true;        
     $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/asignar_turno_cancelar',
           data:{medico_id: medico, consultorio:consul, fecha :valor, primerControl:primerControlNum, horario:horario, canceladoPor:canceladoPor, _token: '{{csrf_token()}}'},
           success:function(data){          
              var contador = 0;
                $("#tabla_pacientes").find("tr:gt(0)").remove();   
                var especialidad_id = document.getElementById("especialidad_id").value;
                if(especialidad_id == 2){
                  generarTablaCardiologo(data);                
                } else {
                  generarTabla(data);                
                }                
                document.getElementById("fechaLibreDisponible1").value = data.fechaLibreMasCercana1;
                if(data.fechaLibreMasCercana2 != null)
                  document.getElementById("fechaLibreDisponible2").value = data.fechaLibreMasCercana2;
                if(data.fechaLibreMasCercana3 != null)
                  document.getElementById("fechaLibreDisponible3").value = data.fechaLibreMasCercana3;
           }
        }); 
  }

  function modalCancelarTurno(horario){
     $('#modal_turno_horario').val(horario);
     $("#modalEstaSeguro").modal();
  }

  function cancelarModificarTurno(){
    document.getElementById("modificar_turno_id").innerHTML = 0;
    document.getElementById("modificar_turno_dni").innerHTML = "";
    document.getElementById("modificar_turno_texto").hidden = true;
    document.getElementById("modificar_turno_btn").hidden = true;
    document.getElementById("modificar_turno_texto").innerHTML = "Modificar Turno";
  }

  function guardarModificarTurno(){
    var dni = document.getElementById("modificar_turno_dni").innerHTML;
    document.getElementById("dni_paciente").value = dni;
    validarPacienteExiste();
  }

  function modalModificarTurno(horario, turnoId, dni){    
    $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/get_turno_id',
           data:{turnoId: turnoId, _token: '{{csrf_token()}}'},
           success:function(data){              
               
               if(data.turno.primerControl == 'SI'){
                  document.getElementById('primerControlCheck').checked = true;
               } else {
                  document.getElementById('primerControlCheck').checked = false;
               }
               document.getElementById("modal_turno_horario_modificar").innerHTML = horario;
               document.getElementById("modificar_turno_id").innerHTML = turnoId;
               document.getElementById("modificar_turno_dni").innerHTML = dni;
               document.getElementById("modificar_turno_texto").hidden = false;
               document.getElementById("modificar_turno_btn").hidden = false;
               document.getElementById("modificar_turno_texto").innerHTML = "Modificar Turno: "+horario;
               $("#modalEstaSeguroModificar").modal();                
           }
        });     
  }  

  function tipoTurnoChange(){
    
    var tipoTurno = document.getElementById("tipo_turno_select").value;
    var estudio_seleccionado = document.getElementById("estudio_seleccionado").value;
    if(estudio_seleccionado == 0 && tipoTurno.localeCompare("PEG")==0){      
      document.getElementById("estudio_seleccionado").value = 1;      
      
    }
    if(estudio_seleccionado == 1 && tipoTurno.localeCompare("PEG")!=0){      
      document.getElementById("estudio_seleccionado").value = 0;
             
    }      
  }

  function checkTurnosVirtuales(){
    var medico_id = document.getElementById("medico_id").value;
    var fecha = document.getElementById("dia").value;
    var dia = diaSemana(fecha);
    if((medico_id == 13 && dia == 4)){ // medico_id 13 en produccion lucas sosa, medico 14 patricia
      document.getElementById("turnos_virtuales_texto").hidden = false;
    } else {
      document.getElementById("turnos_virtuales_texto").hidden = true;
    }
  }

  function diaSemana(fecha){
    var fecha_aux = fecha.split("/");
    var dia = fecha_aux[0];
    var mes = fecha_aux[1];
    var anio = fecha_aux[2];

    var dias=["dom", "lun", "mar", "mie", "jue", "vie", "sab"];
    var dt = new Date(mes+' '+dia+', '+anio+' 12:00:00');
    //return dias[dt.getUTCDay()];    
    return dt.getUTCDay();    
  };

  function mostrarModalBuscar(){
    $("#modalBuscarPacienteSecretaria").modal();  
  }

  function seleccionarPaciente(dni){
    document.getElementById("dni_paciente").value = dni;
    $("#modalBuscarPacienteSecretaria").modal('hide');                
    validarPacienteExiste();
  }
    
</script>

@endsection