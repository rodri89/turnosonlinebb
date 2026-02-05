
@extends('turnos/modelo_plantilla')


@if($hayTurnoLibre==1)
  @section('titulo_header','El dia '.$fechaSolicitada.':')
  @section('body_titulo','Click para elegir el turno:')
@else
  @section('titulo_header','El dia '.$fechaSolicitada.':')
@endif

@section('contenedor')
<script src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="{{asset('js/jquery.min.js')}}"> </script>
<div class="row">
  
  <input type="hidden" id="obra_social_cargada" value="{{$obraSocialCargada}}" />     
  <?php $cont=0; ?>
  @if($hayTurnoLibre == 1)
  @foreach($turnos as $turno)
  
  <!--<form method="POST" action="{{ route('registrarturno') }}">
    @csrf  -->
    @if($tipoTurno != null)
      <input type="hidden" id="tipoTurno" value="{{$tipoTurno}}" />     
    @else
      <input type="hidden" id="tipoTurno" value="0" />     
    @endif
    <input type="hidden" id="esVideollamada" value="{{$esVideollamada}}" />     
    <input type="hidden" id="primer_control" value="{{$primerControl}}" /> 
    <input type="hidden" id="paciente_id" name="paciente_id" value="{{$paciente->id}}" />
    <input type="hidden" id="dni_paciente" name="dni_paciente" value="{{$paciente->dni}}" />
    <input type="hidden" id="obra_social_foto" name="obra_social_foto" value="{{$paciente->obra_social_foto}}" />
    <input type="hidden" id="obra_social" name="obra_social" value="{{$paciente->obra_social}}" />
    <input type="hidden" id="medico_id" name="medico" value="{{$medico->id}}" />
    <input type="hidden" id="especialidad_id" name="especialidad_id" value="{{$medico->especialidad}}" />
    <input type="hidden" name="horario" value="{{$turno['horario']}}" />    
    <input type="hidden" id="consultorio" name="consultorio" value="{{$consultorio->id}}" />
    <input type="hidden" id="dia" name="dia" value="{{$dia}}" />
    <input type="hidden" id="fechaSolicitada" name="fechaSolicitada" value="{{$fechaSolicitada}}" />    

   
    @if($moduloPrimerControlDoble == 0)
      @if($turno["libre"]==1)
        <!--<div class="col-md-2 mb-3">-->
          <button onclick="modalConfirmar('{{$turno['horario']}}')" class="btn btn-primary-outline">
              <div class="circulo img_turno">
                <h1 class="turno_text_size">{{$turno["horario"]}}</h1>
              </div>
          </button>
        <!--</div> -->
      @else
      <!--<div class="col-md-2 mb-3">-->
        <button disabled class="btn btn-primary-outline">
          <div class="circulo circulo_ocupado img_turno">
            <h1 class="turno_text_size">{{$turno["horario"]}}</h1>
          </div>
        </button>
       <!--</div>-->
      @endif   
    @else
      @if($turno["libre"]==1)
      
        @if($primerControl==1)
        <button onclick="modalConfirmarPrimerControl('{{$turno['horario']}}','{{$turno['horario2']}}')" class="btn btn-primary-outline">
            <div class="circulo img_turno">
              <h1 class="turno_text_size">{{$turno["horario"]}}</h1>
            </div>
        </button>
        @else
       		<button onclick="modalConfirmar('{{$turno['horario']}}')" class="btn btn-primary-outline">
            <div class="circulo img_turno">
              <h1 class="turno_text_size">{{$turno["horario"]}}</h1>
            </div>
        </button>
        @endif
          
      @else
        @if($primerControl==0)
        <button disabled class="btn btn-primary-outline">
          <div class="circulo circulo_ocupado img_turno">
            <h1 class="turno_text_size">{{$turno["horario"]}}</h1>
          </div>
        </button>
        @endif
      @endif
    @endif
  <?php $cont++;?>
  @endforeach
  @endif
