<style>
  .aviso-cobro-banner {
    display: none;
    background-color: #fff3cd;
    border: 1px solid #ffecb5;
    border-left: 4px solid #ffc107;
    border-radius: 0.25rem;
    padding: 0.85rem 1rem;
    margin: 0 0 1rem 0;
    color: #664d03;
  }
  .aviso-cobro-banner strong {
    display: block;
    margin-bottom: 0.35rem;
  }
  .aviso-cobro-banner-texto {
    margin: 0;
    white-space: pre-line;
    line-height: 1.45;
  }
</style>

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
        <div id="aviso_cobro_modal_banner" class="aviso-cobro-banner" role="alert" aria-live="polite">
          <strong id="aviso_cobro_modal_banner_titulo"></strong>
          <p id="aviso_cobro_modal_banner_texto" class="aviso-cobro-banner-texto mb-0"></p>
        </div>
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
        <div id="modalTurnoOk_calendar_message" style="display: none; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 0.25rem; padding: 0.75rem; margin-bottom: 1rem;">
          <p style="margin: 0; color: #155724;">
            <span style="color: #28a745; font-size: 1.2rem; margin-right: 0.5rem;">✅</span>
            <strong>Recordatorio agregado a tu calendario:</strong> El recordatorio del día previo ha sido agregado automáticamente a tu Google Calendar.
          </p>
        </div>
        <p>Un mensaje será enviado el día previo para recordarle su turno.</p>        
       </div>
      <div class="modal-footer">
        <button onclick="irHome(1)" class="rodri_button_aceptar" data-dismiss="modal">Finalizar</button>            
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalTurnoFailMP"
     tabindex="-1" role="dialog"
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Pago online requerido</h4>
      </div>
      <div class="modal-body">
        <p id="modalTurnoFailMP_texto"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="rodri_button_cancelar" data-dismiss="modal">Cerrar</button>
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
        id="modalTitleMensaje">Lo siento, ya tienes un turno registrado, para obtener un nuevo turno deberá cancelar el turno actual. Una vez que asiste al turno solicitado podrá solicitar un nuevo turno. 
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

<script type="text/javascript">
function htmlAvisoCobroConSaltos(texto) {
  if (!texto) {
    return '';
  }
  return String(texto)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/\r\n|\r|\n/g, '<br>');
}

function actualizarAvisoCobroModal(aviso) {
  var banner = document.getElementById('aviso_cobro_modal_banner');
  if (!banner) {
    return;
  }
  if (!aviso) {
    banner.style.display = 'none';
    return;
  }
  document.getElementById('aviso_cobro_modal_banner_titulo').innerHTML = aviso.titulo || 'Aviso';
  document.getElementById('aviso_cobro_modal_banner_texto').innerHTML = htmlAvisoCobroConSaltos(aviso.descripcion || '');
  banner.style.display = 'block';
}

$(document).on('show.bs.modal', '#modalConfirmar', function() {
  actualizarAvisoCobroModal(window.avisoCobroData || null);
});
</script>
