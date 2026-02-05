@extends('turnos_admin_secretaria/turnos_admin_secretaria_plantilla_body')

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
  <h2>Listado Pacientes del dia</h2><br>      
  <div class="col-md-2">
    <form> 
      @csrf                         
        @if($medico->id == 5)
            <input type="hidden" name="dias_deshabilitados" id="dias_deshabilitados" value="0,1,3,4,6" />
          @else
            <input type="hidden" name="dias_deshabilitados" id="dias_deshabilitados" value="{{$dias_deshabilitados}}" />        
        @endif
        <input type="hidden" id="consultorio" name="consultorio" value="{{$consultorio}}"  />
        <input type="hidden" id="medico_id" name="medico_id" value="{{$medico->id}}"  />
        <input type="hidden" id="especialidad_id" name="especialidad_id" value="{{$medico->especialidad}}" />
        <input type="text" id="dia" class="form-control datepicker" name="dia" value="{{$dia}}"
         autocomplete="off" onchange="actualizarListado(this.value)">        
    </form>                                                                                   
    </div>  
    @if($moduloCajaComentario == 1)  
      <div class="col-md-2"></div>  
      <div class="col-md-3">      
        <label class="total_caja_size">Total Caja: $</label><label id="total_caja_value" class="marginLeft5px total_caja_size">0</label>
      </div>
    @else
      <label hidden id="total_caja_value">0</label>
    @endif

    <div class="col-md-12 col-center">
      <br>
      <!-- <div class="table-responsive" style="height:600px; overflow-y: scroll;"> -->
      <div class="table-responsive" style="height:400px; overflow-y: scroll;">

       <table class="table table-condensed" id="tabla_pacientes" name="tabla_pacientes">
       <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Tipo</th>
            <th scope="col">Hora</th>
            <th scope="col">Paciente</th>                                 
            <th scope="col">DNI</th>
            <th scope="col">Telefono</th>
            @if($consultorio == 6)
              <th scope="col">Obra Social</th>
            @endif
            <th scope="col">Primer Control</th>
            @if($moduloCajaComentario == 1)            
              <th scope="col">Caja</th>
              <th scope="col">Comentario</th>
            @endif
            @if($medico->especialidad == 2)
              <th scope="col">Consulta</th>
            @endif
            <th scope="col">Asistio</th>                     
          </tr>
        </thead>
        <tbody id="pacientes-list" name="pacientes-list">          
      </tbody>
    </table>
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
        id="modalTitleMensaje">Actualizar</h4>
      </div>      
       <div class="modal-body">
         <label for="text" class="col-sm-0" id="modal_texto_ok">Campo actualizado correctamente.</label>                   
       </div>
      <div class="modal-footer">
      <button type="button" 
           class="rodri_button_aceptar" 
           data-dismiss="modal">Aceptar</button>        
      </div>
    </div>
  </div>
</div>

