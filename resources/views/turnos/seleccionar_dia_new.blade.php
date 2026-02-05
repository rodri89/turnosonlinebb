
@extends('turnos/modelo_plantilla')

@section('titulo_header','Seleccionar día')

@if($esVideollamada == 0)
  @section('descripcion_header','En esta sección deberá elegir el día que desee su turno.')
@else
  @section('headerContainer')
    <ul>  
      <li class="lead fontColorHeader">En esta sección deberá elegir el día que desee su turno. </li>
      <li class="lead fontColorHeader">El modo de abonar la consulta dependerá exclusivamente de lo establecido por cada profesional.</li>
    </ul>
  @endsection
@endif
@if($medico->id == 13 || $medico->id == 14) 
   @section('headerContainer')
    <p  class="lead fontColorHeader">Si usted desea un turno telemático o virtual (video-llamada) contáctese al siguiente correo: <b class="letrasblancas">{{$medico->mail}}</b></p>
  @endsection
@endif
@if($medico->id == 11) 
  @section('headerContainer')
    @if($tipoTurno == 22)
        <p class="lead fontColorHeader"> Este sera un turno online, la profesional se va a comunicar con usted el dia y horario seleccionado.</p>
        <p class="lead fontColorHeader" style="color: white;"> <b>Los turnos online serán para revisión de resultados o para consultas que requieran evaluación.</b></p>
    @else
      
       <p  class="lead fontColorHeader">Para realizar consultas puede hacerlo mediante un whatsapp a <a class="letrasblancas" href="https://api.whatsapp.com/send?phone=542915047616" target="_blank">{{$consultorio->telefono}}</a> (no puede recibir llamadas).</p>
       <p class="lead fontColorHeader">Para mas información puede acceder a <a class="letrasblancas" href="https://www.instagram.com/ccdgineco/" target="_blank">IG @ccdgineco</a></p>
    @endif
  @endsection
@endif
@section('contenedor')

<head>        
    <!-- Optional theme -->
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
  <div class="col-md-2 mb-3">
      <div>     
          <img class="card-img-top img_medico_center" src="images/medicos/{{$medico->foto}}" alt="">
      </div>
    </div>

    <div class="col-md-5 mb-3">
      <div>                               
          <div class="card-body">
            <h4 class="fontMedicoNombre fontColorHeader">{{$medico->apellido}}, {{$medico->nombre}}</h4>      
            <p class="fontColorHeader">Días y horarios de atención:</p>
            @include('turnos.seleccionar_dia_casos_especiales')
          </div>        
      </div>
    </div>    
    <div class="col-md-5 mb-3">
      <div> 
          @if($moduloRecetas == 1 && $esVideollamada == 1)  
           <div class="row">
            <h5 class="fontColorHeader fontMedicoNombre textoReceta">Solicitar receta:</h5> 
             <input type="hidden" name="paciente_domicilio" id="paciente_domicilio" value="{{$paciente->domicilio}}" />                                                
              <button onclick="modalRecetas()" class="rodri_button_receta divMarginCel botonReceta"><img class="card-img-top" src="images/iconos/receta1.png"/></button>                                             
          </div>  
          @endif
          <div class="card-body">
            <h5 class="fontColorHeader fontMedicoNombre">Seleccione un dia:</h5>            
            @if($esVideollamada == 0)
              <form method="POST" action="{{ route('seleccionarturnohorario') }}">            
            @else
              <form method="POST" action="{{ route('seleccionarturnohorariovideollamada') }}">              
            @endif
              @csrf
              <input type="hidden" name="esVideollamada" id="esVideollamada" value="{{$esVideollamada}}" />
              @if($medico->id == 13 || $medico->id == 1)
                @if($medico->id == 1)
                  <input type="hidden" name="dias_deshabilitados" id="dias_deshabilitados" value="0,2,4,5,6" />
                  <input type="hidden" name="dias_habilitados" id="dias_habilitados" value="1,3" />
                @endif
                
                @if($medico->id == 13)
                  <input type="hidden" name="dias_deshabilitados" id="dias_deshabilitados" value="0,3,5,6" />
                  <input type="hidden" name="dias_habilitados" id="dias_habilitados" value="1,2,4" />
                @endif
              @else
                <input type="hidden" name="dias_deshabilitados" id="dias_deshabilitados" value="{{$dias_deshabilitados}}" />
                <input type="hidden" name="dias_habilitados" id="dias_habilitados" value="{{$dias_habilitados}}" />
              @endif
              <input type="hidden" name="end_date" id="end_date" value="{{$end_date}}" />
              <input type="hidden" id="medico_id" name="medico" value="{{$medico->id}}" />             
              <input type="hidden" id="tipoTurno" name="tipoTurno" value="{{$tipoTurno}}" />
              <input type="hidden" id="especialidad_id" name="especialidad" value="{{$medico->especialidad}}" />
              <input type="hidden" id="consultorio_id" name="consultorio" value="{{$consultorio->id}}" />
              <input type="hidden" id="paciente_id" name="paciente_id" value="{{$paciente->id}}" />               
              <input type="hidden" name="primer_control" id="primer_control" value="{{$primerControl}}" />
              <div class="input-group">
                  <input type="text" class="form-control datepicker editText" name="fecha_seleccionada" id="fecha_seleccionada" placeholder="Click aqui para seleccionar la fecha" required autocomplete="off" onchange="validarNotNull()">                                
              </div>
              <input hidden id="diferencia_dias_id" value="{{$diferenciaDias}}" />
              @if($diferenciaDias < 0)
                <?php $diferenciaDias = -1*$diferenciaDias; ?>
                <br>
                <p class="letrasrojo"><i>En {{$diferenciaDias}} dias se habilitaran nuevos turnos</i></p>
              @else
                <p class="fontColorHeader"><i>Sugerencia: fechas disponibles más cercanas </i></p>
                <ul>
                  <li class="fontColorHeader">{{$fechaLibreDisponible}}</li>
                  @if($fechaLibreDisponible1 != null)
                    <li class="fontColorHeader">{{$fechaLibreDisponible1}}</li>
                  @endif
                  @if($fechaLibreDisponible2 != null)
                    <li class="fontColorHeader">{{$fechaLibreDisponible2}}</li>
                  @endif
                </ul>
              @endif
              @if($medico->id == 6)
                <p class="letrasrojo"><i>A partir de agosto no se otorgaran más turnos.</i></p>
              @endif
              <br>
                 <div class="contenedor3">                           
                    <button hidden id="buttonContinuar" class="contenido3 rodri_button">Continuar</button>
                </div>                             
            </form>
                <div class="contenedor3">                           
                    <button disabled id="buttonContinuarDisabled" class="contenido3 rodri_button_disabled">Continuar</button>
                </div> 
          </div>        
      </div>
    </div>
