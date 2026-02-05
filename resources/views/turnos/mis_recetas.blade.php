
@extends('turnos/modelo_plantilla')

@section('title','Consultar Receta')

@section('headerContainer')
<ul>  
  <li class="lead fontColorHeader">En esta sección podra consultar el estado de su receta.</li>
  <li class="lead fontColorHeader">Ingrese el número de DNI y click en "Consultar". </li>
  <li class="lead fontColorHeader">Si la solicitud fue rechazada usted podrá ver el motivo haciendo click en "Ver". </li>
  <li class="lead fontColorHeader">La solicitud de recetas por este medio dependerá de la aceptación previa del profesional.</li>  
  <li class="lead fontColorHeader">Posibles estados de la receta y significado. <a data-target="#modalAyudaEstados" data-toggle="modal" class="MainNavText" id="MainNavHelp" 
       href="#modalAyuda">Click aquí</a></li>
</ul>

@endsection

@section('titulo_header','Consultar mis recetas')

@section('body_titulo','')

@section('contenedor')
 <div class="row">
 
  		<form method="POST" action="{{ route('consultarreceta') }}">
  		  @csrf   
		  	<h2 class="fontHomeTitulo lead marginLeft10px">DNI del Paciente:</h2>
		  	@if($dni_paciente)
			  <input class="marginLeft10px marginTop20px" type="text" name="dni_paciente" value="{{$dni_paciente}}" />       			    		
			  @else
				<input class="marginLeft10px marginTop20px" type="text" name="dni_paciente" value="" />       			    				 
			  @endif
		  
		  <button type="submit" class="rodri_button">Consultar</button>                          
		</form>
	
	@if($mensaje)
		<div class="col-md-8">
			<h2 class="text-center fontHomeTitulo lead marginTop50px">{{$mensaje}}</h2>
		</div>
	@else
	@if($dni_paciente)
		@if($recetas)
		<br>
		<div class="col-md-8 marginTop20px">
			<h4>Mis Recetas:</h4>
			<div class="table-responsive" style="min-height:400px; ">
			<table class="table table-condensed" id="tabla_pacientes" name="tabla_pacientes">
	 		 <thead>
			    <tr>
			      <th class="editText" scope="col">#</th>			      		    
			      <th class="editText" scope="col">Medico</th>
			      <th class="editText" scope="col">Motivo</th>
			      <th class="editText" scope="col">Estado</th>
			      <th class="editText" scope="col">Fotos</th>
			      <th class="editText" scope="col">Detalles</th>
			    </tr>
	  		</thead>
	  		 <tbody id="pacientes-list" name="pacientes-list">
	  			<?php $cont = 1 ?>
	  			@foreach($recetas as $tr)
          
			    <tr>
			      <th class="editText" scope="row">{{$cont++}}</th>			      		     
			      <td class="editText">{{$tr["apellido"].', '.$tr["nombre"]}}</td>		      
			      <?php $motivo_aux = substr($tr["motivo"], 0, 20).'...';?>
			      <td class="editText">{{$motivo_aux}}</td>
			      @if($tr["estado_id"] == 4)
			      	<td class="editText letrasrojo">{{$tr["estado"]}}</td>
			      @else
					<td class="editText">{{$tr["estado"]}}</td>
			      @endif
			      <form>
			      	 @csrf   
			  		<input type="hidden" name="turno_id" value="{{$tr['trid']}}" />
			  		<input type="hidden" name="dni_paciente" value="{{$dni_paciente}}" />			  		
			  	  <td class="editText"><button onclick="verFotoRecetas('{{$tr['trid']}}')" type="button" class="rodri_button_aceptar_si">Ver</button></td>
			      <td class="editText"><button onclick="verRecetaPaciente('{{$tr['trid']}}')" type="button" class="rodri_button_aceptar_si">Ver</button></td>
			  	  </form>
			    </tr>
          
			    @endforeach
			</tbody>
		</table>
		</div>
		</div>
		@else
		<div class="col-md-8">
			<h2 class="text-center fontHomeTitulo lead marginTop50px">No tiene recetas registradas.</h2>
		</div>
		@endif
	@endif
	@endif
	<br>
	<br>
	<br>
	<br><br><br><br>	
</div>

