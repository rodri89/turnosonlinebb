@extends('turnos_admin_secretaria/turnos_admin_secretaria_plantilla')

@section('contenedor')
<?php $lucas_sosa_id = 13; // id 13 en produccion 12 en local?>
<?php $marcelo_cardiologo_id = 9; // id 9 en produccion 9 en local?>
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

     @include('modal.snackbar')
</head>

<div class="row">
   <div class="col-md-1">
    <img class="card-img-top img_medico_center" src="images/medicos/{{$medico->foto}}" alt="">        
  </div>  
  <h2>Listado Pacientes del día</h2>
  <div class="col-md-2">
    <form> 
      @csrf                 
        <input type="hidden" id="moduloAfiliadoObligatorio" name="moduloAfiliadoObligatorio" value="{{$moduloAfiliadoObligatorio}}">
        <input type="hidden" name="paciente_id_lpa" id="paciente_id_lpa" value="{{$paciente_dni}}" />        
        @if($medico->id == $lucas_sosa_id || $medico->id == $marcelo_cardiologo_id)
          <input type="hidden" name="dias_deshabilitados" id="dias_deshabilitados" value="0,6" />
        @else
          @if($medico->id == 5)
            <input type="hidden" name="dias_deshabilitados" id="dias_deshabilitados" value="0,1,3,4,6" />
          @else
      	   <input type="hidden" name="dias_deshabilitados" id="dias_deshabilitados" value="{{$dias_deshabilitados}}" />
          @endif
        @endif
        <input type="hidden" id="consultorio" name="consultorio" value="{{$consultorio}}"  />
        <input type="hidden" id="medico_id" name="medico_id" value="{{$medico->id}}"  />
        <input type="hidden" id="especialidad_id" name="especialidad_id" value="{{$medico->especialidad}}"  />
        <input type="text" id="dia" class="form-control datepicker" name="dia" value="{{$dia}}"
         autocomplete="off" onchange="actualizarListado()">        
    </form>                                                                                   
    </div>
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

        <h2 hidden id="turnos_virtuales_texto" class="letrasrojo">Turnos virtuales</h2>  
      <!-- <div class="table-responsive" style="height:600px; overflow-y: scroll;"> -->
        <p>Sobreturnos dados: <b><input class="sinBackground" id="cantSobreturnos" name="cantSobreturnos" value='{{$cantSobreturnos}}'></input></b></p>
      	<div class="table-responsive" style="height:400px; overflow-y: scroll;">

	        <table class="table table-condensed" id="tabla_pacientes" name="tabla_pacientes">
		       <thead>
		          <tr>
		            <th scope="col">#</th>
		            <th scope="col">Horario</th>
		            <th scope="col">Paciente</th>
		            <th scope="col">DNI</th> 
                 @if($medico->especialidad == 2)
                  <th scope="col">Consulta</th>
                  <th scope="col">Comentario</th>
                @endif  
                <th scope="col">Cancelar</th>                                                     
		          </tr>
		        </thead>
		        <tbody id="pacientes-list" name="pacientes-list">
		          <?php $cont = 1 ?>
		          @foreach($turnosPaciente as $tr)
		          <tr>
		            <th scope="row">{{$cont++}}</th>
		            <td>{{$tr->horario}}</td>
                @if($tr->dni == 99999) 
  		            <td class="editText">Cancelado</td>  
  		            <td></td> 
                @else
                  <td class="editText">{{$tr->apellidop.', '.$tr->nombrep}}</td>  
                  <td class="editText">{{$tr->dni}}</td>
                   @if($medico->especialidad == 2)
                      <?php if($tr->tipo_turno == 1){$tipoTurno = "Consulta y ECG";}
                            if($tr->tipo_turno == 2){$tipoTurno = "Ecocardiograma Doppler Color";}
                            if($tr->tipo_turno == 3){$tipoTurno = "Ecodoppler de vasos de cuello";} ?>
                      <td> {{$tipoTurno}}</td>
                      <td class="editText"><input type="text" onchange="guardarComentario('{{$tr->trid}}')" id="comentario{{$tr->trid}}" name="comentario" value="{{$tr->comentario}}"/></td>  
                   @endif 
                @endif
                @if($tr->sobreturno==1)
                  <td><button onclick="modalCancelarSobreturno('{{$tr->trid}}')" class="rodri_button_cancelar_no">X</button></td>
                @else
                  <td><button hidden class="rodri_button_cancelar_no">X</button></td>
                @endif
		          </tr>
		          @endforeach
		      </tbody>
	    	</table>
    	</div>
    </div>
    </div>
    <div class="col-md-3">
      @if($medico->especialidad == 2)
      <form method="POST" action="{{ route('secretariaasignarsobreturnospeg') }}">
       @csrf                         
        <input type="hidden" id="tipo_estudio" name="tipo_estudio" value="{{$tipo_estudio}}"  />
        <input type="hidden" id="consultorio" name="consultorio" value="{{$consultorio}}"  />
        <input type="hidden" id="medico_id3" name="medico_id" value="{{$medico->id}}"  />
      <button class="sinBackground"><h4><u>PEG</u></h4></button>
      </form>

      <h4>Tipo Turno:</h4>
      <select class="form-control mercadopago_collapse_250px" id="tipo_turno_select" name="tipo_turno_select">            
              <option>Consulta y ECG</option>    
              <option>Ecocardiograma Doppler Color</option>    
              <option>Ecodoppler de Vasos de Cuello</option>    
      </select>
      @endif

			<h4>Ingresar paciente:</h4>		  		  
			<input class="editText" type="text" id="dni_paciente" name="dni_paciente" value="" placeholder="Ingrese el DNI" onchange="validarPacienteExiste()" />
      <button class="rodri_button_calendario divMarginCel" type="button" onclick="mostrarModalBuscar()"><img class="card-img-top" src="images/iconos/buscar.png"/></button>
			<!--<a onclick="validarPacienteExiste()" class="btn btn-secondary">Consultar</a>-->
      <label hidden id="msj_no_asistio" class="letrasrojo">El paciente no asistio la consulta anterior</label>
		<table>			
			<tbody id="pacientes-list" name="pacientes-list">		          
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
			<h5>Ingrese el horario que desea:</h5>
			<p>El horario no debe coincidir con ninguno de los horarios ya establecidos.</p>			
			<input type="text" id="horario" name="horario" value="" placeholder="" />
			<br><br>
      <button onclick="registrarTurno()" class="rodri_button">Registrar</button>
		</div>			
		<br>
		<div id="horarioIncorrecto" hidden>
			<p>El horario ingresado es incorrecto: el formato correcto es el siguiente dos digitos para la hora seguido de dos puntos(:) seguido de dos digitos para los minutos Ej: 08:00.</p>			
		</div>			
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

          <label for="text" class="col-sm-0 control-label">Domicilio</label>      
          <input type="text" class="form-control" id="modal_domicilio"  placeholder="Domicilio" />

          <label for="text" class="col-sm-0 control-label">Localidad</label>      
          <input type="text" class="form-control" id="modal_localidad"  placeholder="Localidad" />

          <label for="text" class="col-sm-0 control-label">Mail</label>      
          <input type="text" class="form-control" id="modal_mail" placeholder="Mail"  />

          <input type="hidden" id="moduloAfiliadoObligatorioId" value="{{$moduloAfiliadoObligatorio}}">
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
    </div>ss
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
      <button onclick="mostrarSnackbar(null)" type="button" 
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
        id="favoritesModalLabel">Eliminar Sobreturno:</h4>
      </div>
      <div class="modal-body">
          <input hidden class="modal_input_medico" type="text" id="modal_sobreturno_id"></input>         
          <label for="text" class="col-sm-0" id="modal_texto_dia_fail">¿Esta seguro que desea eliminar el sobreturno?</label>
      </div>
      <div class="modal-footer">
        <button type="button" 
           class="rodri_button_cancelar" 
           data-dismiss="modal">Cancelar</button>  
        <button type="button" 
           onclick="accionCancelarSobreturno()" 
           class="rodri_button_aceptar" 
           data-dismiss="modal">Aceptar</button>                                      
      </div>
      </div>
  </div>
