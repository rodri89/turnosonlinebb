<div data-backdrop="static" data-keyboard="false" class="modal fade" id="modalNoAceptaMasPacientes" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">      	        
        <h4 class="modal-title"         
        id="modalTitleMensaje">TURNOS</h4>
      </div>      
       <div class="modal-body">                
        <p>El especialista no recibe nuevos pacientes. Por cualquier consulta por favor comunicarse al TEL: 4814538</p>
        <p>Muchas Gracias!</p>       	
       </div>
      <div class="modal-footer">
        <button type="button"
           class="rodri_button_aceptar" 
           onclick="redidectToHome()" 
           data-dismiss="modal">Aceptar</button> 
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
    function redierctToHome(){
      window.location.href = "/homes";
    }
</script>