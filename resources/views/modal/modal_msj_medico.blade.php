<div class="modal fade" id="modalMsjMedico" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">      	        
        <!--<h4 class="modal-title"         
        id="modalTitleMensaje">AVISO</h4>-->
      </div>      
       <div class="modal-body">
        <!--<h1>Coronavirus</h1>-->
        <p>Estimadas Familias:</p>
        <p>
        Por el momento no voy a estar realizando atención en consultorio.<br>
        Los turnos que ya estaban reservados, los atenderá la Dra. Garcia Elliot. Quién ya cuenta con las respectivas historias clínicas.<br><br>
        Aquellos pacientes que no tengan turno asignado, si lo desean, también pueden sacar turno con ella, que gustosa los atenderá y estamos en permanente contacto.<br>
        Les envío un cálido saludo.<br><br>
        Dra. Gisele Durán.        
       </div>
      <div class="modal-footer">
        <button type="button"
           class="rodri_button_aceptar"
            onclick="redirectohome()" 
           data-dismiss="modal">Continuar</button> 
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  function redirectohome() {
    location.href = '/seleccionar_medico';    
  }

</script>