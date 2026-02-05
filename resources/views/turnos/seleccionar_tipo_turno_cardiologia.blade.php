@extends('turnos/modelo_plantilla')

@section('titulo_header','Seleccionar Tipo Turno')

@section('descripcion_header','En esta sección deberá elegir el tipo de turno.')

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
  <form method="POST" action="{{ route('tipoturnocardiologo') }}">
      @csrf
      <input type="hidden" name="tipo_turno" value="1"  />           
      <input type="hidden" name="medico_id" value="{{$medico->id}}"  />           
      <input type="hidden" name="consultorio_id" value="{{$consultorio->id}}"  /> 
      <input type="hidden" name="consultorio_telefono" id="consultorio_telefono" value="{{$consultorio->telefono}}"  />
      <input type="hidden" name="paciente_id" value="{{$paciente->id}}"  />                             
      <input type="hidden" name="primerControl" value="{{$primerControl}}"  />                               
      <input type="hidden" name="moduloRecetas" value="{{$moduloRecetas}}"  />                     
      <button class="btn btn-primary-outline img-responsive img_home">
        <img class="card-img-top " src="images/iconos/consulta_cardiologo.png" alt="">
      </button>
      <div class="card-body">
        <h6 class="fontImage" align="center">Consulta y ECG</h6>      
      </div>        
  </form>

  <form method="POST" action="{{ route('tipoturnocardiologo') }}">
      @csrf
      <input type="hidden" name="tipo_turno" value="2"  />           
      <input type="hidden" name="medico_id" value="{{$medico->id}}"  />           
      <input type="hidden" name="consultorio_id" value="{{$consultorio->id}}"  />                     
      <input type="hidden" name="paciente_id" value="{{$paciente->id}}"  />                             
      <input type="hidden" name="primerControl" value="{{$primerControl}}"  />                               
      <input type="hidden" name="moduloRecetas" value="{{$moduloRecetas}}"  />
      <button class="btn btn-primary-outline img-responsive img_home">
        <img class="card-img-top " src="images/iconos/ecocardiograma.png" alt="">
      </button>
      <div class="card-body">
        <h6 class="fontImage mercadopago_collapse_150px" align="center">Ecocardiograma Doppler Color</h6>      
      </div>        
  </form>

   <form method="POST" action="{{ route('tipoturnocardiologo') }}">
      @csrf
      <input type="hidden" name="tipo_turno" value="3"  />           
      <input type="hidden" name="medico_id" value="{{$medico->id}}"  />           
      <input type="hidden" name="consultorio_id" value="{{$consultorio->id}}"  />                     
      <input type="hidden" name="paciente_id" value="{{$paciente->id}}"  />                             
      <input type="hidden" name="primerControl" value="{{$primerControl}}"  />                               
      <input type="hidden" name="moduloRecetas" value="{{$moduloRecetas}}"  />
      <button class="btn btn-primary-outline img-responsive img_home">
        <img class="card-img-top " src="images/iconos/ecodoppler_vasos_cuello.png" alt="">
      </button>
      <div class="card-body">
        <h6 class="fontImage mercadopago_collapse_150px" align="center">Ecodoppler de Vasos de Cuello</h6>      
      </div>        
  </form>

  <form method="POST" action="{{ route('tipoturnocardiologo') }}">
      @csrf
      <input type="hidden" name="tipo_turno" value="4"  />           
      <input type="hidden" name="medico_id" value="{{$medico->id}}"  />           
      <input type="hidden" name="consultorio_id" value="{{$consultorio->id}}"  />                     
      <input type="hidden" name="paciente_id" value="{{$paciente->id}}"  />                             
      <input type="hidden" name="primerControl" value="{{$primerControl}}"  />                               
      <input type="hidden" name="moduloRecetas" value="{{$moduloRecetas}}"  />
      <button class="btn btn-primary-outline img-responsive img_home">
        <img class="card-img-top " src="images/iconos/ergometria.png" alt="">
      </button>
      <div class="card-body">
        <h6 class="fontImage mercadopago_collapse_150px" align="center">Ergometria de 12 Derivaciones (PEG)</h6>      
      </div>        
  </form>

    <div>      
      <input type="hidden" name="tipo_turno" value="5"  />           
      <input type="hidden" name="medico_id" value="{{$medico->id}}"  />           
      <input type="hidden" name="consultorio_id" value="{{$consultorio->id}}"  />                     
      <input type="hidden" name="paciente_id" value="{{$paciente->id}}"  />                             
      <input type="hidden" name="primerControl" value="{{$primerControl}}"  />                               
      <input type="hidden" name="moduloRecetas" value="{{$moduloRecetas}}"  />
      <button type="button" class="btn btn-primary-outline img-responsive img_home" onclick="modalConsultarSecre()">
        <img class="card-img-top " src="images/iconos/mapa_presurometria.png" alt="">
      </button>
      <div class="card-body">
        <h6 class="fontImage mercadopago_collapse_150px" align="center">MAPA Presurometria</h6>      
      </div>        
  </div>

    <div>      
      <input type="hidden" name="tipo_turno" value="6"  />           
      <input type="hidden" name="medico_id" value="{{$medico->id}}"  />           
      <input type="hidden" name="consultorio_id" value="{{$consultorio->id}}"  />                     
      <input type="hidden" name="paciente_id" value="{{$paciente->id}}"  />                             
      <input type="hidden" name="primerControl" value="{{$primerControl}}"  />                               
      <input type="hidden" name="moduloRecetas" value="{{$moduloRecetas}}"  />
      <button type="button" class="btn btn-primary-outline img-responsive img_home" onclick="modalConsultarSecre()">
        <img class="card-img-top " src="images/iconos/holter.png" alt="">
      </button>
      <div class="card-body">
        <h6 class="fontImage mercadopago_collapse_150px" align="center">Holter 24 hs</h6>      
      </div>        
  </div>
