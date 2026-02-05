<div class="modal fade" id="modalBuscarPacienteSecretaria" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">              
        <h4 class="modal-title"         
        id="modalTitleMensaje">Buscar Paciente</h4>
      </div>      
       <div class="modal-body">                        
         <div class="container">
          
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

       </div>
                
      </div>
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
           ajax: "{{ url('modal_buscar_pacientes_secretaria_list') }}",
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

  </script>

 
