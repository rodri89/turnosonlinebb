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
  <h2 class="medicoTituloTextoCel">Agenda Semanal</h2><br>      
    <div class="col-md-5">
      <div class="col-md-5">
        <form> 
        @csrf                         
          @if($medico->id == 11)
            <input type="hidden" name="dias_deshabilitados" id="dias_deshabilitados" value="0,6" />
          @else
            <input type="hidden" name="dias_deshabilitados" id="dias_deshabilitados" value="{{$dias_deshabilitados}}" />
          @endif
          
          <input type="hidden" id="consultorio" name="consultorio" value="{{$consultorio}}"  />
          <input type="hidden" id="medico_id" name="medico_id" value="{{$medico->id}}"  />
          <input type="hidden" id="especialidad_id" name="especialidad_id" value="{{$medico->especialidad}}"  />
          <input type="hidden" id="moduloAfiliadoObligatorio" name="moduloAfiliadoObligatorio" value="{{$moduloAfiliadoObligatorio}}"  />
          
          <input type="text" id="dia" class="form-control datepicker editText" name="dia" value="{{$dia}}"
           autocomplete="off" onchange="actualizarListado(this.value)">       
        </div>
        <div class="col-md-8">
          <label for="text" class="col-sm-0 control-label editText">Fecha libre mas cercana: {{$fechaLibreDisponible}}</label>           
        </div>
      </form>                                                                                   
    </div>
    @if($medico->especialidad == 2)
    <div id="seccionElegirTipoEstudio" class="form-group col-md-3">
     <label>Tipo Turno</label> 
      <select class="form-control" id="tipo_estudio_select" name="tipo_estudio_select" onchange="actualizarListadoPeg()">            
        <option>Otros Estudios</option>                            
        <option>PEG</option>                            
      </select>
    </div>    
    @endif