</div>

<div id="snackbar"><p id="snackbar_text"></p></div>


@include('modal.modal_es_feriado')
@include('modal.modal_buscar_paciente_secretaria')

<script type="text/javascript">
var dias_deshabilitados=document.getElementById("dias_deshabilitados").value;  
  $('.datepicker').datepicker({
    weekStart: 0,
    startDate: '-3d',
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

    if(document.getElementById("modal_obra_social").value.localeCompare("PARTICULAR") != 0 && document.getElementById("modal_numero_afiliado").value.localeCompare("") == 0){
      value = false;
      document.getElementById("label_numero_afiliado").setAttribute('class', 'letrasrojo');
    } else {
      document.getElementById("label_numero_afiliado").setAttribute('class', '');
    }

    if(document.getElementById("moduloAfiliadoObligatorioId").value == 1){
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

   function guardarComentario(comentario_id){
  //  alert(comentario_id);
  //  
    var turnoRegistradoId = comentario_id;
    var comentario = document.getElementById("comentario"+comentario_id).value;
    var medico = document.getElementById("medico_id").value;    
    var consul = document.getElementById("consultorio").value;
    var dia = document.getElementById("dia").value;    
    var tipo_estudio = 0;
     $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/update_comentario_listado_pacientes_secretaria',
           data:{medico_id: medico, consultorio:consul, fecha :dia, comentario:comentario, turnoRegistradoId: turnoRegistradoId, tipo_estudio:tipo_estudio, _token: '{{csrf_token()}}'},
           success:function(data){              
              actualizarListado();  
              mostrarSnackbar("Comentario Actualizado");
           }
        }); 
  }

  function actualizarListado() {  
    var valor = document.getElementById("dia").value;    
    var medico = document.getElementById("medico_id").value;    
    var consul = document.getElementById("consultorio").value; 
    var tipo_estudio = 0;   
     $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/actualizar_listado_pacientes',
           data:{medico_id: medico, consultorio:consul, fecha :valor, tipo_estudio:tipo_estudio, _token: '{{csrf_token()}}'},
           success:function(data){              
            //alert(data.turnosPaciente.length);              
              //alert(data.turnosPaciente[0].nombrep);
              var especialidad_id = document.getElementById("especialidad_id").value;
              if(especialidad_id == 2){
                generarTablaCardiologo(data);                
              } else {
                generarTabla(data);                
              }

              var text = "Cantidad de sobreturnos asignados para este dia: "+data.cantSobreturnos;
              document.getElementById("cantSobreturnos").value = data.cantSobreturnos;
              //var cs = document.getElementById("cantSobreturnos").value;
              mostrarSnackbar(text);

              checkTurnosVirtuales();
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
    $('#modal_plan_obra_social').val("N/A");
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

  function registrarPaciente(){
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
             	   data:{dni :dni,nombre:nombre,apellido:apellido,fecha_nacimiento:fecha_nacimiento,telefono:telefono,mail:mail,obra_social:obrasocial,numero_afiliado:numero_afiliado,plan:plan, consultorio:consultorio, domicilio:domicilio, afiliado_obligatorio:afiliado_obligatorio, localidad:localidad,_token: '{{csrf_token()}}'},
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
      						document.getElementById("horario").value="";
      						document.getElementById("pacienteNoExiste").hidden=true;
      			      document.getElementById("altaTurno").hidden=false;			       		
      						document.getElementById("horarioIncorrecto").hidden=true;
      						vaciarCamposModal();
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
        document.getElementById("msj_no_asistio").hidden = true;
	       if(data.paciente != null){        
	       		validarAsistioUltimaVes(data.paciente.id);
            document.getElementById("pacienteNoExiste").hidden=true;
	       		document.getElementById("altaTurno").hidden=false;
	       		document.getElementById("horario").value="";
				    document.getElementById("horarioIncorrecto").hidden=true;
	       		paciente.innerHTML = data.paciente.apellido+", "+data.paciente.nombre;
	       		dni.innerHTML = data.paciente.dni;
    				telefono.innerHTML = data.paciente.telefono;
    				mail.innerHTML = data.paciente.mail;
    				obrasocial.innerHTML = data.paciente.obra_social;
    				paciente_id.innerHTML = data.paciente.id;				
	        } else {           		
	       		document.getElementById("pacienteNoExiste").hidden=false;          				
	       		document.getElementById("altaTurno").hidden=true;
	       		document.getElementById("horarioIncorrecto").hidden=true;
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

  function registrarTurno(){
  		if(!validarHorario()){
  			document.getElementById("horarioIncorrecto").hidden=false;          				
  		}
		else{
			document.getElementById("horarioIncorrecto").hidden=true;
			var paciente_id = document.getElementById("paciente_id").innerHTML;      		
			var medico_id = document.getElementById("medico_id").value;
			var consultorio = document.getElementById("consultorio").value;
			var fechaTurno = document.getElementById("dia").value;
			var horario = document.getElementById("horario").value;

      var especialidad_id = document.getElementById("especialidad_id").value;
      document.getElementById("msj_no_asistio").hidden = true;
      var tipo_turno = 1;
      if(especialidad_id == 2){
        var select = document.getElementById("tipo_turno_select").value;
        if(select.localeCompare("Consulta y ECG") ==0 )
          tipo_turno = 1;
        if(select.localeCompare("Ecocardiograma Doppler Color") == 0)
          tipo_turno = 2;
        if(select.localeCompare("Ecodoppler de Vasos de Cuello") == 0)
          tipo_turno = 3;
      }				
      document.getElementById("msj_no_asistio").hidden = true;    		  	   
		   $.ajax({
		       type:'POST',
		       dataType:'JSON',
		       url:'/registrar_sobreturno',
		       data:{paciente_id :paciente_id, medico_id:medico_id, consultorio:consultorio, fechaTurno:fechaTurno, horario:horario, tipo_turno:tipo_turno, _token: '{{csrf_token()}}'},
		       success:function(data){      
			       if(data.turnoRegistrado == 1){

			       		$("#modalTurnoOk").modal();
			           actualizarListado();
               /* var especialidad_id = document.getElementById("especialidad_id").value;                
                if(especialidad_id == 2){
                  generarTablaCardiologo(data);                
                } else {
                  generarTabla(data);                
                }

                var cs = document.getElementById("cantSobreturnos").value;
                var text = "Cantidad de sobreturnos asignados para este dia: "+cs;
                mostrarSnackbar(text);*/
                vaciarCampos();
			       	} else {           		
			       		$("#modalTurnoFail").modal();
					    }	              
		       }
	    	});
		}
  }

  function vaciarCampos(){
    document.getElementById("altaTurno").hidden=true;
    document.getElementById("horario").value ="";
    document.getElementById("paciente").innerHTML="";         
    document.getElementById("dni").innerHTML="";
    document.getElementById("telefono").innerHTML="";
    document.getElementById("mail").innerHTML="";
    document.getElementById("obrasocial").innerHTML="";
    document.getElementById("dni_paciente").value ="";
    vaciarCamposModal();      
  }

  function validarHorario(){
  	var horario = document.getElementById("horario").value;
  	var cont=0;
  	var validar = true;
  	if(horario.length!=5)
  		validar = false;  	
  	if(horario.substring(0,2)<0 || horario.substring(0,2)>23)
  		validar = false;
	if(horario.substring(3,5)<0 || horario.substring(3,5)>59)
		validar = false;
  	while(cont<horario.length && validar){
  		if(cont==2){
  			if(horario.charAt(cont)!=':'){
  				validar = false;  				
  			}
  		} else {
  			if(isNaN(horario.charAt(cont)))
  				validar = false;  				  			
  		}
  		cont++;
  	}
  	return validar;			
  }

  function generarTabla(data){
    var contador = 0;
    $("#tabla_pacientes").find("tr:gt(0)").remove();
    if(data.esFeriado != null){            
      $("#modal_input_texto").val(data.esFeriado.descripcion);
      $("#modalEsFeriado").modal();
    } else {
      for (i = 0; i < data.turnosPaciente.length; i++){
          contador = contador + 1;
          if(data.turnosPaciente[i].dni == 99999){
              var paciente = '<tr><td>'+contador+'</td><td>'+data.turnosPaciente[i].horario+'</td><td>Cancelado</td><td></td><td><button hidden class="rodri_button_cancelar_no">X</button></td></tr>';  
          } else {
            if(data.turnosPaciente[i].sobreturno == 0){
                var paciente = '<tr><td>'+contador+'</td><td>'+data.turnosPaciente[i].horario+'</td><td>'+data.turnosPaciente[i].apellidop+', '+data.turnosPaciente[i].nombrep+'</td><td>'+data.turnosPaciente[i].dni+'</td><td><button hidden class="rodri_button_cancelar_no">X</button></td></tr>';                                                           
            } else {        
              var paciente = '<tr><td>'+contador+'</td><td>'+data.turnosPaciente[i].horario+'</td><td>'+data.turnosPaciente[i].apellidop+', '+data.turnosPaciente[i].nombrep+'</td><td>'+data.turnosPaciente[i].dni+'</td><td><button onclick="modalCancelarSobreturno('+data.turnosPaciente[i].trid+')" class="rodri_button_cancelar_no">X</button></td></tr>';                                                           
            }
          }
          $('#pacientes-list').append(paciente); 
        }
      }
  }

    function generarTablaCardiologo(data){
    var contador = 0;
    $("#tabla_pacientes").find("tr:gt(0)").remove();
    if(data.esFeriado != null){            
      $("#modal_input_texto").val(data.esFeriado.descripcion);
      $("#modalEsFeriado").modal();
    } else {
          var tipoTurno = "";
      for (i = 0; i < data.turnosPaciente.length; i++){
          var comentario_id = "comentario"+data.turnosPaciente[i].trid;
          if(data.turnosPaciente[i].tipo_turno == 1)
            tipoTurno = "Consulta y ECG";
          if(data.turnosPaciente[i].tipo_turno == 2)
            tipoTurno = "Ecocardiograma Doppler Color";
          if(data.turnosPaciente[i].tipo_turno == 3)
            tipoTurno = "Ecodoppler de vasos de cuello";

          contador = contador + 1;
          if(data.turnosPaciente[i].dni == 99999){
              var paciente = '<tr><td>'+contador+'</td><td>'+data.turnosPaciente[i].horario+'</td><td>Cancelado</td><td></td><td></td><td></td><td></td><td><button hidden class="rodri_button_cancelar_no">X</button></td></tr>';  
          } else {
            if(data.turnosPaciente[i].sobreturno == 0){
                var paciente = '<tr><td>'+contador+'</td><td>'+data.turnosPaciente[i].horario+'</td><td>'+data.turnosPaciente[i].apellidop+', '+data.turnosPaciente[i].nombrep+'</td><td>'+data.turnosPaciente[i].dni+'</td><td>'+tipoTurno+'</td><td><input type="text" onchange=guardarComentario('+data.turnosPaciente[i].trid+') id='+comentario_id+' name="comentario" value="'+data.turnosPaciente[i].comentario+'"></td><td><button hidden class="rodri_button_cancelar_no">X</button></td></tr>';                                                           
            } else {        
              var paciente = '<tr><td>'+contador+'</td><td>'+data.turnosPaciente[i].horario+'</td><td>'+data.turnosPaciente[i].apellidop+', '+data.turnosPaciente[i].nombrep+'</td><td>'+data.turnosPaciente[i].dni+'</td><td>'+tipoTurno+'</td><td><input type="text" onchange=guardarComentario('+data.turnosPaciente[i].trid+') id='+comentario_id+' name="comentario" value="'+data.turnosPaciente[i].comentario+'"></td><td><button onclick="modalCancelarSobreturno('+data.turnosPaciente[i].trid+')" class="rodri_button_cancelar_no">X</button></td></tr>';                                                           
            }
          }
          $('#pacientes-list').append(paciente); 
        }
      }
  }

  function accionCancelarSobreturno(){
    var id = document.getElementById("modal_sobreturno_id").value;
    var tipo_estudio = 0;
    $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/cancelar_sobreturno',
           data:{sobreturno_id:id, tipo_estudio:tipo_estudio, _token: '{{csrf_token()}}'},
           success:function(data){        
              actualizarListado();          
              document.getElementById("cantSobreturnos").value = data.cantSobreturnos;
              var cs = document.getElementById("cantSobreturnos").value;
              var text = "Cantidad de sobreturnos asignados para este dia: "+data.cantSobreturnos;
              mostrarSnackbar(text);                                       
        }});  
  }

  function modalCancelarSobreturno(id){
     $('#modal_sobreturno_id').val(id);
     $("#modalEstaSeguro").modal();
  }  

  function checkTurnosVirtuales(){
    var medico_id = document.getElementById("medico_id").value;
    var fecha = document.getElementById("dia").value;
    var dia = diaSemana(fecha);
    if((medico_id == 13 && dia == 4)){ // 13 lucas sosa id en produccion, 14 es patricia
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

 window.onload=function() {    
    checkRecetasPendientes();
    if(document.getElementById("feriadoDescripcion") != null){
        var feriado = document.getElementById("feriadoDescripcion").value;      
          $("#modal_input_texto").val(feriado);
          $("#modalEsFeriado").modal();      
    } else {
      var cs = document.getElementById("cantSobreturnos").value;
        var text = "Cantidad de sobreturnos asignados para este dia: "+cs;
        mostrarSnackbar(text);
    }
    var dni_existe = document.getElementById("paciente_id_lpa").value;
    if(dni_existe.localeCompare("")!=0){
      document.getElementById("dni_paciente").value = dni_existe;
      validarPacienteExiste();
    }

    checkTurnosVirtuales();

  }

  function mostrarModalBuscar(){
      $("#modalBuscarPacienteSecretaria").modal();  
    }

    function seleccionarPaciente(dni){
        document.getElementById("dni_paciente").value = dni;
        $("#modalBuscarPacienteSecretaria").modal('hide');                
        validarPacienteExiste();
    }

  function mostrarSnackbar(cs) {      
      document.getElementById("snackbar_text").innerHTML = cs;
      var x = document.getElementById("snackbar");
      x.className = "show";
      setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
    }

</script>

@endsection