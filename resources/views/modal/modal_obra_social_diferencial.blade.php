<style type="text/css">
	
</style>

 <div class="modal fade" id="modalBuscarObraSocialDiferencial_2" 
 tabindex="-1" role="dialog" 
 aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog modal-l">
    <div class="modal-content">
      <div class="modal-header">              
        <h4 class="modal-title"         
        id="modalTitleMensaje">Diferencial Obra Social</h4>        
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">x</button>
      </div>      
       <div class="modal-body">                        
         <div class="container">	      
	      
	      	<div class="input-group">
            <small>Aqui se muestra el costo diferencial que se le cobrará segun su obra social</small>
            <br>
            <input oninput="buscarObraSocialDiferencial()" id="search_input" type="text" name="search_input" class="form-control" placeholder="BUSCAR">
              
                <button hidden id="search_button" class="btn active_color" type="submit" onclick="buscar()"> 
                  <i class="fas fa-search white_color" style="margin-left: 20px"></i>
                </button>
            </div>	          
      	  
          <br>
          <div class="table-responsive" style="height:600px; overflow-y: scroll;">
          <table class="table table-striped" id="tabla_obrasocial_dif" name="tabla_obrasocial_dif">
               <thead class="active_color">
                  <tr>
                    <th>Obra Social</th>
                    <th class="text-center">Diferencial </th>                                      
                  </tr>
               </thead>

               <tbody id="obra-social-list" name="obra-social-list">
               	@foreach($obrasSociales as $os)
               		<tr>
	               		<td>{{$os->nombre}}</td>
	               		<td class="text-center">${{$os->importe}}</td>
               		</tr>
               	@endforeach
               </tbody>
            </table>
         </div>
      </div>

       </div>
                
      </div>
    </div>
  </div>

  <script>
   
    function mostrarArancelDiferencial(){    	
    	$('#modalBuscarObraSocialDiferencial_2').appendTo("body").modal('show');   
		// $("#modalBuscarObraSocialDiferencial_2").modal();  
	}
   
    function ocultarArancelDiferencial(){      
     	$("#modalBuscarObraSocialDiferencial_2").modal('hide');                      
    } 

    function buscarObraSocialDiferencial() {
      var medico_id = document.getElementById("medico_id").value;    
    	var texto = document.getElementById("search_input").value;
    	    	
		  $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/get_obra_social_diferencial',
           data:{medico_id:medico_id, texto: texto, _token: '{{csrf_token()}}'},
	           success:function(data) {  	           	
	                 var listContainer = $('#obra-social-list');
                    if (listContainer.length === 0) {
                        console.error('No se encontró el contenedor #obra-social-list');
                        return;
                    }

                    listContainer.empty();

                    if (data.length === 0) {
                        listContainer.append('<tr><td colspan="2" class="text-center">Sin resultados</td></tr>');
                        return;
                    }
                    console.log(data.obraSocial.length);
                    for (var i = 0; i < data.obraSocial.length; i++) {
                        var obrasocial = '<tr><td>' + data.obraSocial[i].nombre + '</td><td class="text-center">$' + data.obraSocial[i].importe + '</td></tr>';
                        listContainer.append(obrasocial);
                    }
	           }
	        }); 
    	
    } 

  </script>
