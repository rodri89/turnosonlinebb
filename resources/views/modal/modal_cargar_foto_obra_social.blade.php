<div class="modal fade" id="modalCargarFotoObraSocial" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Cargar Foto</h4>
      </div>      
       <div class="modal-body">            
        <h6 class="modal-title"         
        id="modalTitleMensaje">Seleccione una foto de la obra social y haga click en "Guardar".</h6>
        <form method="post" action="{{ route('cargarfotoobrasocialv') }}" enctype="multipart/form-data">
           @csrf 
           <input type="hidden" id="turno_id" name="turno_id" value="{{$turnoRegistrado->id}}" />             
           <input type="hidden" id="paciente_id" name="paciente_id" value="{{$paciente->id}}" />                        
           <input type="file" id="foto" name="foto" placeholder="Foto Carnet Obra Social" /> 
       </div>
      <div class="modal-footer">
        <button type="submit" 
           class="rodri_button_aceptar" 
           >Guardar</button>
      </form> 
        <button type="button" 
           class="rodri_button_cancelar" 
           data-dismiss="modal">Cancelar</button>        
      </div>
    </div>
  </div>
</div>