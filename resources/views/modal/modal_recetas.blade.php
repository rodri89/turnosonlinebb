<div class="modal fade" id="modalRecetas" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">      	
        <h4 class="modal-title"         
        id="modalTitleMensaje">Solicitar Receta</h4>
      </div>      
       <div class="modal-body">       	
        <p>Ingrese la información requerida para la solicitud de su receta.</p>
        <textarea class="width200px" id="motivo_receta" name="motivo_receta" rows="10" cols="55"></textarea>
        <br>
        <small>Nota: Indicar Droga, Nombre Comercial, Presentación y Cantidad.</small>
        <br>
        <small><b>-NO DEBE NOTIFICARLE AL ESPECIALISTA QUE SOLICITÓ UNA RECETA</b></small><br>
        <small><b>-LA CONFECCIÓN DE LA MISMA PUEDE TOMAR HASTA 7 DÍAS</b></small>
           <div hidden class="row marginLeft5px">
            <label for="text" class="col-sm-0 control-label">Retira en consultorio</label> 
           </div> 
           <div hidden class="row marginLeft5px">
            <div class="custom-control custom-radio">
                <input type="radio" id="retira_si" name="retira_consultorio_group" class="custom-control-input">
                <label class="custom-control-label" for="retira_si">Si</label>
            </div>
            <div class="custom-control custom-radio marginLeft5px">
               <input type="radio" id="retira_no" name="retira_consultorio_group" class="custom-control-input">
               <label class="custom-control-label" for="retira_no">No</label>
            </div>
          </div>
          <div hidden class="row marginLeft5px">
            <small for="text" class="col-sm-0 control-label">Si la opcion seleccionada es NO, podrá descargar la misma en su dispositivo.</small> 
           </div> 
          
        </div>
             
      <div class="modal-footer">
        <button type="button" 
           class="rodri_button_cancelar" 
           data-dismiss="modal">Cancelar</button>        
           <button type="button"
           onclick="enviarSolicitudReceta()"
           class="rodri_button_aceptar" 
           data-dismiss="modal">Aceptar</button>        
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="modalAdminReceta" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Solicitud Receta</h4>
      </div>      
       <div class="modal-body">           
        <!--<p><u>Información del paciente</u></p>-->
        <b>Paciente:</b><input class="sinBackgroundMedioAncho" type="text" id="modal_receta_paciente"></input><br>
        <b>DNI:</b><input class="sinBackgroundAnchoFechaDNI" type="text" id="modal_receta_dni"></input><br>
        <b>Fecha Nacimiento:</b><input class="sinBackgroundAnchoFechaDNI" type="text" id="modal_receta_fecha_nacimiento"></input><br>
        <b>Domicilio:</b><input class="sinBackgroundMedioAncho" type="text" id="modal_receta_direccion"></input><br>
        <b>Obra Social:</b><input class="sinBackgroundMedioAncho" type="text" id="modal_receta_obra_social"></input><br>
        <b>N°Afiliado:</b><input class="sinBackgroundMedioAncho" type="text" id="modal_receta_n_afiliado"></input><br>
        <b>Plan:</b><input class="sinBackgroundMedioAncho" type="text" id="modal_receta_plan"></input><br>
        <b>Fecha ultimo control:</b><input class="sinBackgroundAncho" type="text" id="modal_receta_fecha_ultimo_control"></input><br><br>
        <p><b>Motivo de la receta:</b></p>
        <textarea disabled class="width200px" id="modal_receta_motivo" name="modal_receta_motivo" rows="8" cols="50"></textarea>
        <b>Retira en consultorio:</b><input class="sinBackgroundMedioAncho" type="text" id="modal_retira_consultorio"></input><br>
       </div>

       <div id="seccion_receta_fotos_22" style="margin-left: 30px;margin-bottom: 15px;">
         <h6 class="modal-title"         
          id="modalTitleMensaje">Seleccione una foto de la receta y haga click en "Guardar".</h6>
          <form method="post" action="{{ route('cargarfotoreceta2') }}" enctype="multipart/form-data">
             @csrf 
             <div id="seccion_receta_fotos">
               <input hidden class="sinBackgroundMedioAncho" type="text" id="modal_receta_id" name="modal_receta_id"></input>         
               <input type="hidden" id="cantidad_fotos" name="cantidad_fotos"/>                                   
               <input type="file" id="foto_1" name="foto_1" /> 
            </div>
            <br>
            <button onclick="agregarBotonNuevaFoto()" type="button" class="rodri_button_aceptar">Agregar</button>   
            <br>  
            <br>     
            <small>Agregue un comentario para que pueda ver el paciente</small>
            <textarea class="width200px" id="modal_receta_comentario_secre" name="modal_receta_comentario_secre" rows="2" cols="50" placeholder="Puede pasar a retirar receta el dia dd/mm"></textarea>
       </div>

      <div class="modal-footer">
          <button hidden id="modal_receta_btn_cancelar_2" type="button" 
           onclick="cancelarSolicitudReceta2()"
           class="rodri_button_cancelar" 
           data-dismiss="modal">Cancelar</button>        
          <button id="modal_receta_btn_volver" type="button" 
           class="rodri_button_volver" 
           data-dismiss="modal">Volver</button>        
           <button id="modal_receta_btn_rechazar" type="button"
           onclick="rechazarSolicitudReceta()"
           class="rodri_button_cancelar" 
           data-dismiss="modal">Rechazar</button>        
           <button hidden id="modal_receta_btn_completa" type="button"
           onclick="completarSolicitudReceta()"
           class="rodri_button_aceptar" 
           data-dismiss="modal">Completa</button>   
                      
           <button hidden id="modal_receta_btn_completa_2" type="submit" 
           class="rodri_button_aceptar" 
           >Completa</button>

           <button hidden id="modal_receta_btn_confirmar" type="button"
           onclick="confirmarSolicitudReceta()"
           class="rodri_button_aceptar" 
           data-dismiss="modal">Confirmar</button>        
           <button hidden id="modal_receta_btn_entregada" hidden type="button"
           onclick="entregarSolicitudReceta()"
           class="rodri_button_aceptar" 
           data-dismiss="modal">Entregada</button>        
      </div>
    </form>
    </div>
  </div>
