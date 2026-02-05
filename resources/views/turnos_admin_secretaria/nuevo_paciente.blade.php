@extends('turnos_admin_secretaria/turnos_admin_secretaria_plantilla')

@section('contenedor')
<head>            
    <script src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{asset('js/jquery.min.js')}}"> </script>
</head>
<h2> Datos del paciente:</h2>
<div class="row">
    
      <div class="col-md-6">
      <form>            
          @csrf          
          <input type="hidden" id="consultorio" name="consultorio" value="{{$consultorio}}"/>
          <input type="hidden" id="moduloAfiliadoObligatorio" name="moduloAfiliadoObligatorio" value="{{$moduloAfiliadoObligatorio}}">

          <label id="label_dni" for="text" class="col-sm-0 control-label">* DNI</label>      
           <input type="text" class="form-control" id="dni" name="dni" placeholder="DNI" value="" onchange="validarPacienteExiste()" required/>
          <label id="label_nombre" for="text" class="col-sm-0 control-label">* Nombre</label>      
          <input type="text" class="form-control" id="nombre" name="nombre"  placeholder="Nombre Paciente" value="" required />

          <label id="label_apellido" for="text" class="col-sm-0 control-label">* Apellido</label>      
          <input type="text" class="form-control" id="apellido" name="apellido"  placeholder="Apellido Paciente" required />

           <label for="text" class="col-sm-0 control-label">Fecha Nacimiento</label><br>
          <div class="row">
             <div class="col-md-2">
              <input type="text" maxlength="2" class="form-control" id="fecha_nacimiento_dia" name="fecha_nacimiento"  placeholder="dd" required />
            </div>
             <label for="text" class="col-sm-0 control-label">/</label>
            <div class="col-md-2">
              <input type="text" maxlength="2" class="form-control" id="fecha_nacimiento_mes" name="fecha_nacimiento"  placeholder="mm" required />
            </div>
            <label for="text" class="col-sm-0 control-label">/</label>
            <div class="col-md-3">   
              <input type="text" maxlength="4" class="form-control" id="fecha_nacimiento_anio" name="fecha_nacimiento"  placeholder="YYYY" required />
            </div>
         </div>


          <label id="label_telefono" for="text" class="col-sm-0 control-label">* Telefono</label>      
          <input type="text" class="form-control" id="telefono" name="telefono"  placeholder="Telefono solo numeros (EJ: 2915050050)"  />

          <label for="text" class="col-sm-0 control-label">Domicilio</label>      
          <input type="text" class="form-control" id="domicilio" name="domicilio"  placeholder="Domicilio"  />

          <label for="text" class="col-sm-0 control-label">Localidad</label>      
          <input type="text" class="form-control" id="localidad" name="localidad"  placeholder="Localidad"  />

          <label for="text" class="col-sm-0 control-label">Mail</label>      
          <input type="text" class="form-control" id="mail" name="mail" placeholder="Mail"  />
          
          <br>          
        
  </div>

  <div class="col-md-6">
            <input type="hidden" id="moduloAfiliadoObligatorioId" value="{{$moduloAfiliadoObligatorio}}">
     @if($moduloAfiliadoObligatorio == 1)                                  
              <label id="label_afiliado_obligatorio" for="text" class="col-sm-0 control-label editText">* ¿Es afiliado obligatorio?</label>      <br>                  
              <div class="form-check">                        
                <label class="form-check-label editText" for="materialUnchecked">Si</label>
                <input type="radio" id="check_afiliado_obligatorio_si" name="radio1" value="1" required>
                            
                <label class="form-check-label editText" for="materialChecked">No</label>
                <input type="radio" id="check_afiliado_obligatorio_no" name="radio1" value="0" required>
              </div>
              <br> 
          @endif

          <label for="text" class="col-sm-0 control-label">Obra Social</label>      
          <select class="form-control" id="obrasocial" name="obra_social">            
            <option>N/A</option>            
            @foreach($obraSociales as $os)
            <option>{{$os->nombre}}</option>            
            @endforeach
          </select>
          <!--<input type="text" class="form-control" id="obrasocial" name="obra_social" placeholder="Obra Social"  /> -->

          <label id="label_numero_afiliado" for="text" class="col-sm-0 control-label">* N° Afiliado</label>      
          <input type="text" class="form-control" id="numero_afiliado" name="numero_afiliado" placeholder="N° Afiliado"  />
          @if($moduloAfiliadoObligatorio == 1)
            <label id="label_plan" for="text" class="col-sm-0 control-label">* Plan</label>                  
          @else
            <label id="label_plan" for="text" class="col-sm-0 control-label">Plan</label>                  
          @endif
          <input type="text" class="form-control" id="plan_obra_social" name="plan_obra_social" placeholder="Plan Obra Social"  />
             <br>
           <label for="text" class="col-sm-0 control-label">(*) Campos Requeridos</label> 
  </div>  

