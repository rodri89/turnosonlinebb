@extends('turnos_admin_medico/turnos_admin_medico_plantilla')

@section('contenedor')

<head>

<script src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="{{asset('js/jquery.min.js')}}"> </script>
</head>

<h2> Historial Videollamadas</h2>
<form>            
  <label for="text" class="col-sm-0 control-label editText">El siguiente link lo llevara a la página del metavalidador.</label>      
  <form>            
    <a href="https://www.ambb.org.ar/metavalidador/users/login" target="_blank" class="letraAzul">Link Metavalidador</a>
  </form>
<br><br>

@include('modal.modal_ver_imagen')

 <div class="container">
  <div class="table-responsive" style="overflow-y: scroll;">
  <table class="table table-striped" id="laravel_datatable"> 
               <thead class="fondoNav text-white">
                  <tr>
                    <th class="editText">Fecha</th>
                    <th class="editText">Horario</th>                                        
                    <th class="editText">Paciente</th>                    
                    <th class="editText">Obra Social</th>
                    <th class="editText">N°Afiliado</th>                                        
                    <th class="editText">Plan</th>                                        
                    <th class="editText">Foto</th>
                    <th class="editText">Cargado</th>                                        
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
           ajax: "{{ url('historial-videollamada-list') }}",
           columns: [
                    { data: 'fecha', name: 'fecha' },                    
                    { data: 'horario', name: 'horario' },                    
                    { data: 'paciente', name: 'paciente' },                    
                    { data: 'obra_social', name: 'obra_social' },                    
                    { data: 'numero_afiliado', name: 'numero_afiliado' },                    
                    { data: 'obra_social_plan', name: 'obra_social_plan' },                                      
                    { data: 'foto', name: 'foto', orderable: false, searchable: false},                                       
                    { data: 'cargado', name: 'cargado' },                                      
                    { data: 'action', name: 'action', orderable: false, searchable: false}                                       
                 ]
        });
     });

   function reloadPagina(){
    location.reload();
   }

     function actualizarCargado(turno_id){   
      var estado = 1;    
      $.ajax({
        type:'POST',
        dataType:'JSON',
        url:'/medico_actualizar_cargado',
        data:{turno_id :turno_id, estado:estado, _token: '{{csrf_token()}}'},
        success:function(data){      
          location.reload();
        }
      });    
    }

  function verCarnetObraSocial(foto){
    onClickVer(foto);
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