
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
    
    <style>
      /* Estilos para días disponibles (verde) */
      .datepicker table tr td.dia-disponible {
        background-color: #28a745 !important;
        background-image: none !important;
        color: #fff !important;
        border-color: #28a745 !important;
        font-weight: bold;
      }
      
      .datepicker table tr td.dia-disponible:hover {
        background-color: #218838 !important;
        color: #fff !important;
      }
      
      /* Día disponible y seleccionado (más oscuro) */
      .datepicker table tr td.dia-disponible.active,
      .datepicker table tr td.dia-disponible.active:hover,
      .datepicker table tr td.dia-disponible.selected,
      .datepicker table tr td.dia-disponible.selected:hover,
      .datepicker table tr td.active.dia-disponible,
      .datepicker table tr td.selected.dia-disponible {
        background-color: #155724 !important;
        background-image: none !important;
        color: #fff !important;
        border-color: #155724 !important;
        font-weight: bold;
        box-shadow: inset 0 0 5px rgba(0,0,0,0.3) !important;
      }
      
      /* Estilos para días sin turnos disponibles (rojo) */
      .datepicker table tr td.dia-sin-turnos {
        background-color: #dc3545 !important;
        background-image: none !important;
        color: #fff !important;
        border-color: #dc3545 !important;
        font-weight: bold;
      }
      
      .datepicker table tr td.dia-sin-turnos:hover {
        background-color: #c82333 !important;
        color: #fff !important;
      }
      
      .datepicker table tr td.dia-sin-turnos.active,
      .datepicker table tr td.dia-sin-turnos.active:hover,
      .datepicker table tr td.dia-sin-turnos.selected,
      .datepicker table tr td.dia-sin-turnos.selected:hover {
        background-color: #bd2130 !important;
        background-image: none !important;
        color: #fff !important;
        border-color: #bd2130 !important;
        font-weight: bold;
        box-shadow: inset 0 0 5px rgba(0,0,0,0.3) !important;
      }
      
      /* Ocultar el datepicker inline */
      /* Ocultar el input pero mantenerlo funcional para el datepicker */
      #fecha_seleccionada {
        position: absolute;
        opacity: 0;
        pointer-events: none;
        height: 0;
        width: 0;
        border: none;
        padding: 0;
        margin: 0;
      }
      
      /* Estilos para el contenedor del datepicker inline */
      #datepicker-container {
        width: 100%;
      }
      
      #datepicker-container .datepicker {
        width: 100%;
        border: 1px solid rgba(0, 0, 0, 0.125);
        border-radius: 0.25rem;
        padding: 0.75rem;
        background-color: #fff;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
      }
      
      #datepicker-container .datepicker table {
        margin: 0 auto;
      }            
    </style>
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
              <input type="hidden" id="diferencia_dias_id" value="{{$diferenciaDias}}" />
              <div class="input-group">
                  <input type="text" class="form-control editText" name="fecha_seleccionada" id="fecha_seleccionada" placeholder="Click aqui para seleccionar la fecha" required autocomplete="off" onchange="actualizarHorarios()">                                
              </div>
              <div class="row">
                <div class="col-md-7">
                  <div id="datepicker-container" style="display: inline-block;"></div>
                </div>
                <div class="col-md-5">
                  @if($diferenciaDias < 0)
                    <?php $diferenciaDias = -1*$diferenciaDias; ?>
                    <p hidden class="letrasrojo"><i hidden>En {{$diferenciaDias}} dias se habilitaran nuevos turnos</i></p>
                  @else
                    <p class="fontColorHeader"><i><strong>Sugerencia: fechas disponibles más cercanas</strong></i></p>
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
                </div>
              </div>
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
  var fechasDisponibles = [];
  var fechasSinTurnos = [];
  
  // Cargar fechas disponibles desde el endpoint
  function cargarFechasDisponibles() {
    var medico_id = document.getElementById("medico_id").value;
    var tipoTurno = document.getElementById("tipoTurno").value;
    var esVideollamada = document.getElementById("esVideollamada").value;
    var consultorio = document.getElementById("consultorio_id").value;
    var primer_control = document.getElementById("primer_control").value;
    
    // Calcular fecha hasta (end_date)
    var fechaHasta = end_date;
    var fechaDesde = new Date();
    
    // Convertir end_date si viene como '+180d'
    if (typeof fechaHasta === 'string' && fechaHasta.startsWith('+')) {
      var dias = parseInt(fechaHasta.replace(/\D/g, ''));
      fechaHasta = new Date();
      fechaHasta.setDate(fechaHasta.getDate() + dias);
      fechaHasta = fechaHasta.toISOString().split('T')[0];
    } else if (typeof fechaHasta === 'string' && fechaHasta.includes('/')) {
      // Ya viene en formato DD/MM/YYYY, convertir a YYYY-MM-DD
      var fechaArray = fechaHasta.split('/');
      fechaHasta = fechaArray[2] + '-' + fechaArray[1] + '-' + fechaArray[0];
    }
    
    var fechaDesdeStr = fechaDesde.toISOString().split('T')[0];
    
    // Convertir formato de fecha para el endpoint (DD/MM/YYYY)
    var fechaDesdeFormato = fechaDesdeStr.split('-').reverse().join('/');
    var fechaHastaFormato = fechaHasta.split('-').reverse().join('/');
    
    $.ajax({
      type: 'POST',
      dataType: 'JSON',
      url: '/get_fechas_disponibles',
      data: {
        medico: medico_id,
        tipoTurno: tipoTurno,
        esVideollamada: esVideollamada,
        consultorio: consultorio,
        primer_control: primer_control,
        fecha_desde: fechaDesdeFormato,
        fecha_hasta: fechaHastaFormato,
        _token: '{{csrf_token()}}'
      },
      success: function(data) {
        fechasDisponibles = data.fechas || [];
        fechasSinTurnos = data.fechasSinTurnos || [];
        
        // Actualizar ambos datepickers para que muestren los días en verde y rojo
        setTimeout(function() {
          if ($('#datepicker-container').data('datepicker')) {
            $('#datepicker-container').datepicker('update');
          }
          if ($('#fecha_seleccionada').data('datepicker')) {
            $('#fecha_seleccionada').datepicker('update');
          }
        }, 100);
        
        // Seleccionar automáticamente el primer día disponible
        if (fechasDisponibles.length > 0) {
          var primeraFecha = fechasDisponibles[0]; // Formato: DD/MM/YYYY
          var fechaArray = primeraFecha.split('/');
          var fechaDate = new Date(fechaArray[2], fechaArray[1] - 1, fechaArray[0]);
          
          // Establecer el valor en el input y seleccionar en ambos datepickers
          setTimeout(function() {
            // Establecer el valor en el input primero
            $('#fecha_seleccionada').val(primeraFecha);
            
            // Seleccionar en ambos datepickers
            if ($('#datepicker-container').data('datepicker')) {
              $('#datepicker-container').datepicker('setDate', fechaDate);
            }
            if ($('#fecha_seleccionada').data('datepicker')) {
              $('#fecha_seleccionada').datepicker('setDate', fechaDate);
            }
            
            // Actualizar ambos datepickers para que se muestren los estilos
            setTimeout(function() {
              $('#datepicker-container').datepicker('update');
              $('#fecha_seleccionada').datepicker('update');
              
              // Usar la función manejarSeleccionFecha para actualizar horarios
              setTimeout(function() {
                if ($('#fecha_seleccionada').val()) {
                  actualizarHorarios();
                }
              }, 300);
            }, 200);
          }, 400);
        }
      },
      error: function(xhr, status, error) {
        console.error('Error al cargar fechas disponibles:', error);
      }
    });
  }

  // Función para inicializar los datepickers
  function inicializarDatepickers() {
    // Inicializar datepicker inline en el contenedor (siempre visible)
    $('#datepicker-container').datepicker({
      weekStart: 0,
      language: "es",
      startDate: '-0d',
      endDate: end_date,
      keyboardNavigation: false,
      forceParse: false,
      autoclose: false,
      daysOfWeekHighlighted: dias_habilitados, 
      daysOfWeekDisabled: dias_deshabilitados,
      beforeShowDay: function(date) {
        // Formatear fecha como DD/MM/YYYY
        var fechaStr = ('0' + date.getDate()).slice(-2) + '/' + ('0' + (date.getMonth() + 1)).slice(-2) + '/' + date.getFullYear();
        
        // Verificar si la fecha está en el array de fechas disponibles
        var disponible = fechasDisponibles.indexOf(fechaStr) !== -1;
        
        // Verificar si la fecha está en el array de fechas sin turnos
        var sinTurnos = fechasSinTurnos.indexOf(fechaStr) !== -1;
        
        var result = {
          enabled: true
        };
        
        if (disponible) {
          result.classes = 'dia-disponible';
        } else if (sinTurnos) {
          result.classes = 'dia-sin-turnos';
        }
        
        return result;
      }
    });
    
    // Inicializar datepicker en el input (oculto, para compatibilidad)
    $('#fecha_seleccionada').datepicker({
      weekStart: 0,
      language: "es",
      startDate: '-0d',
      endDate: end_date,
      keyboardNavigation: false,
      forceParse: false,
      autoclose: false,
      daysOfWeekHighlighted: dias_habilitados, 
      daysOfWeekDisabled: dias_deshabilitados,
      beforeShowDay: function(date) {
        var fechaStr = ('0' + date.getDate()).slice(-2) + '/' + ('0' + (date.getMonth() + 1)).slice(-2) + '/' + date.getFullYear();
        var disponible = fechasDisponibles.indexOf(fechaStr) !== -1;
        var sinTurnos = fechasSinTurnos.indexOf(fechaStr) !== -1;
        var result = { enabled: true };
        if (disponible) {
          result.classes = 'dia-disponible';
        } else if (sinTurnos) {
          result.classes = 'dia-sin-turnos';
        }
        return result;
      }
    });
    
    // Función para manejar la selección de fecha
    function manejarSeleccionFecha(fechaDate) {
      if (!fechaDate) return;
      
      var fecha = ('0' + fechaDate.getDate()).slice(-2) + '/' + ('0' + (fechaDate.getMonth() + 1)).slice(-2) + '/' + fechaDate.getFullYear();
      
      // Establecer el valor en el input
      $('#fecha_seleccionada').val(fecha);
      
      // Asegurar que ambos datepickers tengan la fecha seleccionada
      if ($('#fecha_seleccionada').data('datepicker')) {
        $('#fecha_seleccionada').datepicker('setDate', fechaDate);
      }
      
      // Actualizar horarios después de un pequeño delay
      setTimeout(function() {
        var fechaActual = $('#fecha_seleccionada').val();
        if (fechaActual) {
          actualizarHorarios();
        }
      }, 200);
    }
    
    // Cuando se seleccione una fecha en el datepicker inline
    $('#datepicker-container').on('changeDate', function(e) {
      if (e && e.date) {
        manejarSeleccionFecha(e.date);
      }
    });
    
    // También capturar cambios en el input
    $('#fecha_seleccionada').on('changeDate', function(e) {
      if (e && e.date) {
        manejarSeleccionFecha(e.date);
      }
    });
    
    // Fallback: capturar cambios en el valor del input
    $('#fecha_seleccionada').on('change', function() {
      var valor = $(this).val();
      if (valor && valor.length > 0) {
        setTimeout(function() {
          actualizarHorarios();
        }, 200);
      }
    });
    
    // Capturar clicks directos en los días del datepicker (fallback adicional)
    $(document).on('click', '#datepicker-container .datepicker-days tbody td:not(.old):not(.new):not(.disabled)', function() {
      setTimeout(function() {
        var selectedDate = $('#datepicker-container').datepicker('getDate');
        if (selectedDate) {
          manejarSeleccionFecha(selectedDate);
        }
      }, 100);
    });
  }
  
  // Inicializar datepickers primero (aunque aún no tengan las fechas)
  inicializarDatepickers();
  
  // Luego cargar fechas disponibles - esto actualizará los datepickers automáticamente
  setTimeout(function() {
    cargarFechasDisponibles();
  }, 200);

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

    /*if(medico == 29 && fechaFormateada >= '2025-10-01') {      
      document.getElementById("modalMensajeTitulo").innerHTML = "AVISO";
      document.getElementById("modalMensajeTexto").innerHTML = "¡Hola! Me encuentro de licencia por maternidad hasta nuevo aviso. Durante este tiempo -para turnos programados- me van a estar ayudando dos colegas, especialistas en Pediatría, Florencia García Elliot y Antonela Luini. Los turnos podrán obtenerlos a través de la página www.turnosonlinebb.com, o bien comunicandose al teléfono de la secretaria: 2915134858. Nos vemos al regreso! Saludos!";            
        
        $('#modalMensaje').modal();
      return;      
    } */
    
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

                // Si el evento ya se agregó automáticamente a Google Calendar, no hacer nada más
                if(data.calendar_event_added) {
                    console.log('✅ Evento agregado automáticamente a Google Calendar');
                } else if(data.calendar_reminder_url) {
                    // Si no tiene OAuth, abrir Google Calendar directamente con el evento pre-cargado
                    // Abrir en nueva ventana/pestaña para que el usuario pueda guardar el evento
                    window.open(data.calendar_reminder_url, '_blank');
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

                // Si el evento ya se agregó automáticamente a Google Calendar, no hacer nada más
                if(data.calendar_event_added) {
                    console.log('✅ Evento agregado automáticamente a Google Calendar');
                } else if(data.calendar_reminder_url) {
                    // Si no tiene OAuth, abrir Google Calendar directamente con el evento pre-cargado
                    // Abrir en nueva ventana/pestaña para que el usuario pueda guardar el evento
                    window.open(data.calendar_reminder_url, '_blank');
                }

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
    if(medico_id == 1) {      
      document.getElementById("modalMensajeTitulo").innerHTML = "Aviso";
      document.getElementById("modalMensajeTexto").innerHTML = 
      "<strong>📢 NUEVA MODALIDAD DE RESERVA DE TURNOS – Desde el 1/1/26</strong><br><br>" +
      "Debido al aumento de inasistencias, los turnos pediátricos se confirmarán solo con pago o validación previa ⏰<br><br>" +
      "🔹 La semana previa a la consulta, la secretaria (📞 291-6450538) se comunicará para:<br><br>" +
      "<ul style='text-align: left;'>" +
      "<li>Solicitar token o código de la obra social, o</li>" +
      "<li>En caso de consulta particular, transferencia de una seña.</li>" +
      "</ul>" +
      "⚠️ Si no hay respuesta, el turno se cancelará automáticamente.<br><br>" +
      "📅 Reprogramaciones: solo con aviso de 24 hs de anticipación.<br><br>" +
      "🩺🩺 UNICA Excepción: turnos por enfermedad otorgados el mismo día de la consulta<br><br>" +
      "Gracias por su comprensión y por cuidar nuestro tiempo y trabajo 💙<br><br>" +
      "<strong>Dra. Florencia García Elliot</strong>";
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

<!-- Firebase Cloud Messaging (FCM) - Inicialización y obtención de token -->
<script type="module">
  // Import the functions you need from the SDKs you need
  import { initializeApp } from "https://www.gstatic.com/firebasejs/12.8.0/firebase-app.js";
  import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/12.8.0/firebase-messaging.js";

  // Your web app's Firebase configuration
  const firebaseConfig = {
    apiKey: "AIzaSyBLLw7_0CJZ4mepUZjWbdHN9SAwFbZTtz0",
    authDomain: "turnosonlinebb-8406b.firebaseapp.com",
    projectId: "turnosonlinebb-8406b",
    storageBucket: "turnosonlinebb-8406b.firebasestorage.app",
    messagingSenderId: "991206372015",
    appId: "1:991206372015:web:0ef83da53a43d5a3f11a5a",
    measurementId: "G-KBD5NC6E1V"
  };

  // Initialize Firebase
  const app = initializeApp(firebaseConfig);
  
  // Variable para almacenar la instancia de messaging
  let messaging = null;
  let serviceWorkerRegistration = null;

  // Función para detectar si es móvil
  function esDispositivoMovil() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
  }

  // Función para detectar el sistema operativo
  function obtenerSistemaOperativo() {
    const userAgent = navigator.userAgent;
    if (/iPhone|iPad|iPod/i.test(userAgent)) {
      return 'iOS';
    } else if (/Android/i.test(userAgent)) {
      return 'Android';
    }
    return 'Desktop';
  }

  // Función para mostrar modal informativo sobre notificaciones
  function mostrarModalNotificaciones() {
    if (typeof document !== 'undefined' && document.getElementById('modalMensajeTitulo')) {
      const esMovil = esDispositivoMovil();
      const sistema = obtenerSistemaOperativo();
      
      let mensaje = 'Para recibir recordatorios de tus turnos, necesitamos tu permiso para enviar notificaciones.<br><br>';
      
      if (esMovil) {
        mensaje += '<strong>📱 Instrucciones para ' + (sistema === 'iOS' ? 'iPhone/iPad' : 'Android') + ':</strong><br>';
        
        if (sistema === 'iOS') {
          mensaje += '1. Cuando aparezca la ventana, toca "Permitir"<br>' +
                     '2. Si no aparece, ve a Configuración > Safari > Notificaciones<br>' +
                     '3. Activa las notificaciones para este sitio<br>';
        } else {
          mensaje += '1. Cuando aparezca la ventana, toca "Permitir" o "Permitir notificaciones"<br>' +
                     '2. Si no aparece, toca el menú (⋮) en Chrome > Configuración > Notificaciones<br>' +
                     '3. Activa las notificaciones para este sitio<br>';
        }
      } else {
        mensaje += 'Cuando aparezca la ventana, haz clic en "Permitir" para activar las notificaciones.<br>';
      }
      
      mensaje += '<br><strong>¿Qué recibirás?</strong><br>' +
                 '• Recordatorios automáticos de tus turnos programados<br>' +
                 '• Notificaciones cuando se acerque la fecha de tu consulta<br>' +
                 '• Avisos importantes sobre tus turnos<br><br>' +
                 'Las notificaciones te ayudarán a no olvidar tus citas médicas.';
      
      document.getElementById('modalMensajeTitulo').innerHTML = '🔔 Recordatorios de Turnos';
      document.getElementById('modalMensajeTexto').innerHTML = mensaje;
      
      // Mostrar el modal
      $('#modalMensaje').modal('show');
    }
  }

  // Función para mostrar modal cuando las notificaciones están bloqueadas
  function mostrarModalNotificacionesBloqueadas() {
    if (typeof document !== 'undefined' && document.getElementById('modalMensajeTitulo')) {
      const esMovil = esDispositivoMovil();
      const sistema = obtenerSistemaOperativo();
      
      let mensaje = 'Las notificaciones están bloqueadas. Para recibir recordatorios de tus turnos, necesitas habilitarlas manualmente.<br><br>';
      
      if (esMovil) {
        mensaje += '<strong>📱 Cómo habilitar notificaciones en ' + (sistema === 'iOS' ? 'iPhone/iPad' : 'Android') + ':</strong><br><br>';
        
        if (sistema === 'iOS') {
          mensaje += '<strong>En iPhone/iPad (Safari):</strong><br>' +
                     '1. Abre Configuración en tu iPhone<br>' +
                     '2. Desplázate y toca "Safari"<br>' +
                     '3. Toca "Notificaciones"<br>' +
                     '4. Busca "turnosonlinebb.com" y actívalo<br>' +
                     '5. Vuelve a esta página y recárgala<br><br>';
        } else {
          mensaje += '<strong>En Android (Chrome):</strong><br>' +
                     '1. Toca el menú (⋮) en la esquina superior derecha de Chrome<br>' +
                     '2. Toca "Configuración"<br>' +
                     '3. Toca "Configuración del sitio"<br>' +
                     '4. Toca "Notificaciones"<br>' +
                     '5. Busca "turnosonlinebb.com" y cámbialo a "Permitir"<br>' +
                     '6. Vuelve a esta página y recárgala<br><br>';
        }
      } else {
        mensaje += '<strong>En computadora:</strong><br>' +
                   '1. Haz clic en el ícono de candado 🔒 o información ℹ️ en la barra de direcciones<br>' +
                   '2. Busca la opción "Notificaciones"<br>' +
                   '3. Cambia el estado a "Permitir"<br>' +
                   '4. Recarga la página<br><br>';
      }
      
      mensaje += '<strong>✅ Beneficios de activar las notificaciones:</strong><br>' +
                 '• Recordatorios automáticos de tus turnos programados<br>' +
                 '• No olvidarás tus citas médicas<br>' +
                 '• Avisos importantes sobre cambios en tus turnos<br>' +
                 '• Te avisaremos cuando se acerque la fecha de tu consulta';
      
      document.getElementById('modalMensajeTitulo').innerHTML = '⚠️ Notificaciones Bloqueadas';
      document.getElementById('modalMensajeTexto').innerHTML = mensaje;
      
      // Mostrar el modal
      $('#modalMensaje').modal('show');
    }
  }

  // Función para mostrar confirmación cuando se activan las notificaciones
  function mostrarModalNotificacionesActivadas() {
    if (typeof document !== 'undefined' && document.getElementById('modalMensajeTitulo')) {
      document.getElementById('modalMensajeTitulo').innerHTML = '✅ Notificaciones Activadas';
      document.getElementById('modalMensajeTexto').innerHTML = 
        '¡Perfecto! Las notificaciones han sido activadas correctamente.<br><br>' +
        '<strong>Ahora recibirás:</strong><br>' +
        '• Recordatorios automáticos de tus turnos programados<br>' +
        '• Notificaciones cuando se acerque la fecha de tu consulta<br>' +
        '• Avisos importantes sobre tus turnos<br><br>' +
        'Los recordatorios te ayudarán a no olvidar tus citas médicas. ¡Gracias por activar las notificaciones!';
      
      // Mostrar el modal brevemente (se cierra automáticamente después de 3 segundos)
      $('#modalMensaje').modal('show');
      setTimeout(function() {
        $('#modalMensaje').modal('hide');
      }, 4000);
    }
  }

  // Función para solicitar permisos de notificación
  function requestNotificationPermission() {
    return Notification.requestPermission().then((permission) => {
      if (permission === 'granted') {
        console.log('Permiso de notificación concedido');
        return true;
      } else {
        console.log('Permiso de notificación denegado:', permission);
        return false;
      }
    });
  }

  // Función para obtener el token FCM
  async function getFCMToken() {
    try {
      // Verificar que messaging esté inicializado
      if (!messaging) {
        console.error('Messaging no está inicializado');
        return;
      }

      // Verificar el estado actual de permisos
      let permission = Notification.permission;
      
      // Si el permiso está en "default" (no se ha preguntado), mostrar mensaje informativo primero
      if (permission === 'default') {
        // Mostrar modal informativo sobre las notificaciones
        mostrarModalNotificaciones();
        
        // Esperar un momento para que el usuario lea el mensaje
        await new Promise(resolve => setTimeout(resolve, 1500));
        
        console.log('🔔 Solicitando permisos de notificación...');
        permission = await Notification.requestPermission();
      }
      
      // Si el permiso fue denegado, mostrar mensaje explicativo
      if (permission === 'denied') {
        console.warn('⚠️ Permisos de notificación bloqueados.');
        mostrarModalNotificacionesBloqueadas();
        return;
      }
      
      // Si el permiso no fue concedido, salir
      if (permission !== 'granted') {
        console.log('Permiso de notificación no concedido:', permission);
        return;
      }
      
      console.log('✅ Permisos de notificación concedidos');

      // IMPORTANTE: Necesitas obtener tu VAPID Key desde Firebase Console
      // Ve a Firebase Console > Project Settings > Cloud Messaging > Web Push certificates
      // Si no tienes una, haz clic en "Generate key pair"
      const vapidKey = 'BGCdVTZQ0UMPueqSKR-T-swAeBH48-ZJ1GsPEobFw3X8JsuOo00fm03tWd6WUx6uB9RwybXsZ52FMRg-_u3Ae8w';
      
      // Esperar a que el service worker esté listo
      if (serviceWorkerRegistration) {
        await serviceWorkerRegistration.update();
      }

      // Verificar que tengamos el service worker registration
      if (!serviceWorkerRegistration) {
        console.log('Obteniendo service worker registration...');
        try {
          serviceWorkerRegistration = await navigator.serviceWorker.ready;
          console.log('Service Worker ready:', serviceWorkerRegistration);
        } catch (error) {
          console.error('Error al obtener service worker ready:', error);
          // Intentar obtener el registration de Firebase específicamente
          const registrations = await navigator.serviceWorker.getRegistrations();
          const firebaseSW = registrations.find(reg => reg.active && reg.active.scriptURL.includes('firebase-messaging-sw'));
          if (firebaseSW) {
            serviceWorkerRegistration = firebaseSW;
            console.log('✅ Encontrado service worker de Firebase:', firebaseSW);
          } else {
            throw new Error('No se encontró el service worker de Firebase');
          }
        }
      }

      console.log('🔑 Intentando obtener token con VAPID Key:', vapidKey.substring(0, 20) + '...');
      console.log('📋 Service Worker Registration:', {
        scope: serviceWorkerRegistration.scope,
        active: serviceWorkerRegistration.active ? serviceWorkerRegistration.active.state : 'null'
      });
      
      // Verificar que el service worker esté activo
      if (serviceWorkerRegistration.active) {
        console.log('✅ Service Worker activo, estado:', serviceWorkerRegistration.active.state);
      } else {
        console.warn('⚠️ Service Worker no está activo');
      }
      
      const currentToken = await getToken(messaging, { 
        vapidKey: vapidKey,
        serviceWorkerRegistration: serviceWorkerRegistration
      });

      if (currentToken) {
        console.log('✅ FCM Token obtenido:', currentToken);
        // Enviar token al servidor
        sendTokenToServer(currentToken);
        
        // Mostrar mensaje de confirmación
        mostrarModalNotificacionesActivadas();
      } else {
        console.log('No se pudo obtener el token. El usuario debe otorgar permisos.');
      }
    } catch (err) {
      console.error('Error al obtener el token:', err);
      console.error('Código del error:', err.code);
      console.error('Mensaje del error:', err.message);
      
      // Manejo específico de errores
      if (err.code === 'messaging/invalid-vapid-key') {
        console.error('❌ VAPID Key inválida. Verifica que la clave sea correcta.');
      } else if (err.code === 'messaging/permission-blocked') {
        console.error('❌ Permisos de notificación bloqueados por el usuario.');
      } else if (err.code === 'messaging/permission-default') {
        console.error('❌ Permisos de notificación no otorgados. El usuario debe aceptar.');
      } else if (err.code === 'messaging/unsupported-browser') {
        console.error('❌ Navegador no compatible con FCM.');
      } else {
        console.error('❌ Error desconocido:', err);
      }
    }
  }

  // Registrar service worker y luego inicializar messaging
  async function initializeFCM() {
    try {
      console.log('🚀 Iniciando FCM en:', window.location.href);
      
      // Verificar que estemos en localhost o HTTPS
      const isLocalhost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
      const isSecure = window.location.protocol === 'https:' || isLocalhost;
      
      if (!isSecure) {
        console.warn('⚠️ FCM requiere HTTPS o localhost. Protocolo actual:', window.location.protocol);
      }
      
      // Registrar service worker primero
      if ('serviceWorker' in navigator) {
        try {
          // Verificar si ya hay un service worker registrado (puede ser OneSignal)
          const existingRegistrations = await navigator.serviceWorker.getRegistrations();
          console.log('Service Workers existentes:', existingRegistrations.length);
          
          // Registrar el service worker de Firebase
          serviceWorkerRegistration = await navigator.serviceWorker.register('/firebase-messaging-sw.js', {
            scope: '/'
          });
          console.log('✅ Service Worker de Firebase registrado:', serviceWorkerRegistration);
          
          // Esperar a que el service worker esté activo
          let swState = serviceWorkerRegistration.installing || serviceWorkerRegistration.waiting || serviceWorkerRegistration.active;
          
          if (serviceWorkerRegistration.installing) {
            console.log('Service Worker instalando...');
            await new Promise((resolve, reject) => {
              const timeout = setTimeout(() => {
                reject(new Error('Timeout esperando service worker'));
              }, 10000); // 10 segundos timeout
              
              serviceWorkerRegistration.installing.addEventListener('statechange', function() {
                console.log('Estado del SW:', this.state);
                if (this.state === 'activated') {
                  clearTimeout(timeout);
                  resolve();
                } else if (this.state === 'redundant') {
                  clearTimeout(timeout);
                  reject(new Error('Service worker se volvió redundante'));
                }
              });
            });
          } else if (serviceWorkerRegistration.waiting) {
            console.log('Service Worker esperando, activando...');
            serviceWorkerRegistration.waiting.postMessage({ type: 'SKIP_WAITING' });
            await serviceWorkerRegistration.update();
          } else if (serviceWorkerRegistration.active) {
            console.log('✅ Service Worker ya está activo');
          }
          
          // Esperar un momento adicional para que el service worker se estabilice
          await new Promise(resolve => setTimeout(resolve, 1500));
          
        } catch (swError) {
          console.error('❌ Error al registrar Service Worker:', swError);
          // Continuar de todas formas, puede que funcione sin el SW en algunos casos
        }
      } else {
        console.error('❌ Service Workers no están soportados en este navegador');
        return;
      }

      // Inicializar messaging después de que el service worker esté listo
      messaging = getMessaging(app);
      console.log('✅ Messaging inicializado');
      
      // Intentar obtener el token con un pequeño delay adicional
      await new Promise(resolve => setTimeout(resolve, 500));
      await getFCMToken();
      
    } catch (error) {
      console.error('❌ Error al inicializar FCM:', error);
      console.error('Stack trace:', error.stack);
    }
  }

  // Función para enviar el token al servidor
  function sendTokenToServer(token) {
    var paciente_id = document.getElementById("paciente_id") ? document.getElementById("paciente_id").value : null;
    
    if (paciente_id) {
      fetch('/save_fcm_token', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          fcm_token: token,
          paciente_id: paciente_id
        })
      })
      .then(response => response.json())
      .then(data => {
        console.log('Token FCM guardado:', data);
      })
      .catch(error => {
        console.error('Error al guardar token FCM:', error);
      });
    } else {
      console.log('No se encontró paciente_id, el token se guardará cuando haya un paciente disponible');
      // Guardar token temporalmente en localStorage para enviarlo después
      localStorage.setItem('fcm_token_pending', token);
    }
  }

  // Verificar si hay un token pendiente cuando se carga la página
  window.addEventListener('load', function() {
    // Inicializar FCM (esto registrará el service worker y obtendrá el token)
    initializeFCM();
    
    // Si hay un token pendiente en localStorage, intentar enviarlo
    var pendingToken = localStorage.getItem('fcm_token_pending');
    if (pendingToken) {
      var paciente_id = document.getElementById("paciente_id") ? document.getElementById("paciente_id").value : null;
      if (paciente_id) {
        sendTokenToServer(pendingToken);
        localStorage.removeItem('fcm_token_pending');
      }
    }
  });

  // Escuchar mensajes cuando la aplicación está en primer plano
  onMessage(messaging, (payload) => {
    console.log('Mensaje recibido:', payload);
    // Mostrar notificación personalizada
    if (payload.notification) {
      const notificationTitle = payload.notification.title;
      const notificationOptions = {
        body: payload.notification.body,
        icon: payload.notification.icon || '/images/iconos/turnosonlinebb_icon.png'
      };
      new Notification(notificationTitle, notificationOptions);
    }
  });
</script>

@endsection





