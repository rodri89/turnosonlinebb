@extends('turnos_admin_medico/turnos_admin_medico_plantilla_body')

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
     @include('modal.snackbar')
     @include('modal.modal_recetas')
</head>

<div class="row">
  <div class="col-md-1">
    <img class="card-img-top img_medico_center" src="images/medicos/{{$medico->foto}}" alt="">        
  </div>  
  <div class="col-md-5"><br>
    <h2 class="medicoTituloTextoCel">Videollamadas</h2><br> 
    <div class="col-md-12">                
          <input type="hidden" name="medico_id" id="medico_id" value="{{$medico->id}}" />
          <input type="hidden" name="consultorio" id="consultorio" value="{{$medico->consultorio}}" />
          @if($videollamada->link)
            <input type="hidden" name="videollamada_link" id="videollamada_link" value="{{$videollamada->link}}" />          
            <div class="col-md-12" id="seccion_videollamada_link">
              <label for="text" class="col-sm-0" id="link_videollamada_label" for="link_videollamada">Link para conectarse a la videollamada:</label>  <br>
              <div class="col-md-12">
                @if($videollamada->link)           
                  <a href="{{$videollamada->link}}" target="_blank" id="a_link_existe">Click aquí para iniciar la videollamada.</a>                  
                @endif                                                                                   
              </div>
            </div>
          @else
            <input type="hidden" name="videollamada_link" id="videollamada_link" value="" />          
            <label for="text" class="col-sm-0" id="link_videollamada_label" for="link_videollamada">Debe ir a configuracion y cargar un link.</label>
          @endif
    </div>  
  </div>
  <div class="col-md-5"><br>
    <h2 class="medicoTituloTextoCel">Sobreturno</h2><br> 
          <div class="col-md-12">                       
          <input type="hidden" name="medico_id" id="medico_id" value="{{$medico->id}}" />
          <input type="hidden" name="consultorio" id="consultorio" value="{{$medico->consultorio}}" />
          <table>
            <tr>
              <td>
              Paciente:              
              </td>
              <td>
                <div class="col-md-8"> 
                  <input class="form-control" type="text" name="sobreturno_dni_paciente" id="sobreturno_dni_paciente" placeholder="DNI Paciente" /> 
                </div>
              </td>
            </tr>
            <tr>
              <td>
              Horario:            
              </td>
                <td>
                  <div class="col-md-8"> 
                  <input class="form-control" type="text" name="sobreturno_horario" id="sobreturno_horario" placeholder="08:00" /> 
                </div>
              </td>                     
            </tr>
        </table>
        <br>
        <div class="text-left">
        <button onclick="agregarSobreturnoVideollamada()" type="button" class="rodri_button">Agregar</button>
      </div>
    </div>
  </div>
</div>
<br>
<div class="row">
    <h4>Turnos dia:</h4>
    <div class="col-md-2">
      <form>
        @csrf          
        <input type="hidden" name="dias_deshabilitados" id="dias_deshabilitados" value="{{$dias_deshabilitados}}" />
        <input type="text" id="dia" class="form-control datepicker" name="dia" value="{{$fecha}}"
           autocomplete="off" onchange="actualizarListado()">
      </form>
      <br>
    </div>    
          <div class="table-responsive" style="height:400px; ">
            <table class="table table-condensed" id="tabla_pacientes" name="tabla_pacientes">
              <thead>
                  <tr>
                    <th class="editText" scope="col">#</th>
                    <th class="editText" scope="col">Paciente</th>                                                
                    <th class="editText" scope="col">Horario</th>
                    <th class="editText" scope="col">Disponible</th>                    
                    <th class="editText" scope="col">Estado</th>                    
                    <th class="editText" scope="col">Link</th>
                    <th class="editText" scope="col">Cancelar Turno</th>
                  </tr>
                </thead>
                <tbody id="pacientes-list" name="pacientes-list">
                  <?php $cont = 1 ?>
                  @foreach($turnosRegistradosVideollamadas as $tr)
                    <tr>
                      <th class="editText" scope="row">{{$cont++}}</th>
                        @if($tr["libre"] == 0)
                          @if($tr["dni"] == 99999)
                            <td class="editText">CANCELADO</td>                                                          
                            <td class="editText">{{$tr["horario"]}}</td>
                            <td class="editText"></td>
                            <td class="editText"></td>
                            <td class="editText"></td>
                            <td class="editText"><button type="button" onclick="descancelarTurnoVideollamada('{{$tr['trid']}}')" class="rodri_button_cancelar_si">D</button></td>
                          @else
                            <td class="editText">{{$tr["paciente"]}}</td>                                                          
                            <td class="editText">{{$tr["horario"]}}</td>
                            
                            @if($tr["disponible"] == 1)
                              <td class="editText">SI</td>
                            @else
                              <td class="editText">NO</td>
                            @endif                        
                            <td class="editText">{{$tr["comentario"]}}</td>
                            @if($isMedicoDisponible == 1) 
                              @if($tr["disponible_medico"] == 1)
                                <td class="editText"><button id="{{$tr['trid']}}" type="button" onclick="enviarLink('{{$tr['trid']}}', 2)" class="rodri_button_aceptar">Finalizar</button></td>
                              @else
                                <td disabled class="editText"><button id="{{$tr['trid']}}" type="button" class="rodri_button_disabled">Enviar Link</button></td>
                              @endif                         
                            @else
                              <td class="editText"><button id="{{$tr['trid']}}" type="button" onclick="enviarLink('{{$tr['trid']}}', 1)" class="rodri_button_aceptar">Enviar Link</button></td>
                            @endif             
                            <td class="editText"><button type="button" onclick="cancelarTurnoVideollamadaId('{{$tr['trid']}}')" class="rodri_button_cancelar_no">C</button></td>                        
                          @endif
                        @else
                          <td class="editText">LIBRE</td>                                                          
                          <td class="editText">{{$tr["horario"]}}</td>
                          <td class="editText"></td>
                          <td class="editText"></td>
                          <td class="editText"></td>
                          <td class="editText"></td>
                        @endif                                              
                    </tr>
                  @endforeach
              </tbody>
            </table>
          </div>
