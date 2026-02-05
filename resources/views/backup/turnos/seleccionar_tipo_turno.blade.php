
@extends('turnos/modelo_plantilla')

@section('titulo_header','Seleccionar Tipo Turno o Solicitud de Receta')

@if($medico->id == 11)
  @section('headerContainer')
      
      <p style="color: white;font-size: 1.4rem;">Si tu consulta es por control anual y preferís las consultas presenciales saca dos turnos separados de 1 mes y medio para realizar el control y revisar luego los resultados. </p>    
  @endsection  
@else
  @section('descripcion_header','En esta sección deberá elegir el tipo de turno y podrá solicitar una receta.')
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
    <form method="POST" action="{{ route('tipoturno') }}">
        @csrf
        <input type="hidden" name="tipo_turno" value="1"  />           
        <input type="hidden" name="medico_id" value="{{$medico->id}}"  />           
        <input type="hidden" name="consultorio_id" value="{{$consultorio->id}}"  />                     
        <input type="hidden" name="paciente_id" value="{{$paciente->id}}"  />                             
        <input type="hidden" name="primerControl" value="{{$primerControl}}"  />                               
        <input type="hidden" name="moduloRecetas" value="{{$moduloRecetas}}"  />                     
        <button class="btn btn-primary-outline img-responsive img_home">
          <img class="card-img-top " src="images/iconos/turno_comun.png" alt="">
        </button>
        <div class="card-body">
          <h6 class="fontImage" align="center">Presencial</h6>      
        </div>        
    </form>
    @if($moduloVideollamadas == 1) 
     <form method="POST" action="{{ route('tipoturno') }}">
        @csrf
        <input type="hidden" name="tipo_turno" value="2"  />           
        <input type="hidden" name="medico_id" value="{{$medico->id}}"  />           
        <input type="hidden" name="consultorio_id" value="{{$consultorio->id}}"  />                     
        <input type="hidden" name="paciente_id" value="{{$paciente->id}}"  />                             
        <input type="hidden" name="primerControl" value="{{$primerControl}}"  />                               
        <input type="hidden" name="moduloRecetas" value="{{$moduloRecetas}}"  />
        <button class="btn btn-primary-outline img-responsive img_home">
          <img class="card-img-top " src="images/iconos/turno_videollamada.png" alt="">
        </button>
        <div class="card-body">
          <h6 class="fontImage" align="center">Videollamada</h6>      
        </div>        
    </form>
    @endif
    @if($moduloRecetas == 1 && $medico->id != 1) 
        <div>  
          <input type="hidden" name="paciente_obra_social_numero" id="paciente_obra_social_numero" value="{{$paciente->numero_afiliado}}" />
          <input type="hidden" name="paciente_domicilio" id="paciente_domicilio" value="{{$paciente->domicilio}}" />
          <input type="hidden" name="paciente_id_receta" id="paciente_id_receta" value="{{$paciente->id}}"  /> 
          <input type="hidden" name="medico_id_receta" id="medico_id_receta" value="{{$medico->id}}"  /> 
          <input type="hidden" name="consultorio_id_receta" id="consultorio_id_receta" value="{{$consultorio->id}}"  />           
          <!--<button onclick="modalRecetas()" class="rodri_button_receta divMarginCel botonReceta">-->
          <button onclick="modalRecetas()" class="btn btn-primary-outline img-responsive img_home">
               <img class="card-img-top " src="images/iconos/receta2.png" alt="">
               <!--<img class="card-img-top" src="images/iconos/receta1.png"/></button>-->           
           </button>  
          <div class="card-body">
            <h6 class="fontImage" align="center">Recetas</h6>      
          </div>        
      </div>      
    @endif
     @if($medico->id == 1) 
        <div>                    
          <!--<button onclick="modalRecetas()" class="rodri_button_receta divMarginCel botonReceta">-->
          <button onclick="modalConsultaPrenatal()" class="btn btn-primary-outline img-responsive img_home">
               <img class="card-img-top " src="images/iconos/consulta_prenatal.png" alt="">
               <!--<img class="card-img-top" src="images/iconos/receta1.png"/></button>-->           
           </button>  
          <div class="card-body">
            <h6 class="fontImage" align="center">Consulta Prenatal</h6>      
          </div>        
      </div>

      <div>            
          <a href="https://api.whatsapp.com/send?phone=542916450538" target="_blank" class="btn btn-primary-outline img-responsive img_home">
               <img class="card-img-top " src="images/iconos/receta2.png" alt="">
               <!--<img class="card-img-top" src="images/iconos/receta1.png"/></button>-->           
           </a>  
          <div class="card-body">
            <h6 class="fontImage" align="center">Recetas</h6>      
          </div>        
      </div>
    @endif

    @if($medico->id == 11) 
        <form method="POST" hidden action="{{ route('tipoturno') }}">
          @csrf
          <input type="hidden" name="tipo_turno" value="22"  />           
          <input type="hidden" name="medico_id" value="{{$medico->id}}"  />           
          <input type="hidden" name="consultorio_id" value="{{$consultorio->id}}"  />                     
          <input type="hidden" name="paciente_id" value="{{$paciente->id}}"  />                             
          <input type="hidden" name="primerControl" value="{{$primerControl}}"  />                               
          <input type="hidden" name="moduloRecetas" value="{{$moduloRecetas}}"  />
          <button class="btn btn-primary-outline img-responsive img_home">
            <img class="card-img-top " src="images/iconos/turno_videollamada.png" alt="">
          </button>
          <div class="card-body">
            <h6 class="fontImage" align="center">Consulta Virtual</h6>      
          </div>        
      </form>
    @endif

    @if($medico->id == 2) 
        <div>                    
          <!--<button onclick="modalRecetas()" class="rodri_button_receta divMarginCel botonReceta">-->
          <button onclick="modalDeportologia()" class="btn btn-primary-outline img-responsive img_home">
               <img class="card-img-top " src="images/iconos/deportologia_icono.png" alt="">
               <!--<img class="card-img-top" src="images/iconos/receta1.png"/></button>-->           
           </button>  
          <div class="card-body">
            <h6 class="fontImage" align="center">Deportologia</h6>      
          </div>        
      </div>
    @endif

    @if($medico->id == 13)
    <form method="POST" hidden action="{{ route('tipoturno') }}">
          @csrf
          <input type="hidden" name="tipo_turno" value="23"  />           
          <input type="hidden" name="medico_id" value="{{$medico->id}}"  />           
          <input type="hidden" name="consultorio_id" value="{{$consultorio->id}}"  />                     
          <input type="hidden" name="paciente_id" value="{{$paciente->id}}"  />                             
          <input type="hidden" name="primerControl" value="{{$primerControl}}"  />                               
          <input type="hidden" name="moduloRecetas" value="{{$moduloRecetas}}"  />
          <button class="btn btn-primary-outline img-responsive img_home">
            <img class="card-img-top " src="images/iconos/turno_videollamada.png" alt="">
          </button>
          <div class="card-body">
            <h6 class="fontImage" align="center">Ecografias Tiroideas</h6>  
          </div>        
      </form>         
    @endif