</div>
@if($hayTurnoLibre != 1)
  @if($hayTurnoLibre == 0)
  <br>
  <div class="row">
    <div class="col-md-4 mb-5">
      <img class="card-img-top img_medico_center" src="images/iconos/calendario_lleno_icono.png" alt="">
    </div>
    <div class="col-md-1"></div>
    <div class="col-md-7 mb-3 centrarVerticalHorizontal letrasrojo">
      <h3 class="fontHomeSubtitulo">No hay turnos disponibles para esta fecha.</h3>
    </div>
  </div>
  @endif
  @if($hayTurnoLibre == 2)
  <br>
  <div class="row">
    <div class="col-md-4 mb-5">
      <img class="card-img-top img_medico_center" src="images/iconos/feriados_icono.png" alt="">
    </div>
    <div class="col-md-1"></div>
    <div class="col-md-7 mb-3 centrarVerticalHorizontal">
      <h3 class="fontHomeSubtitulo">No hay turnos disponibles para esta fecha.<br>
      Feriado: {{$validarFeriado[0]->descripcion}}</h3>
    </div>
  </div>
  @endif
  @if($hayTurnoLibre == 3)
  <br>
  <div class="row">
    <div class="col-md-4 mb-5">
      <img class="card-img-top img_medico_center" src="images/iconos/calendario_lleno_icono.png" alt="">
    </div>
    <div class="col-md-1"></div>
    <div class="col-md-7 mb-3 centrarVerticalHorizontal">
      <h3 class="fontHomeSubtitulo editText">Ya tiene un turno registrado para este mes,
                                    para registrar un nuevo turno por favor comunicarse
                                    con el consultorio (Tel:{{$consultorio->telefono}}).<br>
                                    Muchas Gracias!</h3>
    </div>
  </div>
  @endif
  <form method="POST" action="{{ route('seleccionardia') }}">
    @csrf   
    <input type="hidden" name="tipoTurno" value="{{$tipoTurno}}" />
    <input type="hidden" name="esVideollamada" value="{{$esVideollamada}}" />
    <input type="hidden" name="medico_id" value="{{$medico->id}}" />
    <input type="hidden" name="dni_paciente" value="{{$paciente->dni}}" />               
    <input type="hidden" name="paciente_id" value="{{$paciente->id}}" /> 
    <input type="hidden" name="primer_control" value="{{$primerControl}}" />               
    <br>
    <div class="contenedor3">                           
      <button class="contenido3 rodri_button">Volver</button>
    </div>                          
  </form>
@endif

<br><br><br>
<div id="dialog-modal" title=""></div>