</div>

<div id="snackbar"><p><input class="input_snackbar_220" id="snackbar_msj" name="snackbar_msj"></input></p></div>

<script type="text/javascript">  

var dias_deshabilitados=document.getElementById("dias_deshabilitados").value;  
  $('.datepicker').datepicker({
    weekStart: 0,
    startDate: '-3d',
    language: "es",
    keyboardNavigation: false,
    forceParse: false,
    autoclose: true,
    //daysOfWeekDisabled: dias_deshabilitados    
  }).attr('readonly','readonly');    
  

$.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

// opcion 1 = enviarLink, opcion=2 finalizar.
function enviarLink(turno_id, opcion){
  var dia = document.getElementById("dia").value;      
    $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/medico_enviar_link_videollamadas',
           data:{dia: dia, turno_id:turno_id, opcion:opcion, _token: '{{csrf_token()}}'},
           success:function(data){                        
              generarTabla(data);                     
              mostrarSnackbar("El link ha sido enviado");
           }
        });    
}

function actualizarListado(){
    var dia = document.getElementById("dia").value;     
     $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/medico_actualizar_listado_videollamadas',
           data:{dia: dia, _token: '{{csrf_token()}}'},
           success:function(data){                        
              generarTabla(data);                     
           }
        });
}

function generarTabla(data){
    var contador = 0;
    $("#tabla_pacientes").find("tr:gt(0)").remove();
    if(data.turno != null){     
    var count = Object.keys(data.turno).length;               
      for (i = 0; i < count; i++){        
         contador = i + 1;        
        if(data.turno[i].libre == 0){
          if(data.turno[i].dni == 99999){
            var paciente = '<tr><th scope="row">'+contador+'</th><td>CANCELADO</td><td>'+data.turno[i].horario+'</td><td></td><td></td><td></td><td><button type="button" onclick=descancelarTurnoVideollamada("'+data.turno[i].trid+'") class="rodri_button_aceptar_si">D</button></td></tr>';
          } else {                       
            var disponible = 'NO';
            if(data.turno[i].disponible == 1)
              disponible = 'SI';
              if(data.isMedicoDisponible == 1){ // medico esta ocupado                       
                if(data.turno[i].disponible_medico == 1){
                var paciente = "<tr><th class='editText' scope='row'>"+contador+"</th><td class='editText'>"+data.turno[i].paciente+"</td><td class='editText'>"+data.turno[i].horario+"</td><td class='editText'>"+disponible+"</td><td class='editText'>"+data.turno[i].comentario+"</td><td class='editText'><button onclick='enviarLink("+data.turno[i].trid+", 2)' class='rodri_button_volver editText'>Finalizar</button></td><td class='editText'><button onclick=cancelarTurnoVideollamadaId("+data.turno[i].trid+") class='rodri_button_cancelar_no editText'>C</button> </td></tr>";
                } else {
                var paciente = "<tr><th class='editText' scope='row'>"+contador+"</th><td class='editText'>"+data.turno[i].paciente+"</td><td class='editText'>"+data.turno[i].horario+"</td><td class='editText'>"+disponible+"</td><td class='editText'>"+data.turno[i].comentario+"</td><td class='editText'><button class='rodri_button_disabled editText' disabled>Enviar Link</button></td><td class='editText'><button onclick=cancelarTurnoVideollamadaId("+data.turno[i].trid+") class='rodri_button_cancelar_no editText'>C</button> </td></tr>";
                }
              } else {
              var paciente = "<tr><th class='editText' scope='row'>"+contador+"</th><td class='editText'>"+data.turno[i].paciente+"</td><td class='editText'>"+data.turno[i].horario+"</td><td class='editText'>"+disponible+"</td><td class='editText'>"+data.turno[i].comentario+"</td><td class='editText'><button onclick='enviarLink("+data.turno[i].trid+", 1)' class='rodri_button_aceptar editText'>Enviar Link</button></td><td class='editText'><button onclick=cancelarTurnoVideollamadaId("+data.turno[i].trid+") class='rodri_button_cancelar_no editText'>C</button> </td></tr>";
            }
            }          
        } else {
          var paciente = "<tr><th class='editText' scope='row'>"+contador+"</th><td class='editText'>LIBRE</td><td class='editText'>"+data.turno[i].horario+"</td><td class='editText'></td><td></td><td></td><td class='editText'><button onclick=cancelarTurnoVideollamada('"+data.turno[i].horario+"') class='rodri_button_cancelar_no editText'>C</button> </td></tr>";
        }
        $('#pacientes-list').append(paciente);                        
      }
    }
}

  function cancelarTurnoVideollamada(horario){    
    var dia = document.getElementById("dia").value;     
    $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/medico_cancelar_turno_videollamadas',
           data:{dia: dia, horario:horario, _token: '{{csrf_token()}}'},
           success:function(data){                        
              generarTabla(data);                     
           }
        });
  }

  function cancelarTurnoVideollamadaId(turno_id){    
    var dia = document.getElementById("dia").value; 
    //alert(turno_id); 
    $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/medico_cancelar_turno_videollamadas_id',
           data:{dia: dia, turno_id:turno_id, _token: '{{csrf_token()}}'},
           success:function(data){                        
              generarTabla(data);                     
           }
        });
  }

