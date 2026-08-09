@extends('turnos_admin/modelo_plantilla_admin')

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

<div>
    <h3>Administrar Horarios</h3>
    <label>En esta sección podrá agregar dias puntuales y horarios para ese día.</label>

    <div class="row">
        <div class="col-md-4">
            <br>
            <label>Seleccionar especialista</label>
            <select class="form-control margin_top_menos_5px" id="select_especialista" name="select_especialista" onchange="actualizarListado()" @if(!empty($solo_medico_logueado)) disabled @endif>
                @foreach($especialistas as $especialista)
                    <option value="{{$especialista->id}}" @if(!empty($solo_medico_logueado)) selected @endif>{{$especialista->apellido.', '.$especialista->nombre}}</option>
                @endforeach
            </select>

            <label>Seleccionar día</label>
            <input type="text" id="dia" class="form-control datepicker" name="dia" value=""
            autocomplete="off">        

            <label>Seleccionar hora</label>            
            <div class="row" style="margin-left: 1px;">                
                <input type="number" class="form-control" name="hora" id="hora" style="width: 90px" placeholder="hh">
                <label style="margin-left: 5px;margin-right: 5px">:</label>
                <input type="number" class="form-control" name="min" id="min" style="width: 90px" placeholder="mm">
            </div>
            
            <br>
            <button onclick="registrarNuevoHorario()" class="rodri_button editText">Agregar</button>
                        
        </div>
        <div class="col-md-1"></div>
        <div class="col-md-3">
            <label>Listado fechas</label>
            <div class="table-responsive" style="height:400px;">
                <table class="table table-condensed" id="tabla_fechas" name="tabla_fechas">
                   <thead>
                      <tr>
                        <th class="editText" scope="col">Fecha</th>                          
                        <th class="editText" scope="col">Eliminar</th>
                      </tr>
                    </thead>
                    <tbody id="fechas-list" name="fechas-list">
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-md-3">
            <label>Listado horarios</label>

            <div class="table-responsive" style="height:400px;">
                <table class="table table-condensed" id="tabla_horarios" name="tabla_horarios">
                   <thead>
                      <tr>
                        <th class="editText" scope="col">Horario</th>                          
                        <th class="editText" scope="col">Eliminar</th>
                      </tr>
                    </thead>
                    <tbody id="horarios-list" name="horarios-list">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEstaSeguroEliminar" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title" 
        id="favoritesModalLabel">Eliminar</h4>
      </div>
      <div class="modal-body">
          <input hidden class="modal_input_medico" type="text" id="modal_horario_id"></input>         
          <input hidden class="modal_input_medico" type="text" id="modal_fecha_id"></input>         
          <label for="text" class="col-sm-0" id="modal_texto_dia_fail">¿Esta seguro que desea eliminar?</label>
      </div>
      <div class="modal-footer">
        <button type="button" 
           class="rodri_button_cancelar" 
           data-dismiss="modal">Cancelar</button>  
        <button type="button" 
           onclick="accionEliminar()" 
           class="rodri_button_aceptar" 
           data-dismiss="modal">Aceptar</button>                                      
      </div>
      </div>
  </div>
</div>

<script type="text/javascript">

    $('.datepicker').datepicker({
        weekStart: 0,
        startDate: '0d',
        language: "es",
        keyboardNavigation: false,
        forceParse: false,
        autoclose: true,
        //daysOfWeekHighlighted: diasHabilitados(),
        //daysOfWeekDisabled: dias_deshabilitados    
      }).attr('readonly','readonly');    
  
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }})

    function modalEliminarHorario(id) {
        document.getElementById("modal_horario_id").value = id;
        $("#modalEstaSeguroEliminar").modal();
    }

    function modalEliminarFecha(id) {
        document.getElementById("modal_fecha_id").value = id;
        $("#modalEstaSeguroEliminar").modal();
    }
    

    function accionEliminar() {
        var horario = document.getElementById("modal_horario_id").value;
        var fecha = document.getElementById("modal_fecha_id").value;        
        $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/actualizar_listado_horarios_fecha_eliminar',
           data:{fecha: fecha, horario:horario, _token: '{{csrf_token()}}'},
           success:function(data) {    
              if(horario != null) {
                actualizarTablaHorarios(data);
              } 
              if(data.fechaAuxId == null) {                
                actualizarListado();
                $("#tabla_horarios").find("tr:gt(0)").remove();
              }                       
           }
        });
    }

    function actualizarListado() {
        var medico_id = document.getElementById("select_especialista").value;        

        $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/actualizar_listado_nuevo_horario',
           data:{medico_id: medico_id, _token: '{{csrf_token()}}'},
           success:function(data) {              
              actualizarTabla(data);  
              // mostrarSnackbar("Nuevo horario agregado");
           }
        });
    }

    function mostrarHorariosFecha(fechaId) {        

        $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/actualizar_listado_horarios_fecha',
           data:{fechaId: fechaId, _token: '{{csrf_token()}}'},
           success:function(data) {              
              actualizarTablaHorarios(data);  
              // mostrarSnackbar("Nuevo horario agregado");
           }
        });
    }

    function actualizarTablaHorarios(data) {
        $("#tabla_horarios").find("tr:gt(0)").remove();
        for (i = 0; i < data.listadoHorarios.length; i++) {
            var nueva = '<tr><td><button onclick="mostrarHorariosFecha('+data.listadoHorarios[i].id+')" class="sinBackground">'+data.listadoHorarios[i].horario+'</button></td><td><button onclick="modalEliminarHorario('+data.listadoHorarios[i].id+')" class="rodri_button_cancelar_no">X</button></td></tr>';
            $('#horarios-list').append(nueva); 
        }
    }

    function actualizarTabla(data) {
        $("#tabla_fechas").find("tr:gt(0)").remove();
        for (i = 0; i < data.listadoFechas.length; i++) {
            var nuevaFecha = '<tr><td><button onclick="mostrarHorariosFecha('+data.listadoFechas[i].id+')" class="sinBackground">'+data.listadoFechas[i].fecha+'</button></td><td><button onclick="modalEliminarFecha('+data.listadoFechas[i].id+')" class="rodri_button_cancelar_no">X</button></td></tr>';
            $('#fechas-list').append(nuevaFecha); 
        }
    }

    function registrarNuevoHorario() {
        var medico_id = document.getElementById("select_especialista").value;
        
        var fecha = document.getElementById("dia").value;
        var hora = document.getElementById("hora").value;
        var min = document.getElementById("min").value;
        var horario = hora+":"+min;

        $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/registrar_nuevo_horario',
           data:{medico_id: medico_id, fecha :fecha, horario:horario, _token: '{{csrf_token()}}'},
           success:function(data){
              actualizarTabla(data);
              if (data.listadoHorarios && data.listadoHorarios.length) {
                actualizarTablaHorarios(data);
              }
           }
        });
    }

     window.onload=function() {
        actualizarListado();
     }
</script>

@endsection