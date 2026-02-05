@extends('turnos_admin_medico/turnos_admin_medico_plantilla')

@section('contenedor')

<head>

<script src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="{{asset('js/jquery.min.js')}}"> </script>
</head>

<h2> Pacientes:</h2>
<!--<div class="col">
<input type="checkbox" class="form-check-input" id="check_pago" name="check_pago">
<label onchange="reloadPagina()" class="form-check-label editText" for="videollamadaCheck">Check para ver todos los que faltan validar</label><br><br>
</div> -->
 <div class="container">
<input type="hidden" id="consultorio" name="consultorio" value="{{$consultorio}}"/> 
  <div class="table-responsive" style="height:700px; overflow-y: scroll;">
  <table class="table table-striped" id="laravel_datatable"> 
               <thead class="fondoNav text-white">
                  <tr>
                    <th class="editText">Turno</th>                    
                    <th class="editText">Paciente</th>
                    <th class="editText">DNI</th>
                    <th class="editText">Telefono</th>                      
                    <th class="editText">Obra Social</th>
                    <th class="editText">N°Afiliado</th>
                    <th class="editText">Plan</th>                    
                    <th class="editText">N°Operacion</th>
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
           ajax: "{{ url('medico-validar-pagos-list/1') }}",
           columns: [
                    { data: 'turno', name: 'turno' },                    
                    { data: 'paciente', name: 'paciente' },                  
                    { data: 'dni', name: 'dni' },
                    { data: 'telefono', name: 'telefono' },                    
                    { data: 'obra_social', name: 'obra_social' },
                    { data: 'numero_afiliado', name: 'numero_afiliado' },
                    { data: 'obra_social_plan', name: 'obra_social_plan' },                    
                    { data: 'pago_ticket', name: 'pago_ticket' },
                    { data: 'action', name: 'action', orderable: false, searchable: false}                                       
                 ]
        });
     });

   function reloadPagina(){
    location.reload();
   }
    
    function validarPago(turno_id){   
      $.ajax({
        type:'POST',
        dataType:'JSON',
        url:'/medico_accion_validar_pago',
        data:{turno_id :turno_id, _token: '{{csrf_token()}}'},
        success:function(data){      
          location.reload();
        }
      });    
    }

  </script>


<script type="text/javascript">
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

</script>

@endsection