function descancelarTurnoVideollamada(turno_id){
    var dia = document.getElementById("dia").value;     
    $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/medico_descancelar_turno_videollamadas',
           data:{dia: dia, turno_id:turno_id, _token: '{{csrf_token()}}'},
           success:function(data){                        
              generarTabla(data);                     
           }
        });
  }

function guardarLinkVideollamada(){
  var link = document.getElementById("link_videollamada").value;
  var medico_id = document.getElementById("medico_id").value;
  var consultorio = document.getElementById("consultorio").value;
   $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/guardar_link_videollamada',
           data:{medico_id:medico_id, link:link, consultorio:consultorio, _token: '{{csrf_token()}}'},
           success:function(data){              
              mostrarSnackbar("Link ha sido guardado");      
              document.getElementById("seccion_videollamada_link").hidden = false;                       
              location.reload();              
           }
        });  
}

function mostrarSnackbar(text) {    
    document.getElementById("snackbar_msj").value = text;
    var x = document.getElementById("snackbar");
    x.className = "show";
    setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
  }

function recargarListado(){
    actualizarListado();    
}

function agregarSobreturnoVideollamada(){
  var medico_id = document.getElementById("medico_id").value;
  var horario = document.getElementById("sobreturno_horario").value;
  var paciente_dni = document.getElementById("sobreturno_dni_paciente").value;
  var fecha = document.getElementById("dia").value;  
  if(!validarHorario(horario)){
    mostrarSnackbar("Formato horario incorrecto.");  
  } else {
  $.ajax({
         type:'POST',
         dataType:'JSON',
         url:'/agregar_sobreturno_videollamada',
         data:{medico_id:medico_id, horario:horario, paciente_dni:paciente_dni, fecha:fecha, _token: '{{csrf_token()}}'},
         success:function(data){              
            if(data.response == 0){
              mostrarSnackbar("Paciente no existe");  
            } else {
              if(data.response == 2){
                mostrarSnackbar("Turno esta ocupado"); 
              } else {          
              mostrarSnackbar("Sobreturno agregado");  
              document.getElementById("sobreturno_horario").value = "";
              document.getElementById("sobreturno_dni_paciente").value = "";
              actualizarListado();
            }            
         }
       }
      });
    }
}

  function validarHorario(horario){    
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
  /*window.onload=function() {    
    var link = document.getElementById("videollamada_link").value;
    if(link.localeCompare('')==0){
      alert(link);
      document.getElementById("a_link_no_existe").href = link;
    }
    document.getElementById("seccion_videollamada_link").hidden = false;
  }
  */
</script>

@endsection