</div>


<div class="modal fade" id="modalRechazarReceta" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Rechazar Receta</h4>
      </div>      
       <div class="modal-body">   
        <input hidden class="sinBackgroundMedioAncho" type="text" id="modal_receta_rechazar_id"></input>
        <!--<p><u>Información del paciente</u></p>-->
        <b>Paciente:</b><input class="sinBackgroundMedioAncho" type="text" id="modal_receta_rechazar_paciente"></input><br>        
        <p><b>Motivo de la receta:</b></p>
        <textarea disabled class="width200px" id="modal_receta_rechazar_motivo_paciente" name="modal_receta_rechazar_motivo_paciente" rows="4" cols="50"></textarea>
        <p><b>Motivo rechazo:</b></p>
        <textarea class="width200px" id="modal_receta_rechazar_motivo_medico" name="modal_receta_rechazar_motivo_medico" rows="4" cols="50"></textarea>
       </div>
      <div class="modal-footer">
        <button id="modal_receta_btn_volver" type="button" 
           class="rodri_button_volver" 
           data-dismiss="modal">Volver</button>        
           <button id="modal_receta_btn_rechazar" type="button"
           onclick="rechazarSolicitudRecetaAccion()"
           class="rodri_button_cancelar" 
           data-dismiss="modal">Rechazar</button>                      
      </div>
    </div>
  </div>
</div>