<div class="modal fade" id="modalCancelarTurno" 
     tabindex="-1" role="dialog" 
     aria-labelledby="favoritesModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">        
        <h4 class="modal-title"         
        id="modalTitleMensaje">Turno Cancelado</h4>
      </div>      
       	<div class="modal-body">
       		
        	<!--<img id="modalImagenMensaje" class="card-img-top" src="images/iconos/ic_ok.png" alt="">                       -->
        	<p>Su turno ha sido cancelado.</p>
       	</div>
      	<div class="modal-footer">
        	<button class="rodri_button_aceptar" data-dismiss="modal">Finalizar</button>            
      	</div>
  
    </div>
  </div>
</div>


@include('modal.modal_recetas')
@include('modal.snackbar')

<div id="snackbar"><p> No tiene fotos cargadas </p></div>


<script type="text/javascript">

$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

function formatMotivo(motivo){
  var motivoArray = motivo.split("|");
  var motivoFormat = "";
  for(var i = 0; i<motivoArray.length; i++){
    motivoFormat = motivoFormat + motivoArray[i]+"\r\n";  
  }
  return motivoFormat;
}

// input 2020-02-01 16:08:54
function formatFecha(fecha){
	var fechaArray = fecha.split(" ");
	var fechaArray2 = fechaArray[0].split("-");
	return fechaArray2[2]+"/"+fechaArray2[1]+"/"+fechaArray2[0];
}

function verRecetaPaciente(receta_id){   
  $.ajax({
   type:'POST',
   dataType:'JSON',
   url:'/ver_receta_paciente',
   data:{receta_id:receta_id, _token: '{{csrf_token()}}'},
   success:function(data){              	  
      motivo = formatMotivo(data.receta.motivo);
      fecha = formatFecha(data.receta.solicitud);
	  if(data.receta.estado_id != 4){ 
	  	  if(data.receta.estado_id == 1)
	  	   	document.getElementById("estadoSolicitada").hidden = false;
	  	   if(data.receta.estado_id == 2)
	  	   	document.getElementById("estadoConfirmada").hidden = false;
	  	   if(data.receta.estado_id == 3){
	  	   	document.getElementById("estadoCompleta").hidden = false;
	  	   	document.getElementById("modal_receta_btn_cancelar").hidden = true;
	  	   }
         if(data.receta.estado_id == 5){          
          document.getElementById("modal_receta_btn_cancelar").hidden = true;
         }
	      $('#modal_receta_paciente_id').val(data.receta.rec_id);
	      $('#modal_receta_paciente_nombre').val(data.receta.papellido+", "+data.receta.pnombre);
	      $('#modal_receta_paciente_dni').val(data.receta.dni);
	      $('#modal_receta_paciente_estado').val(data.receta.estado);
	      $('#modal_receta_paciente_motivo').val(motivo);
	      $('#modal_receta_paciente_fecha_solicitud').val(fecha);	 

        if(data.receta.comentario.localeCompare('') != 0){
          document.getElementById("seccion_comentario_secretaria").hidden = false;
          document.getElementById("modal_receta_comentario_secretaria").innerHTML = data.receta.comentario;
        } else {
          document.getElementById("seccion_comentario_secretaria").hidden = true;
          document.getElementById("modal_receta_comentario_secretaria").innerHTML = '';
        }   
	      
        $('#modalRecetaPaciente').modal();     		 
  	  } else {
  	  	$('#modal_receta_paciente_rechazar_id').val(data.receta.rec_id);
	      $('#modal_receta_paciente_rechazar_paciente').val(data.receta.papellido+", "+data.receta.pnombre);	      
	      $('#modal_receta_paciente_rechazar_motivo_paciente').val(motivo);	      
  	  	  $('#modalRechazarRecetaPaciente').modal();     	
  	  }
   }
  });
}

function actualizarSolicitudRecetaAccion(){
      var receta_id =  document.getElementById("modal_receta_paciente_rechazar_id").value;
      var estado_id = 1; // estado solicitada
      var motivo_rechazo = document.getElementById("modal_receta_paciente_rechazar_motivo_medico").value;
      $.ajax({
         type:'POST',
         dataType:'JSON',
         url:'/actualizar_estado_receta_paciente',
         data:{receta_id:receta_id,motivo_rechazo:motivo_rechazo, estado_id:estado_id, _token: '{{csrf_token()}}'},
         success:function(data){                  
            location.reload();
           }
      });
    }

