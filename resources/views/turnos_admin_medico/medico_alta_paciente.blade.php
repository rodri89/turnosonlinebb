@extends('turnos_admin_medico/turnos_admin_medico_plantilla')

@section('contenedor')
<div class="row"> 
    <h5>Pacientes pendietes de alta.</h5>
</div><br>
<div class="table-responsive" style="height:400px; overflow-y: scroll;">  
<table class="table table-striped" id="tabla_pacientes" name="tabla_pacientes">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Apellido y Nombre</th>
      <th scope="col">DNI</th>
      <th scope="col">Telefono</th>
      <th scope="col">Mail</th>
      <th scope="col">Acción</th>      
    </tr>
  </thead>
  <?php $cont = 1; ?>
  @foreach($pacientes as $paciente)  
  <tbody id="pacientes-list" name="pacientes-list">
    <tr>
      <th scope="row">{{$cont++}}</th>
      <td>{{$paciente->apellido.', '.$paciente->nombre}}</td>
      <td>{{$paciente->dni}}</td>
      <td>{{$paciente->telefono}}</td>
      <td>{{$paciente->mail}}</td>      
      <td><button onclick="activarPaciente('{{$paciente->id}}','{{$paciente->pac_id}}')" class="rodri_button_aceptar">ACTIVAR</button></td>                
    </tr>    
  </tbody>
  @endforeach

</table>
</div>

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

<script type="text/javascript">
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

 function activarPaciente(paciente, pacienteSecretariaId){     
     $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/medico_activar_paciente',
           data:{paciente:paciente,pacienteSecretariaId:pacienteSecretariaId, _token: '{{csrf_token()}}'},
           success:function(data){              
              $('#mensajeModal').modal();
              var contador = 0;
              $("#tabla_pacientes").find("tr:gt(0)").remove();
              for (i = 0; i < data.pacientes.length; i++){
                  contador = contador + 1;                  
                  var paciente = "<tr><td>"+contador+"</td><td>"+data.pacientes[i].apellido+", "+data.pacientes[i].nombre+"</td><td>"+data.pacientes[i].dni+"</td><td>"+data.pacientes[i].telefono+"</td><td>"+data.pacientes[i].mail+"</td><td><button class='rodri_button_aceptar' onclick='activarPaciente("+data.pacientes[i].id+","+data.pacientes[i].pac_id+")'>ACTIVAR</button></td></tr>";                                      

                  $('#pacientes-list').append(paciente); 
              }
           }
        });
  }

    window.onload=function() {          
      checkRecetasPendientes();
    }

</script>

@endsection