</div>

<div id="snackbar"><p>La receta ha sido solicitada</p></div>

@include('modal.modal_recetas')
@include('modal.modal_mensaje_recetas_flor')
@include('modal.snackbar')
@include('modal.modal_coronavirus')
@include('modal.modal_consulta_prenatal')
@include('modal.modal_mensaje')

<script type="text/javascript">  

$.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  function modalConsultaPrenatal(){
    $("#modalConsultaPrenatal").modal(); 
  }

  function modalConsultaTelemedicina() {
    $("#modalConsultaTelemedicina").modal(); 
  }

  function modalDeportologia(){
    $("#modalDeportologia").modal();     
  }

  function modalMensajeClick() {
    $("#modalRecetas").modal(); 
  }

  function modalRecetas() {
    var tieneNumeroAfiliado = false;
    var tieneDomicilio = false;

    var domicilio = document.getElementById("paciente_domicilio").value;    
    if((domicilio == null) || (domicilio.localeCompare('')==0)){
      var paciente_id = document.getElementById("paciente_id_receta").value;
      $('#modal_ingresar_domicilio_paciente_id').val(paciente_id);  
      $("#modalIngresarDomicilio").modal(); 
    } else { 
      tieneDomicilio = true;      
    }

    var numero_afiliado = document.getElementById("paciente_obra_social_numero").value;
    if((numero_afiliado == null) || (numero_afiliado.localeCompare('')==0)) {
      var paciente_id = document.getElementById("paciente_id_receta").value;
      $('#modal_ingresar_numero_afiliado_paciente_id').val(paciente_id);  
      $("#modalIngresarNumeroAfiliado").modal(); 
    } else {
      tieneNumeroAfiliado = true;
    }
    
    var medicoId = document.getElementById("medico_id_receta").value;
    if(tieneDomicilio && tieneNumeroAfiliado) {
      if(medicoId == 1) {        
        $("#modalMensajeFlorReceteas").modal(); 
      } else {
        // if(medicoId == 11) {                
        //  $("#modalMensajeCeciVacaciones").modal();   
        //} else {
        $("#modalRecetas").modal(); 
        //}
      }
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
            modalRecetas(); 
          }                                                                     
       } 
    });      
  }

  function cargarNumeroAfiliadoAccion(){
    var paciente_id = document.getElementById("modal_ingresar_numero_afiliado_paciente_id").value;
    var numero_afiliado = document.getElementById("modal_ingresar_numero_afiliado_paciente_na").value;
    $.ajax({
       type:'POST',
       dataType:'JSON',
       url:'/cargar_numero_afiliado_paciente',
       data:{paciente_id:paciente_id, numero_afiliado:numero_afiliado ,_token: '{{csrf_token()}}'},
       success:function(data){      
          if(data.response == 1){
            document.getElementById("paciente_obra_social_numero").value = data.paciente.numero_afiliado;
            modalRecetas(); 
          }                                                                     
       } 
    });
  }

    function enviarSolicitudReceta(){
    var paciente_id = document.getElementById("paciente_id_receta").value;
    var medico_id = document.getElementById("medico_id_receta").value;
    var consultorio_id = document.getElementById("consultorio_id_receta").value;
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

    function mostrarSnackbarReceta() {
    var x = document.getElementById("snackbar");
    x.className = "show";
    setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
  }

</script>

@endsection





