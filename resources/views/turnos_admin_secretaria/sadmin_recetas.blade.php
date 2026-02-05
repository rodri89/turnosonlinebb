@extends('turnos_admin_secretaria/turnos_admin_secretaria_plantilla_body')

@section('contenedor')

<head>

<script src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="{{asset('js/jquery.min.js')}}"> </script>
</head>

<h2> Recetas Solicitadas</h2>
 <div class="container">
<input type="hidden" id="consultorio" name="consultorio" value="{{$consultorio}}"/>  
  <table class="table table-striped" id="laravel_datatable">
               <thead class="fondoNav text-white">
                  <tr>                    
                    <th scope="col">Solicitud</th>
                    <th scope="col">Medico</th> 
                    <th scope="col">Paciente</th>      
                    <th scope="col">Telefono</th>
                    <th scope="col">Motivo</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Acción</th>
                    <th class="editText">Foto</th>      
                  </tr>
               </thead>
            </table>
         </div>

  @include('modal.snackbar')
  <div id="snackbar"><p id="snackbar_text"></p></div>

   <script>
   
   $(document).ready( function () {
    $('#laravel_datatable').DataTable({
           language: {
              "decimal": "",
              "emptyTable": "No hay información",
              "info": "Mostrando _START_ a _END_ de _TOTAL_ Entradas",
              "infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
              "infoFiltered": "(Filtrado de _MAX_ total entradas)",
              "infoPostFix": "",
              "thousands": ",",
              "lengthMenu": "Mostrar _MENU_ Entradas",
              "loadingRecords": "Cargando...",
              "processing": "Procesando...",
              "search": "Buscar:",
              "zeroRecords": "Sin resultados encontrados",
              "paginate": {
                  "first": "Primero",
                  "last": "Ultimo",
                  "next": "Siguiente",
                  "previous": "Anterior"
              },
           },
           processing: false,
           serverSide: false,
           ajax: "{{ url('receta-listado') }}",
           columns: [
                    { data: 'solicitud', name: 'solicitud' },
                    { data: 'medico', name: 'medico' },                    
                    { data: 'paciente', name: 'paciente' },                    
                    { data: 'telefono', name: 'telefono' },                    
                    { data: 'motivo_aux', name: 'motivo_aux' },
                    { data: '_estado', name: '_estado' },                    
                    { data: 'action', name: 'action', orderable: false, searchable: false},
                    { data: 'fotto', name: 'fotto', orderable: false, searchable: false}                                       
                 ]
        });
     });

   function mostrarSnackbar(cs) {      
      document.getElementById("snackbar_text").innerHTML = cs;
      var x = document.getElementById("snackbar");
      x.className = "show";
      setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
    }

   function recetaListaEnviar(receta_id){
      $.ajax({
       type:'POST',
       dataType:'JSON',
       url:'/receta_lista_enviar',
       data:{receta_id:receta_id, _token: '{{csrf_token()}}'},
       success:function(data){                  
          if(data.receta.estado == 7){            
            mostrarSnackbar("Receta lista para enviar"); 
            location.reload();
          }
         }
    });       
   }
    
    function verReceta(receta_id){                
      $.ajax({
        type:'POST',
        dataType:'JSON',
        url:'/secretaria_ver_receta',
        data:{receta_id:receta_id, _token: '{{csrf_token()}}'},
        success:function(data){      
          var paciente = data.receta.papellido+", "+data.receta.pnombre;          
         
          $('#modal_receta_paciente').val(paciente);  
          $('#modal_receta_dni').val(data.receta.dni);
          $('#modal_receta_fecha_ultimo_control').val(data.fechaUltimaConsulta);
          var fechaNacimientoAuxArray = data.receta.fecha_nacimiento.split("-");
          var fechaNacimientoAux = fechaNacimientoAuxArray[2]+"/"+fechaNacimientoAuxArray[1]+"/"+fechaNacimientoAuxArray[0];
          $('#modal_receta_fecha_nacimiento').val(fechaNacimientoAux);
          $('#modal_receta_direccion').val(data.receta.domicilio);
          $('#modal_receta_obra_social').val(data.receta.obra_social);
          $('#modal_receta_n_afiliado').val(data.receta.numero_afiliado);
          $('#modal_receta_plan').val(data.receta.obra_social_plan);
          var motivo = formatMotivo(data.receta.motivo);
          $('#modal_receta_motivo').val(motivo);
          $('#modal_receta_id').val(data.receta.rec_id);
          $('#cantidad_fotos').val(1);
          
          if(data.receta.comentario.localeCompare('') != 0){
            document.getElementById("modal_receta_comentario_secre").value = data.receta.comentario;
          } else {
            document.getElementById("modal_receta_comentario_secre").value = "";
          }

          document.getElementById("seccion_receta_fotos_22").hidden = true;

          var retiraConsultorio = '-';
          if(data.receta.retira_consultorio == 1)
            retiraConsultorio = 'Si';
          if(data.receta.retira_consultorio == 2)
            retiraConsultorio = 'No';

          $('#modal_retira_consultorio').val(retiraConsultorio);
          ocultarBotonesModal();
          
          if(data.receta.estado_id == 1){ // estado 1 es solicitado
         //   alert("solicitado");           
            document.getElementById("modal_receta_btn_rechazar").hidden = false; 
            //document.getElementById("modal_receta_btn_confirmar").hidden = false; 
            document.getElementById("modal_receta_btn_completa_2").hidden = false; 
            document.getElementById("seccion_receta_fotos_22").hidden = false; 
          }
          if(data.receta.estado_id == 2){ // estado 2 es confirmada                                 
            document.getElementById("modal_receta_btn_completa").hidden = false; 
          }
          if(data.receta.estado_id == 3 || data.receta.estado_id == 7){ // estado 3 es completa            
           // alert("Entregada");                       
            document.getElementById("modal_receta_btn_entregada").hidden = false;             
          }     
          if(data.receta.estado_id ==4){ // estado 4 es rechazada            
           // alert("Entregada");                       
            document.getElementById("modal_receta_btn_cancelar_2").hidden = false; 
          }    
          $('#modalAdminReceta').modal();  
        }
      });
    }

    function verFoto(receta_id){          
    $.ajax({
       type:'POST',
       dataType:'JSON',
       url:'/obtener_fotos_receta',
       data:{receta_id:receta_id, _token: '{{csrf_token()}}'},
       success:function(data){                  
            //$("#img-0").attr("src", "images/recetas/"+data.recetas[0].foto);
            for(i = 0; i < data.recetas.length; i++){
              if(i == 0){                
                $("#img-"+i).attr("src", "images/recetas/"+data.recetas[i].foto);
              } else {                              
                var seccion_ol = document.getElementById("seccion_ol"); 
                var li = document.createElement("LI");
                
                li.setAttribute('data-target', '#carouselExampleIndicators');
                li.setAttribute('data-slide-to', i);
                seccion_ol.appendChild(li);

                var seccion_mostrar_fotos = document.getElementById("seccion_mostrar_fotos"); 
                var div = document.createElement("DIV");
                div.setAttribute('class', 'carousel-item');

                var img = document.createElement("IMG");
                img.src = "images/recetas/"+data.recetas[i].foto;                
                img.setAttribute('class', 'd-block w-100');                
               
                seccion_mostrar_fotos.appendChild(div);      
                 div.appendChild(img);          
              }
            }
            $('#modalMostrarFotos').modal();            
         }
    }); 
 }

    function confirmarSolicitudReceta(){
      var receta_id = document.getElementById("modal_receta_id").value; 
      var estado_id = 2;
      $.ajax({
         type:'POST',
         dataType:'JSON',
         url:'/actualizar_estado_receta',
         data:{receta_id:receta_id, estado_id:estado_id, _token: '{{csrf_token()}}'},
         success:function(data){                  
            location.reload();
           }
      });
    }

    function formatMotivo(motivo){
      var motivoArray = motivo.split("|");
      var motivoFormat = "";
      for(var i = 0; i<motivoArray.length; i++){
        motivoFormat = motivoFormat + motivoArray[i]+"\r\n";  
      }
      return motivoFormat;
    }

    function rechazarSolicitudReceta(){
      var receta_id =  document.getElementById("modal_receta_id").value;
      var paciente =  document.getElementById("modal_receta_paciente").value;
      var motivo =  document.getElementById("modal_receta_motivo").value;      
      motivo = formatMotivo(motivo);
      $('#modal_receta_rechazar_id').val(receta_id);
      $('#modal_receta_rechazar_paciente').val(paciente);
      $('#modal_receta_rechazar_motivo_paciente').val(motivo);
      $('#modalRechazarReceta').modal();  
    }

    function rechazarSolicitudRecetaAccion(){
      var receta_id =  document.getElementById("modal_receta_rechazar_id").value;
      var estado_id = 4; // estado rechazado
      var motivo_rechazo = document.getElementById("modal_receta_rechazar_motivo_medico").value;
      $.ajax({
         type:'POST',
         dataType:'JSON',
         url:'/actualizar_estado_receta',
         data:{receta_id:receta_id,motivo_rechazo:motivo_rechazo, estado_id:estado_id, _token: '{{csrf_token()}}'},
         success:function(data){                  
            location.reload();
           }
      });
    }

    function entregarSolicitudReceta(){
      var receta_id = document.getElementById("modal_receta_id").value; 
      var estado_id = 5; // estado Entregada
      $.ajax({
         type:'POST',
         dataType:'JSON',
         url:'/actualizar_estado_receta',
         data:{receta_id:receta_id, estado_id:estado_id, _token: '{{csrf_token()}}'},
         success:function(data){                  
            location.reload();
           }
      });
    }

    function completarSolicitudReceta(){
      var receta_id = document.getElementById("modal_receta_id").value; 
      var estado_id = 3; // estado Completa
      var comentario = document.getElementById("modal_receta_comentario_secre").value;
      alert(comentario);
      $.ajax({
         type:'POST',
         dataType:'JSON',
         url:'/actualizar_estado_receta',
         data:{receta_id:receta_id, estado_id:estado_id, comentario:comentario, _token: '{{csrf_token()}}'},
         success:function(data){                  
            location.reload();
           }
      });
    }

    function ocultarBotonesModal(){
      document.getElementById("modal_receta_btn_rechazar").hidden = true;
      document.getElementById("modal_receta_btn_completa").hidden = true;
      document.getElementById("modal_receta_btn_confirmar").hidden = true;
      document.getElementById("modal_receta_btn_entregada").hidden = true;       
    }

    function cargarRecetaFoto(receta_id){
      $('#cantidad_fotos').val(1);
      $('#foto_receta_id').val(receta_id);  
      $('#modalCargarFotoReceta').modal();  
     }

 function cerrarModal(){
    //location.reload();    
    var carouselExampleIndicators = document.getElementById("carouselExampleIndicators");
    var seccion_mostrar_fotos = document.getElementById("seccion_mostrar_fotos");
    seccion_mostrar_fotos.remove();
    var div = document.createElement("DIV");
    div.setAttribute('class', 'carousel-item active');
    var img = document.createElement("IMG");    
    img.setAttribute('class', 'd-block w-100'); 
    img.id = 'img-0';  
    seccion_mostrar_fotos.appendChild(div);      
    div.appendChild(img); 
    carouselExampleIndicators.appendChild(seccion_mostrar_fotos);

    var seccion_descargas = document.getElementById("seccion_descargas");
    seccion_descargas.remove();
 }

  </script>

@include('modal.modal_recetas')
 
<script type="text/javascript">
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  // tendria que hacer un ajax que me diga si hubo algun cambio en la base entonces actualizo la pag sino no.
 function recargarListado(){
  //location.reload();
  checkRecetasPendientes();  
 }

 /*window.onload=function() {          
    checkRecetasPendientes();
    checkResolutionMobile();
      alert("Tets ");
  } */

</script>
<br><br><br><br><br>
@endsection
