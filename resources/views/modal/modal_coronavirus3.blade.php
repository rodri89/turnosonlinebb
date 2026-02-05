<div class="modal fade" id="modalCoronavirus3" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">      	        
        <h4 class="modal-title"         
        id="modalTitleMensaje">IMPORTANTE</h4>
      </div>      
       <div class="modal-body">                        
        <p>          
        En caso de que Ud. o algún conviviente cuente con signos o síntomas de COVID-19 (temperatura mayor o igual a 37,4°, tos seca, dolor de garganta, dificultad para respirar, pérdida del olfato o gusto, dolor de cabeza, diarrea, vómitos o dolor muscular) o sea contacto estrecho de un caso sospechoso o confirmado de la enfermedad; por favor, no asista a la consulta y comuníquese con su médico de cabecera o al 148. </p>
    		<p>Por ahora se permite el ingreso al consultorio de un solo paciente utilizando correctamente el tapa boca-nariz.
    		Al ingresar a la institución se les tomará la temperatura y se les ofrecerá higienizar sus manos con alcohol en gel.</p>
    		
        @if($medico->id == 13)  <!-- id lucas sosa -->
          <p>Estimado paciente, en el contexto de la actual situación sanitaria, contemple que si su obra social no ha generado convenio con la AMBB se encontrará con un costo adicional (de 500 pesos) por el uso de elementos de protección de bioseguridad de moderada-baja exposición (EPP – COD. 42.02.92). Para más información comunicarse con nosotros. TEL: 4520091</p>
        @else
        <p>Ante cualquier duda o inquietud tienen a disposición nuestros teléfonos de para contactarnos.</p>        
    		<p>Esperamos sepan comprender la situación y ayudar a cuidarnos entre todos.</p>
        @endif
        
        <p>Agradecemos su colaboración.</p>
    		<p>Cordialmente.</p>
    		<p>INIDEN (Instituto de Investigación en Diabetes, Endocrinología y Nutrición de Bahía Blanca)</p>
        </p>       	
       </div>
      <div class="modal-footer">
        <button type="button"
           class="rodri_button_aceptar" 
           data-dismiss="modal">Aceptar</button> 
      </div>
    </div>
  </div>
</div>