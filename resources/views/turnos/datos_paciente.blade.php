
@extends('turnos/modelo_plantilla')

@section('titulo_header','Registrar Paciente')

@section('descripcion_header','En esta sección deberá ingresar los datos del paciente.')
@section('headerContainer')
<ul>  
  @if($moduloActivarPaciente == 0)
  <li class="lead fontColorHeader">Si es la primera vez que utiliza el sitio, ingrese sus datos y click en "Continuar". sus datos serán guardados para poder realizar la reserva de su turno. </li>
  @endif
  @if($moduloActivarPaciente == 1)
  <li class="lead fontColorHeader">Si es la primera vez que utiliza el sitio, ingrese sus datos y click en "Registrarme". Una registración quedará pendiente y la secretaria se comunicará con usted para completarla y así asignar su turno. </li>
  @endif
  <li class="lead fontColorHeader">Si usted ya ha utilizado el sitio, solo deberá ingresar el DNI y click en "Continuar".</li>
  @if($especialidad == 1)
  <li class="lead fontColorHeader">Por urgencias o recien nacido llame al consultorio (Tel: {{$consultorio->telefono}}).</li>
  <li class="lead fontColorHeader">En caso del niño no contar con DNI, ingresar el DNI y número afiliado de su madre.</li>
  @endif
  <li class="lead fontColorHeader">¿No puedo registrarme? <a data-target="#modalAyuda" data-toggle="modal" class="MainNavText" id="MainNavHelp" 
       href="#modalAyuda">Click aquí</a></li>
</ul>

@endsection

@section('body_titulo','Datos del paciente:')

@section('contenedor')

<script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async></script>
<script>

  window.OneSignal = window.OneSignal || [];
  
  OneSignal.push(function() {
    // Inicialización
    OneSignal.init({
      appId: "87602532-8d15-4f44-9888-faec4e96673a", // Reemplaza con tu App ID
      autoRegister: true, // Registro automático de usuarios
      notifyButton: { enable: false }, // Opcional: Desactiva el botón flotante
      promptOptions: {
        /* Personaliza el mensaje de solicitud de permisos */
        slidedown: {
          enabled: true,
          autoPrompt: true,
          timeDelay: 3,
          pageViews: 1,
          message: "¡Activa notificaciones para recibir recordatorios semanales!",
          acceptButtonText: "ACTIVAR",
          cancelButtonText: "NO, GRACIAS"
        }
      }
    });

    // Obtener el user_id y enviarlo a tu backend
    OneSignal.getUserId().then(function(userId) {
      if (userId) {
        console.log("OneSignal User ID:", userId);
        
        /* Enviar el user_id a tu backend (ejemplo con Fetch API)
        fetch('https://tudominio.com/guardar-user-id', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ 
            user_id: userId,
            fecha_registro: new Date().toISOString() 
          })
        });*/
      }
    });
  });
</script>

<head>            
    <script src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{asset('js/jquery.min.js')}}"> </script>
</head>