</div>

  <br>
    <div class="row">
      <br>
      <!-- <div class="table-responsive" style="height:600px; overflow-y: scroll;"> -->
      <div class="col-md-2">
      @if(isset($cincoDias[0]))
        <table class="table table-condensed" id="tabla_pacientes1" name="tabla_pacientes1">
         <thead id="pacientes-list-head1" name="pacientes-list-head1">
            <tr>                    
                <th id="head1" scope="col" class="textAlignCenterCel"><small id="dia_semana_1_letras"></small><label id="dia_semana_1">{{$cincoDias[0]}}</label></th>                            
            </tr>
          </thead>         
           <tbody id="pacientes-list1" name="pacientes-list1">        
            <?php $cont = 1 ?>
            @if($turnos1 != null)
              @foreach($turnos1 as $tr)
                <tr>            
                @if($tr["libre"]==1)
                <td class="textAlignCenterCel">{{$tr["nombre"]}}</td>            
                @else
                <td><button class="rodri_button_agenda" onclick="reservarTurno('{{$tr['horario']}}','{{$cincoDias[0]}}')">{{$tr["horario"]}}</button></td>            
                @endif
              </tr>
              @endforeach
            @endif
          </tbody>
        </table>
      @endif
    </div>

    <div class="col-md-2">
      @if(isset($cincoDias[1]))
        <table class="table table-condensed" id="tabla_pacientes2" name="tabla_pacientes2">
         <thead id="pacientes-list-head2" name="pacientes-list-head2">
              <tr>              
                  <th id="head2" scope="col" class="textAlignCenterCel"><small id="dia_semana_2_letras"></small><label id="dia_semana_2">{{$cincoDias[1]}}</label></th>                                    
              </tr>
            </thead>
             
             <tbody id="pacientes-list2" name="pacientes-list2">        
              <?php $cont = 1 ?>
              @if($turnos2 != null)
                @foreach($turnos2 as $tr)
                   <tr>            
                    @if($tr["libre"]==1)
                    <td class="textAlignCenterCel">{{$tr["nombre"]}}</td>            
                    @else
                    <td><button class="rodri_button_agenda" onclick="reservarTurno('{{$tr['horario']}}','{{$cincoDias[1]}}')">{{$tr["horario"]}}</button></td>
                    @endif
                  </tr>
                @endforeach
              @endif
            </tbody>
        </table>
      @endif
    </div>

    <div class="col-md-2">
      @if(isset($cincoDias[2]))

        <table class="table table-condensed" id="tabla_pacientes3" name="tabla_pacientes3">
         <thead id="pacientes-list-head3" name="pacientes-list-head3">
              <tr>                                  
                <th id="head3" scope="col" class="textAlignCenterCel"><small id="dia_semana_3_letras"></small><label id="dia_semana_3">{{$cincoDias[2]}}</label></th>                
              </tr>
            </thead>
             
             <tbody id="pacientes-list3" name="pacientes-list3">        
              <?php $cont = 1 ?>
              @if($turnos3 != null)
                @foreach($turnos3 as $tr)
                <tr>            
                    @if($tr["libre"]==1)
                    <td class="textAlignCenterCel">{{$tr["nombre"]}}</td>            
                    @else
                    <td><button class="rodri_button_agenda" onclick="reservarTurno('{{$tr['horario']}}','{{$cincoDias[2]}}')">{{$tr["horario"]}}</button></td>
                    @endif
                  </tr>
                @endforeach
              @endif
            </tbody>
        </table>
      @endif
  </div>

  <div class="col-md-2">
    @if(isset($cincoDias[3]))

      <table class="table table-condensed" id="tabla_pacientes4" name="tabla_pacientes4">
       <thead id="pacientes-list-head4" name="pacientes-list-head4">
            <tr>                              
              <th id="head4" scope="col" class="textAlignCenterCel"><small id="dia_semana_4_letras"></small><label id="dia_semana_4">{{$cincoDias[3]}}</label></th>                    
            </tr>
          </thead>
           
           <tbody id="pacientes-list4" name="pacientes-list4">        
            <?php $cont = 1 ?>
            @if($turnos4 != null)
              @foreach($turnos4 as $tr)
              <tr>            
                  @if($tr["libre"]==1)
                  <td class="textAlignCenterCel">{{$tr["nombre"]}}</td>            
                  @else
                  <td><button class="rodri_button_agenda" onclick="reservarTurno('{{$tr['horario']}}','{{$cincoDias[3]}}')">{{$tr["horario"]}}</button></td>          
                  @endif
                </tr>
              @endforeach
            @endif
          </tbody>
      </table>
    @endif
  </div>

  <div class="col-md-2">
    @if(isset($cincoDias[4]))
    <table class="table table-condensed" id="tabla_pacientes5" name="tabla_pacientes5">
     <thead id="pacientes-list-head5" name="pacientes-list-head5">
          <tr>                                  
            <th id="head5" scope="col" class="textAlignCenterCel"><small id="dia_semana_5_letras"></small><label id="dia_semana_5">{{$cincoDias[4]}}</label></th>                
          </tr>
        </thead>
         
         <tbody id="pacientes-list5" name="pacientes-list5">        
          <?php $cont = 0; ?>
          @if($turnos5 != null)
            @foreach($turnos5 as $tr)
            <?php $cont++;?>
            <tr>            
                @if($tr["libre"]==1)
                <td class="textAlignCenterCel">{{$tr["nombre"]}}</td>            
                @else
                <td><button class="rodri_button_agenda" onclick="reservarTurno('{{$tr['horario']}}','{{$cincoDias[4]}}')">{{$tr["horario"]}}</button></td>    
                @endif
              </tr>
            @endforeach
          @endif
        </tbody>        
    </table>
    @endif
  </div>
</div>

@if($cont==4)
<br><br><br><br>
@endif
@if($cont==5)
<br><br>
@endif

