
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
        <p class="lead fontColorHeader" style="color: white;"> <b>Los turnos online serán para revisión de resultados o para consultas que no requieran examen fisico.</b></p>
        <p class="lead fontColorHeader"> La especialista se comunicará con usted a traves de un llamado el dia del turno.</p>
        
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

<input type="hidden" id="valor_consulta" value="{{$valorConsulta}}">
<input type="hidden" id="horario_seleccionado" value="">

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
                @if($medico->id == 13 && $tipoTurno == 1)
                  <input type="hidden" name="dias_deshabilitados" id="dias_deshabilitados" value="0,3,5,6" />
                  <input type="hidden" name="dias_habilitados" id="dias_habilitados" value="1,2,4" />
                @else
                  <input type="hidden" name="dias_deshabilitados" id="dias_deshabilitados" value="{{$dias_deshabilitados}}" />
                  <input type="hidden" name="dias_habilitados" id="dias_habilitados" value="{{$dias_habilitados}}" />
                @endif
                @if($medico->id == 1)
                  <input type="hidden" name="dias_deshabilitados" id="dias_deshabilitados" value="0,2,5,6" />
                  <input type="hidden" name="dias_habilitados" id="dias_habilitados" value="1,3,4" />
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
                  <input type="text" class="form-control datepicker editText" name="fecha_seleccionada" id="fecha_seleccionada" placeholder="Click aqui para seleccionar la fecha" required autocomplete="off" onchange="actualizarHorarios()">                                
              </div>
              <input hidden id="diferencia_dias_id" value="{{$diferenciaDias}}" />
              @if($diferenciaDias < 0)
                <?php $diferenciaDias = -1*$diferenciaDias; ?>
                <br>
                <p hidden class="letrasrojo"><i hidden>En {{$diferenciaDias}} dias se habilitaran nuevos turnos</i></p>
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
              @if($medico->id == 2 && $tipoTurno == 24)              
                <p><i>Para conocer el valor de la consulta segun su obra social haga <a style="color:red; cursor:pointer" onclick="showObraSocialDiferncial()">clic aqui</a></i></p>
              @endif
              <br>
                 <div hidden class="contenedor3">                           
                    <button hidden id="buttonContinuar" class="contenido3 rodri_button">Continuar</button>
                </div>                             
            </form>
                <div hidden class="contenedor3">                           
                    <button disabled id="buttonContinuarDisabled" class="contenido3 rodri_button_disabled">Continuar</button>
                </div> 
          </div>        
      </div>
    </div>
</div>

<div class="row">  
  <div id="seccionTurnosMedicos">
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
@include('modal.modals_confirm_fail_turnos')
@include('modal.modal_obra_social_diferencial')

<script type="text/javascript">  