<div class="modal fade" id="modalRecetaPaciente" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Receta Solicitada</h4>
      </div>      
       <div class="modal-body">   
        <input hidden class="sinBackgroundMedioAncho" type="text" id="modal_receta_paciente_id"></input>
        <!--<p><u>Información del paciente</u></p>-->
        <b>Paciente:</b><input class="sinBackgroundMedioAncho" type="text" id="modal_receta_paciente_nombre"></input><br>
        <b>DNI:</b><input class="sinBackgroundMedioAncho" type="text" id="modal_receta_paciente_dni"></input><br>        
        <b>Fecha Solicitud:</b><input class="sinBackgroundMedioAncho" type="text" id="modal_receta_paciente_fecha_solicitud"></input><br>
        <b>Estado:</b><input class="sinBackgroundMedioAncho" type="text" id="modal_receta_paciente_estado"></input><br>
        <p hidden id="estadoConfirmada">La receta ha sido aceptada y esta en proceso.</p>
        <p hidden id="estadoCompleta">La receta esta lista para retirar por el consultorio.</p>
        <p hidden id="estadoSolicitada">La receta ha sido solicitada por el paciente y esta siendo evaluada.</p>        
        <br>
        <p><b>Motivo de la receta:</b></p>
        <textarea disabled class="width200px" id="modal_receta_paciente_motivo" name="modal_receta_paciente_motivo" rows="8" cols="50"></textarea>

        <div hidden id="seccion_comentario_secretaria">          
          <b><label disabled class="letrasrojo" id="modal_receta_comentario_secretaria" name="modal_receta_paciente_motivo"></label></b>
        </div>
       </div>
      <div class="modal-footer">
        <button id="modal_receta_btn_volver" type="button" 
           class="rodri_button_volver" 
           data-dismiss="modal">Volver</button>                   
           <button id="modal_receta_btn_cancelar" type="button"
           onclick="cancelarSolicitudReceta()"
           class="rodri_button_cancelar" 
           data-dismiss="modal">Cancelar</button>        
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="modalRechazarRecetaPaciente" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Receta Rechazada</h4>
      </div>      
       <div class="modal-body">   
        <input hidden class="sinBackgroundMedioAncho" type="text" id="modal_receta_paciente_rechazar_id"></input>
        <!--<p><u>Información del paciente</u></p>-->
        <b>Paciente:</b><input class="sinBackgroundMedioAncho" type="text" id="modal_receta_paciente_rechazar_paciente"></input><br>        
        <p><b>Motivo de la receta:</b></p>
        <textarea disabled class="width200px" id="modal_receta_paciente_rechazar_motivo_paciente" rows="8" cols="50"></textarea>
        <p><b>Actualizar motivo receta:</b></p>
        <textarea class="width200px" id="modal_receta_paciente_rechazar_motivo_medico" rows="4" cols="50"></textarea>
       </div>
      <div class="modal-footer">
        <button id="modal_receta_btn_volver" type="button" 
           class="rodri_button_volver" 
           data-dismiss="modal">Volver</button>        
           <button id="modal_receta_btn_actualizar" type="button"
           onclick="actualizarSolicitudRecetaAccion()"
           class="rodri_button_aceptar" 
           data-dismiss="modal">Actualizar</button>                      
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalAyudaEstados" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title" 
        id="favoritesModalLabel">Receta Estados</h4>
      </div>
      <div class="modal-body"> 
          <h4>Estados en los que puede estar una receta:</h4><br>
          <h6><b>Solicitada:</b>La receta ha sido solicitada por el paciente.</h6>
          <h6><b>Confirmada:</b>La receta ha sido aceptada y esta en proceso.</h6>    
          <h6><b>Completa:</b> La receta esta lista para retirar.</h6>
          <h6><b>Rechazada:</b> La receta ha sido rechazada y usted puede ver el motivo en "Ver". </h6>
          <h6><b>Cancelada:</b> La receta ha sido cancelada por el paciente.</h6>
          <h6><b>Entregada:</b> La receta ha sido entregada a el paciente.</h6>
      </div>
      <div class="modal-footer">
        <button type="button" 
           class="rodri_button_aceptar" 
           data-dismiss="modal">Aceptar</button>                                      
      </div>                                      
      </div>
    </div>
