@extends('turnos_admin_medico/turnos_admin_medico_plantilla')

@section('contenedor')

<input type="hidden" name="medico_id" id="medico_id" value="{{$medicoConfig->medico}}">
<div class="row"> 
    <div class="col-xl-4">
      <label>Ingrese el valor de una consulta</label>
      <input id="valor_consulta_id" name="valor_consulta_id" value='{{$medicoConfig->valor_consulta}}' class="form-control"></input>
      <br>
      <button class="rodri_button" onclick="actualizarValorConsulta()">Actualizar</button>
    </div>
    
</div>

@if($moduloCantidadPrimerControl == 1)
<div class="row"> 
    <h5>Configurar Primer Control.</h5>
</div>
<br>
<p>Debo ingresar la cantidad de primeros controles que deseo cada dia.</p>
<input hidden id="actualizar" name="actualizar" value='{{$actualizar}}'></input>
<div class="table-responsive" style="height:400px; width: 250px;">  
  <table class="table table-striped" id="tabla_pacientes" name="tabla_pacientes">
    <thead>
      <tr>
        <th scope="col">#</th>        
        <th scope="col">Dia</th>
        <th scope="col">Cantidad</th>      
      </tr>
    </thead>
    <?php $cont = 1; ?>
    @foreach($dias as $dia)
    <form method="POST" action="{{ route('configPrimerControlActualizar') }}">  
    @csrf
    <tbody id="pacientes-list" name="pacientes-list">
      <tr>
        <th scope="row">{{$cont++}}</th>
        <td>{{$dia["dia"]}}</td>
        <td><input class="input_configuracion_horario" name="{{$dia['id']}}" value="{{$dia['cantidad']}}"></input></td>
      </tr>    
    </tbody>
    @endforeach
  </table>
  <button class="rodri_button">Actualizar</button>
  </form>
</div>
@endif

@if($moduloVideollamada == 1)
<h4>Configurar Videollamada</h4>
<div style="height:400px; width: 600px;">          
    
         
          <input type="hidden" id="videollamada_id" name="videollamada_id" value='{{$videollamada[0]->id}}'  />                

          <label for="text" class="col-sm-0 control-label editText">Debo ingresar el importe que voy a cobrar por las videollamadas:</label>      
          <input type="text" class="form-control editText" id="importe" name="importe"  value='{{$videollamada[0]->importe}}' required />

          <label for="text" class="col-sm-0 control-label editText">Debo ingresar el link de la videollamada</label>      
          <input type="text" class="form-control editText" id="link_videollamada" name="link_videollamada"  value='{{$videollamada[0]->link}}' required />
    
    <br>
    <label for="text" class="col-sm-0 control-label">Seleccione una de las opciones para las videollamadas.</label> 
    
    <div class="r_inline">
      <div class="custom-control custom-radio">
        @if($videollamada[0]->perfil == 1)
          <input type="radio" id="perfil1" name="perfil_group" class="custom-control-input" checked>
        @else
          <input type="radio" id="perfil1" name="perfil_group" class="custom-control-input">
        @endif
          <label class="custom-control-label" for="perfil1">Si tiene los datos de la obra social completa no se le cobrará. Sino tiene los datos de la obra social completos deberá abonar la consulta.</label>
      </div>
      <div class="custom-control custom-radio">
        @if($videollamada[0]->perfil == 2)
         <input type="radio" id="perfil2" name="perfil_group" class="custom-control-input" checked>
         @else
         <input type="radio" id="perfil2" name="perfil_group" class="custom-control-input">
         @endif
         <label class="custom-control-label" for="perfil2">Siempre debe abonar la consulta. No importa si tiene completo los datos de la obra social</label>
      </div>
      <small>NOTA: Los cobros serán realizados mediante MercadoPago.</small>
    </div>
    <br> <br>
    <button type="submit" class="rodri_button" onclick="actualizarConfigVideollamada()">Actualizar</button>
  </div>
@endif

<div class="modal fade" id="mensajeModal" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Activado:</h4>
      </div>      
       <div class="modal-body">
        <!--<img id="modalImagenMensaje" class="card-img-top" src="images/iconos/ic_ok.png" alt="">                       -->        
        <h6>El paciente ha sido activado!</h6><br>                            
       </div>
      <div class="modal-footer">
        <button class="rodri_button_aceptar" data-dismiss="modal">Aceptar</button>            
      </div>
    </div>
  </div>
</div>
@include('modal.snackbar')
<div id="snackbar"><p><input class="input_snackbar_220" id="snackbar_msj" name="snackbar_msj"></input></p></div>

<script type="text/javascript">

  function mostrarSnackbar(cs) {
    document.getElementById("snackbar_msj").value = cs;
    var x = document.getElementById("snackbar");    
    x.className = "show";
    setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
  }

  function actualizarConfigVideollamada(){    
    var link_videollamada = document.getElementById("link_videollamada").value;    
    var importe = document.getElementById("importe").value;        
    var videollamada_id = document.getElementById("videollamada_id").value;

    var perfil = 0;
    if(document.getElementById("perfil1").checked)
      perfil = 1
    if(document.getElementById("perfil2").checked)
      perfil = 2    

      $.ajax({
         type:'POST',
         dataType:'JSON',
         url:'/actualizar_config_videollamada',
         data:{importe:importe, link:link_videollamada, videollamada_id:videollamada_id, perfil:perfil, _token: '{{csrf_token()}}'},
         success:function(data){  
            mostrarSnackbar("Datos actualizados"); 
            //location.reload();         
         }
      });

  }

  function actualizarValorConsulta() {
    var valorConsulta = document.getElementById("valor_consulta_id").value;
    var medico_id = document.getElementById("medico_id").value;
    $.ajax({
         type:'POST',
         dataType:'JSON',
         url:'/actualizar_valor_consulta',
         data:{valorConsulta:valorConsulta, medico_id: medico_id, _token: '{{csrf_token()}}'},
         success:function(data){  
            mostrarSnackbar("El valor ha sido actualizado");             
         }
      });
  }

  window.onload=function() {
    checkRecetasPendientes();  
   }

</script>

@endsection