<div class="row">
      <div class="col-md-6">
              
        <form method="POST" action="{{ route('altapaciente') }}" enctype="multipart/form-data">
          @csrf
          <input type="hidden" id="especialidad_id" name="especialidad_id" value="{{$especialidad_id}}"  />
          <input type="hidden" id="consultorio" name="consultorio" value="{{$medico->consultorio}}"  />
          <input type="hidden" id="consultorio_telefono" name="consultorio_telefono" value="{{$consultorio->telefono}}"  />
          <input type="hidden" id="medico_id" name="medico_id" value="{{$medico->id}}"  />          
          <input type="hidden" id="moduloActivarPaciente" name="moduloActivarPaciente" value="{{$moduloActivarPaciente}}"  />
          <input type="hidden" id="paciente_activo" name="paciente_activo" />
          <label for="text" class="col-sm-0 control-label editText">* DNI</label>      
          <input type="number" class="form-control editText" id="dni" name="dni" placeholder="DNI" value="{{old('input_dni')}}" onchange="validarPacienteExiste(this.value)" required/>
          {!! $errors->first('dni','<small>:message</small><br>') !!}

          <label for="text" class="col-sm-0 control-label editText" id="label_nombre">* Nombre</label>      
          <input onchange="validarDatos()" type="text" class="form-control editText" id="nombre" name="nombre"  placeholder="Nombre Paciente" value="" required />

          <label for="text" class="col-sm-0 control-label editText" id="label_apellido">* Apellido</label>      
          <input onchange="validarDatos()" type="text" class="form-control editText" id="apellido" name="apellido"  placeholder="Apellido Paciente" required />

          <label for="text" class="col-sm-0 control-label editText" id="label_fecha_nacimiento">* Fecha Nacimiento</label><br>
          <div class="row">
             <div class="fechaNacEditText">
              <input onchange="validarDatos()" type="text" maxlength="2" class="form-control editText" id="fecha_nacimiento_dia" name="fecha_nacimiento_dia"  placeholder="dd" />
            </div>
             <label for="text" class="col-sm-0 control-label editText margin5">/</label>
            <div class="fechaNacEditText">
              <input onchange="validarDatos()" type="text" maxlength="2" class="form-control editText" id="fecha_nacimiento_mes" name="fecha_nacimiento_mes"  placeholder="mm" />
            </div>
            <label for="text" class="col-sm-0 control-label margin5">/</label>
            <div class="fechaNacAnioEditText">   
              <input onchange="validarDatos()" type="text" maxlength="4" class="form-control editText" id="fecha_nacimiento_anio" name="fecha_nacimiento_anio"  placeholder="YYYY" />
            </div>
            <input type="hidden" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento"  />
         </div>

         <label for="text" class="col-sm-0 control-label editText" id="label_celular">* Celular</label>      
          <input onchange="validarDatos()" type="number" class="form-control editText" id="telefono" name="telefono"  placeholder="Telefono solo numeros (EJ: 2915050050)"  />

          <label for="text" class="col-sm-0 control-label editText">Localidad</label>      
          <input onchange="validarDatos()" type="text" class="form-control editText" id="localidad" name="localidad"  placeholder="Localidad"  />

          <label for="text" class="col-sm-0 control-label editText">Domicilio</label>      
          <input onchange="validarDatos()" type="text" class="form-control editText" id="domicilio" name="domicilio"  placeholder="Domicilio"  />

          <label for="text" class="col-sm-0 control-label editText">Mail</label>      
          <input onchange="validarDatos()" type="text" class="form-control editText" id="mail" name="mail" placeholder="Mail"  />

          <br>          
        
  </div>

  <div class="col-md-6">         
          <div id="seccionElegirObraSocial" class="form-group">
          <label for="obraSociales" id="label_obra_social">* Obra Social</label>
          <select onchange="validarObraSocial()" class="form-control" id="obraSociales" name="obraSociales">            
            <option>PARTICULAR</option>            
            @foreach($obraSociales as $os)
            <option>{{$os->nombre}}</option>            
            @endforeach
          </select>
          <label hidden id="obra_social_label_info" class="letrasrojo">El especialista NO trabaja con esta obra social</label>
          </div>

          <!--<div id="seccionTieneObraSocial" hidden>
          <label for="text" class="col-sm-0 control-label editText">Obra Social</label><button onclick="actualizarObraSocial()" type="button" class="sinBackgroundAzul editText">(Actualizar) </button>    
          <input type="text" class="form-control editText" id="obrasocial" name="obra_social" placeholder="Obra Social" disabled />
          </div>-->          
          <label for="text" class="col-sm-0 control-label editText" id="label_numero_afiliado">* N° Afiliado</label>                
          <input onchange="validarDatos()" type="text" class="form-control editText" id="numero_afiliado" name="numero_afiliado" placeholder="N° Afiliado"  />
          
          <label for="text" class="col-sm-0 control-label editText">Plan</label>      
          <input type="text" class="form-control editText" id="plan_obra_social" name="plan_obra_social" placeholder="Plan Obra Social"  />
          <div id="seccionTieneNoTieneFoto">
              <label for="text" class="col-sm-0 control-label editText">Foto Carnet Obra Social</label>      <br>
              <input type="file" id="foto" name="foto" placeholder="Foto Carnet Obra Social" />                            
          </div>
          <div id="seccionTieneFoto" hidden>            
              <label for="text" class="col-sm-0 control-label editText">Actualizar Foto Carnet </label>      <br>
              <input type="file" id="actualizar_foto" name="actualizar_foto" placeholder="Foto Carnet Obra Social" />                            
          </div>          
          <br>
          <label for="text" class="col-sm-0 control-label editText" id="label_primera_consulta">¿Es primera consulta?</label>      <br>                  
          <div class="form-check">                        
            <label class="form-check-label editText" for="materialUnchecked">Si</label>
            <input onchange="validarDatos()" type="radio" id="check_si" name="radio" value="1" required>
                        
            <label class="form-check-label editText" for="materialChecked">No</label>
            <input onchange="validarDatos()" type="radio" id="check_no" name="radio" value="0" required>
          </div>
          <br>
          <div class="form-check">
            <input onchange="validarDatos()" type="checkbox" class="form-check-input" id="terminos_condiciones_check" name="terminos_condiciones">
            <label class="form-check-label editText" for="exampleCheck1" id="label_terminos_condiciones">He leído y acepto los </label><br>
            <label class="form-check-label" for="exampleCheck1">
              <button type="button" class="sinBackgroundAzul editText" data-toggle="modal" data-target=".bd-example-modal-xl">Términos y Condiciones </button>

              <!--<a data-target=".bd-example-modal-xl">Términos y Condiciones </a></label>-->
          </div>
  </div>  

