<div class="modal fade" id="modalImprimirRecetas" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">      	        
        <h4 class="modal-title"         
        id="modalMensajeTitulo">Ingresar Datos Receta</h4>
      </div> 
      
      	<div class="contenedor_rodri" style="width: 400px; float: left;">
	        <label>Seleccionar Paciente</label>	      	        
	        <input type="hidden" id="mir_paciente_id">	        
	        <input type="text" id="dni_paciente" value="" placeholder="Ingrese el DNI" onchange="validarPacienteExiste()" />
      		<button class="rodri_button_calendario divMarginCel" type="button" onclick="mostrarModalBuscar()"><img class="card-img-top" src="images/iconos/buscar.png"/></button>
	        
    	</div>

      <div class="modal-footer">
        <button type="button"
           class="rodri_button_aceptar" 
           data-dismiss="modal" onclick="VoucherPrint('/images/iconos/receta_modelo.png', '/images/iconos/firma.png')">Imprimir</button> 
      </div>
    </div>
  </div>
</div>

<style type="text/css">
	
	.contenedor_rodri{
    position: relative;
    display: inline-block;
    text-align: center;
	}
	.texto_encima_rodri{
	    position: absolute;
	    top: 10px;
	    left: 10px;
	}
	.centrado_rodri{
	    position: absolute;
	    top: 50%;
	    left: 50%;
	    transform: translate(-50%, -50%);
	}

</style>

<script type="text/javascript">
	
	function imprimirReceta(){
		$("#modalImprimirRecetas").modal('show'); 
	}

	function VoucherSourcetoPrint(source, source2) {		
            return "<html><head><style>.firma_stylo{widht:50px;height:50px;position: absolute;top: 80%;right: 10%;transform: translate(-50%, -50%);} .contenedor_rodri{position: relative;display: inline-block;text-align: center;}.texto_encima_rodri{position: absolute;top: 10px;left: 10px;}.centrado_rodri{position: absolute;top: 50%;left: 50%;transform: translate(-50%, -50%);}</style><script>function step1(){\n" +
                    "setTimeout('step2()', 10);}\n" +
                    "function step2(){window.print();window.close()}\n" +
                    "</scri" + "pt></head><body onload='step1()'>\n" +
                    "<div class='contenedor_rodri'><img src='" + source + "' /><img class='firma_stylo' src='" + source2 + "' /><label hidden class='centrado_rodri'>Rodri</label></div></body></html>";
        }
        function VoucherPrint(source, source2) {
            Pagelink = "about:blank";
            var pwa = window.open(Pagelink, "_new");
            pwa.document.open();
            pwa.document.write(VoucherSourcetoPrint(source, source2));
            pwa.document.close();
        }


function validarPacienteExiste() {
  	var dni = document.getElementById("dni_paciente").value;      		
    var consultorio = document.getElementById("consultorio").value;
		$.ajax({
       type:'POST',
       dataType:'JSON',
       url:'/paciente_consultar',
       data:{dni_paciente :dni, consultorio:consultorio, _token: '{{csrf_token()}}'},
       success:function(data){           		
	       	var paciente = document.getElementById("paciente");	       	
  	   		var dni = document.getElementById("dni");
  	   		var telefono = document.getElementById("telefono");
  	   		var mail = document.getElementById("mail");
  	   		var obrasocial = document.getElementById("obrasocial");
  	   		var horarioSeleccionado = document.getElementById("horario_seleccionado").value;
          document.getElementById("msj_no_asistio").hidden = true;
	        if (data.paciente != null) {
            validarAsistioUltimaVes(data.paciente.id);        	       	
	       		document.getElementById("pacienteNoExiste").hidden=true;	       			       					       		
	       		paciente.innerHTML = data.paciente.apellido+", "+data.paciente.nombre;	       			       		
	       		dni.innerHTML = data.paciente.dni;
				    telefono.innerHTML = data.paciente.telefono;
    				mail.innerHTML = data.paciente.mail;
    				obrasocial.innerHTML = data.paciente.obra_social;
    				paciente_id.innerHTML = data.paciente.id;	
								
    				if (horarioSeleccionado.localeCompare('') == 0) {					
    					document.getElementById("debeSeleccionarHorario").hidden=false;									
    				} else {					
    					document.getElementById("altaTurno").hidden=false;
    					document.getElementById("debeSeleccionarHorario").hidden=true;					
    				}				
  	       } else {       
  	       		document.getElementById("debeSeleccionarHorario").hidden=false;	    
  	       		document.getElementById("pacienteNoExiste").hidden=false;          				
  	       		document.getElementById("debeSeleccionarHorario").hidden=true;					
  	       		document.getElementById("altaTurno").hidden=true;	     	       		  	
  	       		paciente.innerHTML = "";
  	       		dni.innerHTML = "";
      				telefono.innerHTML = "";
      				mail.innerHTML = "";
      				obrasocial.innerHTML = "";
      				paciente_id.innerHTML = "";
      			}	              
	       }
    });  	
  }

  function mostrarModalBuscar(){
    $("#modalBuscarPaciente").modal();  
  }

</script>