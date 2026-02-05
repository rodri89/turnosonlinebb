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

<div class="row">
  <div class="col-md-1">
    <img class="card-img-top img_medico_center" src="images/medicos/{{$medico->foto}}" alt="">        
  </div>  
  <h2>Bloquear Turno</h2><br>      
    <div class="col-md-2">
    <form> 
      @csrf                         
        <input type="hidden" name="dias_deshabilitados" id="dias_deshabilitados" value="{{$dias_deshabilitados}}" />
        <input type="hidden" id="consultorio" name="consultorio" value="{{$consultorio}}"  />
        <input type="hidden" id="medico_id" name="medico_id" value="{{$medico->id}}"  />
        <input type="hidden" id="especialidad_id" name="especialidad_id" value="{{$medico->especialidad}}"  />
        <input type="text" id="dia" class="form-control datepicker" name="dia" value="{{$dia}}"
         autocomplete="off" onchange="actualizarListado(this.value)">        
    </form>                                                                                   
    </div>  
    @if($medico->especialidad == 2)
    <div class="col-md-2"></div>
    <div>
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
      <div class="col-md-2">
          
        <table class="table table-condensed" id="tabla_pacientes1" name="tabla_pacientes1">
         <thead id="pacientes-list-head1" name="pacientes-list-head1">
            <tr>                                    
                <small id="dia_semana_1_letras"></small>
                <th id="head1" scope="col"><button class="rodri_button_agenda" onclick="bloquearDia('{{$cincoDias[0]}}')"><b><label id="dia_semana_1">{{$cincoDias[0]}}</label></b></button></th>                              
            </tr>
          </thead>         
           <tbody id="pacientes-list1" name="pacientes-list1">        
            <?php $cont = 1 ?>
            @if($turnos1 != null)
              @foreach($turnos1 as $tr)
                <tr>            
                @if($tr["libre"]==1)              
                <td><button class="rodri_button_agenda" onclick="borrarTurno('{{$tr['dni']}}','{{$cincoDias[0]}}','{{$tr['horario']}}')">{{$tr["nombre"]}}</button></td>             
                @else
                <td><button class="rodri_button_agenda" onclick="reservarTurno('{{$tr['horario']}}','{{$cincoDias[0]}}')">{{$tr["horario"]}}</button></td>            
                @endif
              </tr>
              @endforeach
            @endif
          </tbody>
        </table>
        
    </div>

    <div class="col-md-2">
    <table class="table table-condensed" id="tabla_pacientes2" name="tabla_pacientes2">
     <thead id="pacientes-list-head2" name="pacientes-list-head2">
          <tr>           
            <small id="dia_semana_2_letras"></small>      
            <th id="head2" scope="col"><button class="rodri_button_agenda" onclick="bloquearDia('{{$cincoDias[1]}}')"><b><label id="dia_semana_2">{{$cincoDias[1]}}</label></b></button></th>              
          </tr>
        </thead>
         
         <tbody id="pacientes-list2" name="pacientes-list2">        
          <?php $cont = 1 ?>
          @if($turnos2 != null)
            @foreach($turnos2 as $tr)
               <tr>            
                @if($tr["libre"]==1)
                <td><button class="rodri_button_agenda" onclick="borrarTurno('{{$tr['dni']}}','{{$cincoDias[1]}}','{{$tr['horario']}}')">{{$tr["nombre"]}}</button></td>            
                @else
                <td><button class="rodri_button_agenda" onclick="reservarTurno('{{$tr['horario']}}','{{$cincoDias[1]}}')">{{$tr["horario"]}}</button></td>
                @endif
              </tr>
            @endforeach
          @endif
        </tbody>
    </table>
    </div>

    <div class="col-md-2">
    <table class="table table-condensed" id="tabla_pacientes3" name="tabla_pacientes3">
     <thead id="pacientes-list-head3" name="pacientes-list-head3">
          <tr>                    
            <small id="dia_semana_3_letras"></small>                   
            <th id="head2" scope="col"><button class="rodri_button_agenda" onclick="bloquearDia('{{$cincoDias[2]}}')"><b><label id="dia_semana_3">{{$cincoDias[2]}}</label></b></button></th>                          
          </tr>
        </thead>
         
         <tbody id="pacientes-list3" name="pacientes-list3">        
          <?php $cont = 1 ?>
          @if($turnos3 != null)
            @foreach($turnos3 as $tr)
            <tr>            
                @if($tr["libre"]==1)
                <td><button class="rodri_button_agenda" onclick="borrarTurno('{{$tr['dni']}}','{{$cincoDias[2]}}','{{$tr['horario']}}')">{{$tr["nombre"]}}</button></td>          
                @else
                <td><button class="rodri_button_agenda" onclick="reservarTurno('{{$tr['horario']}}','{{$cincoDias[2]}}')">{{$tr["horario"]}}</button></td>
                @endif
              </tr>
            @endforeach
          @endif
        </tbody>
    </table>
  </div>

  <div class="col-md-2">
    <table class="table table-condensed" id="tabla_pacientes4" name="tabla_pacientes4">
     <thead id="pacientes-list-head4" name="pacientes-list-head4">
          <tr>          
            <small id="dia_semana_4_letras"></small>           
            <th id="head2" scope="col"><button class="rodri_button_agenda" onclick="bloquearDia('{{$cincoDias[3]}}')"><b><label id="dia_semana_4">{{$cincoDias[3]}}</label></b></button></th>                          
          </tr>
        </thead>
         
         <tbody id="pacientes-list4" name="pacientes-list4">        
          <?php $cont = 1 ?>
          @if($turnos4 != null)
            @foreach($turnos4 as $tr)
            <tr>            
                @if($tr["libre"]==1)
                <td><button class="rodri_button_agenda" onclick="borrarTurno('{{$tr['dni']}}','{{$cincoDias[3]}}','{{$tr['horario']}}')">{{$tr["nombre"]}}</button></td>        
                @else
                <td><button class="rodri_button_agenda" onclick="reservarTurno('{{$tr['horario']}}','{{$cincoDias[3]}}')">{{$tr["horario"]}}</button></td>          
                @endif
              </tr>
            @endforeach
          @endif
        </tbody>
    </table>
  </div>

  <div class="col-md-2">
    <table class="table table-condensed" id="tabla_pacientes5" name="tabla_pacientes5">
     <thead id="pacientes-list-head5" name="pacientes-list-head5">
          <tr>               
            <small id="dia_semana_5_letras"></small>                              
            <th id="head2" scope="col"><button class="rodri_button_agenda" onclick="bloquearDia('{{$cincoDias[4]}}')"><b><label id="dia_semana_5">{{$cincoDias[4]}}</label></b></button></th>                          
          </tr>
        </thead>
         
         <tbody id="pacientes-list5" name="pacientes-list5">        
          <?php $cont = 0; ?>
          @if($turnos5 != null)
            @foreach($turnos5 as $tr)
            <?php $cont++; ?>
            <tr>            
                @if($tr["libre"]==1)
                <td><button class="rodri_button_agenda" onclick="borrarTurno('{{$tr['dni']}}','{{$cincoDias[4]}}','{{$tr['horario']}}')">{{$tr["nombre"]}}</button></td> 
                @else
                <td><button class="rodri_button_agenda" onclick="reservarTurno('{{$tr['horario']}}','{{$cincoDias[4]}}')">{{$tr["horario"]}}</button></td>    
                @endif
              </tr>
            @endforeach
          @endif
        </tbody>        
    </table>
  </div>