</div>
<br>
@if($moduloActivarPaciente == 0)
<div class="contenedor3">            
  <button hidden id="btnContinuar" class="rodri_button contenido3 fontNav">Continuar</button>
</div>
</form>
<div class="contenedor3">            
  <button disabled id="btnContinuarDisabled" class="rodri_button_disabled contenido3 fontNav">Continuar</button>
</div>
@else  
  <div class="contenedor3">            
    <button hidden id="btnContinuar" class="rodri_button contenido3 fontNav">Continuar</button>
  </div>
  </form>
  <div class="contenedor3">            
  <button hidden disabled id="btnContinuarDisabled" class="rodri_button_disabled contenido3 fontNav">Continuar</button>
  </div>
  <div class="contenedor3">            
    <button id="btnRegistrarme" onclick="registrarPacientePendiente()" class="rodri_button contenido3 fontNav">Registrarme</button>
  </div>
    <div class="contenedor3">            
    <button hidden disabled id="btnRegistrarmeDisabled" onclick="registrarPacientePendiente()" class="rodri_button_disabled contenido3 fontNav">Registrarme</button>
  </div>
@endif

<div class="contenedor3">            
  <h4 id="registroPendiente" hidden class="contenido3 display-4 fontColorHeader">Registración pendiente</h4>
</div>

<div class="modal fade" id="modalAyuda" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title" 
        id="favoritesModalLabel">Ayuda para registrarme</h4>
      </div>
      <div class="modal-body"> 
          <h4>Campos que debo completar:</h4><br>
          <h6><b>Nombre.</b></h6>
          <h6><b>Apellido.</b></h6>    
          <h6><b>DNI:</b> No debe incluir puntos, solo debe contener números.</h6>
          <h6><b>Fecha Nacimiento:</b> Año debe contener 4 digitos. Ej: 2020.</h6>
          <h6><b>Teléfono:</b> Solo debe contener números.</h6>
          <h6><b>Terminos y condiciones:</b> Debe ser tildado.</h6>
      </div>
      <div class="modal-footer">
        <button type="button" 
           class="rodri_button_aceptar" 
           data-dismiss="modal">Aceptar</button>                                      
      </div>                                      
      </div>
    </div>
</div>

@include('turnos.terminos_condiciones_modal')
@include('modal.modal_no_acepta_mas_pacientes')
@include('modal.modal_no_trabaja_obra_social')

