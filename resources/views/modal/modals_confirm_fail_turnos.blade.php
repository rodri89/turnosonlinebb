<div class="modal fade" id="modalConfirmar" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Confirmar</h4>
      </div>      
       <div id="seccion_modalConfirmar_body" class="modal-body">   
        <input class="modal_input_horario" type="hidden" id="turno_id">     
        <p id="modalConfirmar_texto"></p>
        <div id="seccion_tipo_turnos" hidden>
          <br>
          <div class="radio-group" id="miRadioGroup">
              <label>Seleccion el tipo de turno que desea:</label>
              <div class="radio-item">
                  <input type="radio" name="opcion_miRadioGroup" value="1" id="radio1">
                  <label for="radio1">Consulta endocrina</label>
              </div>
              <div class="radio-item">
                  <input type="radio" name="opcion_miRadioGroup" value="23" id="radio2">
                  <label for="radio2">Ecografía</label>
              </div>
              <div class="radio-item">
                  <input type="radio" name="opcion_miRadioGroup" value="25" id="radio3">
                  <label for="radio3">Consula endocrina + Ecografía</label>
              </div>
          </div>
        </div>
       </div>
      <div class="modal-footer">        
        <button type="button" 
           class="rodri_button_cancelar" 
           data-dismiss="modal" onclick="cancelarTurno()">Cancelar</button> 
        
        <button onclick="confirmarTurno()" class="rodri_button_aceptar" data-dismiss="modal">Confirmar</button>                                  
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalTurnoOk" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Turno Registrado</h4>
      </div>      
       <div class="modal-body">        
        <h6 id="modalTurnoOk_fecha_seleccionada"></h6><br>        
        <h6 id="modalTurnoOk_horario"></h6><br>        
        <h6 id="modalTurnoOk_medico"></h6><br>        
        <h6 id="modalTurnoOk_direccion"></h6><br>
        <h6 id="modalTurnoOk_tipo_turno"></h6><br>                  
        <p>Un mail o mensaje de texto será enviado el día previo para recordarle su turno.</p>        
       </div>
      <div class="modal-footer">
        <button onclick="irHome(1)" class="rodri_button_aceptar" data-dismiss="modal">Finalizar</button>            
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalTurnoFail" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Turno Ocupado</h4>
      </div>      
       <div class="modal-body">             
        <h6 class="modal-title"         
        id="modalTitleMensaje">Lo siento, el turno ha sido registrado por otra persona, por favor actualice la página y seleccione otro horario.<br>Muchas gracias.</h6>
       </div>
      <div class="modal-footer">
        <button type="button" 
           class="rodri_button_cancelar" 
           data-dismiss="modal">Cerrar</button>        
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalTurnoFail2" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Error</h4>
      </div>      
       <div class="modal-body">            
        <h6 class="modal-title"         
        id="modalTitleMensaje">Lo siento, ya tienes un turno registrado con el medico para este dia.</h6>
       </div>
      <div class="modal-footer">
        <button type="button" 
           class="rodri_button_cancelar" 
           data-dismiss="modal">Cerrar</button>        
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalTurnoFail4" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Error</h4>
      </div>      
       <div class="modal-body">            
        <h6 class="modal-title"         
        id="modalTitleMensaje">Lo siento, ya tienes un turno registrado para realizar un PEG en ese horario.</h6>
       </div>
      <div class="modal-footer">
        <button type="button" 
           class="rodri_button_cancelar" 
           data-dismiss="modal">Aceptar</button>        
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalTurnoFail5" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Error</h4>
      </div>      
       <div class="modal-body">            
        <h6 class="modal-title"         
        id="modalTitleMensaje">Lo siento, ya tienes un turno registrado esta semana, solo puedes solicitar un turno por semana.</h6>
       </div>
      <div class="modal-footer">
        <button type="button" 
           class="rodri_button_cancelar" 
           data-dismiss="modal">Aceptar</button>        
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalTurnoFail3" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Error</h4>
      </div>      
       <div class="modal-body">            
        <h6 class="modal-title"         
        id="modalTitleMensaje">Lo siento, ya tienes un turno registrado para realizar otro estudio en ese horario.</h6>
       </div>
      <div class="modal-footer">
        <button type="button" 
           class="rodri_button_cancelar" 
           data-dismiss="modal">Aceptar</button>        
      </div>
    </div>
  </div>
</div>