</div>


<div id="snackbar"><p>La receta ha sido solicitada</p></div>

@include('modal.modal_recetas')
@include('modal.snackbar')
@include('modal.modal_consultar_secretaria')

<script type="text/javascript">  

$.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  function modalRecetas(){
    var domicilio = document.getElementById("paciente_domicilio").value;    
    if((domicilio == null) || (domicilio.localeCompare('')==0)){
      var paciente_id = document.getElementById("paciente_id_receta").value;
      $('#modal_ingresar_domicilio_paciente_id').val(paciente_id);  
      $("#modalIngresarDomicilio").modal(); 
    } else { 
      $("#modalRecetas").modal(); 
    }
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

    function enviarSolicitudReceta(){
    var paciente_id = document.getElementById("paciente_id_receta").value;
    var medico_id = document.getElementById("medico_id_receta").value;
    var consultorio_id = document.getElementById("consultorio_id_receta").value;
    var motivo_receta = document.getElementById("motivo_receta").value;
    document.getElementById("motivo_receta").value = "";    

    $.ajax({
       type:'POST',
       dataType:'JSON',
       url:'/solicitar_nueva_receta',
       data:{paciente:paciente_id, medico:medico_id, consultorio:consultorio_id, motivo:motivo_receta ,_token: '{{csrf_token()}}'},
       success:function(data){      
          if(data.response == 1){
            mostrarSnackbarReceta();
          }                                                                     
       } 
    });
  }

  function modalConsultarSecre(){
    var telefono = document.getElementById("consultorio_telefono").value;
    document.getElementById("modalConsultarSecre_mensaje").innerHTML = "Para solicitar un turno debe comunicarse al telefono: <b>"+telefono+"</b>";
    $("#modalConsultarSecre").modal(); 
  }

    function mostrarSnackbarReceta() {
    var x = document.getElementById("snackbar");
    x.className = "show";
    setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
  }

</script>

@endsection