$.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  var medico_id = document.getElementById("medico_id").value;

  var dias_deshabilitados = document.getElementById("dias_deshabilitados").value;
  var dias_habilitados = document.getElementById("dias_habilitados").value;
  if(medico_id == 29) { // eli
    dias_deshabilitados = "0,1,3,5,6";
    dias_habilitados = "2,4";
  }
  if(medico_id == 30) { // anto
    dias_deshabilitados = "0,2,4,5,6";
    dias_habilitados = "1,3";
  }

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

  function borrarSeccion(seccion){
    var myNode = document.getElementById(seccion);
    while (myNode.firstChild) {
           myNode.removeChild(myNode.firstChild);
    }
  }

  function validarNotNull() {
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
     if(medico == 6 && fecha_seleccionada != null) {
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
     // para volver todo para atras debo comentar este metodo
     //actualizarHorarios();
  }

  function showObraSocialDiferncial() {
    var medico_id = document.getElementById("medico_id").value;    
    mostrarArancelDiferencial()
  }

  function actualizarHorarios() {
    var medico = document.getElementById("medico_id").value;    
    var fecha_seleccionada = document.getElementById("fecha_seleccionada").value;
    var tipoTurno = document.getElementById("tipoTurno").value;
    var paciente_id = document.getElementById("paciente_id").value;
    var primer_control = document.getElementById("primer_control").value;
    var dias_deshabilitados = document.getElementById("dias_deshabilitados").value;
    var esVideollamada = 0;//document.getElementById("fecha_seleccionada").value;
    var consultorio = document.getElementById("consultorio_id").value;  
      
    let [dia, mes, anio] = fecha_seleccionada.split("/");    
    let fechaFormateada = `${anio}-${mes}-${dia}`;

    if(medico == 29 && fechaFormateada >= '2025-10-01') {      
      document.getElementById("modalMensajeTitulo").innerHTML = "AVISO";
      document.getElementById("modalMensajeTexto").innerHTML = "¡Hola! Me encuentro de licencia por maternidad hasta nuevo aviso. Durante este tiempo -para turnos programados- me van a estar ayudando dos colegas, especialistas en Pediatría, Florencia García Elliot y Antonela Luini. Los turnos podrán obtenerlos a través de la página www.turnosonlinebb.com, o bien comunicandose al teléfono de la secretaria: 2915134858. Nos vemos al regreso! Saludos!";            
        
        $('#modalMensaje').modal();
      return;      
    }
    
    $.ajax({
       type:'POST',
       dataType:'JSON',
       url:'/get_horarios_medico',
       data:{paciente_id:paciente_id, medico:medico, fecha_seleccionada:fecha_seleccionada, tipoTurno:tipoTurno, primer_control:primer_control, dias_deshabilitados:dias_deshabilitados, esVideollamada:esVideollamada, consultorio:consultorio ,_token: '{{csrf_token()}}'},
       success:function(data){        
          //document.getElementById("seccionTurnosMedicos").scrollIntoView({ behavior: 'smooth' });
          navigateToSeccion('seccionTurnosMedicos', this);
          borrarSeccion("seccionTurnosMedicos");
          var seccion = document.getElementById("seccionTurnosMedicos");
          var h1 = document.createElement("h5");
          var br = document.createElement("br");
          if(data.hayTurnoLibre == 0) {            
            // mostrar no hay turnos disponibles
            h1.setAttribute("class", "fontColorHeader fontMedicoNombre");
            h1.innerHTML = "No hay turnos disponibles para la fecha seleccionada, por favor seleccione otra fecha.";                        
            seccion.appendChild(h1);
            seccion.appendChild(br);
          } else {
            if(data.hayTurnoLibre == 1) {            
              h1.innerHTML = "Seleccione un horario";                        
              h1.setAttribute("class", "fontColorHeader fontMedicoNombre");
              seccion.appendChild(h1);
              seccion.appendChild(br);

              for(let i = 0; i < data.turnos.length; i++) {                                     
                var button1 = document.createElement("button");
                button1.setAttribute("class", "btn btn-primary-outline");                
                if(primer_control == 1){
                  button1.onclick = function() {
                    console.log(data.turnos[i].horario);
                    console.log(data.turnos[i+1].horario);
                    modalConfirmarPrimerControl(data.turnos[i].horario, data.turnos[i+1].horario);
                  };
                } else {
                  button1.onclick = function() {                                  
                    console.log("modalConfirmar(data.turnos[i].horario);");
                    modalConfirmar(data.turnos[i].horario);
                  };  
                }                
                var h1 = document.createElement("h1");
                h1.setAttribute("class", "turno_text_size");
                h1.innerHTML = data.turnos[i].horario;
                var div = document.createElement("div");
                if(data.turnos[i].libre == 1){
                  div.setAttribute("class", "circulo img_turno");

                } else {
                  div.setAttribute("class", "circulo circulo_ocupado img_turno");
                  button1.disabled = true;
                }
                
                div.appendChild(h1);
                button1.appendChild(div);

                seccion.appendChild(button1);
              }
            } else {
              if(data.hayTurnoLibre == 2) {
                h1.innerHTML = "Feriado. Por favor seleccione otra fecha. "; 
                h1.setAttribute("class", "fontColorHeader fontMedicoNombre");                       
                seccion.appendChild(h1);
                seccion.appendChild(br);
              } else {
                // turnos libres == 3
                h1.innerHTML = "Ya tiene un turno registrado para este mes, para registrar un nuevo turno por favor comunicarse con el consultorio (Tel: "+data.consultorio.telefono+").<br> Muchas Gracias!";                        
                h1.setAttribute("class", "fontColorHeader fontMedicoNombre");
                seccion.appendChild(h1);
                seccion.appendChild(br);  
              }
          } 
        }                                                                              
       } 
    });
  }

  function navigateToSeccion(seccion, object){
    $('html, body').animate({
    scrollTop: $("#"+seccion).offset().top - 100
    }, 2000);                
  }

  function formatearNumero(numero) {
        return numero.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
  }

  function modalConfirmarPrimerControl(horario1,horario2){
    var paciente_id = document.getElementById("paciente_id").value;         
    var medico_id = document.getElementById("medico_id").value;
    var consultorio = document.getElementById("consultorio_id").value;
    //var dia = document.getElementById("dia").value;   
    var fechaTurno = document.getElementById("fecha_seleccionada").value;
    var primerControl = document.getElementById("primer_control").value;        
    var esVideollamada = document.getElementById("esVideollamada").value;  
    var tipoTurno = document.getElementById("tipoTurno").value;      
    //alert("modalConfirmarPrimerControl");

     $.ajax({
         type:'POST',
         dataType:'JSON',
         url:'/registrar_turno_primer_control',
         data:{paciente_id :paciente_id, medico_id:medico_id, consultorio:consultorio, dia:fechaTurno, fechaTurno:fechaTurno, horario1:horario1,horario2:horario2, primerControl: primerControl, tipoTurno:tipoTurno, esVideollamada:esVideollamada, _token: '{{csrf_token()}}'},
         success:function(data){              
           if(data.turnoRegistrado == 0){                      
              $('#modalTurnoFail').modal();                  
          }
          if(data.turnoRegistrado == 1){        
                $('#horario').val(data.horario);
                $('#turno_id').val(data.turno_id);
                var paciente = data.paciente.apellido +", "+ data.paciente.nombre;
                var fechaAux = data.datosTurno.fechaTurno.split("-");
                var fecha = fechaAux[2]+"/"+fechaAux[1]+"/"+fechaAux[0];
                var medicoSexo = "la Dra. ";
                var extraMensaje = "";
                
                if(data.medico.sexo.localeCompare('M') == 0) {                  
                    medicoSexo = "el Dr. ";
                  
                }
                var valorConsulta = document.getElementById("valor_consulta").value;
                var medico = data.medico.apellido +", "+data.medico.nombre;
                var horario = data.datosTurno.horario;

                var modalConfirmar_texto = "El turno será registrado a nombre de <b>"+paciente+"</b> el dia <b>"+fecha+"</b> a las <b> "+horario+" hs, </b> con "+medicoSexo+" <b> "+medico+". </b>";

                if(valorConsulta != 0) {
                  var valorNumerico = parseFloat(valorConsulta) || 0;  
                  modalConfirmar_texto += "<br><br><b>El valor de la consulta es de $"+formatearNumero(valorNumerico)+"</b><br>";
                }                                
                modalConfirmar_texto += extraMensaje;

                document.getElementById("modalConfirmar_texto").innerHTML = modalConfirmar_texto;
                
                document.getElementById("modalTurnoOk_fecha_seleccionada").innerHTML = "<b>Fecha: </b>" + fecha;
                document.getElementById("modalTurnoOk_horario").innerHTML = "<b>Horario: </b>" + horario;
                document.getElementById("modalTurnoOk_medico").innerHTML = "<b>Especialista: </b>" + medico;
                var consultorio = data.consultorio.direccion;
                if(data.medico.id == 2) {                  
                  consultorio = "Equipo Dubie ";
                }              
                document.getElementById("modalTurnoOk_direccion").innerHTML = "<b>Dirección: </b>" + consultorio;            
                document.getElementById("seccion_tipo_turnos").hidden = true;
                if(data.medico.id == 13){
                  //document.getElementById("modalTurnoOk_tipo_turno").innerHTML = "<b>Tipo Turno: </b>" + obtenerTextoSeleccionado();            
                  document.getElementById("seccion_tipo_turnos").hidden = false;
                }

                $('#modalConfirmar').modal();
          }
           if(data.turnoRegistrado == 2){                      
              $('#modalTurnoFail2').modal();                  
          }            
        }
      });
  }

  function modalConfirmar(horario) {
    var paciente_id = document.getElementById("paciente_id").value;         
    var medico_id = document.getElementById("medico_id").value;
    var consultorio = document.getElementById("consultorio_id").value;
    //var dia = document.getElementById("dia").value;   
    var fechaTurno = document.getElementById("fecha_seleccionada").value;
    var primerControl = document.getElementById("primer_control").value;        
    var esVideollamada = document.getElementById("esVideollamada").value;  
    var tipoTurno = document.getElementById("tipoTurno").value;  

    document.getElementById('horario_seleccionado').value = horario;

    //alert("primerControl");
     $.ajax({
         type:'POST',
         dataType:'JSON',
         url:'/registrar_turno',
         data:{paciente_id :paciente_id, medico_id:medico_id, consultorio:consultorio, fechaTurno:fechaTurno, horario:horario,primerControl:primerControl, esVideollamada:esVideollamada, tipoTurno:tipoTurno, _token: '{{csrf_token()}}'},
         success:function(data){                 
           if(data.turnoRegistrado == 1){          
                $('#horario').val(data.horario);
                $('#turno_id').val(data.turno_id);
                var paciente = data.paciente.apellido +", "+ data.paciente.nombre;
                var fechaAux = data.datosTurno.fechaTurno.split("-");
                var fecha = fechaAux[2]+"/"+fechaAux[1]+"/"+fechaAux[0];
                var medicoSexo = "la Dra. ";
                var extraMensaje = "";
                if(data.medico.id == 31) {
                    medicoSexo = "la Lic."
                    extraMensaje = "Traer los utensilios y alimentos que consume.";
                } else {                  
                  if(data.medico.sexo.localeCompare('M') == 0) {                  
                    medicoSexo = "el Dr. ";
                  }
                }
                var valorConsulta = document.getElementById("valor_consulta").value;
                var medico = data.medico.apellido +", "+data.medico.nombre;
                var horario = data.datosTurno.horario;

                var modalConfirmar_texto = "El turno será registrado a nombre de <b>"+paciente+"</b> el dia <b>"+fecha+"</b> a las <b> "+horario+" hs, </b> con "+medicoSexo+" <b> "+medico+". </b>";

                if(valorConsulta != 0) {
                  var valorNumerico = parseFloat(valorConsulta) || 0;  
                  modalConfirmar_texto += "<br><br><b>El valor de la consulta es de $"+formatearNumero(valorNumerico)+"</b><br>";
                }                                
                modalConfirmar_texto += extraMensaje;

                document.getElementById("modalConfirmar_texto").innerHTML = modalConfirmar_texto;
                
                document.getElementById("modalTurnoOk_fecha_seleccionada").innerHTML = "<b>Fecha: </b>" + fecha;
                document.getElementById("modalTurnoOk_horario").innerHTML = "<b>Horario: </b>" + horario;
                document.getElementById("modalTurnoOk_medico").innerHTML = "<b>Especialista: </b>" + medico;
                
                document.getElementById("seccion_tipo_turnos").hidden = true;
                if(data.medico.id == 13) {
                  document.getElementById("seccion_tipo_turnos").hidden = false;
                  document.getElementById("modalTurnoOk_tipo_turno").innerHTML = "<b>Tipo Turno: </b>" + obtenerTextoSeleccionado();            
                }

                var consultorio = data.consultorio.direccion;
                if(data.medico.id == 1){
                  if(data.datosTurno.fechaTurno < '2025-02-01') {
                    consultorio = "Unimed. 12 de Octubre 53, piso 9";
                  } else {
                    consultorio = "Viamonte 276";
                  }
                }
                if(data.medico.id == 36){
                  if(data.datosTurno.dia == 1) {
                    consultorio = "Fournier 927 - Punta Alta";
                  } else {
                    consultorio = "Urquiza 562 - Punta Alta";
                  }
                }
                if(data.medico.id == 12){
                  if(data.datosTurno.dia == 5) {
                    consultorio = "Garibaldi 44";
                  } else {
                    consultorio = "Blandengues 505";
                  }
                }
                if(data.medico.id == 24){
                  if(data.datosTurno.dia == 1 || data.datosTurno.dia == 5) {
                    consultorio = "Luiggi 463";
                  }
                }
                if(data.medico.id == 25) {                   
                  consultorio = "Blandengues 505";                  
                }
                if(tipoTurno == 24) {                   
                  consultorio = "Florida 700";                  
                }
                document.getElementById("modalTurnoOk_direccion").innerHTML = "<b>Dirección: </b>" + consultorio;            

                $('#modalConfirmar').modal();
            }  
            if(data.turnoRegistrado == 0){                      
              $('#modalTurnoFail').modal();                  
            }
            if(data.turnoRegistrado == 2){                      
              $('#modalTurnoFail2').modal();                  
            } 
            if(data.turnoRegistrado == 3){                      
              $('#modalTurnoFail3').modal();                  
            }
            if(data.turnoRegistrado == 4){                      
              $('#modalTurnoFail4').modal();                  
            }
            if(data.turnoRegistrado == 5){                      
              $('#modalTurnoFail5').modal();                  
            }                 
          }
      });
  }

  function actualizarTipoTurno(turno_id, tipoTurno) {
     $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/actualizar_tipo_turno',
           data:{turno_id:turno_id, tipoTurno:tipoTurno, _token: '{{csrf_token()}}'},
           success:function(data){       
           //no necesito hacer nada. me quedo en la misma pantalla       
           }
        });  
  }

  function confirmarTurno() {
    var esVideollamada = document.getElementById("esVideollamada").value;
    var especialidad_id = document.getElementById("especialidad_id").value;  
    var medico_id = document.getElementById("medico_id").value;
    var turno_id = document.getElementById("turno_id").value;    

    if(esVideollamada == 0 || especialidad_id == 2) {        
      var tipoTurno = obtenerRadioSeleccionado();
      if(medico_id == 13) {
        document.getElementById("modalTurnoOk_tipo_turno").innerHTML = "<b>Tipo Turno: </b>" + obtenerTextoSeleccionado();
        actualizarTipoTurno(turno_id, tipoTurno);
      }        
      $('#modalTurnoOk').modal();
    } else {
    //  var turno_id = document.getElementById("turno_id").value;    
      var obra_social_foto = document.getElementById("obra_social_foto").value;      
        $('#turno_id_videollamada').val(turno_id);    
        $('#horarioTurnoRegistradoVideollamada').val(horario);             
        $('#modalTurnoOkVideollamada').modal();    
    }
  }

  function obtenerRadioSeleccionado() {
    const radios = document.getElementsByName('opcion_miRadioGroup');
    
    for (let i = 0; i < radios.length; i++) {
        if (radios[i].checked) {
            return radios[i].value;
        }
    }
    
    return null; // Si ningún radio está seleccionado
  }

  function obtenerTextoSeleccionado() {
    var radioSeleccionado = obtenerRadioSeleccionado();
    
    if(radioSeleccionado == 1)
      return "Consulta endocrina";
    if(radioSeleccionado == 23)
      return "Ecografía";
    if(radioSeleccionado == 24)
      return "Consula endocrina + Ecografía";

  }


  function cancelarTurno() {
    var turno_id = document.getElementById("turno_id").value;  
    var esVideollamada = document.getElementById("esVideollamada").value;  
    
    $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/cancelar_turno',
           data:{turno_id:turno_id, esVideollamada:esVideollamada, _token: '{{csrf_token()}}'},
           success:function(data){       
           //no necesito hacer nada. me quedo en la misma pantalla       
           }
        });  
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
    if(medico_id == 11 && diferenciaDias < 0) {
      var dias = diferenciaDias * -1;
      document.getElementById("modalMensajeTitulo").innerHTML = "Aviso";
      document.getElementById("modalMensajeTexto").innerHTML = "No hay turnos disponibles en este momento. En "+dias+" dias, se habilitaran nuevos turnos. Por favor, intente obtener un turno en "+dias+" dias. Muchas Gracias.";
      $("#modalMensaje").modal();
    }    
  }

  function mostrarMsjEspecial(){
    var medico_id = document.getElementById("medico_id").value;    
    var tipoTurno = document.getElementById("tipoTurno").value;    
    
    if(medico_id == 17) { // sofia vanzini
      //document.getElementById("modalMensajeTitulo").innerHTML = "ATENCION";
      //document.getElementById("modalMensajeTexto").innerHTML = "En el mes de enero, atenderemos excepcionalmente el miércoles 15. Este será el único miércoles disponible durante el mes. Los días martes continuarán con su atención habitual. <br><br>¡Gracias por su comprensión!";            
      //$('#modalMensaje').modal();      
    }

    if(medico_id == 24) { // pablo prado
      //document.getElementById("modalMensajeTitulo").innerHTML = "ATENCION";
      //document.getElementById("modalMensajeTexto").innerHTML = "A partir del mes de marzo los lunes estaré atendiendo en Luiggi 463 y los miércoles en Blandengues 505. Muchas Gracias.";            
      //$('#modalMensaje').modal();      
    }

    if(medico_id == 11) {      
      if(tipoTurno == 22) {
        document.getElementById("modalMensajeTitulo").innerHTML = "AVISO";
        document.getElementById("modalMensajeTexto").innerHTML = "La especialista se comunicará con usted a traves de un llamado el dia del turno";            
        $('#modalMensaje').modal();      
      } else {
        document.getElementById("modalMensajeTitulo").innerHTML = "ATENCION";
        document.getElementById("modalMensajeTexto").innerHTML = "Los turnos correspondientes a cada mes seran habilitados mas cerncanos a la fecha. Muchas Gracias!";            
        $('#modalMensaje').modal();      
      } 
    }

    if(medico_id == 12) {      
      document.getElementById("modalMensajeTitulo").innerHTML = "Aviso";
      document.getElementById("modalMensajeTexto").innerHTML = "Antes de asistir a la consulta por favor consulte por la cobertura de su obra social. Muchas Gracias.";
      $("#modalMensaje").modal();
    }

    if(medico_id == 35) {      
      document.getElementById("modalMensajeTitulo").innerHTML = "Aviso";
      document.getElementById("modalMensajeTexto").innerHTML = "El especialista no trabaja con las obras sociales, solo atiende de manera PARTICULAR. Muchas Gracias.";
      $("#modalMensaje").modal();
    }

    if(medico_id == 15) {      
      document.getElementById("modalMensajeTitulo").innerHTML = "Aviso";
      document.getElementById("modalMensajeTexto").innerHTML = "El modo de pago para las consultas es unicamente en EFECTIVO. Muchas Gracias.";
      $("#modalMensaje").modal();
    }
    
    if(medico_id == 3) {      
      document.getElementById("modalMensajeTitulo").innerHTML = "Aviso";
      document.getElementById("modalMensajeTexto").innerHTML = "A partir de Enero 2025 el profesional NO va a trabajar mas con la obra social IOSFA. Muchas Gracias!";
      $("#modalMensaje").modal();
    }
    if(medico_id == 18) {      
      document.getElementById("modalMensajeTitulo").innerHTML = "Aviso";
      document.getElementById("modalMensajeTexto").innerHTML =     
      "Por excepción las atención del viernes 2 de mayo se atenderá durante la mañana y no a la tarde! Para solicitar turno comunicarse al telefono 4814538 o <a href='https://api.whatsapp.com/send?phone=2915107335' target='_blank'>2915107335</a> (Lunes, martes y jueves de 15 a 19, miércoles de 9:30 a 15)"  +
      "Saludos!!";
      $("#modalMensaje").modal();
    }    

    if(medico_id == 2 && tipoTurno == 24) {
      showObraSocialDiferncial();
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

  function agregarACalendar(turnoData) {
    // Función para crear fecha correctamente
    function crearFecha(fechaDDMMAAAA, horaHHMM) {
      const [dia, mes, anio] = fechaDDMMAAAA.split('/').map(Number);
      const [horas, minutos] = horaHHMM.split(':').map(Number);
      return new Date(anio, mes - 1, dia, horas, minutos);
    }

    // Crear fechas
    const startDate = crearFecha(turnoData.fecha, turnoData.hora);
    const endDate = new Date(startDate.getTime() + (60 * 60 * 1000)); // +1 hora

    // Función para formatear para Google Calendar
    function formatarParaGoogle(date) {
      const pad = num => num.toString().padStart(2, '0');
      return [
        date.getUTCFullYear(),
        pad(date.getUTCMonth() + 1),
        pad(date.getUTCDate()),
        'T',
        pad(date.getUTCHours()),
        pad(date.getUTCMinutes()),
        pad(date.getUTCSeconds()),
        'Z'
      ].join('');
    }

    // Crear URL para Google Calendar
    const url = new URL('https://www.google.com/calendar/render');
    url.searchParams.append('action', 'TEMPLATE');
    url.searchParams.append('text', `Turno: ${turnoData.servicio}`);
    url.searchParams.append('dates', `${formatarParaGoogle(startDate)}/${formatarParaGoogle(endDate)}`);
    url.searchParams.append('details', turnoData.detalles || '');
    url.searchParams.append('location', turnoData.ubicacion || '');

    // Abrir en nueva pestaña
    window.open(url.toString(), '_blank');
  }

  function irHome(opcion) {
    if(opcion == 1) {
      var turno_id = document.getElementById("turno_id").value;    
      $.ajax({
         type:'POST',
         dataType:'JSON',
         url:'/enviar_mail_confirmacion',
         data:{turno_id:turno_id ,_token: '{{csrf_token()}}'},
         success:function(data){                  
            location.href ="/homes";                    
         } 
      });    
      return;
    }

    /*  const turnoData = {
      fecha: document.getElementById('fecha_seleccionada').value,
      hora: document.getElementById('horario_seleccionado').value,
      servicio: "servicio", //document.getElementById('servicio').value,
      detalles: "detalles",//document.getElementById('detalles').value,
      ubicacion: "ubicacion"//document.getElementById('ubicacion').value || 'Tu ubicación'
    };
    
    agregarACalendar(turnoData);*/
      
    // location.href ="/homes";
  }

</script>

@endsection