</div>

<div class="modal fade" id="modalIngresarDomicilio" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Cargar Domicilio</h4>
      </div>      
       <div class="modal-body">   
        <input hidden class="sinBackgroundMedioAncho" type="text" id="modal_ingresar_domicilio_paciente_id"></input>
        <p>Para solicitar una receta es necesario cargar su domicilio.</p><br>
        <b>Domicilio:</b><input class="domicilio" type="text" id="modal_ingresar_domicilio_paciente_domicilio"></input><br>
       </div>
      <div class="modal-footer">
        <button id="modal_receta_btn_volver" type="button" 
           class="rodri_button_volver" 
           data-dismiss="modal">Volver</button>        
           <button id="modal_receta_btn_actualizar" type="button"
           onclick="cargarDomicilioAccion()"
           class="rodri_button_aceptar" 
           data-dismiss="modal">Aceptar</button>                      
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalIngresarNumeroAfiliado" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Cargar Numero Afiliado</h4>
      </div>      
       <div class="modal-body">   
        <input hidden class="sinBackgroundMedioAncho" type="text" id="modal_ingresar_numero_afiliado_paciente_id"></input>
        <p>Para solicitar una receta es necesario cargar su numero de afiliado de la obra social.</p><br>
        <b>Numero Afiliado:</b><input class="domicilio" type="text" id="modal_ingresar_numero_afiliado_paciente_na"></input><br>
       </div>
      <div class="modal-footer">
        <button id="modal_receta_btn_volver" type="button" 
           class="rodri_button_volver" 
           data-dismiss="modal">Volver</button>        
           <button id="modal_receta_btn_actualizar" type="button"
           onclick="cargarNumeroAfiliadoAccion()"
           class="rodri_button_aceptar" 
           data-dismiss="modal">Aceptar</button>                      
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="modalVerDatosPaciente" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title" 
        id="favoritesModalLabel">Datos del paciente:</h4>
      </div>
      <div class="modal-body">
        <label for="text" class="col-sm-0 control-label"><b>DNI</b></label>      
          <input type="text" class="form-control editText" id="modal_dni" placeholder="DNI"/>          

          <label for="text" class="col-sm-0 control-label"><b>Nombre</b></label>      
          <input type="text" class="form-control editText" id="modal_nombre"  placeholder="Nombre Paciente" />

          <label for="text" class="col-sm-0 control-label"><b>Apellido</b></label>      
          <input type="text" class="form-control editText" id="modal_apellido"  placeholder="Apellido Paciente"  />

          <label for="text" class="col-sm-0 control-label editText"><b>Fecha Nacimiento</b></label><br>
          <div class="row">
             <div class="fechaNacEditText">
              <input type="text" maxlength="2" class="form-control editText" id="modal_fecha_nacimiento_dia" name="fecha_nacimiento"  placeholder="dd" />
            </div>
             <label for="text" class="col-sm-0 control-label editText margin5"><b>/</b></label>
            <div class="fechaNacEditText">
              <input type="text" maxlength="2" class="form-control editText" id="modal_fecha_nacimiento_mes" name="fecha_nacimiento"  placeholder="mm" />
            </div>
            <label for="text" class="col-sm-0 control-label margin5"><b>/</b></label>
            <div class="fechaNacAnioEditText">   
              <input type="text" maxlength="4" class="form-control editText" id="modal_fecha_nacimiento_anio" name="fecha_nacimiento"  placeholder="YYYY" />
            </div>
            <input type="hidden" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento"  />
         </div>        

          <label for="text" class="col-sm-0 control-label"><b>Telefono</b></label>      
          <input type="text" class="form-control editText" id="modal_telefono"  placeholder="Telefono solo numeros (EJ: 2915050050)" />

          <label for="text" class="col-sm-0 control-label"><b>Localidad</b></label>      
          <input type="text" class="form-control editText" id="modal_localidad"  placeholder="Localidad" />

          <label for="text" class="col-sm-0 control-label"><b>Domicilio</b></label>      
          <input type="text" class="form-control editText" id="modal_domicilio"  placeholder="Domicilio" />

          <label for="text" class="col-sm-0 control-label"><b>Mail</b></label>      
          <input type="text" class="form-control editText" id="modal_mail" placeholder="Mail"  />

          <label for="text" class="col-sm-0 control-label"><b>Obra Social</b></label>      
          <input type="text" class="form-control editText" id="modal_obra_social" name="obra_social" placeholder="Obra Social"  />

          <label for="text" class="col-sm-0 control-label"><b>N° Afiliado</b></label>      
          <input type="text" class="form-control editText" id="modal_numero_afiliado" name="numero_afiliado" placeholder="N° Afiliado"  />
          
          <label for="text" class="col-sm-0 control-label"><b>Plan</b></label>      
          <input type="text" class="form-control editText" id="modal_plan_obra_social" name="plan_obra_social" placeholder="Plan Obra Social"  />
      </div>
      <div class="modal-footer">
        <button type="button" 
           class="rodri_button_volver" 
           data-dismiss="modal">Volver</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalCargarFotoReceta" 
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
        id="modalTitleMensaje">Seleccione una foto de la receta y haga click en "Guardar".</h6>
        <form method="post" action="{{ route('cargarfotoreceta') }}" enctype="multipart/form-data">
           @csrf 
           <div id="seccion_receta_fotos_2">
             <input type="hidden" id="foto_receta_id" name="foto_receta_id" />                                   
             <input type="hidden" id="cantidad_fotos_2" name="cantidad_fotos_2" />                                   
             <input type="file" id="foto-1" name="foto-1" /> 
          </div>
          <br>
          <button onclick="agregarBotonNuevaFoto()" type="button" class="rodri_button_aceptar">Agregar</button>  
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