<div class="modal fade" id="modalConfirmar" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Confirmar</h4>
      </div>      
       <div class="modal-body">        
        <input class="modal_input_horario" type="hidden" id="turno_id">
        <?php if($medico->sexo === 'F') {$medicoSexo = 'la Dra';} else {$medicoSexo = 'el Dr.';} ?>
        <p class="modal-body" id="modalTitleMensajep">El turno será registrado a nombre de <b>{{$paciente->apellido.', '.$paciente->nombre}}</b> el dia <b>{{$fechaSolicitada}}</b> a las <b><input class="modal_input_horario" type="text" id="horario"></input> hs, </b> con {{$medicoSexo}} <b>{{$medico->apellido.', '.$medico->nombre}}. </b></p> 
       </div>

       
         <div class="radio-group" id="miRadioGroup">
            <div class="radio-item">
                <input type="radio" name="opcion" value="opcion1" id="radio1">
                <label for="radio1">Opción 1</label>
            </div>
            <div class="radio-item">
                <input type="radio" name="opcion" value="opcion2" id="radio2">
                <label for="radio2">Opción 2</label>
            </div>
            <div class="radio-item">
                <input type="radio" name="opcion" value="opcion3" id="radio3">
                <label for="radio3">Opción 3</label>
            </div>
        </div>
       
      <div class="modal-footer">        
        <button type="button" 
           class="rodri_button_cancelar" 
           data-dismiss="modal" onclick="cancelarTurno()">Cancelar</button> 
        
        <button onclick="confirmarTurno()" class="rodri_button_aceptar" data-dismiss="modal">Confirmar</button>                                  
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
        <!--<img id="modalImagenMensaje" class="card-img-top" src="images/iconos/ic_ok.png" alt="">                       -->
        <h6><b>Dia:</b> {{$fechaSolicitada}}</h6><br>
        <h6><b>Horario: </b><input class="modal_input_horario" type="text" id="horarioTurnoRegistrado"></input> hs </h6><br>
        <h6><b>Médico:</b> {{$medico->apellido.', '.$medico->nombre}} </h6><br>
          @if($medico->id == 12)
            @if($dia == 5)
              <h6><b>Dirección:</b> Garibaldi 44</h6><br>
            @else
              <h6><b>Dirección:</b> Blandengues 505</h6><br>
            @endif
          @endif

          @if($medico->id == 25)            
            <h6><b>Dirección:</b> Blandengues 505</h6><br>            
          @endif
        
          @if($medico->id == 24)
            @if($dia == 3)
                <h6><b>Dirección:</b> Luiggi 463</h6><br>
            @else
                <h6><b>Dirección:</b> {{$consultorio->direccion}}</h6><br>
            @endif
          @endif
        
        <p>Un mail o mensaje de texto será enviado el día previo para recordarle su turno.</p>        
       </div>
      <div class="modal-footer">
        <button onclick="irHome()" class="rodri_button_aceptar" data-dismiss="modal">Finalizar</button>            
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalTurnoOkVideollamada" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Turno Registrado</h4>
      </div>      
       <div class="modal-body">
        <!--<img id="modalImagenMensaje" class="card-img-top" src="images/iconos/ic_ok.png" alt="">                       -->
        <h6><b>Dia:</b> {{$fechaSolicitada}}</h6><br>
        <h6><b>Horario: </b><input class="modal_input_horario" type="text" id="horarioTurnoRegistradoVideollamada"></input> hs </h6><br>
        <h6><b>Médico:</b> {{$medico->apellido.', '.$medico->nombre}} </h6><br>
        
        <p>En la sección de videollamadas tendrá un instructivo de como realizar su consulta. Al dar click en finalizar, será redirigido a dicha pantalla.</p>        
       </div>
        <form method="POST" action="{{ route('asistirvideollamada') }}">
            @csrf   
          
          <input type="hidden" id="turno_id_videollamada" name="turno_id" value="" />
          <input type="hidden" name="dni_paciente" value="{{$paciente->dni}}" />
          <div class="text-center mt-3">
          <button type="submit" class="rodri_button_aceptar">Finalizar</button>
          </div>
            <br>
        </form>
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
        id="modalTitleMensaje">Turno Ocupado</h4>
      </div>      
       <div class="modal-body">             
        <h6 class="modal-title"         
        id="modalTitleMensaje">Lo siento, el turno ha sido registrado por otra persona, por favor actualice la página y seleccione otro horario.<br>Muchas gracias.</h6>
       </div>
      <div class="modal-footer">
        <button type="button" 
           class="rodri_button_cancelar" 
           data-dismiss="modal">Cerrar</button>        
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalTurnoFail2" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Error</h4>
      </div>      
       <div class="modal-body">            
        <h6 class="modal-title"         
        id="modalTitleMensaje">Lo siento, ya tienes un turno registrado con el medico para este dia.</h6>
       </div>
      <div class="modal-footer">
        <button type="button" 
           class="rodri_button_cancelar" 
           data-dismiss="modal">Cerrar</button>        
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalTurnoFail4" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Error</h4>
      </div>      
       <div class="modal-body">            
        <h6 class="modal-title"         
        id="modalTitleMensaje">Lo siento, ya tienes un turno registrado para realizar un PEG en ese horario.</h6>
       </div>
      <div class="modal-footer">
        <button type="button" 
           class="rodri_button_cancelar" 
           data-dismiss="modal">Aceptar</button>        
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalTurnoFail5" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Error</h4>
      </div>      
       <div class="modal-body">            
        <h6 class="modal-title"         
        id="modalTitleMensaje">Lo siento, ya tienes un turno registrado esta semana, solo puedes solicitar un turno por semana.</h6>
       </div>
      <div class="modal-footer">
        <button type="button" 
           class="rodri_button_cancelar" 
           data-dismiss="modal">Aceptar</button>        
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalTurnoFail3" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Error</h4>
      </div>      
       <div class="modal-body">            
        <h6 class="modal-title"         
        id="modalTitleMensaje">Lo siento, ya tienes un turno registrado para realizar otro estudio en ese horario.</h6>
       </div>
      <div class="modal-footer">
        <button type="button" 
           class="rodri_button_cancelar" 
           data-dismiss="modal">Aceptar</button>        
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalCargarFotoObraSocial" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Cargar Foto</h4>
      </div>      
       <div class="modal-body">            
        <h6 class="modal-title"         
        id="modalTitleMensaje">Es necesario que cargue una foto de la obra social.</h6>
        <form method="post" id="upload_form" enctype="multipart/form-data">
           @csrf
           <input type="hidden" name="esVideollamada" value="{{$esVideollamada}}" />
           <input type="hidden" name="medico" value="{{$medico->id}}" />
           <input type="hidden" id="consultorio" name="consultorio" value="{{$consultorio->id}}" />
           <input type="hidden" name="dni_paciente" value="{{$paciente->dni}}" />               
           <input type="hidden" name="paciente_id" value="{{$paciente->id}}" /> 
           <input type="hidden" name="primer_control" value="{{$primerControl}}" />    
           <input type="hidden" id="fecha_seleccionada" name="fecha_seleccionada" value="{{$fechaSolicitada}}" />             
           <input type="hidden" id="activar_modal_foto" name="activar_modal_foto" value="0" />             
           <input type="file" id="foto" name="foto" placeholder="Foto Carnet Obra Social" /> 
       </div>
      <div class="modal-footer">
        <button type="submit" 
           class="rodri_button_aceptar" 
           >Guardar</button>
      </form> 
        <button type="button" 
           class="rodri_button_cancelar" 
           data-dismiss="modal">Cancelar</button>        
      </div>
    </div>
  </div>