<div class="modal fade" id="pacienteRegistradoPendiente" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Datos registrados correctamente:</h4>
      </div>      
       <div class="modal-body">
        <!--<img id="modalImagenMensaje" class="card-img-top" src="images/iconos/ic_ok.png" alt="">                       -->        
        <h6>La secretaria se pondrá en contacto a través de un mail o un llamado telefónico y luego podrá elegir sus turnos.</h6><br>        
        <p>Muchas Gracias!</p>                
       </div>
      <div class="modal-footer">
        <button onclick="irHome()" class="rodri_button_aceptar" data-dismiss="modal">Finalizar</button>            
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalPacienteBloqueado" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleBloqueado">Paciente Bloqueado</h4>
      </div>      
       <div class="modal-body">
        <!--<img id="modalImagenMensaje" class="card-img-top" src="images/iconos/ic_ok.png" alt="">                       -->        
        <h6>El paciente se encuentra bloqueado. Para solicitar un turno comuniquese al teléfono {{$consultorio->telefono}}</h6><br>        
        <p>Muchas Gracias!</p>                
       </div>
      <div class="modal-footer">
        <button onclick="irHome()" class="rodri_button_aceptar" data-dismiss="modal">Finalizar</button>            
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">  

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

function irHome(){
  location.href ="homes";
}

function soloNumeros(num){
 for (i = 0; i < num.length; i++) {
  if(num.charAt(i)=='.')
    return false;
 }
 return true;
}

function invocarTerminosCondiciones(){
  $('#modalTerminosCondiciones').modal();
}

function validarDatosClass(label, value) {
  var ob = document.getElementById(label);
  if(value) {    
    ob.setAttribute('class', 'col-sm-0 control-label editText letrasrojo');
  } else {    
    ob.setAttribute('class', 'col-sm-0 control-label editText');
  }
}

function validarObraSocial() {
  var obra_social = document.getElementById("obraSociales").value;
  var medico_id = document.getElementById("medico_id").value;    
  $.ajax({
         type:'POST',
         dataType:'JSON',
         url:'/verificar_obra_social_medico',
         data:{obra_social :obra_social,medico_id:medico_id, _token: '{{csrf_token()}}'},
         success:function(data) {                            
            if(data.response == 0) {
              document.getElementById("obra_social_label_info").hidden = true;
            }
            else {
              document.getElementById("obra_social_label_info").hidden = false;
            }
           }
      });
}