<div class="modal fade" id="modalMostrarFotos" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Ver Fotos</h4>
      </div>      
       <div class="modal-body">            
         <input type="hidden" id="modal_ver_fotos_receta_id" name="modal_ver_fotos_receta_id"/>  
         <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
          <ol id="seccion_ol" class="carousel-indicators">
            <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>          
          </ol>
          <div id="seccion_mostrar_fotos" class="carousel-inner">
            <div class="carousel-item active">
              <img id="img-0" class="d-block w-100">              
            </div>            
          </div>
          
          <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon img_size_carrousel" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
          </a>
          <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
            <span class="carousel-control-next-icon img_size_carrousel" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
          </a>
        </div>
       <div id="seccion_descargas">       
       </div>
      <div class="modal-footer">  
        <button onclick="cerrarModal()" type="button" 
           class="rodri_button_volver" 
           data-dismiss="modal">Cerrar</button>        
      </div>
    </div>
  </div>
</div>
</div>

<script>
  
  function agregarBotonNuevaFoto(){
    var cantidadFotos = document.getElementById("cantidad_fotos").value;  //1      
    var viejoValor = parseInt(cantidadFotos); // 1
    var nuevoValor = parseInt(cantidadFotos) + 1;      
  //  var btn = document.getElementById("foto-"+viejoValor);
    var ultimoFile = document.getElementById("foto_"+viejoValor);
    var f = ultimoFile.value;
    if(f.localeCompare('') != 0){
      $('#cantidad_fotos').val(nuevoValor);
      var seccion = document.getElementById("seccion_receta_fotos");                  
      var input = document.createElement("INPUT");
      input.type = 'file';
      input.id = 'foto_'+nuevoValor;
      input.name = 'foto_'+nuevoValor;
      seccion.appendChild(input);
    }
  }

  function borrarSeccion(seccion){
    var myNode = document.getElementById(seccion);
            while (myNode.firstChild) {
                   myNode.removeChild(myNode.firstChild);
            }
  }

</script>
