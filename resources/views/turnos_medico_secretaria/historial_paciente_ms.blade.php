@extends('turnos_admin_secretaria/turnos_admin_secretaria_plantilla')

@section('contenedor')
@include('modal.modal_mensaje')
<head>

<script src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="{{asset('js/jquery.min.js')}}"> </script>
</head>

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
                    <th class="editText">Profesional</th>                      
                    <th class="editText">Fecha</th>
                    <th class="editText">Horario</th>
                    <th class="editText">Sobreturno</th>
                    <th class="editText">Asistio</th>
                   <!-- <th class="editText">Acción</th>                                       -->
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
           ajax: "{{ url('historial_pacientes_list') }}",
           columns: [
                    { data: 'apellido', name: 'apellido' },
                    { data: 'nombre', name: 'nombre' },
                    { data: 'dni', name: 'dni' },                    
                    { data: 'medico_', name: 'medico_', orderable: true, searchable: true},                    
                    { data: 'fechaTurno_', name: 'fechaTurno_', orderable: true, searchable: true},
                    { data: 'horario', name: 'horario' },
                    { data: 'sobreturno_', name: 'sobreturno_', orderable: true, searchable: true},
                    { data: 'asistio_', name: 'asistio_', orderable: true, searchable: true}
                    // { data: 'action', name: 'action', orderable: false, searchable: false}                                       
                 ]
        });
     });
    
  </script>

<script type="text/javascript">
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  function verComentario(turnoRegistradoId){
       $.ajax({
         type:'POST',
         dataType:'JSON',
         url:'/get_turno_registrado_id',
         data:{turnoRegistradoId: turnoRegistradoId, _token: '{{csrf_token()}}'},
         success:function(data){   
            var titulo = document.getElementById('modalMensajeTitulo');
            var texto = document.getElementById('modalMensajeTexto');       
            titulo.innerHTML = "";
            texto.innerHTML = "";
            if(data.turno != null){              
              titulo.innerHTML = "Detalles";
              var fecha_aux = data.turno.updated_at.split(" "); //2021-04-12
              var fecha_aux_2 = fecha_aux[0].split("-");
              var fecha = fecha_aux_2[2]+"/"+fecha_aux_2[1]+"/"+fecha_aux_2[0];
              texto.innerHTML = data.turno.comentario+" el dia "+fecha+".";
              $('#modalMensaje').modal();
            }
         }
      });
  }

</script>

@endsection