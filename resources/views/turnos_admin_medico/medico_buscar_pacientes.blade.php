@extends('turnos_admin_medico/turnos_admin_medico_plantilla')

@section('contenedor')

<head>

<script src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="{{asset('js/jquery.min.js')}}"> </script>
</head>

@include('modal.modal_recetas')

<h2> Pacientes:</h2>
 <div class="container">
<input type="hidden" id="consultorio" name="consultorio" value="{{$consultorio}}"/> 
  <div class="table-responsive" style="height:700px; overflow-y: scroll;">
  <table class="table table-striped" id="laravel_datatable"> 
               <thead class="fondoNav text-white">
                  <tr>
                    <th class="editText">Apellido</th>
                    <th class="editText">Nombre</th>
                    <th class="editText">DNI</th>
                    <th class="editText">Telefono</th>                      
                    <th class="editText">Obra Social</th>
                    <th class="editText">N°Afiliado</th>
                    <th class="editText">Plan</th>
                    <th class="editText">Acción</th>                                       
                  </tr>
               </thead>
            </table>
         </div>
      </div>
   <script>
   
   $(document).ready( function () {
    $('#laravel_datatable').DataTable({
           language: {
              "decimal": "",
              "emptyTable": "No hay información",
              "info": "Mostrando _START_ a _END_ de _TOTAL_ Entradas",
              "infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
              "infoFiltered": "(Filtrado de _MAX_ total entradas)",
              "infoPostFix": "",
              "thousands": ",",
              "lengthMenu": "Mostrar _MENU_ Entradas",
              "loadingRecords": "Cargando...",
              "processing": "Procesando...",
              "search": "Buscar:",
              "zeroRecords": "Sin resultados encontrados",
              "paginate": {
                  "first": "Primero",
                  "last": "Ultimo",
                  "next": "Siguiente",
                  "previous": "Anterior"
              },
           },
           processing: false,
           serverSide: false,
           ajax: "{{ url('medico-users-list') }}",
           columns: [
                    { data: 'apellido', name: 'apellido' },
                    { data: 'nombre', name: 'nombre' },
                    { data: 'dni', name: 'dni' },
                    { data: 'telefono', name: 'telefono' },                    
                    { data: 'obra_social', name: 'obra_social' },
                    { data: 'numero_afiliado', name: 'numero_afiliado' },
                    { data: 'obra_social_plan', name: 'obra_social_plan' },
                    { data: 'action', name: 'action', orderable: false, searchable: false}                                       
                 ]
        });
     });
    
    function darTurno(dni){     
      var consultorio = document.getElementById("consultorio").value; 

      $.ajax({
        type:'POST',
        dataType:'JSON',
        url:'/paciente_consultar',
        data:{dni_paciente :dni, consultorio:consultorio, _token: '{{csrf_token()}}'},
        success:function(data){      
          var paciente = data.paciente.apellido+", "+data.paciente.nombre;
          $('#pacienteSeleccionado').val(paciente);  
          $('#dniPacienteSeleccionado').val(data.paciente.dni);
          $('#pacienteAct').val(data.paciente.dni);
          $('#pacienteAsg').val(data.paciente.dni);
          $('#pacienteSbr').val(data.paciente.dni);
          $('#optionsModal').modal();  
        }
      });
    }

    function bloquearEliminar(paciente_id){
      $.ajax({
        type:'POST',
        dataType:'JSON',
        url:'/consultar_paciente_id',
        data:{paciente_id :paciente_id, _token: '{{csrf_token()}}'},
        success:function(data){      
          var paciente = data.paciente.apellido+", "+data.paciente.nombre;
          $('#pacienteSeleccionadoBE').val(paciente);  
          $('#dniPacienteSeleccionadoBE').val(data.paciente.dni);
          $('#pacienteIdBloquear').val(paciente_id);
          $('#pacienteIdEliminar').val(paciente_id);
          $('#optionsModalBloquearEliminar').modal();          
        }
      });

    }

    function desbloquear(paciente_id){      
         $.ajax({
        type:'POST',
        dataType:'JSON',
        url:'/medico_desbloquear_paciente',
        data:{paciente_id :paciente_id, _token: '{{csrf_token()}}'},
        success:function(data){                   
             location.reload();       
        }
      });          
    }

    function eliminarPaciente(){
      var paciente_id = document.getElementById("pacienteIdEliminar").value;
         $.ajax({
        type:'POST',
        dataType:'JSON',
        url:'/medico_eliminar_paciente',
        data:{paciente_id :paciente_id, _token: '{{csrf_token()}}'},
        success:function(data){                   
             location.reload();       
        }
      });
    }

    function bloquearPaciente(){
      var paciente_id = document.getElementById("pacienteIdBloquear").value;
         $.ajax({
        type:'POST',
        dataType:'JSON',
        url:'/medico_bloquear_paciente',
        data:{paciente_id :paciente_id, _token: '{{csrf_token()}}'},
        success:function(data){                   
             location.reload();       
        }
      });
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

  </script>

<div class="modal fade" id="optionsModalBloquearEliminar" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Opciones:</h4>
      </div>      
       <div class="modal-body">
           <h6><b>Paciente: </b><input class="sinBackground" type="text" id="pacienteSeleccionadoBE"></input></h6>
           <h6><b>DNI: </b><input class="sinBackground" type="text" id="dniPacienteSeleccionadoBE"></input></h6><br>
          <div class="row contenedor3">
            <div class="contenido3">                        
              <input type="hidden" id="pacienteIdBloquear" name="paciente_id"/>    
              <button class="btn btn-primary-outline" onclick="bloquearPaciente()">
                <div class="circulo circulo_ocupado img_turno_apl">
                  <h1 class="turno_text_size_apl">Bloquear</h1>
                </div>
              </button>
            </div>
            <div class="contenido3">             
              <input type="hidden" id="pacienteIdEliminar" name="paciente_id"/>                  
              <button class="btn btn-primary-outline" onclick="eliminarPaciente()">
                <div class="circulo circulo_ocupado img_turno_apl">
                  <h1 class="turno_text_size_apl">Eliminar</h1>
                </div>
              </button>
            </div>            
          </div>
          <small>Eliminar: Solo elimina el paciente del listado buscar. El paciente no será eliminado del sistema.</small>        
       </div>
      <div class="modal-footer">
        <button class="rodri_button_cancelar" data-dismiss="modal">Cancelar</button>            
      </div>
    </div>
  </div>
</div>
 

<div class="modal fade" id="optionsModal" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Opciones:</h4>
      </div>      
       <div class="modal-body">
           <h6><b>Paciente: </b><input class="sinBackground" type="text" id="pacienteSeleccionado"></input></h6>
           <h6><b>DNI: </b><input class="sinBackground" type="text" id="dniPacienteSeleccionado"></input></h6><br>
          <div class="row contenedor3">
            <form class="contenido3" method="POST" action="{{route('medicoactualizarpaciente') }}">
              @csrf
              <input type="hidden" id="consultorioAct" name="consultorio" value="{{$consultorio}}"/>
              <input type="hidden" id="pacienteAct" name="paciente_dni"/>    
              <button class="btn btn-primary-outline">
                <div class="circulo img_turno_apl">
                  <h1 class="turno_text_size_apl">Actualizar</h1>
                </div>
              </button>
            </form>
            <form class="contenido3" method="POST" action="{{route('medicoasignarturnos') }}">
             @csrf
              <input type="hidden" id="pacienteAsg" name="paciente_dni"/>                  
              <button class="btn btn-primary-outline">
                <div class="circulo img_turno_apl">
                  <h1 class="turno_text_size_apl">Asignar</h1>
                </div>
              </button>
            </form>
            <form class="contenido3" method="POST" action="{{route('medicoadminsobreturnos') }}">
              @csrf
              <input type="hidden" id="pacienteSbr" name="paciente_dni"/>              
              <button class="btn btn-primary-outline">
                <div class="circulo img_turno_apl">
                  <h1 class="turno_text_size_apl">Sobreturno</h1>
                </div>
              </button>
            </form>
          </div>        
       </div>
      <div class="modal-footer">
        <button class="rodri_button_cancelar" data-dismiss="modal">Cancelar</button>            
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

  window.onload=function() {
    checkRecetasPendientes();    
   }

</script>

@endsection