</div>

@include('modal.modal_mensaje')

<script type="text/javascript">

$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});


function modalConfirmarTest(horario){  
  $('#horario').val(horario);
  $('#modalConfirmar').modal();
}

function confirmarTurno(){
  var esVideollamada = document.getElementById("esVideollamada").value;
  var especialidad_id = document.getElementById("especialidad_id").value;  
  var horario = document.getElementById("horario").value;
  if(esVideollamada == 0 || especialidad_id == 2) {  
    $('#horarioTurnoRegistrado').val(horario);                
    $('#modalTurnoOk').modal();
  } else {
    var turno_id = document.getElementById("turno_id").value;    
    var obra_social_foto = document.getElementById("obra_social_foto").value;
    
      $('#turno_id_videollamada').val(turno_id);    
      $('#horarioTurnoRegistradoVideollamada').val(horario);             
      $('#modalTurnoOkVideollamada').modal();    
  }
}

function irHome(){
  location.href ="homes";
}

function cancelarTurno(){
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


$("#upload_form").submit(function(){
  var formData = new FormData($(this)[0]);
  $.ajax({
   url:"{{ route('cargarfotoobrasocial') }}",
   type: 'POST',
   data: formData,
   success:function(data)
   {
    //alert(data.paciente.obra_social_foto);    
   },
    cache: false,
    processData: false
  });
 });

function modalConfirmar(horario) {
    var paciente_id = document.getElementById("paciente_id").value;         
    var medico_id = document.getElementById("medico_id").value;
    var consultorio = document.getElementById("consultorio").value;
    var dia = document.getElementById("dia").value;   
    var fechaTurno = document.getElementById("fechaSolicitada").value;
    var primerControl = document.getElementById("primer_control").value;        
    var esVideollamada = document.getElementById("esVideollamada").value;  
    var tipoTurno = document.getElementById("tipoTurno").value;  

     $.ajax({
         type:'POST',
         dataType:'JSON',
         url:'/registrar_turno',
         data:{paciente_id :paciente_id, medico_id:medico_id, consultorio:consultorio, dia:dia, fechaTurno:fechaTurno, horario:horario,primerControl:primerControl, esVideollamada:esVideollamada, tipoTurno:tipoTurno, _token: '{{csrf_token()}}'},
         success:function(data){                 
           if(data.turnoRegistrado == 1){          
                $('#horario').val(data.horario);
                $('#turno_id').val(data.turno_id);
                
                // Si el evento ya se agregó automáticamente a Google Calendar, no descargar .ics
                if(data.calendar_event_added) {
                    console.log('✅ Evento agregado automáticamente a Google Calendar');
                } else if(data.calendar_reminder_url) {
                    // Si no tiene OAuth, descargar archivo .ics como fallback
                    var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                    
                    if (isMobile) {
                        // En móviles, usar un iframe oculto para forzar la descarga/apertura
                        var iframe = document.createElement('iframe');
                        iframe.style.display = 'none';
                        iframe.src = data.calendar_reminder_url;
                        document.body.appendChild(iframe);
                        
                        // Remover el iframe después de un momento
                        setTimeout(function() {
                            document.body.removeChild(iframe);
                        }, 2000);
                    } else {
                        // En desktop, descargar el archivo
                        var link = document.createElement('a');
                        link.href = data.calendar_reminder_url;
                        link.download = 'recordatorio-turno.ics';
                        link.style.display = 'none';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    }
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

function modalConfirmarPrimerControl(horario1,horario2){
    var paciente_id = document.getElementById("paciente_id").value;         
    var medico_id = document.getElementById("medico_id").value;
    var consultorio = document.getElementById("consultorio").value;
    var dia = document.getElementById("dia").value;   
    var fechaTurno = document.getElementById("fechaSolicitada").value;  
    var primerControl = document.getElementById("primer_control").value;     
    
     $.ajax({
         type:'POST',
         dataType:'JSON',
         url:'/registrar_turno_primer_control',
         data:{paciente_id :paciente_id, medico_id:medico_id, consultorio:consultorio, dia:dia, fechaTurno:fechaTurno, horario1:horario1,horario2:horario2, primerControl: primerControl, _token: '{{csrf_token()}}'},
         success:function(data){              
           if(data.turnoRegistrado == 0){                      
              $('#modalTurnoFail').modal();                  
          }
          if(data.turnoRegistrado == 1){        
                $('#horario').val(data.horario);
                $('#turno_id').val(data.turno_id);                
                $('#modalConfirmar').modal();
          }
           if(data.turnoRegistrado == 2){                      
              $('#modalTurnoFail2').modal();                  
          }            
        }
      });
  }

  function mostrarMsjEspecial(){
    var medico_id = document.getElementById("medico_id").value;    
    if(medico_id == 14){
      document.getElementById("modalMensajeTitulo").innerHTML = "IMPORTANTE";
      document.getElementById("modalMensajeTexto").innerHTML = 'Para asistir a la consulta se le cobrará un adicional de $1000 por material de bioseguridad a aquellos pacientes que concurran por obra social. En caso de los particulares este importe está incluido';            
      $('#modalMensaje').modal();
    }
  }

  function mostrarMsjTurnoVirtual(){
    /* var patricia_sosa_id = 14; // 13 en produccion 12 en local
    var medico_id = document.getElementById("medico_id").value;
    var fecha = document.getElementById("fechaSolicitada").value;
    var diaSeman = diaSemana(fecha);
    if(medico_id == patricia_sosa_id)
      document.getElementById("msj_turno_virtual").hidden = false;    
    */
  }

  function diaSemana(fecha){
    var fecha_aux = fecha.split("/");
    var dia = fecha_aux[0];
    var mes = fecha_aux[1];
    var anio = fecha_aux[2];
    
    var dias = ["Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado"];
    var dt = new Date(mes+' '+dia+', '+anio+' 12:00:00');
    return dias[dt.getUTCDay()];        
  }

window.onload=function() {
    var esVideollamada = document.getElementById("esVideollamada").value;  
    var especialidad_id = document.getElementById("especialidad_id").value;    

    if(esVideollamada == 1 && especialidad_id != 2) {    
    var paciente_id = document.getElementById("paciente_id").value;    
    $.ajax({
         type:'POST',
         dataType:'JSON',
         url:'/check_tiene_foto_os',
         data:{paciente_id :paciente_id, _token: '{{csrf_token()}}'},
         success:function(data){              
          if(data.response == 0)
            $('#modalCargarFotoObraSocial').modal();
         }  
      });
    }
    mostrarMsjTurnoVirtual();
    // mostrarMsjEspecial();
    

    //verificar si tiene obra social foto    
    /*var obra_social_cargada = document.getElementById("obra_social_cargada").value;    
    var obra_social_foto = document.getElementById("obra_social_foto").value;    
    var obra_social = document.getElementById("obra_social").value; 
    //alert(obra_social_foto.length);   
    if(obra_social_cargada == 0){
      if(obra_social.localeCompare('PARTICULAR') != 0) {
        if(obra_social_foto.length == 0){
          $('#modalCargarFotoObraSocial').modal();
        }
      }
    }*/
}

</script>
@endsection