function cancelarSolicitudReceta(){
  var receta_id = document.getElementById("modal_receta_paciente_id").value; 
  var estado_id = 6; // cancelado
  $.ajax({
     type:'POST',
     dataType:'JSON',
     url:'/actualizar_estado_receta_paciente',
     data:{receta_id:receta_id, estado_id:estado_id, _token: '{{csrf_token()}}'},
     success:function(data){                  
        location.reload();
       }
  });
}

function verFotoRecetas(receta_id){	  
  console.log("rodri mi receta_id "+receta_id);
	// $( "a" ).remove( ".modal_mostrar_fotos_s" );
	  $.ajax({
       type:'POST',
       dataType:'JSON',
       url:'/ver_receta_paciente_fotos',
       data:{receta_id:receta_id, _token: '{{csrf_token()}}'},
       success:function(data){ 
          console.log("rodri data "+data.recetas); 
       		var br = document.createElement('br');   
       		var seccion_descargas = document.getElementById("seccion_descargas");          
            //$("#img-0").attr("src", "images/recetas/"+data.recetas[0].foto);
            for(i = 0; i < data.recetas.length; i++){
            	var numeroFoto = i+1;
              if(i == 0){                
                $("#img-"+i).attr("src", "images/recetas/"+data.recetas[i].foto);                
                var a = document.createElement('a');      
                a.href = "images/recetas/"+data.recetas[i].foto;
               	a.setAttribute('download', data.recetas[i].foto);  
               //	a.setAttribute('class', "modal_mostrar_fotos_s");  
               	a.innerHTML = "Descargar foto"+numeroFoto;              
                seccion_descargas.appendChild(a);                 
              } else {                              
                var seccion_ol = document.getElementById("seccion_ol"); 
                var li = document.createElement("LI");
                
                li.setAttribute('data-target', '#carouselExampleIndicators');
                li.setAttribute('data-slide-to', i);
              //  li.setAttribute('class', "modal_mostrar_fotos_s");  
                seccion_ol.appendChild(li);

                var seccion_mostrar_fotos = document.getElementById("seccion_mostrar_fotos"); 
                var div = document.createElement("DIV");
                div.setAttribute('class', 'carousel-item');

                var img = document.createElement("IMG");
                img.src = "images/recetas/"+data.recetas[i].foto;                
                img.setAttribute('class', 'd-block w-100');                
             //  	img.setAttribute('class', "modal_mostrar_fotos_s");  

                seccion_mostrar_fotos.appendChild(div);      
                div.appendChild(img);  
                                
                var a = document.createElement('a');      
                a.href = "images/recetas/"+data.recetas[i].foto;
               	a.setAttribute('download', data.recetas[i].foto);
              // 	a.setAttribute('class', "modal_mostrar_fotos_s");                  	
               	a.innerHTML = "Descargar foto"+numeroFoto;
                seccion_descargas.appendChild(br);
                seccion_descargas.appendChild(a);                 
              }
            }
            console.log(data.recetas);
            //alert(data.recetas[0]);
            if(data.recetas[0]!=null){
              document.getElementById("modal_ver_fotos_receta_id").value = data.recetas[0].receta; 
              //document.getElementById("download").hidden = false;             			   
              $('#modalMostrarFotos').modal();
            } else {
              mostrarSnackbar("No tiene fotos cargadas");            
            }
         }
    }); 
}

function cerrarModal(){
	location.reload();
}

function descargarRecetas(){
	var receta_id = document.getElementById("modal_ver_fotos_receta_id").value;	
	
	 $.ajax({
       type:'POST',
       dataType:'JSON',
       url:'/obtener_fotos_receta',
       data:{receta_id:receta_id, _token: '{{csrf_token()}}'},
       success:function(data){        
	       	for(i = 0; i < data.recetas.length; i++){
//	       	<a href="http://localhost/file.xml" download="file.xml">descargar</a>
       			         	
	         	window.open("images/recetas/"+data.recetas[i].foto, '_blank');
	         	//location.href = "images/recetas/"+data.recetas[i].foto; 
	        }           	                  
	                    
        }
    }); 

}

function mostrarSnackbar() {
    //var cs = document.getElementById("cantSobreturnos").value;    
    var x = document.getElementById("snackbar");
    x.className = "show";
    setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
  }


</script>

@endsection