function validarDatos() {    
      var moduloActivarPaciente = document.getElementById("moduloActivarPaciente").value; 
      var nombre = document.getElementById("nombre").value;
      var apellido = document.getElementById("apellido").value;
      var dni = document.getElementById("dni").value;
      var telefono = document.getElementById("telefono").value;
      var mail = document.getElementById("mail").value;
      var fecha_nacimiento_dia = document.getElementById("fecha_nacimiento_dia").value;     
      var fecha_nacimiento_mes = document.getElementById("fecha_nacimiento_mes").value;     
      var fecha_nacimiento_anio = document.getElementById("fecha_nacimiento_anio").value;
      var paciente_activo = document.getElementById("paciente_activo").value;
      var terminos_condiciones = document.getElementById("terminos_condiciones_check").checked;
      var faltanDatos = 0;
      if(dni == null || (dni.localeCompare('')==0) || !soloNumeros(dni)) faltanDatos = 1;
      

      var check_si = document.getElementById("check_si").checked;
      var check_no = document.getElementById("check_no").checked;
      if(check_si == false && check_no == false) {
        validarDatosClass("label_primera_consulta", true);
      } else {
        validarDatosClass("label_primera_consulta", false);
      }

      if(nombre == null || (nombre.localeCompare('')==0)) {        
        faltanDatos = 1
        validarDatosClass("label_nombre", true);
      } else {
        validarDatosClass("label_nombre", false);
      };

      if(apellido == null || (apellido.localeCompare('')==0)) {        
        faltanDatos = 1
        validarDatosClass("label_apellido", true);
      } else {
        validarDatosClass("label_apellido", false);
      };

      if(telefono == null || (telefono.localeCompare('')==0)){        
        faltanDatos = 1
        validarDatosClass("label_celular", true);
      } else {
        validarDatosClass("label_celular", false);
      };

      if(fecha_nacimiento_dia == null || (fecha_nacimiento_dia.localeCompare('')==0)){        
        faltanDatos = 1
        validarDatosClass("label_fecha_nacimiento", true);
      } else {
        validarDatosClass("label_fecha_nacimiento", false);
      };

      if(fecha_nacimiento_mes == null || (fecha_nacimiento_mes.localeCompare('')==0)){        
        faltanDatos = 1
        validarDatosClass("label_fecha_nacimiento", true);
      } else {
        validarDatosClass("label_fecha_nacimiento", false);
      };

      if(fecha_nacimiento_anio == null || (fecha_nacimiento_anio.localeCompare('')==0) || (fecha_nacimiento_anio.length != 4)){        
        faltanDatos = 1
        validarDatosClass("label_fecha_nacimiento", true);
      } else {
        validarDatosClass("label_fecha_nacimiento", false);
      };

      var medico = document.getElementById("medico_id").value;
      var numero_afiliado = document.getElementById("numero_afiliado").value;
      var obra_social = document.getElementById("obraSociales").value;
      if(numero_afiliado == null || (medico == 1 && obra_social.localeCompare('PARTICULAR') != 0  && numero_afiliado.localeCompare('') == 0 )) {        
        faltanDatos = 1
        validarDatosClass("label_numero_afiliado", true);
      } else {
        validarDatosClass("label_numero_afiliado", false);
      };

     if(terminos_condiciones == false ) {
        faltanDatos = 1        
        validarDatosClass("label_terminos_condiciones", true);
     } else {
        validarDatosClass("label_terminos_condiciones", false);
     }
     
     if(moduloActivarPaciente == 0){
      document.getElementById("btnContinuar").hidden = true;
      document.getElementById("btnContinuarDisabled").hidden = true;   
      document.getElementById("registroPendiente").hidden = true;
      if(faltanDatos == 1){
        if((paciente_activo == 1) && (terminos_condiciones)){
          document.getElementById("btnContinuar").hidden = false;   
        } else {
          document.getElementById("btnContinuarDisabled").hidden = false;   
        }
      }
      if(faltanDatos == 0) {
        document.getElementById("fecha_nacimiento").value = fecha_nacimiento_anio+"-"+fecha_nacimiento_mes+"-"+fecha_nacimiento_dia;document.getElementById("btnContinuar").hidden = false;     
      }
    }

    if(moduloActivarPaciente == 1) {
      document.getElementById("btnContinuar").hidden = true;
      document.getElementById("btnRegistrarme").hidden = true;
      document.getElementById("btnRegistrarmeDisabled").hidden = true; 
      document.getElementById("registroPendiente").hidden = true;
      document.getElementById("btnContinuarDisabled").hidden = true;
      if(faltanDatos == 1) { // faltan datos SI
        if(paciente_activo == 1) {
          if(!terminos_condiciones) {
            document.getElementById("btnContinuarDisabled").hidden =false; // probar este caso. 
          }
        } else {
          if(paciente_activo == 2) {
            document.getElementById("registroPendiente").hidden =false; 
          } else {
            document.getElementById("btnRegistrarmeDisabled").hidden = false; 
          } 
        }
      } 
      if(faltanDatos == 0) { // faltan datos NO
        document.getElementById("fecha_nacimiento").value = fecha_nacimiento_anio+"-"+fecha_nacimiento_mes+"-"+fecha_nacimiento_dia;
        if(paciente_activo == 1) {
          document.getElementById("btnContinuar").hidden = false; 
        } else {
        if(paciente_activo == 2) {
          document.getElementById("registroPendiente").hidden = false;         
        } else{
          document.getElementById("btnRegistrarme").hidden = false;
        }
      }
      }
    }
}