</div>

<div id="snackbar"><p>La receta ha sido solicitada</p></div>

@include('modal.modal_msj_medico')
@include('modal.modal_recetas')
@include('modal.snackbar')
@include('modal.modal_coronavirus')
@include('modal.modal_coronavirus2')
@include('modal.modal_coronavirus3')
@include('modal.modal_mensaje')

<script type="text/javascript">  

$.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  var dias_deshabilitados = document.getElementById("dias_deshabilitados").value;
  var dias_habilitados = document.getElementById("dias_habilitados").value;
  var end_date = document.getElementById("end_date").value;
  $('.datepicker').datepicker({
    weekStart: 0,
    language: "es",
    startDate: '-0d',
    endDate: end_date,
    keyboardNavigation: false,
    forceParse: false,
    autoclose: true,
    daysOfWeekHighlighted: dias_habilitados, 
    daysOfWeekDisabled: dias_deshabilitados    
  }).attr('readonly','readonly');

  function validarNotNull(){
    var medico = document.getElementById("medico_id").value;    
    var fecha_seleccionada = document.getElementById("fecha_seleccionada").value;
    if(fecha_seleccionada != null) {
       document.getElementById("buttonContinuar").hidden = false;
       document.getElementById("buttonContinuarDisabled").hidden = true;
     } else{
       document.getElementById("buttonContinuar").hidden = true;
       document.getElementById("buttonContinuarDisabled").hidden = false;
     }
     // validarLucia(medico, fecha_seleccionada);
     if(medico == 6 && fecha_seleccionada != null){
        var newDateArray = fecha_seleccionada.split("/");
        var newDate = new Date(newDateArray[2]+"-"+newDateArray[1]+"-"+newDateArray[0]);
        var agostDate = new Date('2022-08-01');   

        if(newDate > agostDate){
          document.getElementById("buttonContinuar").hidden = true;
          document.getElementById("buttonContinuarDisabled").hidden = false;    
        } else {
          document.getElementById("buttonContinuar").hidden = false;
          document.getElementById("buttonContinuarDisabled").hidden = true;
        }
     }
  }

  function validarLucia(medico, fecha_seleccionada){
    if(medico == 5 && fecha_seleccionada != null){
        var newDateArray = fecha_seleccionada.split("/");
        var newDate = new Date(newDateArray[2]+"-"+newDateArray[1]+"-"+newDateArray[0]);
        var agostDate = new Date('2022-08-15');   

        if(newDate > agostDate){
          document.getElementById("buttonContinuar").hidden = true;
          document.getElementById("buttonContinuarDisabled").hidden = false; 
          document.getElementById("modalMensajeTitulo").innerHTML = "ATENCION";
          document.getElementById("modalMensajeTexto").innerHTML = "Lucía Diomedi se encuentra de Licencia y por el momento no esta tomando turnos. <br> Pueden sacar turno por esta misma via con Magali Lopez, Celeste Reina, Barbara Lopez o Sabrina Bronfen quienes quedaran a cargo de sus pacientes momentaneamente. <br> Saludos!";            
          $('#modalMensaje').modal();   
        } else {
          document.getElementById("buttonContinuar").hidden = false;
          document.getElementById("buttonContinuarDisabled").hidden = true;
        }
     }
  }

  function mostrarSnackbarReceta() {
    var x = document.getElementById("snackbar");
    x.className = "show";
    setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
  }

  function cargarDomicilioAccion(){
    var paciente_id = document.getElementById("modal_ingresar_domicilio_paciente_id").value;
    var domicilio = document.getElementById("modal_ingresar_domicilio_paciente_domicilio").value;
    $.ajax({
       type:'POST',
       dataType:'JSON',
       url:'/cargar_domicilio_paciente',
       data:{paciente_id:paciente_id, domicilio:domicilio ,_token: '{{csrf_token()}}'},
       success:function(data){      
          if(data.response == 1){
            document.getElementById("paciente_domicilio").value = data.paciente.domicilio;
            $("#modalRecetas").modal(); 
          }                                                                     
       } 
    });      
  }

  function modalRecetas(){
    var domicilio = document.getElementById("paciente_domicilio").value;
    if((domicilio == null) || (domicilio.localeCompare('')==0)){
      var paciente_id = document.getElementById("paciente_id").value;
      $('#modal_ingresar_domicilio_paciente_id').val(paciente_id);  
      $("#modalIngresarDomicilio").modal(); 
    } else { 
      $("#modalRecetas").modal(); 
    }
  }

  function enviarSolicitudReceta(){
    var paciente_id = document.getElementById("paciente_id").value;
    var medico_id = document.getElementById("medico_id").value;
    var consultorio_id = document.getElementById("consultorio_id").value;
    var motivo_receta = document.getElementById("motivo_receta").value;
    var retira_si = document.getElementById("retira_si").checked;
    var retira_no = document.getElementById("retira_no").checked;
    document.getElementById("motivo_receta").value = "";    
    var retira = 0;
    if(retira_si){
      retira = 1;
    }
    if(retira_no){
      retira = 2;
    }
    
    $.ajax({
       type:'POST',
       dataType:'JSON',
       url:'/solicitar_nueva_receta',
       data:{paciente:paciente_id, medico:medico_id, consultorio:consultorio_id, motivo:motivo_receta, retira:retira ,_token: '{{csrf_token()}}'},
       success:function(data){      
          if(data.response == 1){
            mostrarSnackbarReceta();
          }                                                                     
       } 
    });
  }

  function mostrarMensajeCeci(){
    var medico_id = document.getElementById("medico_id").value;
    var diferenciaDias = document.getElementById("diferencia_dias_id").value;
    if(medico_id == 11 && diferenciaDias < 0){
      var dias = diferenciaDias * -1;
      document.getElementById("modalMensajeTitulo").innerHTML = "Aviso";
      document.getElementById("modalMensajeTexto").innerHTML = "No hay turnos disponibles en este momento. En "+dias+" dias, se habilitaran nuevos turnos. Por favor, intente obtener un turno en "+dias+" dias. Muchas Gracias.";
      $("#modalMensaje").modal();
    }    
  }

  function mostrarMsjEspecial(){
    var medico_id = document.getElementById("medico_id").value;    
    if(medico_id == 11){
      document.getElementById("modalMensajeTitulo").innerHTML = "ATENCION";
      document.getElementById("modalMensajeTexto").innerHTML = "* La profesional NO atiende pacientes obstétricos.";            
      $('#modalMensaje').modal();      
    }

    if(medico_id == 12){      
      document.getElementById("modalMensajeTitulo").innerHTML = "Aviso";
      document.getElementById("modalMensajeTexto").innerHTML = "Antes de asistir a la consulta por favor consulte por la cobertura de su obra social. Muchas Gracias.";
      $("#modalMensaje").modal();
    }
  }


  window.onload=function() {    
    var consultorio_id = document.getElementById("consultorio_id").value;
    var medico_id = document.getElementById("medico_id").value;
    var especialidad_id = document.getElementById("especialidad_id").value;
    mostrarMensajeCeci();
    mostrarMsjEspecial();
    if(consultorio_id == 7 && especialidad_id == 1){
      if(medico_id == 1){
        $("#modalCoronavirus2").modal(); 
      }
      else{
        $("#modalMsjMedico").modal(); 
        document.getElementById("buttonContinuarDisabled").hidden = true;
      }
    }
    if(consultorio_id == 2)
      // $("#modalCoronavirus").modal(); 
    if(consultorio_id == 6)
      $("#modalCoronavirus3").modal(); 
    }

</script>

@endsection





