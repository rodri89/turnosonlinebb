@extends('turnos_admin_medico/turnos_admin_medico_plantilla')

@section('contenedor')

@include('modal.snackbar')

<head>

<script src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="{{asset('js/jquery.min.js')}}"> </script>
</head>

<h2> Administrar Obras Sociales</h2>
@if($activarObraSociales == 0)
<form method="POST" action="{{ route('activarobrassociales') }}">            
  @csrf
  <label for="text" class="col-sm-0 control-label editText">Click en el boton "Activar" para vincular todas las obras sociales.</label>   
    <button class="rodri_button contenido3 fontNav">Activar</button>
  </form>
@else
  <label for="text" class="col-sm-0 control-label editText">Click en el boton "Desactivar" para desactivar todas las obras sociales.</label>      
  <form>            
    <button onclick="desactivarTodos()" class="rodri_button contenido3 fontNav">Desactivar</button>
  </form>
@endif
<br><br>

<div id="snackbar"><p id="snackbar_text">Importe Actualizado</p></div>
 
 <div class="container">
  <div class="table-responsive" style="overflow-y: scroll;">
  <table class="table table-striped" id="laravel_datatable"> 
               <thead class="fondoNav text-white">
                  <tr>
                    <th class="editText">Obra Social</th>
                    <th class="editText">Importe</th>                                        
                    <th class="editText">Estado</th>                                        
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
           ajax: "{{ url('medico-obras-sociales-list') }}",
           columns: [
                    { data: 'nombre', name: 'nombre' },                    
                    { data: 'importe', name: 'importe' },
                    { data: 'estado', name: 'estado' },                                      
                    { data: 'action', name: 'action', orderable: false, searchable: false}                                       
                 ]
        });
     });

   function reloadPagina(){
    location.reload();
   }
    
   function updateImporte(newValue, obra_social_id) {      
      $.ajax({
        type:'POST',
        dataType:'JSON',
        url:'/medico_obra_social_actualizar_importe',
        data:{importe :newValue, obra_social_id:obra_social_id, _token: '{{csrf_token()}}'},
        success:function(data){      
          //location.reload();
          mostrarSnackbar("Import Actualizado");
        }
      });    
   }

    function activarObraSocialMedico(medicoObraSocialId){   
      var estado = 1;      
      $.ajax({
        type:'POST',
        dataType:'JSON',
        url:'/medico_obra_social_estado',
        data:{medicoObraSocialId :medicoObraSocialId, estado:estado, _token: '{{csrf_token()}}'},
        success:function(data){      
          location.reload();
        }
      });    
    }

     function desactivarObraSocialMedico(medicoObraSocialId){   
      var estado = 0;    
      $.ajax({
        type:'POST',
        dataType:'JSON',
        url:'/medico_obra_social_estado',
        data:{medicoObraSocialId :medicoObraSocialId, estado:estado, _token: '{{csrf_token()}}'},
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

  function mostrarSnackbar(cs) {      
      document.getElementById("snackbar_text").innerHTML = cs;
      var x = document.getElementById("snackbar");
      x.className = "show";
      setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
  }

  function desactivarTodos(){
    $.ajax({
        type:'POST',
        dataType:'JSON',
        url:'/desactivar_obras_sociales',
        data:{_token: '{{csrf_token()}}'},
        success:function(data){      
          location.reload();
        }
      });  
  }

  window.onload=function() {          
    checkRecetasPendientes();
  }

</script>

@endsection