<div class="modal fade" id="reservarTurnoModal" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title" 
        id="favoritesModalLabel">Registrar Turno:</h4>
      </div>
      <div class="modal-body">
        @csrf        
          <div hidden id="msj_no_asistio">        
            <label class="letrasrojo">El paciente no asistio la consulta anterior</label><br>
          </div>

          <label for="text" class="col-sm-0 control-label">DNI</label>      
          <input type="text" class="form-control" id="modal_dni" placeholder="DNI" onchange="validarPacienteExiste(this.value)" />          
          <label for="text" class="col-sm-0 rodri_input_error" id="modal_paciente_no_existe" hidden>El paciente no existe.</label><br hidden id="br1">          
          
          <label for="text" class="col-sm-0 control-label">Nombre</label>      
          <input type="text" class="form-control" id="modal_nombre"  placeholder="Nombre Paciente" />

          <label for="text" class="col-sm-0 control-label">Apellido</label>      
          <input type="text" class="form-control" id="modal_apellido"  placeholder="Apellido Paciente"  />

          <div hidden id="seccion_nuevo_paciente">
            <label for="text" class="col-sm-0 control-label editText">Fecha Nacimiento</label><br>
          <div class="row">
             <div class="fechaNacEditText">
              <input type="text" maxlength="2" class="form-control editText" id="modal_fecha_nacimiento_dia" name="fecha_nacimiento"  placeholder="dd" required />
            </div>
             <label for="text" class="col-sm-0 control-label margin5">/</label>
            <div class="fechaNacEditText">
              <input type="text" maxlength="2" class="form-control editText" id="modal_fecha_nacimiento_mes" name="fecha_nacimiento"  placeholder="mm" required />
            </div>
            <label for="text" class="col-sm-0 control-label margin5">/</label>
             <div class="fechaNacAnioEditText">  
              <input type="text" maxlength="4" class="form-control editText" id="modal_fecha_nacimiento_anio" name="fecha_nacimiento"  placeholder="YYYY" required />
            </div>
        </div>
          <label for="text" class="col-sm-0 control-label editText">Telefono</label>      
          <input type="text" class="form-control editText" id="modal_telefono"  placeholder="Telefono solo numeros (EJ: 2915050050)" />

          <label for="text" class="col-sm-0 control-label editText">Localidad</label>      
          <input type="text" class="form-control editText" id="modal_localidad"  placeholder="Localidad" />

          <label for="text" class="col-sm-0 control-label editText">Domicilio</label>      
          <input type="text" class="form-control editText" id="modal_domicilio"  placeholder="Domicilio" />

          <label for="text" class="col-sm-0 control-label editText">Mail</label>      
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

          <label for="text" class="col-sm-0 control-label editText">Obra Social</label>      
          <select class="form-control" id="modal_obra_social" name="obra_social">            
            <option>N/A</option>            
            @foreach($obraSociales as $os)
            <option>{{$os->nombre}}</option>            
            @endforeach
          </select>
          <!--<input type="text" class="form-control editText" id="modal_obra_social" name="obra_social" placeholder="Obra Social"  />-->

          <label for="text" class="col-sm-0 control-label editText">N° Afiliado</label>      
          <input type="text" class="form-control editText" id="modal_numero_afiliado" name="numero_afiliado" placeholder="N° Afiliado"  />
          
          <label for="text" class="col-sm-0 control-label editText">Plan</label>      
          <input type="text" class="form-control editText" id="modal_plan_obra_social" name="plan_obra_social" placeholder="Plan Obra Social"  />
          </div>

          @if($medico->especialidad == 2)
          <label id="tipo_turno_label" for="text" class="col-sm-0 control-label">Tipo Turno</label>
          <select class="form-control mercadopago_collapse_250px" id="tipo_turno_select" name="tipo_turno_select">            
                  <option>Consulta y ECG</option>    
                  <option>Ecocardiograma Doppler Color</option>    
                  <option>Ecodoppler de Vasos de Cuello</option>    
          </select>
          @endif

          <label for="text" class="col-sm-0 control-label">Fecha</label>      
          <input disabled type="text" class="form-control" id="modal_fecha"  placeholder="Fecha" />

          <label for="text" class="col-sm-0 control-label">Hora</label>      
          <input disabled type="text" class="form-control" id="modal_hora" placeholder="Hora"  />
          
          <br>
          <label hidden id="error_mensaje_registrar" for="text" class="col-sm-0 control-label letrasrojo">Para registrar "Debe ingresar un numero de teléfono válido"</label>
      </div>
      <div class="modal-footer">
        <button type="button" 
           class="rodri_button_cancelar" 
           data-dismiss="modal">Cancelar</button>
        <span class="pull-right">
          <button id="btn_registrar_turno" onclick="registrarTurno()" class="rodri_button_aceptar" data-dismiss="modal">Registrar</button>
          <button hidden id="btn_registrar_turno_paciente" onclick="registrarTurnoPaciente()" class="rodri_button_aceptar">Registrar</button>
        </span>
      </form>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="modalMensaje" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title" 
        id="favoritesModalLabel">Registrar Turno:</h4>
      </div>
      <div class="modal-body">          
          <label for="text" class="col-sm-0" id="modal_texto_fail" hidden>Lo siento, el turno se encuentra registrado, intente elegir otro horario.</label>          
          <label for="text" class="col-sm-0" id="modal_texto_ok" hidden>El turno ha sido registrado.</label>          
      </div>
      <div class="modal-footer">
        <button type="button" 
           class="rodri_button_aceptar" 
           data-dismiss="modal">Aceptar</button>                                      
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">  
  var dias_deshabilitados=document.getElementById("dias_deshabilitados").value;  
  $('.datepicker').datepicker({
    weekStart: 0,
    language: "es",
    startDate: '-60d',
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

  function borrarCamposModal(){
      $('#modal_dni').val("");
      $('#modal_nombre').val("");
      $('#modal_apellido').val(""); 
      $('#modal_fecha_nacimiento_dia').val(""); 
      $('#modal_fecha_nacimiento_mes').val(""); 
      $('#modal_fecha_nacimiento_anio').val(""); 
      $('#modal_telefono').val(""); 
      $('#modal_domicilio').val(""); 
      $('#modal_localidad').val(""); 
      $('#modal_mail').val(""); 
      $('#modal_obra_social').val(""); 
      $('#modal_numero_afiliado').val(""); 
      $('#modal_plan_obra_social').val("");
      document.getElementById("check_afiliado_obligatorio_si").checked = false;
      document.getElementById("check_afiliado_obligatorio_no").checked = false;
  }

  function reservarTurno(hora, fecha){    
    $('#modal_fecha').val(fecha);
    $('#modal_hora').val(hora);
    $('#modal_dni').val("");
    $('#modal_nombre').val("");
    $('#modal_apellido').val(""); 
    document.getElementById("modal_paciente_no_existe").hidden=true;
    if(document.getElementById("especialidad_id").value == 2){
      document.getElementById("tipo_turno_select").hidden = false;
      document.getElementById("tipo_turno_label").hidden =false;
    }
    $("#reservarTurnoModal").modal();                                                           
  }

  function reservarTurnoPeg(hora, fecha){        
    $('#modal_fecha').val(fecha);
    $('#modal_hora').val(hora);
    $('#modal_dni').val("");
    $('#modal_nombre').val("");
    $('#modal_apellido').val("");
    document.getElementById("tipo_turno_select").hidden = true;
    document.getElementById("tipo_turno_label").hidden = true;    
    
    document.getElementById("modal_paciente_no_existe").hidden=true;
    $("#reservarTurnoModal").modal();                                                           
  }

  function actualizarListado(fechaSeleccionada) {    
    var medico = document.getElementById("medico_id").value;    
    var consul = document.getElementById("consultorio").value;     
    var especialidad_id = document.getElementById("especialidad_id").value;
    
    if(especialidad_id == 2){
      var tipoEstudio = document.getElementById("tipo_estudio_select").value;
      if(tipoEstudio.localeCompare("PEG") == 0)
        actualizarListadoPeg();
      else
        actualizarListadoComun(fechaSeleccionada);
    } else {
      actualizarListadoComun(fechaSeleccionada);     
    }
  }
  
  function actualizarListadoComun(fechaSeleccionada) {    
    var medico = document.getElementById("medico_id").value;    
    var consul = document.getElementById("consultorio").value;    
     $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/medico_actualizar_listado_ver_semana',
           data:{medico_id: medico, consultorio:consul, fechaSeleccionada :fechaSeleccionada, _token: '{{csrf_token()}}'},
           success:function(data){              
            var contador = 0;
            
            $("#tabla_pacientes1 th").remove(); $("#tabla_pacientes1").find("tr:gt(0)").remove();                               
            $("#tabla_pacientes2 th").remove(); $("#tabla_pacientes2").find("tr:gt(0)").remove();                               
            $("#tabla_pacientes3 th").remove(); $("#tabla_pacientes3").find("tr:gt(0)").remove();                               
            $("#tabla_pacientes4 th").remove(); $("#tabla_pacientes4").find("tr:gt(0)").remove();                               
            $("#tabla_pacientes5 th").remove(); $("#tabla_pacientes5").find("tr:gt(0)").remove();                               
            
            $('#pacientes-list1').append("<tr><th class='textAlignCenterCel'><small id='dia_semana_1_letras'></small><label id='dia_semana_1'>"+data.cincodias[0]+"</label></th></tr>"); 
                                                     
            for (i = 0; i < data.turnos1.length; i++){
              if(data.turnos1[i].libre == 1){
                var head = "<tr><td class='textAlignCenterCel'>"+data.turnos1[i].nombre+"</td></tr>";                    
              } else{
                var head = '<tr><td><button class="rodri_button_agenda" onclick=reservarTurno("'+data.turnos1[i].horario+'","'+data.cincodias[0]+'")>'+data.turnos1[i].horario+'</button></td></tr>';                     
              } 
              $('#pacientes-list1').append(head);                                       
             }

             $('#pacientes-list2').append("<tr><th class='textAlignCenterCel'><small id='dia_semana_2_letras'></small><label id='dia_semana_2'>"+data.cincodias[1]+"</label></th></tr>"); 
                                                       
             for (i = 0; i < data.turnos2.length; i++){
              if(data.turnos2[i].libre == 1){
                var head = "<tr><td class='textAlignCenterCel'>"+data.turnos2[i].nombre+"</td></tr>";                    
              } else{
                var head = '<tr><td><button class="rodri_button_agenda" onclick=reservarTurno("'+data.turnos2[i].horario+'","'+data.cincodias[1]+'")>'+data.turnos2[i].horario+'</button></td></tr>';                     
              } 
              $('#pacientes-list2').append(head);                                       
             }

             $('#pacientes-list3').append("<tr><th class='textAlignCenterCel'><small id='dia_semana_3_letras'></small><label id='dia_semana_3'>"+data.cincodias[2]+"</label></th></tr>"); 
                                                      
             for (i = 0; i < data.turnos3.length; i++){
              if(data.turnos3[i].libre == 1){
                var head = "<tr><td class='textAlignCenterCel'>"+data.turnos3[i].nombre+"</td></tr>";                    
              } else{
                var head = '<tr><td><button class="rodri_button_agenda" onclick=reservarTurno("'+data.turnos3[i].horario+'","'+data.cincodias[2]+'")>'+data.turnos3[i].horario+'</button></td></tr>';                     
              } 
              $('#pacientes-list3').append(head);                                       
             }

             $('#pacientes-list4').append("<tr><th class='textAlignCenterCel'><small id='dia_semana_4_letras'></small><label id='dia_semana_4'>"+data.cincodias[3]+"</label></th></tr>"); 
                                                      
             for (i = 0; i < data.turnos4.length; i++){
              if(data.turnos4[i].libre == 1){
                var head = "<tr><td class='textAlignCenterCel'>"+data.turnos4[i].nombre+"</td></tr>";                    
              } else{
                var head = '<tr><td><button class="rodri_button_agenda" onclick=reservarTurno("'+data.turnos4[i].horario+'","'+data.cincodias[3]+'")>'+data.turnos4[i].horario+'</button></td></tr>';                     
              } 
              $('#pacientes-list4').append(head);                                       
             }

             $('#pacientes-list5').append("<tr><th class='textAlignCenterCel'><small id='dia_semana_5_letras'></small><label id='dia_semana_5'>"+data.cincodias[4]+"</label></th></tr>"); 
                                                    
             for (i = 0; i < data.turnos5.length; i++){
              if(data.turnos5[i].libre == 1){
                var head = "<tr><td class='textAlignCenterCel'>"+data.turnos5[i].nombre+"</td></tr>";                    
              } else{
                var head = '<tr><td><button class="rodri_button_agenda" onclick=reservarTurno("'+data.turnos5[i].horario+'","'+data.cincodias[4]+'")>'+data.turnos5[i].horario+'</button></td></tr>';                     
              } 
              $('#pacientes-list5').append(head);                                       
             }
             checkTurnosVirtuales();
            }
        });
      
  }

  function registrarTurnoPaciente(){

    var medico = document.getElementById("medico_id").value;    
    var consul = document.getElementById("consultorio").value;
    var paciente = document.getElementById("modal_dni").value;
    var dni_paciente = paciente;
    var nombre = document.getElementById("modal_nombre").value;
    var apellido = document.getElementById("modal_apellido").value;
    var fechaNacimientoDia = document.getElementById("modal_fecha_nacimiento_dia").value;
    var fechaNacimientoMes = document.getElementById("modal_fecha_nacimiento_mes").value;
    var fechaNacimientoAnio = document.getElementById("modal_fecha_nacimiento_anio").value;

    var fecha_nacimiento = null;
    if(fechaNacimientoDia.localeCompare("")!=0 && fechaNacimientoMes.localeCompare("")!=0 && fechaNacimientoAnio.localeCompare("")!=0)
      fecha_nacimiento = fechaNacimientoAnio+"-"+fechaNacimientoMes+"-"+fechaNacimientoDia;
   
    var telefono = document.getElementById("modal_telefono").value;
    var domicilio = document.getElementById("modal_domicilio").value;
    var localidad = document.getElementById("modal_localidad").value;
    var mail = document.getElementById("modal_mail").value;

    var afiliado_obligatorio = 2;
    if(document.getElementById("moduloAfiliadoObligatorio").value == 1){
      if(document.getElementById("check_afiliado_obligatorio_si").checked)
        afiliado_obligatorio = 1;
      if(document.getElementById("check_afiliado_obligatorio_no").checked)
        afiliado_obligatorio = 0;
    }

    var obraSocial = document.getElementById("modal_obra_social").value;        
    var numeroAfiliado = document.getElementById("modal_numero_afiliado").value;    
    var plan_obra_social = document.getElementById("modal_plan_obra_social").value;    

    var horario = document.getElementById("modal_hora").value;    
    var fechaTurno = document.getElementById("modal_fecha").value;    
    fechaTurno = convertirFecha(fechaTurno);
    var fechaSeleccionada = document.getElementById("dia").value;  
    
    var tipo_turno = 0;
    if(document.getElementById("especialidad_id").value == 2){
      var tipoEstudio = document.getElementById("tipo_estudio_select").value;
      if(tipoEstudio.localeCompare("PEG")==0){
        tipo_turno = 4;
      } else {
        var select = document.getElementById("tipo_turno_select").value;
        if(select.localeCompare("Consulta y ECG")==0)
          tipo_turno = 1;
        if(select.localeCompare("Ecocardiograma Doppler Color")==0)
          tipo_turno = 2;
        if(select.localeCompare("Ecodoppler de Vasos de Cuello")==0)
          tipo_turno = 3;
      }
    }

    if(!validarTelefono()){       
      document.getElementById("error_mensaje_registrar").hidden = false;
    } else {
      $.ajax({
             type:'POST',
             dataType:'JSON',
             url:'/medico_registrar_turno_paciente_agenda_semanal',
             data:{medico_id: medico, consultorio:consul,horario:horario,paciente:paciente, fechaInput:fechaSeleccionada, fechaTurno:fechaTurno, tipo_turno:tipo_turno, nombre:nombre, apellido:apellido, dni_paciente:dni_paciente, fecha_nacimiento:fecha_nacimiento, telefono:telefono, domicilio:domicilio, localidad:localidad, mail:mail, afiliado_obligatorio:afiliado_obligatorio, obra_social:obraSocial, numero_afiliado:numeroAfiliado, obra_social_plan:plan_obra_social, _token: '{{csrf_token()}}'},
             success:function(data){              
                actualizarListadoComun(fechaSeleccionada);
                borrarCamposModal();
                document.getElementById("seccion_nuevo_paciente").hidden = true;
                $('#reservarTurnoModal').modal('hide');  
              }
          });
    }
  }

  function registrarTurno() {    
    var medico = document.getElementById("medico_id").value;    
    var consul = document.getElementById("consultorio").value;
    var paciente = document.getElementById("modal_dni").value;
    var horario = document.getElementById("modal_hora").value;    
    var fechaTurno = document.getElementById("modal_fecha").value;    
    fechaTurno = convertirFecha(fechaTurno);
    var fechaSeleccionada = document.getElementById("dia").value;        

    var tipo_turno = 0;
    if(document.getElementById("especialidad_id").value == 2){
      var tipoEstudio = document.getElementById("tipo_estudio_select").value;
      if(tipoEstudio.localeCompare("PEG")==0){
        tipo_turno = 4;
      } else {
        var select = document.getElementById("tipo_turno_select").value;
        if(select.localeCompare("Consulta y ECG")==0)
          tipo_turno = 1;
        if(select.localeCompare("Ecocardiograma Doppler Color")==0)
          tipo_turno = 2;
        if(select.localeCompare("Ecodoppler de Vasos de Cuello")==0)
          tipo_turno = 3;
      }
    }
     $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/medico_registrar_turno_agenda_semanal',
           data:{medico_id: medico, consultorio:consul,horario:horario,paciente:paciente, fechaInput:fechaSeleccionada, fechaTurno :fechaTurno, tipo_turno:tipo_turno, _token: '{{csrf_token()}}'},
           success:function(data){    
              actualizarListadoComun(fechaSeleccionada);
              $('#reservarTurnoModal').modal('hide');          
              //actualizarTabla(data);
            }
        });
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
    var dni = document.getElementById("modal_dni").value;          
    var consultorio = document.getElementById("consultorio").value;          
    $.ajax({
       type:'POST',
       dataType:'JSON',
       url:'/paciente_consultar',
       data:{dni_paciente :dni, consultorio:consultorio, _token: '{{csrf_token()}}'},
       success:function(data){      
                  
           if(data.paciente != null) { 
              validarAsistioUltimaVes(data.paciente.id);           
              $('#modal_nombre').val(data.paciente.nombre);
              $('#modal_apellido').val(data.paciente.apellido);                
              document.getElementById("modal_paciente_no_existe").hidden=true;
              document.getElementById("br1").hidden=true;                                            
              document.getElementById("seccion_nuevo_paciente").hidden = true;
              document.getElementById("btn_registrar_turno").hidden = false;
              document.getElementById("btn_registrar_turno_paciente").hidden = true;
         } else {
               document.getElementById("seccion_nuevo_paciente").hidden = false;
               document.getElementById("modal_paciente_no_existe").hidden = false;                                            
               document.getElementById("br1").hidden = false;                   
               document.getElementById("btn_registrar_turno").hidden = true;
               document.getElementById("btn_registrar_turno_paciente").hidden = false;
               $('#modal_nombre').val("");
               $('#modal_apellido').val("");                                         
         }
       }
    });       
  }

    // recibo fecha en formato 29/01/2020
  // return 2020-01-29
  function convertirFecha(fecha){
    var nuevaFecha = fecha.split("/");
    return nuevaFecha[2]+"-"+nuevaFecha[1]+"-"+nuevaFecha[0];
  }

  function actualizarListadoPeg(){
    var select = document.getElementById("tipo_estudio_select").value;
    var fecha = document.getElementById("dia").value;
    if(select.localeCompare("Otros Estudios")==0){
      actualizarListado(fecha);
    } else {
      actualizarListadoPegTabla(fecha);
    }
  }

  function actualizarListadoPegTabla(fechaSeleccionada){
    var medico = document.getElementById("medico_id").value;    
    var consul = document.getElementById("consultorio").value;    
     $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/medico_actualizar_listado_ver_semana_peg',
           data:{medico_id: medico, consultorio:consul, fechaSeleccionada :fechaSeleccionada, _token: '{{csrf_token()}}'},
           success:function(data){
          //alert(data.test);              
            var contador = 0;
            document.getElementById("dias_deshabilitados").value = data.dias_deshabilitados;  
            //alert(dias_deshabilitados);  
            $("#tabla_pacientes1 th").remove(); $("#tabla_pacientes1").find("tr:gt(0)").remove();                               
            $("#tabla_pacientes2 th").remove(); $("#tabla_pacientes2").find("tr:gt(0)").remove();                               
            $("#tabla_pacientes3 th").remove(); $("#tabla_pacientes3").find("tr:gt(0)").remove();                               
            $("#tabla_pacientes4 th").remove(); $("#tabla_pacientes4").find("tr:gt(0)").remove();                               
            $("#tabla_pacientes5 th").remove(); $("#tabla_pacientes5").find("tr:gt(0)").remove();                               
            
            $('#pacientes-list1').append("<tr><th>"+data.cincodias[0]+"</th></tr>");                                         
            for (i = 0; i < data.turnos1.length; i++){
              if(data.turnos1[i].libre == 1){
                var head = "<tr><td>"+data.turnos1[i].nombre+"</td></tr>";                    
              } else{
                var head = '<tr><td><button class="rodri_button_agenda" onclick=reservarTurnoPeg("'+data.turnos1[i].horario+'","'+data.cincodias[0]+'")>'+data.turnos1[i].horario+'</button></td></tr>';                     
              } 
              $('#pacientes-list1').append(head);                                       
             }

              $('#pacientes-list2').append("<tr><th>"+data.cincodias[1]+"</th></tr>");                                         
             for (i = 0; i < data.turnos2.length; i++){
              if(data.turnos2[i].libre == 1){
                var head = "<tr><td>"+data.turnos2[i].nombre+"</td></tr>";                    
              } else{
                var head = '<tr><td><button class="rodri_button_agenda" onclick=reservarTurnoPeg("'+data.turnos2[i].horario+'","'+data.cincodias[1]+'")>'+data.turnos2[i].horario+'</button></td></tr>';                     
              } 
              $('#pacientes-list2').append(head);                                       
             }

             $('#pacientes-list3').append("<tr><th>"+data.cincodias[2]+"</th></tr>");                                         
             for (i = 0; i < data.turnos3.length; i++){
              if(data.turnos3[i].libre == 1){
                var head = "<tr><td>"+data.turnos3[i].nombre+"</td></tr>";                    
              } else{
                var head = '<tr><td><button class="rodri_button_agenda" onclick=reservarTurnoPeg("'+data.turnos3[i].horario+'","'+data.cincodias[2]+'")>'+data.turnos3[i].horario+'</button></td></tr>';                     
              } 
              $('#pacientes-list3').append(head);                                       
             }

             $('#pacientes-list4').append("<tr><th>"+data.cincodias[3]+"</th></tr>");                                         
             for (i = 0; i < data.turnos4.length; i++){
              if(data.turnos4[i].libre == 1){
                var head = "<tr><td>"+data.turnos4[i].nombre+"</td></tr>";                    
              } else{
                var head = '<tr><td><button class="rodri_button_agenda" onclick=reservarTurnoPeg("'+data.turnos4[i].horario+'","'+data.cincodias[3]+'")>'+data.turnos4[i].horario+'</button></td></tr>';                     
              } 
              $('#pacientes-list4').append(head);                                       
             }

             $('#pacientes-list5').append("<tr><th>"+data.cincodias[4]+"</th></tr>");                                         
             for (i = 0; i < data.turnos5.length; i++){
              if(data.turnos5[i].libre == 1){
                var head = "<tr><td>"+data.turnos5[i].nombre+"</td></tr>";                    
              } else{
                var head = '<tr><td><button class="rodri_button_agenda" onclick=reservarTurnoPeg("'+data.turnos5[i].horario+'","'+data.cincodias[4]+'")>'+data.turnos5[i].horario+'</button></td></tr>';                     
              } 
              $('#pacientes-list5').append(head);                                       
             } 
            }
        });

        checkTurnosVirtuales();
  }

    function checkTurnosVirtuales(){    
      var medico_id = document.getElementById("medico_id").value;      
      var id_lucas_sosa = 13; // lucas sosa id en produccion es 13 en en local 12
      var id_patricia_sosa = 14;

      var fecha1 = document.getElementById("dia_semana_1").innerHTML;
      var dia1 = diaSemana(fecha1);   
      var dia1_letras = document.getElementById("dia_semana_1_letras");
      dia1_letras.innerHTML = dia1+" ";
      if((medico_id == id_lucas_sosa && dia1.localeCompare("Jue") == 0) || (medico_id == id_patricia_sosa)){
        dia1_letras.setAttribute('class', 'letrasrojo');
      } else {
          dia1_letras.setAttribute('class', '');
      }
      
      var fecha2 = document.getElementById("dia_semana_2").innerHTML;
      var dia2 = diaSemana(fecha2);
      var dia2_letras = document.getElementById("dia_semana_2_letras");
      dia2_letras.innerHTML = dia2+" ";
      if((medico_id == id_lucas_sosa && dia2.localeCompare("Jue") == 0) || (medico_id == id_patricia_sosa)){
        dia2_letras.setAttribute('class', 'letrasrojo');
      } else {
        dia2_letras.setAttribute('class', '');
      }

      var fecha3 = document.getElementById("dia_semana_3").innerHTML;
      var dia3 = diaSemana(fecha3);   
      var dia3_letras = document.getElementById("dia_semana_3_letras");
      dia3_letras.innerHTML = dia3+" ";
      if((medico_id == id_lucas_sosa && dia3.localeCompare("Jue") == 0) || (medico_id == id_patricia_sosa)){
        dia3_letras.setAttribute('class', 'letrasrojo');
      } else {
        dia3_letras.setAttribute('class', '');
      }

      var fecha4 = document.getElementById("dia_semana_4").innerHTML;
      var dia4 = diaSemana(fecha4);   
      var dia4_letras = document.getElementById("dia_semana_4_letras");
      dia4_letras.innerHTML = dia4+" ";
      if((medico_id == id_lucas_sosa && dia4.localeCompare("Jue") == 0) || (medico_id == id_patricia_sosa)){
        dia4_letras.setAttribute('class', 'letrasrojo');
      } else {
        dia4_letras.setAttribute('class', '');
      }

      var fecha5 = document.getElementById("dia_semana_5").innerHTML;
      var dia5 = diaSemana(fecha5);   
      var dia5_letras = document.getElementById("dia_semana_5_letras");
      dia5_letras.innerHTML = dia5+" ";
      if((medico_id == id_lucas_sosa && dia5.localeCompare("Jue") == 0) || (medico_id == id_patricia_sosa)){
        dia5_letras.setAttribute('class', 'letrasrojo');
      } else {
        dia5_letras.setAttribute('class', '');
      }
  }

  function diaSemana(fecha){
    var fecha_aux = fecha.split("/");
    var dia = fecha_aux[0];
    var mes = fecha_aux[1];
    var anio = fecha_aux[2];

    var dias=["Dom", "Lun", "Mar", "Mie", "Jue", "Vie", "Sab"];
    var dt = new Date(mes+' '+dia+', '+anio+' 12:00:00');
    return dias[dt.getUTCDay()];        
  };

    window.onload=function() {    
    checkTurnosVirtuales();    
    checkRecetasPendientes();    
  }

</script>

@endsection