</div>
@if($cont==4)
<br><br><br><br>
@endif
@if($cont==5)
<br><br>
@endif

<div class="modal fade" id="modalMensaje" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title" 
        id="favoritesModalLabel">Bloquear Turno:</h4>
      </div>
      <div class="modal-body">          
          <label for="text" class="col-sm-0" id="modal_texto_fail" hidden>Lo siento, el turno se encuentra registrado, intente elegir otro horario.</label>          
          <label for="text" class="col-sm-0" id="modal_texto_ok" hidden>El turno ha sido bloqueado.</label>          
          <label for="text" class="col-sm-0" id="modal_texto_dia_ok" hidden>El dia ha sido bloqueado.</label> 
          <label for="text" class="col-sm-0" id="modal_texto_dia_fail" hidden>No debe haber turnos asignados para poder cancelar el dia.</label>
          <label for="text" class="col-sm-0" id="modal_texto_turno_eliminado" hidden>El turno ha sido eliminado.</label>           
      </div>
      <div class="modal-footer">
        <button type="button" 
           class="rodri_button_aceptar" 
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
          <input hidden class="modal_input_medico" type="text" id="modal_medico_id"></input>
          <input hidden class="modal_input_horario" type="text" id="modal_dni"></input>
          <input hidden class="modal_input_horario" type="text" id="modal_horario"></input>
          <input hidden class="modal_input_horario" type="text" id="modal_dia"></input>
          <input hidden class="modal_input_horario" type="text" id="modal_fecha"></input>
          <input hidden class="modal_input_horario" type="text" id="modal_consultorio"></input>           
          <label for="text" class="col-sm-0" id="modal_texto_dia_fail">El turno pertence a un paciente ¿Esta seguro que desea eliminarlo?</label>
      </div>
      <div class="modal-footer">
        <button type="button" 
           class="rodri_button_cancelar" 
           data-dismiss="modal">Cancelar</button>  
        <button type="button" 
           onclick="actionBorrarTurno()" 
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
    startDate: '-3d',
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

  function actualizarListadoComun(fechaSeleccionada){
     var medico = document.getElementById("medico_id").value;    
     var consul = document.getElementById("consultorio").value; 
     $.ajax({
             type:'POST',
             dataType:'JSON',
             url:'/actualizar_listado_ver_semana',
             data:{medico_id: medico, consultorio:consul, fechaSeleccionada :fechaSeleccionada, _token: '{{csrf_token()}}'},
             success:function(data){                          
              generarListado(data);
              }
      });
  }

  function reservarTurno(hora, fecha) {    
  //alert("reservarTurno");    
    var medico = document.getElementById("medico_id").value;    
    var consul = document.getElementById("consultorio").value;
    var paciente = 99999; // dni
    var horario = hora;    
    var fechaTurno = convertirFecha(fecha);     
    var fechaSeleccionada = document.getElementById("dia").value;  
    var tipo_turno = 0; 

    var especialidad = document.getElementById("especialidad_id").value;
    if(especialidad == 2){
      var tipoEstudio = document.getElementById("tipo_estudio_select").value;
      if(tipoEstudio.localeCompare("PEG") ==0 )
        tipo_turno = 4;
    }     
    //alert(tipo_turno);
     $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/registrar_turno_agenda_semanal',
           data:{medico_id: medico, consultorio:consul,horario:horario,paciente:paciente, fechaInput:fechaSeleccionada, fechaTurno :fechaTurno, tipo_turno:tipo_turno, _token: '{{csrf_token()}}'},
           success:function(data){ 
            //alert(data.turnoRegistrado);
            if(data.turnoRegistrado == 1){
              generarListado(data);
              document.getElementById("modal_texto_dia_ok").hidden=true;
              document.getElementById("modal_texto_dia_fail").hidden=true;
              document.getElementById("modal_texto_ok").hidden=false;
              document.getElementById("modal_texto_fail").hidden=true;   
              document.getElementById("modal_texto_turno_eliminado").hidden=true;          
             $("#modalMensaje").modal(); 
            } else {                                   
              document.getElementById("modal_texto_dia_ok").hidden=true;
              document.getElementById("modal_texto_dia_fail").hidden=true;
              document.getElementById("modal_texto_ok").hidden=true;
              document.getElementById("modal_texto_fail").hidden=false;
              document.getElementById("modal_texto_turno_eliminado").hidden=true; 
               $("#modalMensaje").modal();                
            }
          }
        });
  }

  function bloquearDia(fecha){
    var medico = document.getElementById("medico_id").value;    
    var consul = document.getElementById("consultorio").value;
    var paciente = 99999; // dni    
    var fechaTurno = convertirFecha(fecha);  
    var fechaSeleccionada = document.getElementById("dia").value; 

    var tipo_turno = 0;
    var especialidad = document.getElementById("especialidad_id").value;
    if(especialidad == 2){
      var tipoEstudio = document.getElementById("tipo_estudio_select").value;
      if(tipoEstudio.localeCompare("PEG")==0)
        tipo_turno = 4;
    }        
     $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/bloquear_dia_agenda_semanal',
           data:{medico_id: medico, consultorio:consul,paciente:paciente, fechaInput:fechaSeleccionada, fechaTurno :fechaTurno, tipo_turno:tipo_turno, _token: '{{csrf_token()}}'},
           success:function(data){                                
            if(data.turnoCancelado == 1){
               if(especialidad == 2 && tipo_turno == 4){                
                actualizarListadoPeg();
                } else {                                                  
                 generarListado(data);
                }               
               document.getElementById("modal_texto_dia_ok").hidden=false;
               document.getElementById("modal_texto_dia_fail").hidden=true;
               document.getElementById("modal_texto_ok").hidden=true;
               document.getElementById("modal_texto_turno_eliminado").hidden=true; 
               $("#modalMensaje").modal();                
            } else {                                           
               document.getElementById("modal_texto_dia_ok").hidden=true;
               document.getElementById("modal_texto_dia_fail").hidden=false;
               document.getElementById("modal_texto_ok").hidden=true;
               document.getElementById("modal_texto_turno_eliminado").hidden=true; 
               $("#modalMensaje").modal();                
            }
          }
        });
  }

  function borrarTurno(dni, fecha, horario){
    //alert("borrarTurno");
    var medico = document.getElementById("medico_id").value;    
    var consul = document.getElementById("consultorio").value;          
    var fechaSeleccionada = document.getElementById("dia").value;
    fecha = convertirFecha(fecha);
    
    var tipo_turno = 0;
    var especialidad = document.getElementById("especialidad_id").value;
    if(especialidad == 2){
      var tipoEstudio = document.getElementById("tipo_estudio_select").value;
      if(tipoEstudio.localeCompare("PEG")==0)
        tipo_turno = 4;
    }

    if(dni!=99999){
      $('#modal_horario').val(horario);
      $('#modal_dia').val(fechaSeleccionada);
      $('#modal_consultorio').val(consul);
      $('#modal_medico_id').val(medico);
      $('#modal_fecha').val(fecha);
      $('#modal_dni').val(dni);
      $("#modalEstaSeguro").modal();
    } else {      
     $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/borrar_turno_agenda_semanal',
           data:{medico_id: medico, consultorio:consul, fechaInput:fechaSeleccionada, fechaTurno :fecha, horario:horario, tipo_turno:tipo_turno, _token: '{{csrf_token()}}'},
           success:function(data){            
              if(especialidad == 2 && tipo_turno == 4){                
                actualizarListadoPeg();
              } else {                                                  
                 generarListado(data);
              }                  
               document.getElementById("modal_texto_dia_ok").hidden=true;
               document.getElementById("modal_texto_dia_fail").hidden=true;
               document.getElementById("modal_texto_ok").hidden=true;
               document.getElementById("modal_texto_turno_eliminado").hidden=false;               
               $("#modalMensaje").modal();                            
          } 
          
        }); 
      }
  }

  function actionBorrarTurno(){
      var medico = document.getElementById("modal_medico_id").value;    
      var consul = document.getElementById("modal_consultorio").value;          
      var fechaSeleccionada = document.getElementById("modal_dia").value;
      var fecha = document.getElementById("modal_fecha").value;
      var horario = document.getElementById("modal_horario").value;
      //alert(fecha+" "+fechaSeleccionada);

      var tipo_turno = 0;
      var especialidad = document.getElementById("especialidad_id").value;
      if(especialidad == 2){
      var tipoEstudio = document.getElementById("tipo_estudio_select").value;
      if(tipoEstudio.localeCompare("PEG")==0)
        tipo_turno = 4;
      }

      $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/borrar_turno_agenda_semanal',
           data:{medico_id: medico, consultorio:consul, fechaInput:fechaSeleccionada, fechaTurno :fecha, tipo_turno:tipo_turno, horario:horario, _token: '{{csrf_token()}}'},
           success:function(data){                                                  
               generarListado(data);                  
               document.getElementById("modal_texto_dia_ok").hidden=true;
               document.getElementById("modal_texto_dia_fail").hidden=true;
               document.getElementById("modal_texto_ok").hidden=true;
               document.getElementById("modal_texto_turno_eliminado").hidden=false;               
               $("#modalMensaje").modal();                            
          } 
          
        }); 
    }

  function generarListado(data){
    $("#tabla_pacientes1 th").remove(); $("#tabla_pacientes1").find("tr:gt(0)").remove();                               
    $("#tabla_pacientes2 th").remove(); $("#tabla_pacientes2").find("tr:gt(0)").remove();                               
    $("#tabla_pacientes3 th").remove(); $("#tabla_pacientes3").find("tr:gt(0)").remove();                               
    $("#tabla_pacientes4 th").remove(); $("#tabla_pacientes4").find("tr:gt(0)").remove();                               
    $("#tabla_pacientes5 th").remove(); $("#tabla_pacientes5").find("tr:gt(0)").remove();                               
                
    $('#pacientes-list1').append('<tr><th><button class="rodri_button_agenda" onclick=bloquearDia("'+data.cincodias[0]+'")><b><label id="dia_semana_1">'+data.cincodias[0]+'</label></b></button></th></tr>');
    for (i = 0; i < data.turnos1.length; i++){
      if(data.turnos1[i].libre == 1){
        var head = '<tr><td><button class="rodri_button_agenda" onclick=borrarTurno("'+data.turnos1[i].dni+'","'+data.cincodias[0]+'","'+data.turnos1[i].horario+'")>'+data.turnos1[i].nombre+'</button></td></tr>';                
      } else{
        var head = '<tr><td><button class="rodri_button_agenda" onclick=reservarTurno("'+data.turnos1[i].horario+'","'+data.cincodias[0]+'")>'+data.turnos1[i].horario+'</button></td></tr>';                     
      } 
      $('#pacientes-list1').append(head);                                       
     }
      
      $('#pacientes-list2').append('<tr><th><button class="rodri_button_agenda" onclick=bloquearDia("'+data.cincodias[1]+'")><b><label id="dia_semana_2">'+data.cincodias[1]+'</label></b></button></th></tr>');
     for (i = 0; i < data.turnos2.length; i++){
      if(data.turnos2[i].libre == 1){
        var head = '<tr><td><button class="rodri_button_agenda" onclick=borrarTurno("'+data.turnos2[i].dni+'","'+data.cincodias[1]+'","'+data.turnos2[i].horario+'")>'+data.turnos2[i].nombre+'</button></td></tr>';                    
      } else{
        var head = '<tr><td><button class="rodri_button_agenda" onclick=reservarTurno("'+data.turnos2[i].horario+'","'+data.cincodias[1]+'")>'+data.turnos2[i].horario+'</button></td></tr>';                     
      } 
      $('#pacientes-list2').append(head);                                       
     }
    
     $('#pacientes-list3').append('<tr><th><button class="rodri_button_agenda" onclick=bloquearDia("'+data.cincodias[2]+'")><b><label id="dia_semana_3">'+data.cincodias[2]+'</label></b></button></th></tr>');                                   
     for (i = 0; i < data.turnos3.length; i++){
      if(data.turnos3[i].libre == 1){
        var head = '<tr><td><button class="rodri_button_agenda" onclick=borrarTurno("'+data.turnos3[i].dni+'","'+data.cincodias[2]+'","'+data.turnos3[i].horario+'")>'+data.turnos3[i].nombre+'</button></td></tr>';
      } else{
        var head = '<tr><td><button class="rodri_button_agenda" onclick=reservarTurno("'+data.turnos3[i].horario+'","'+data.cincodias[2]+'")>'+data.turnos3[i].horario+'</button></td></tr>';                     
      } 
      $('#pacientes-list3').append(head);                                       
     }
     
     $('#pacientes-list4').append('<tr><th><button class="rodri_button_agenda" onclick=bloquearDia("'+data.cincodias[3]+'")><b><label id="dia_semana_4">'+data.cincodias[3]+'</label></b></button></th></tr>');
     for (i = 0; i < data.turnos4.length; i++){
      if(data.turnos4[i].libre == 1){
        var head = '<tr><td><button class="rodri_button_agenda" onclick=borrarTurno("'+data.turnos4[i].dni+'","'+data.cincodias[3]+'","'+data.turnos4[i].horario+'")>'+data.turnos4[i].nombre+'</button></td></tr>';
      } else{
        var head = '<tr><td><button class="rodri_button_agenda" onclick=reservarTurno("'+data.turnos4[i].horario+'","'+data.cincodias[3]+'")>'+data.turnos4[i].horario+'</button></td></tr>';                     
      } 
      $('#pacientes-list4').append(head);                                       
     }
     
     $('#pacientes-list5').append('<tr><th><button class="rodri_button_agenda" onclick=bloquearDia("'+data.cincodias[4]+'")><b><label id="dia_semana_5">'+data.cincodias[4]+'</label></b></button></th></tr>');                                      
     for (i = 0; i < data.turnos5.length; i++){
      if(data.turnos5[i].libre == 1){
        var head = '<tr><td><button class="rodri_button_agenda" onclick=borrarTurno("'+data.turnos5[i].dni+'","'+data.cincodias[4]+'","'+data.turnos5[i].horario+'")>'+data.turnos5[i].nombre+'</button></td></tr>';
      } else{
        var head = '<tr><td><button class="rodri_button_agenda" onclick=reservarTurno("'+data.turnos5[i].horario+'","'+data.cincodias[4]+'")>'+data.turnos5[i].horario+'</button></td></tr>';                     
      } 
      $('#pacientes-list5').append(head);                                       
     }
     agregarNombresDias();
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
          var nombre = document.getElementById("modal_paciente_no_existe");          
           if(data.paciente != null) {            
              $('#modal_nombre').val(data.paciente.nombre);
              $('#modal_apellido').val(data.paciente.apellido);                
              document.getElementById("modal_paciente_no_existe").hidden=true;
              document.getElementById("br1").hidden=true;                                            
         } else {
               document.getElementById("modal_paciente_no_existe").hidden=false;                                            
               document.getElementById("br1").hidden=false;                   
               $('#modal_nombre').val("");
              $('#modal_apellido').val("");                                         
         }
       }
    });       
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
           url:'/actualizar_listado_ver_semana_peg',
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
            
           $('#pacientes-list1').append('<tr><th><button class="rodri_button_agenda" onclick=bloquearDia("'+data.cincodias[0]+'")><b><label id="dia_semana_1">'+data.cincodias[0]+'</label></b></button></th></tr>');
            for (i = 0; i < data.turnos1.length; i++){
              if(data.turnos1[i].libre == 1){
                var head = '<tr><td><button class="rodri_button_agenda" onclick=borrarTurno("'+data.turnos1[i].dni+'","'+data.cincodias[0]+'","'+data.turnos1[i].horario+'")>'+data.turnos1[i].nombre+'</button></td></tr>';                
              } else{
                var head = '<tr><td><button class="rodri_button_agenda" onclick=reservarTurno("'+data.turnos1[i].horario+'","'+data.cincodias[0]+'")>'+data.turnos1[i].horario+'</button></td></tr>';                     
              } 
              $('#pacientes-list1').append(head);                                       
             }
              
              $('#pacientes-list2').append('<tr><th><button class="rodri_button_agenda" onclick=bloquearDia("'+data.cincodias[1]+'")><b><label id="dia_semana_2">'+data.cincodias[1]+'</label></b></button></th></tr>');
             for (i = 0; i < data.turnos2.length; i++){
              if(data.turnos2[i].libre == 1){
                var head = '<tr><td><button class="rodri_button_agenda" onclick=borrarTurno("'+data.turnos2[i].dni+'","'+data.cincodias[1]+'","'+data.turnos2[i].horario+'")>'+data.turnos2[i].nombre+'</button></td></tr>';                    
              } else{
                var head = '<tr><td><button class="rodri_button_agenda" onclick=reservarTurno("'+data.turnos2[i].horario+'","'+data.cincodias[1]+'")>'+data.turnos2[i].horario+'</button></td></tr>';                     
              } 
              $('#pacientes-list2').append(head);                                       
             }
            
             $('#pacientes-list3').append('<tr><th><button class="rodri_button_agenda" onclick=bloquearDia("'+data.cincodias[2]+'")><b><label id="dia_semana_3">'+data.cincodias[2]+'</label></b></button></th></tr>');                                   
             for (i = 0; i < data.turnos3.length; i++){
              if(data.turnos3[i].libre == 1){
                var head = '<tr><td><button class="rodri_button_agenda" onclick=borrarTurno("'+data.turnos3[i].dni+'","'+data.cincodias[2]+'","'+data.turnos3[i].horario+'")>'+data.turnos3[i].nombre+'</button></td></tr>';
              } else{
                var head = '<tr><td><button class="rodri_button_agenda" onclick=reservarTurno("'+data.turnos3[i].horario+'","'+data.cincodias[2]+'")>'+data.turnos3[i].horario+'</button></td></tr>';                     
              } 
              $('#pacientes-list3').append(head);                                       
             }
             
             $('#pacientes-list4').append('<tr><th><button class="rodri_button_agenda" onclick=bloquearDia("'+data.cincodias[3]+'")><b><label id="dia_semana_4">'+data.cincodias[3]+'</label></b></button></th></tr>');
             for (i = 0; i < data.turnos4.length; i++){
              if(data.turnos4[i].libre == 1){
                var head = '<tr><td><button class="rodri_button_agenda" onclick=borrarTurno("'+data.turnos4[i].dni+'","'+data.cincodias[3]+'","'+data.turnos4[i].horario+'")>'+data.turnos4[i].nombre+'</button></td></tr>';
              } else{
                var head = '<tr><td><button class="rodri_button_agenda" onclick=reservarTurno("'+data.turnos4[i].horario+'","'+data.cincodias[3]+'")>'+data.turnos4[i].horario+'</button></td></tr>';                     
              } 
              $('#pacientes-list4').append(head);                                       
             }
             
             $('#pacientes-list5').append('<tr><th><button class="rodri_button_agenda" onclick=bloquearDia("'+data.cincodias[4]+'")><b><label id="dia_semana_5">'+data.cincodias[4]+'</label></b></button></th></tr>');                                      
             for (i = 0; i < data.turnos5.length; i++){
              if(data.turnos5[i].libre == 1){
                var head = '<tr><td><button class="rodri_button_agenda" onclick=borrarTurno("'+data.turnos5[i].dni+'","'+data.cincodias[4]+'","'+data.turnos5[i].horario+'")>'+data.turnos5[i].nombre+'</button></td></tr>';
              } else{
                var head = '<tr><td><button class="rodri_button_agenda" onclick=reservarTurno("'+data.turnos5[i].horario+'","'+data.cincodias[4]+'")>'+data.turnos5[i].horario+'</button></td></tr>';                     
              } 
              $('#pacientes-list5').append(head);                                       
             } 
             agregarNombresDias();
             }           
        });
  }

  // recibo fecha en formato 29/01/2020
  // return 2020-01-29
  function convertirFecha(fecha){
    var nuevaFecha = fecha.split("/");
    return nuevaFecha[2]+"-"+nuevaFecha[1]+"-"+nuevaFecha[0];
  }

    function agregarNombresDias(){                
      var fecha1 = document.getElementById("dia_semana_1").innerHTML;
      var dia1 = diaSemana(fecha1);   
      var dia1_letras = document.getElementById("dia_semana_1_letras");
      dia1_letras.innerHTML = dia1;

      var fecha2 = document.getElementById("dia_semana_2").innerHTML;
      var dia2 = diaSemana(fecha2);
      var dia2_letras = document.getElementById("dia_semana_2_letras");
      dia2_letras.innerHTML = dia2;
      
      var fecha3 = document.getElementById("dia_semana_3").innerHTML;
      var dia3 = diaSemana(fecha3);   
      var dia3_letras = document.getElementById("dia_semana_3_letras");
      dia3_letras.innerHTML = dia3;
      
      var fecha4 = document.getElementById("dia_semana_4").innerHTML;
      var dia4 = diaSemana(fecha4);   
      var dia4_letras = document.getElementById("dia_semana_4_letras");
      dia4_letras.innerHTML = dia4;

      var fecha5 = document.getElementById("dia_semana_5").innerHTML;
      var dia5 = diaSemana(fecha5);   
      var dia5_letras = document.getElementById("dia_semana_5_letras");
      dia5_letras.innerHTML = dia5;   
  }

  function diaSemana(fecha){
    var fecha_aux = fecha.split("/");
    var dia = fecha_aux[0];
    var mes = fecha_aux[1];
    var anio = fecha_aux[2];

    var dias=["Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado"];
    var dt = new Date(mes+' '+dia+', '+anio+' 12:00:00');
    return dias[dt.getUTCDay()];        
  };

   window.onload=function() {
    checkRecetasPendientes();
    agregarNombresDias();
   }

</script>

@endsection