</div>
<div class="contenedor3">            
  <button onclick="altaPaciente()" type="button" id="btnContinuar" class="rodri_button contenido3">Registrar</button>
</div>
</form>
<div class="modal fade" id="mensajeModal" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Registrado:</h4>
      </div>      
       <div class="modal-body">
        <!--<img id="modalImagenMensaje" class="card-img-top" src="images/iconos/ic_ok.png" alt="">                       -->        
        <h6>El paciente ha sido registrado!</h6><br>                            
       </div>
      <div class="modal-footer">
        <button class="rodri_button_aceptar" data-dismiss="modal">Aceptar</button>            
      </div>
    </div>
  </div>
</div>

@include('modal.snackbar')
<div id="snackbar"><p id="snackbar_text"></p></div>

<script type="text/javascript">
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  function mostrarSnackbar(cs) {      
      document.getElementById("snackbar_text").innerHTML = cs;
      var x = document.getElementById("snackbar");
      x.className = "show";
      setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
    }

  function validarCamposRequired(){
    var value = true;
    var msj = "Faltan completar campos";
        
    if(document.getElementById("nombre").value.localeCompare("") == 0){
      value = false;      
      document.getElementById("label_nombre").setAttribute('class', 'letrasrojo');
    } else {
      document.getElementById("label_nombre").setAttribute('class', '');
    }
    if(document.getElementById("apellido").value.localeCompare("")==0){
      value = false;
      document.getElementById("label_apellido").setAttribute('class', 'letrasrojo');
    } else {
      document.getElementById("label_apellido").setAttribute('class', '');
    }
    if(document.getElementById("dni").value.localeCompare("")==0){
      value = false;
      document.getElementById("label_dni").setAttribute('class', 'letrasrojo');
    } else {
      document.getElementById("label_dni").setAttribute('class', '');
    }
    if(document.getElementById("telefono").value.localeCompare("")==0){
      value = false;
      document.getElementById("label_telefono").setAttribute('class', 'letrasrojo');
    } else {
      document.getElementById("label_telefono").setAttribute('class', '');
    }

    if(document.getElementById("obrasocial").value.localeCompare("PARTICULAR")!=0 && document.getElementById("numero_afiliado").value.localeCompare("")==0){
      value = false;
      document.getElementById("label_numero_afiliado").setAttribute('class', 'letrasrojo');
    } else {
      document.getElementById("label_numero_afiliado").setAttribute('class', '');
    }

    if(document.getElementById("moduloAfiliadoObligatorioId").value == 1){
      if(document.getElementById("check_afiliado_obligatorio_si").checked == false && document.getElementById("check_afiliado_obligatorio_no").checked == false){
        value = false;
        document.getElementById("label_afiliado_obligatorio").setAttribute('class', 'letrasrojo');
      } else {
        document.getElementById("label_afiliado_obligatorio").setAttribute('class', '');   
      }
      if(document.getElementById("obrasocial").value.localeCompare("PARTICULAR") != 0 && document.getElementById("plan_obra_social").value.localeCompare("")==0){
        value = false;
        document.getElementById("label_plan").setAttribute('class', 'letrasrojo');
      } else {
        document.getElementById("label_plan").setAttribute('class', '');
      }
    }

    if(value == false)
      mostrarSnackbar(msj);

    return value;
  }

  function altaPaciente(){     
    if(validarCamposRequired()){
      var nombre = document.getElementById("nombre").value;
      var apellido = document.getElementById("apellido").value;
      var dni = document.getElementById("dni").value;
      var consultorio = document.getElementById("consultorio").value;     
      var telefono = document.getElementById("telefono").value;
      var domicilio = document.getElementById("domicilio").value;
      var localidad = document.getElementById("localidad").value;
      var mail = document.getElementById("mail").value;
      var obrasocial = document.getElementById("obrasocial").value; 
      var numero_afiliado = document.getElementById("numero_afiliado").value; 
      var plan = document.getElementById("plan_obra_social").value;         
      var fecha_nacimiento_dia = document.getElementById("fecha_nacimiento_dia").value;     
      var fecha_nacimiento_mes = document.getElementById("fecha_nacimiento_mes").value;     
      var fecha_nacimiento_anio = document.getElementById("fecha_nacimiento_anio").value;
      var fecha_nacimiento = null;
      if((fecha_nacimiento_dia!=null)&&(fecha_nacimiento_dia.localeCompare('')!=0)){
        var fecha_nacimiento = fecha_nacimiento_anio+"/"+fecha_nacimiento_mes+"/"+fecha_nacimiento_dia;
      }    
      var afiliado_obligatorio = 2;
      if(document.getElementById("moduloAfiliadoObligatorio").value == 1){
        if(document.getElementById("check_afiliado_obligatorio_si").checked)
          afiliado_obligatorio = 1;
        if(document.getElementById("check_afiliado_obligatorio_no").checked)
          afiliado_obligatorio = 0;
      }
       $.ajax({
             type:'POST',
             dataType:'JSON',
             url:'/alta_paciente_medico_secretaria',
             data:{dni :dni,nombre:nombre,apellido:apellido,fecha_nacimiento:fecha_nacimiento,telefono:telefono,mail:mail,obra_social:obrasocial,numero_afiliado:numero_afiliado,plan:plan, consultorio:consultorio, domicilio:domicilio, afiliado_obligatorio:afiliado_obligatorio, localidad:localidad, _token: '{{csrf_token()}}'},
             success:function(data){              
              if(data.paciente!=null){
                $('#mensajeModal').modal();    
                  document.getElementById("nombre").value = "";     
                  document.getElementById("apellido").value = "";
                  document.getElementById("dni").value = "";
                  document.getElementById("fecha_nacimiento_dia").value = "";
                  document.getElementById("fecha_nacimiento_mes").value = "";
                  document.getElementById("fecha_nacimiento_anio").value = ""; 
                  document.getElementById("telefono").value = "";
                  document.getElementById("domicilio").value = "";
                  document.getElementById("localidad").value = "";
                  document.getElementById("mail").value = "";
                  document.getElementById("obrasocial").value = "N/A";
                  document.getElementById("numero_afiliado").value = "";
                  document.getElementById("plan_obra_social").value = ""; 
                  if(document.getElementById("moduloAfiliadoObligatorio").value == 1){
                    document.getElementById("check_afiliado_obligatorio_si").checked = false;
                    document.getElementById("check_afiliado_obligatorio_no").checked = false;
                  }
             }
           }
          }); 
    }
  }

  function validarPacienteExiste(){        
    var dni = document.getElementById("dni").value;     
    var consultorio = document.getElementById("consultorio").value;     

    $.ajax({
       type:'POST',
       dataType:'JSON',
       url:'/paciente_consultar',
       data:{dni_paciente :dni, consultorio:consultorio, _token: '{{csrf_token()}}'},
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

           if(data.paciente != null) {  
              document.getElementById("btnContinuar").disabled = true;    
              document.getElementById("btnContinuar").setAttribute('class', 'rodri_button_disabled contenido3');       
              nombre.value = data.paciente.nombre;
              apellido.value = data.paciente.apellido;
              dni.value = data.paciente.dni;
              telefono.value = data.paciente.telefono;
              domicilio.value = data.paciente.domicilio;
              localidad.value = data.paciente.localidad;
              mail.value = data.paciente.mail;
              obrasocial.value = data.paciente.obra_social;              
              numero_afiliado.value = data.paciente.numero_afiliado;              
              plan_obra_social.value = data.paciente.obra_social_plan;
              if(data.paciente.fecha_nacimiento.localeCompare('')!=0){
                var arrayFechaNacimiento = data.paciente.fecha_nacimiento.split('-');
                 if(arrayFechaNacimiento[0].localeCompare("1000")==0){
                  fecha_nacimiento_anio.value = "";
                  fecha_nacimiento_mes.value = "";
                  fecha_nacimiento_dia.value = "";
                 } else {
                  fecha_nacimiento_anio.value = arrayFechaNacimiento[0];
                  fecha_nacimiento_mes.value = arrayFechaNacimiento[1];
                  fecha_nacimiento_dia.value = arrayFechaNacimiento[2];
                }
              } 
              if(document.getElementById("moduloAfiliadoObligatorio").value == 1) {
                  if(data.paciente.afiliado_obligatorio == 1) {
                    document.getElementById("check_afiliado_obligatorio_si").checked = true;
                    document.getElementById("check_afiliado_obligatorio_no").checked = false;
                  }
                  if(data.paciente.afiliado_obligatorio == 0) {
                    document.getElementById("check_afiliado_obligatorio_si").checked = false;
                    document.getElementById("check_afiliado_obligatorio_no").checked = true;
                  }
                  if(data.paciente.afiliado_obligatorio == 2) {
                    document.getElementById("check_afiliado_obligatorio_si").checked = false;
                    document.getElementById("check_afiliado_obligatorio_no").checked = false;
                  }
                }
            } else{ 
              document.getElementById("btnContinuar").disabled = false;
              document.getElementById("btnContinuar").setAttribute('class', 'rodri_button contenido3'); 
              nombre.value = '';
              apellido.value = '';              
              telefono.value = '';
              domicilio.value = '';
              localidad.value = '';
              mail.value = '';
              obrasocial.value = 'N/A';
              numero_afiliado.value = '';              
              plan_obra_social.value = '';              
              fecha_nacimiento_dia.value = '';  
              fecha_nacimiento_mes.value = '';             
              fecha_nacimiento_anio.value = ''; 
              if(document.getElementById("moduloAfiliadoObligatorio").value == 1) {
                document.getElementById("check_afiliado_obligatorio_si").checked = false;
                document.getElementById("check_afiliado_obligatorio_no").checked = false;
              }
            }                        
         }
    });       
  }

  window.onload=function() {          
    checkRecetasPendientes();
  }



</script>

@endsection