<div id="snackbar"><p>El campo ha sido actualizado</p></div>

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

   function recargarListado(){
    var dia = document.getElementById("dia").value; 
    actualizarListado(dia);    
  }

  function guardarCaja(caja_id){
    //alert(caja_id);
    var turnoRegistradoId = caja_id;
    var caja = document.getElementById("caja"+caja_id).value;
    var medico = document.getElementById("medico_id").value;    
    var consul = document.getElementById("consultorio").value;
    var dia = document.getElementById("dia").value;    
     $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/update_caja_listado_pacientes_secretaria',
           data:{medico_id: medico, consultorio:consul, fecha :dia, caja:caja, turnoRegistradoId: turnoRegistradoId, _token: '{{csrf_token()}}'},
           success:function(data){              
              var especialidad_id = document.getElementById("especialidad_id").value;              
                cargarTabla(data);              
                mostrarSnackbar();
               // $("#modalMensaje").modal();  
           }
        });
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
           url:'/update_comentario_listado_pacientes_secretaria',
           data:{medico_id: medico, consultorio:consul, fecha :dia, comentario:comentario, turnoRegistradoId: turnoRegistradoId, _token: '{{csrf_token()}}'},
           success:function(data){              
              var especialidad_id = document.getElementById("especialidad_id").value;
              
                cargarTabla(data);
              
                mostrarSnackbar();
               // $("#modalMensaje").modal();  
           }
        }); 
  }


  function actualizarListado(valor) {    
    var medico = document.getElementById("medico_id").value;    
    var consul = document.getElementById("consultorio").value;    
    var tipo_estudio = 2;
     $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/actualizar_listado_pacientes',
           data:{medico_id: medico, consultorio:consul, fecha :valor, tipo_estudio:tipo_estudio, _token: '{{csrf_token()}}'},
           success:function(data){              
            //alert(data.turnosPaciente.length);              
              //alert(data.turnosPaciente[0].nombrep);
              
                cargarTabla(data);
              
            }
        });
  }

  function registrarAsistencia(turnoRegistradoId, asistio){
    //alert("holas "+turnoRegistradoId);    
    var dia = document.getElementById("dia").value;    
    var medico = document.getElementById("medico_id").value;    
    var consul = document.getElementById("consultorio").value;    
    
     $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/registrar_asistencia',
           data:{medico_id: medico, consultorio:consul, fecha :dia, tregistradoId:turnoRegistradoId, asistio:asistio, _token: '{{csrf_token()}}'},
           success:function(data){                            
                cargarTabla(data);              
           }
        });
  }

  function getTipoTurno(tipoTurno) {
    if(tipoTurno == 0)
      return "Consulta";
    if(tipoTurno == 1)
      return "Consulta";
    if(tipoTurno == 23)
      return "Ecografia";
    if(tipoTurno == 25)
      return "Consulta + Eco";
  }

  function cargarTabla(data){    
    var contador = 0;
    document.getElementById("total_caja_value").innerHTML = 0;
    $("#tabla_pacientes").find("tr:gt(0)").remove();
    for (i = 0; i < data.turnosPaciente.length; i++){
        contador = contador + 1;
        var caja_id = "caja"+data.turnosPaciente[i].trid;
        var comentario_id = "comentario"+data.turnosPaciente[i].trid;
        var consultorio_id = document.getElementById("consultorio").value;
        sumarCaja(data.turnosPaciente[i].caja);
        console.log(data.turnosPaciente[i]);
        var tipoTurno = getTipoTurno(data.turnosPaciente[i].tipo_turno);
        if(consultorio_id == 6){
          // muestro obra social en el listado
          if(data.turnosPaciente[i].dni == 99999){
            var paciente = "<tr><td>"+contador+"</td><td>"+tipoTurno+"</td><td>"+data.turnosPaciente[i].horario+"</td><td>Cancelado</td><td></td><td></td><td></td><td></td><td></td></tr>";  
            } else {
              if(data.turnosPaciente[i].asistio == 0){ // todavia no se realizo accion
              var paciente = "<tr><td>"+contador+"</td><td>"+tipoTurno+"</td><td>"+data.turnosPaciente[i].horario+"</td><td>"+data.turnosPaciente[i].apellidop+", "+data.turnosPaciente[i].nombrep+"</td><td class='editText'><a type='button' onclick=verDatosPaciente("+data.turnosPaciente[i].dni+")><u>"+data.turnosPaciente[i].dni+"</u></a></td><td>"+data.turnosPaciente[i].telefono+"</td><td>"+data.turnosPaciente[i].obra_social+"</td><td>"+data.turnosPaciente[i].primerControl+"</td><td><button class='rodri_button_aceptar_si' onclick='registrarAsistencia("+data.turnosPaciente[i].trid+",1)'>SI</button><button class='rodri_button_cancelar_no' onclick='registrarAsistencia("+data.turnosPaciente[i].trid+",2)'>NO</button></td></tr>";                    
              } else {                      
                if(data.turnosPaciente[i].asistio == 1){ // asistio SI                      
                var paciente = "<tr><td>"+contador+"</td><td>"+tipoTurno+"</td><td>"+data.turnosPaciente[i].horario+"</td><td>"+data.turnosPaciente[i].apellidop+", "+data.turnosPaciente[i].nombrep+"</td><td class='editText'><a type='button' onclick=verDatosPaciente("+data.turnosPaciente[i].dni+")><u>"+data.turnosPaciente[i].dni+"</u></a></td><td>"+data.turnosPaciente[i].telefono+"</td><td>"+data.turnosPaciente[i].obra_social+"</td><td>"+data.turnosPaciente[i].primerControl+"</td><td><button class='rodri_button_aceptar_si' disabled>SI</button></td></tr>";      
                } else { //asisitio = 2   asistio NO
                var paciente = "<tr><td>"+contador+"</td><td>"+tipoTurno+"</td><td>"+data.turnosPaciente[i].horario+"</td><td>"+data.turnosPaciente[i].apellidop+", "+data.turnosPaciente[i].nombrep+"</td><td class='editText'><a type='button' onclick=verDatosPaciente("+data.turnosPaciente[i].dni+")><u>"+data.turnosPaciente[i].dni+"</u></a></td><td>"+data.turnosPaciente[i].telefono+"</td><td>"+data.turnosPaciente[i].obra_social+"</td><td>"+data.turnosPaciente[i].primerControl+"</td><td><button class='rodri_button_cancelar_no' disabled>NO</button></td></tr>";      
                }
              }
            }
        } else {        
          if(data.moduloCajaComentario == 0){
            if(data.turnosPaciente[i].dni == 99999){
            var paciente = "<tr><td>"+contador+"</td><td>"+tipoTurno+"</td><td>"+data.turnosPaciente[i].horario+"</td><td>Cancelado</td><td></td><td></td><td></td><td></td></tr>";  
            } else {
              if(data.turnosPaciente[i].asistio == 0){ // todavia no se realizo accion
              var paciente = "<tr><td>"+contador+"</td><td>"+tipoTurno+"</td><td>"+data.turnosPaciente[i].horario+"</td><td>"+data.turnosPaciente[i].apellidop+", "+data.turnosPaciente[i].nombrep+"</td><td class='editText'><a type='button' onclick=verDatosPaciente("+data.turnosPaciente[i].dni+")><u>"+data.turnosPaciente[i].dni+"</u></a></td><td>"+data.turnosPaciente[i].telefono+"</td><td>"+data.turnosPaciente[i].primerControl+"</td><td><button class='rodri_button_aceptar_si' onclick='registrarAsistencia("+data.turnosPaciente[i].trid+",1)'>SI</button><button class='rodri_button_cancelar_no' onclick='registrarAsistencia("+data.turnosPaciente[i].trid+",2)'>NO</button></td></tr>";                    
              } else {                      
                if(data.turnosPaciente[i].asistio == 1){ // asistio SI                      
                var paciente = "<tr><td>"+contador+"</td><td>"+tipoTurno+"</td><td>"+data.turnosPaciente[i].horario+"</td><td>"+data.turnosPaciente[i].apellidop+", "+data.turnosPaciente[i].nombrep+"</td><td class='editText'><a type='button' onclick=verDatosPaciente("+data.turnosPaciente[i].dni+")><u>"+data.turnosPaciente[i].dni+"</u></a></td><td>"+data.turnosPaciente[i].telefono+"</td><td>"+data.turnosPaciente[i].primerControl+"</td><td><button class='rodri_button_aceptar_si' disabled>SI</button></td></tr>";      
                } else { //asisitio = 2   asistio NO
                var paciente = "<tr><td>"+contador+"</td><td>"+tipoTurno+"</td><td>"+data.turnosPaciente[i].horario+"</td><td>"+data.turnosPaciente[i].apellidop+", "+data.turnosPaciente[i].nombrep+"</td><td class='editText'><a type='button' onclick=verDatosPaciente("+data.turnosPaciente[i].dni+")><u>"+data.turnosPaciente[i].dni+"</u></a></td><td>"+data.turnosPaciente[i].telefono+"</td><td>"+data.turnosPaciente[i].primerControl+"</td><td><button class='rodri_button_cancelar_no' disabled>NO</button></td></tr>";      
                }
              }
            }
          } else {
            if(data.turnosPaciente[i].dni == 99999){
              var paciente = "<tr><td>"+contador+"</td><td>"+tipoTurno+"</td><td>"+data.turnosPaciente[i].horario+"</td><td>Cancelado</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";  
            } else {
              if(data.turnosPaciente[i].asistio == 0){ // todavia no se realizo accion
              var paciente = "<tr><td>"+contador+"</td><td>"+tipoTurno+"</td><td>"+data.turnosPaciente[i].horario+"</td><td>"+data.turnosPaciente[i].apellidop+", "+data.turnosPaciente[i].nombrep+"</td><td class='editText'><a type='button' onclick=verDatosPaciente("+data.turnosPaciente[i].dni+")><u>"+data.turnosPaciente[i].dni+"</u></a></td><td>"+data.turnosPaciente[i].telefono+"</td><td>"+data.turnosPaciente[i].primerControl+"</td><td><input size='8' type='text' onchange=guardarCaja("+data.turnosPaciente[i].trid+") id="+caja_id+" name='caja' value="+data.turnosPaciente[i].caja+"></td><td><input type='text' onchange=guardarComentario("+data.turnosPaciente[i].trid+") id="+comentario_id+" name='comentario' value='"+data.turnosPaciente[i].comentario+"'></td><td><button class='rodri_button_aceptar_si' onclick='registrarAsistencia("+data.turnosPaciente[i].trid+",1)'>SI</button><button class='rodri_button_cancelar_no' onclick='registrarAsistencia("+data.turnosPaciente[i].trid+",2)'>NO</button></td></tr>";                    
              } else {                      
                if(data.turnosPaciente[i].asistio == 1){ // asistio SI                      
                var paciente = "<tr><td>"+contador+"</td><td>"+tipoTurno+"</td><td>"+data.turnosPaciente[i].horario+"</td><td>"+data.turnosPaciente[i].apellidop+", "+data.turnosPaciente[i].nombrep+"</td><td class='editText'><a type='button' onclick=verDatosPaciente("+data.turnosPaciente[i].dni+")><u>"+data.turnosPaciente[i].dni+"</u></a></td><td>"+data.turnosPaciente[i].telefono+"</td><td>"+data.turnosPaciente[i].primerControl+"</td><td><input size='8' type='text' onchange=guardarCaja("+data.turnosPaciente[i].trid+") id="+caja_id+" name='caja' value="+data.turnosPaciente[i].caja+"></td><td><input type='text' onchange=guardarComentario("+data.turnosPaciente[i].trid+") id="+comentario_id+" name='comentario' value='"+data.turnosPaciente[i].comentario+"'></td><td><button class='rodri_button_aceptar_si' disabled>SI</button></td></tr>";      
                } else { //asisitio = 2   asistio NO
                var paciente = "<tr><td>"+contador+"</td><td>"+tipoTurno+"</td><td>"+data.turnosPaciente[i].horario+"</td><td>"+data.turnosPaciente[i].apellidop+", "+data.turnosPaciente[i].nombrep+"</td><td class='editText'><a type='button' onclick=verDatosPaciente("+data.turnosPaciente[i].dni+")><u>"+data.turnosPaciente[i].dni+"</u></a></td><td>"+data.turnosPaciente[i].telefono+"</td><td>"+data.turnosPaciente[i].primerControl+"</td><td><input size='8' type='text' onchange=guardarCaja("+data.turnosPaciente[i].trid+") id="+caja_id+" name='caja' value="+data.turnosPaciente[i].caja+"></td><td><input type='text' onchange=guardarComentario("+data.turnosPaciente[i].trid+") id="+comentario_id+" name='comentario' value='"+data.turnosPaciente[i].comentario+"'  ></td><td><button class='rodri_button_cancelar_no' disabled>NO</button></td></tr>";      
                }
              }
            }
          }
        }

        $('#pacientes-list').append(paciente); 
    }
  }

  function sumarCaja(valor){    
    var totalCaja = parseInt(document.getElementById("total_caja_value").innerHTML);
    if(valor != null){
      totalCaja = totalCaja + parseInt(valor);
      // alert(totalCaja);
      document.getElementById("total_caja_value").innerHTML = totalCaja;  
    }    
  }

  function verDatosPaciente(dni) {
    $.ajax({
       type:'POST',
       dataType:'JSON',
       url:'/paciente_consultar',
       data:{dni_paciente :dni, _token: '{{csrf_token()}}'},
       success:function(data){      
        if(data.paciente != null){
          $('#modal_dni').val(data.paciente.dni);        
          $('#modal_nombre').val(data.paciente.nombre);        
          $('#modal_apellido').val(data.paciente.apellido);
           if(data.paciente.fecha_nacimiento.localeCompare('') != 0) {
                var arrayFechaNacimiento = data.paciente.fecha_nacimiento.split('-');
                 if(arrayFechaNacimiento[0].localeCompare("1000") == 0) {
                   $('#modal_fecha_nacimiento_dia').val("");        
                   $('#modal_fecha_nacimiento_mes').val("");        
                   $('#modal_fecha_nacimiento_anio').val(""); 
                 } else {
                   $('#modal_fecha_nacimiento_dia').val(arrayFechaNacimiento[2]);        
                   $('#modal_fecha_nacimiento_mes').val(arrayFechaNacimiento[1]);        
                   $('#modal_fecha_nacimiento_anio').val(arrayFechaNacimiento[0]);                   
                }
              }         
                 
          $('#modal_telefono').val(data.paciente.telefono);        
          $('#modal_domicilio').val(data.paciente.domicilio);        
          $('#modal_localidad').val(data.paciente.localidad);        
          $('#modal_mail').val(data.paciente.mail);        
          $('#modal_obra_social').val(data.paciente.obra_social);        
          $('#modal_numero_afiliado').val(data.paciente.numero_afiliado);        
          $('#modal_plan_obra_social').val(data.paciente.obra_social_plan);        
          $('#modalVerDatosPaciente').modal();  
        }
       }
     });
  }

  function mostrarSnackbar() {    
    var x = document.getElementById("snackbar");
    x.className = "show";
    setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
  }

  window.onload=function() {          
    checkRecetasPendientes();
    recargarListado();
  }
  
  
</script>

@endsection