function registrarPacientePendiente(){  
    var nombre = document.getElementById("nombre").value;
    var apellido = document.getElementById("apellido").value;
    var dni = document.getElementById("dni").value;
    var telefono = document.getElementById("telefono").value;
    var domicilio = document.getElementById("domicilio").value;
    var localidad = document.getElementById("localidad").value;
    var mail = document.getElementById("mail").value;
    //var obrasocial = document.getElementById("obrasocial").value; 
    var obrasocial = document.getElementById("obraSociales").value;     
    var numero_afiliado = document.getElementById("numero_afiliado").value; 
    var plan = document.getElementById("plan_obra_social").value;         
    var consultorio = document.getElementById("consultorio").value;     
    var terminos_condiciones = document.getElementById("terminos_condiciones_check").checked;  
    var fecha_nacimiento_dia = document.getElementById("fecha_nacimiento_dia").value;     
    var fecha_nacimiento_mes = document.getElementById("fecha_nacimiento_mes").value;     
    var fecha_nacimiento_anio = document.getElementById("fecha_nacimiento_anio").value;
    var fecha_nacimiento = null;
    if((fecha_nacimiento_dia!=null)&&(fecha_nacimiento_dia.localeCompare('')!=0)){
      var fecha_nacimiento = fecha_nacimiento_anio+"/"+fecha_nacimiento_mes+"/"+fecha_nacimiento_dia;
    }    
    $.ajax({
         type:'POST',
         dataType:'JSON',
         url:'/registrar_paciente_pendiente',
         data:{dni :dni,nombre:nombre,apellido:apellido,fecha_nacimiento:fecha_nacimiento,telefono:telefono,mail:mail,obra_social:obrasocial,numero_afiliado:numero_afiliado,plan:plan,consultorio:consultorio, terminos_condiciones:terminos_condiciones,domicilio:domicilio, localidad:localidad, _token: '{{csrf_token()}}'},
         success:function(data){                  
              $('#pacienteRegistradoPendiente').modal();
           }
      });
  
  }

  function verificarPacienteBloquedo(paciente_id) {    
    var medico_id = document.getElementById("medico_id").value;    
    $.ajax({
         type:'POST',
         dataType:'JSON',
         url:'/verificar_paciente_bloqueado',
         data:{paciente_id:paciente_id, medico_id:medico_id, _token: '{{csrf_token()}}'},
         success:function(data){                       
              if(data.response == 1){                
                document.getElementById("btnContinuar").hidden = true; 
                document.getElementById("btnContinuarDisabled").hidden = false;
                
                $('#modalPacienteBloqueado').modal({backdrop: 'static', keyboard: false});
              } else {                
                document.getElementById("btnContinuar").hidden = false; 
                document.getElementById("btnContinuarDisabled").hidden = true;
              }
           }
      });
  }

 function validarPacienteExiste(){        
    var dni = document.getElementById("dni").value; 
    $.ajax({
       type:'POST',
       dataType:'JSON',
       url:'/paciente_consultar',
       data:{dni_paciente :dni, _token: '{{csrf_token()}}'},
       success:function(data){        
                
          var nombre = document.getElementById("nombre");
          var apellido = document.getElementById("apellido");
          var dni = document.getElementById("dni");
          var telefono = document.getElementById("telefono");
          var domicilio = document.getElementById("domicilio");
          var localidad = document.getElementById("localidad");
          var mail = document.getElementById("mail");
          var obrasocial = document.getElementById("obrasocial");          
          var fecha_nacimiento_dia = document.getElementById("fecha_nacimiento_dia");
          var fecha_nacimiento_mes = document.getElementById("fecha_nacimiento_mes");
          var fecha_nacimiento_anio = document.getElementById("fecha_nacimiento_anio");          
          var plan_obra_social = document.getElementById("plan_obra_social");
          var numero_afiliado = document.getElementById("numero_afiliado");                    
          var moduloActivarPaciente = document.getElementById("moduloActivarPaciente").value; 
          var paciente_activo = document.getElementById("paciente_activo"); 
          var terminos_condiciones = document.getElementById("terminos_condiciones_check");          
          var obra_social_opciones = document.getElementById("obraSociales");
          numero_afiliado.disabled = false;
          plan_obra_social.disabled = false; 
           if(data.paciente != null) {     
              verificarPacienteBloquedo(data.paciente.id);   
              validarTrabajaObraSocial();      
              nombre.value = data.paciente.nombre;
              apellido.value = data.paciente.apellido;
              dni.value = data.paciente.dni;
              telefono.value = data.paciente.telefono;
              domicilio.value = data.paciente.domicilio;
              localidad.value = data.paciente.localidad;
              mail.value = data.paciente.mail;                        
              if(data.paciente.obra_social.length > 0){               
                obra_social_opciones.value = data.paciente.obra_social;                              
              }
              numero_afiliado.value = data.paciente.numero_afiliado;              
              plan_obra_social.value = data.paciente.obra_social_plan;
              paciente_activo.value = data.paciente.activo;              
              if(data.paciente.obra_social_foto.localeCompare('') != 0) {
                  document.getElementById("seccionTieneFoto").hidden = false;
                  document.getElementById("seccionTieneNoTieneFoto").hidden = true;
              } else {
                  document.getElementById("seccionTieneFoto").hidden = true;
                  document.getElementById("seccionTieneNoTieneFoto").hidden = false;
              }

              if(data.paciente.terminos_condiciones == 1)
                terminos_condiciones.checked = true;
              else
                terminos_condiciones.checked = false;
              if(data.paciente.fecha_nacimiento.localeCompare('') != 0) {
                var arrayFechaNacimiento = data.paciente.fecha_nacimiento.split('-');
                 if(arrayFechaNacimiento[0].localeCompare("1000") == 0) {
                  fecha_nacimiento_anio.value = "";
                  fecha_nacimiento_mes.value = "";
                  fecha_nacimiento_dia.value = "";
                 } else {
                  fecha_nacimiento_anio.value = arrayFechaNacimiento[0];
                  fecha_nacimiento_mes.value = arrayFechaNacimiento[1];
                  fecha_nacimiento_dia.value = arrayFechaNacimiento[2];
                }
              } 

              if(data.primerControl > 0) {
                 document.getElementById("check_no").checked=true;
              }
              else {
                 document.getElementById("check_si").checked=false;
                 document.getElementById("check_no").checked=false; 
              }
              
              if(data.paciente.activo == 1) {
                if(data.paciente.terminos_condiciones == 1) {
                 // alert("1");
                  document.getElementById("btnContinuar").hidden=false; 
                  document.getElementById("btnContinuarDisabled").hidden=true;
                  if(document.getElementById("btnRegistrarme")!=null)                  
                    document.getElementById("btnRegistrarme").hidden = true;   
                  document.getElementById("registroPendiente").hidden=true;
                } else {
                  //alert("2");
                  document.getElementById("btnContinuar").hidden=true;
                  document.getElementById("btnContinuarDisabled").hidden=false;     
                  document.getElementById("btnRegistrarme").hidden=true;   
                  document.getElementById("registroPendiente").hidden=true;
                }
              } else {
                if(moduloActivarPaciente == 0) {
                 // alert("3");
                    document.getElementById("btnContinuar").hidden=false;
                    document.getElementById("btnContinuarDisabled").hidden=true;   
                    document.getElementById("registroPendiente").hidden=true; 
                } else {  
               // alert("4");             
                  document.getElementById("btnContinuar").hidden=true;  
                  document.getElementById("btnRegistrarme").hidden=true;  
                  document.getElementById("registroPendiente").hidden=false;
                }  
              }

            } else {  // en caso de que el paciente no existe.
                validarNoAceptaMasPacientes();               
                if(moduloActivarPaciente == 0) {
                  //alert("5");
                    // debo mostrarboton continuar habilitado
                    document.getElementById("btnContinuar").hidden=true; 
                    document.getElementById("btnContinuarDisabled").hidden=false;    
                    document.getElementById("registroPendiente").hidden=true; 
                } else {
                  //alert("6");
                  // debo mostrar boton registrarme habilitado.
                  document.getElementById("btnContinuar").hidden=true;  
                  document.getElementById("btnRegistrarme").hidden=false;  
                  document.getElementById("registroPendiente").hidden=true;  
                  document.getElementById("btnContinuarDisabled").hidden=true;
                }
               vaciarCampos();
            }                        
         }
    });       
  }

  function validarNoAceptaMasPacientes(){    
    var medico_id = document.getElementById("medico_id").value;
    if(medico_id == 6){
      $('#modalNoAceptaMasPacientes').modal();
    }
  }

  // En caso de que el medico no trabaje con esa obra social y el paciente tenga
  // precargada esa obra social se le mostrar un cartel informando que el profesional
  // no trabaja con esa obra social
  function validarTrabajaObraSocial(){
    var dni = document.getElementById("dni").value;
    var medico_id = document.getElementById("medico_id").value;    
    $.ajax({
         type:'POST',
         dataType:'JSON',
         url:'/verificar_paciente_medico_obra_social',
         data:{dni:dni, medico_id:medico_id, _token: '{{csrf_token()}}'},
         success:function(data){                 
              if(data.response == 1){
                var telefono_consultorio = document.getElementById("consultorio_telefono").value;
                var p = document.getElementById("modal_texto_medico_obra_social");
                if(data.medico.sexo.localeCompare("F") == 0)
                   p.innerHTML = "La especialista <b>NO</b> trabaja con la obra social <b>"+data.paciente.obra_social+"</b>. Si necesita más información comunicarse con el consultorio TEL: "+telefono_consultorio+". <br> Muchas Gracias";
                 else
                   p.innerHTML = "El especialista <b>NO</b> trabaja con la obra social <b>"+data.paciente.obra_social+"</b>. Si necesita más información comunicarse con el consultorio TEL: "+telefono_consultorio+". <br> Muchas Gracias";
                 
                $('#modalNoTrabajaObraSocial').modal();
              }
           }
      });
  }

  function actualizarObraSocial(){
    document.getElementById("seccionElegirObraSocial").hidden = false;
    document.getElementById("seccionTieneObraSocial").hidden = true;
    document.getElementById("seccionTieneNoTieneFoto").hidden = false;
    document.getElementById("seccionTieneFoto").hidden = true;
    document.getElementById("plan_obra_social").disabled = false;
    document.getElementById("numero_afiliado").disabled = false;
  }

  function vaciarCampos() {
    nombre.value = '';
    apellido.value = '';              
    telefono.value = '';
    domicilio.value = '';
    localidad.value = '';
    mail.value = '';
    //obrasocial.value = '';    
    numero_afiliado.value = '';              
    plan_obra_social.value = '';              
    fecha_nacimiento_dia.value = '';  
    fecha_nacimiento_mes.value = '';             
    fecha_nacimiento_anio.value = '';
    document.getElementById("check_si").checked=false;
    document.getElementById("check_no").checked=false; 
    paciente_activo.value = 0;
    document.getElementById("terminos_condiciones_check").checked = false;
    document.getElementById("seccionTieneFoto").hidden = true;
    document.getElementById("seccionTieneNoTieneFoto").hidden = false;      
    document.getElementById("obraSociales").value = "PARTICULAR";          
  }

</script>
@endsection

@section('contenedorFooter')
<p class="terminosCondicionesSize">Las solapas con * son de llenado obligatorio para completar la registración.
Las solapas sin * son facultativas.
Mediante el presente dejo constancia que he sido debidamente informado respecto de la finalidad para la cual han sido recabados mis datos personales y quiénes pueden ser sus destinatarios, como así también del derecho que me asiste a tener acceso a ellos, como así a peticionar su rectificación y/o supresión, todo ello conforme a lo normado en la Ley Nacional N° 25326 (Protección de Datos Personales) y su Decreto Reglamentario N° 1558/01. </p><br>
@endsection
