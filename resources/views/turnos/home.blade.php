<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Sitio web para turnos online de medicos en Bahia Blanca.">
  <meta name="author" content="Rodrigo Banegas">
  <meta name="keywords" content="turnos online, medicos, pediatria cardiologo, cardiologia, bahia blanca, turnos, profesionales">

  <title>@yield('title','Turnos Online')</title>
  <link rel="shortcut icon" type="image/x-icon" href="/images/iconos/turnosonlinebb_icon.png" />
  <!-- Bootstrap core CSS -->
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom styles for this template -->
  <link href="css/business-frontpage.css" rel="stylesheet">

   @include('layouts.rodri_style_css')
</head>

<style type="text/css">
   .fontHomeBody3{
    color:#303F9F;
    margin-top: 25px;
    font-size: 1.2rem;   
    float: right;
  }

  .mobile-screens-row {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    align-items: flex-start;
    gap: 12px;
    margin: 0 auto;
  }

  .mobile-screen-shot {
    max-width: 100%;
    height: auto;
  }

  @media (max-width: 767px) {
    .mobile-screen-shot {
      width: 31%;
      min-width: 92px;
    }
  }
</style>

<body>

  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg fontNav fixed-top fondoNav">
    <div class="container">      
      <a class="textheader navbar-brand text-white" href="homes">Turnos Online</a>
    </div>    
    <!--<a href="/login" class="text-white">Login</a>-->
  </nav>  
  <div id="carouselExampleIndicators" class="carousel slide widthImageCarousel" data-ride="carousel">
      <ol class="carousel-indicators">
        <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
        <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
        <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
       <!-- <li data-target="#carouselExampleIndicators" data-slide-to="3"></li>-->
      </ol>
      <div class="carousel-inner">
        <div class="carousel-item active">
          <img class="d-block w-100" src="images/iconos/bebe1.png" alt="First slide">
          <div class="carousel-caption d-none d-md-block">
           <!--<h5>La mejor atención</h5> 
            <p>...</p>-->
          </div>
        </div>
        <div class="carousel-item">
          <img class="d-block w-100" src="images/iconos/imagen4header.png" alt="Second slide">
          <div class="carousel-caption d-none d-md-block">
           <!-- <h5>La mejor atención</h5> 
             <p>...</p> -->
          </div>
        </div>
        <div class="carousel-item">
          <img class="d-block w-100" src="images/iconos/nena2.png" alt="Second slide">
          <div class="carousel-caption d-none d-md-block">
           <!-- <h5>La mejor atención</h5> 
             <p>...</p> -->
          </div>
        </div>
        <!--<div class="carousel-item">
          <img class="d-block w-100" src="images/iconos/imagen3header.png" alt="Third slide">
          <div class="carousel-caption d-none d-md-block">
            <h5>La mejor atención</h5>
            <p>...</p>
          </div> 
        </div>-->
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
  
  <!--</header> -->

  <!-- Page Content -->
  <div class="container">    

    <div class="row">
      <div class="col-md-12 mb-5"> 
        <br>   
          <h3 class="text-center fontHomeTitulo lead">"Reserva un turno de manera fácil y rápida con cualquier dispositivo y desde cualquier lugar".</h3>
        
        <br><br>
        <h3 class="lead fontHomeSubtitulo"><b>¿Cómo obtener un turno?</b></h3>
        <br>
        <hr class="style4">
        <br>
        <div class="row">
           <div class="col-md-9 mx-auto">
            <img src="images/iconos/obtenerTurnos1.png" class="img_home r_inline">
            <h5 class="text-center lead fontHomeBody r_inline">1. Click en COMENZAR. <br> En el panel principal "Seleccione el <br> médico que desea".</h5>            
           </div> 
		    </div>      
      <br>
      <br>
      <hr class="style4">
      <br>
      <div class="row">
        <div class="col-md-9 mx-auto">
          <h5 class="text-center lead fontHomeBody r_inline">2. Ingrese los datos del niño <br> para poder realizar la reserva de su turno.</h5> 
          <img src="images/iconos/obtenerTurnos2.png" class="img_home r_inline">  
        </div>
      </div>      
      <br>
      <br>
      <hr class="style4">
      <br>
      <div class="row">           
        <div class="col-md-9 mx-auto">
          <img src="images/iconos/obtenerTurnos3.png" class="r_inline img_home">  
          <h5 class="text-center lead fontHomeBody r_inline">3. Seleccione el día y horario que desee, <br> y elija su turno.</h5> 
        </div>
                 
      </div>
      </div>
    </div>  
    
    <div hidden>
      <hr class="style4">
      <h3 class="lead fontHomeSubtitulo"><b>Videollamadas</b></h3>
      <hr class="style4">
      <div class="row">
         <div class="col-md-9 mx-auto">            
          <h5 class="text-left lead fontHomeBody3">Para ver un video instructivo de como utilizar las videollamadas haga click en el siguiente link.<a target="_blank" href="https://www.youtube.com/watch?v=-Qu5siNgulo&feature=youtu.be">Click aqui</a>.</h5><br>
         </div> 
      </div>
    </div>  

    
    
    <hr class="style4">
        <h3 class="lead fontHomeSubtitulo"><b>¿Cómo consultar o cancelar un turno?</b></h3>
        
        <hr class="style4">
        <div class="row">
           <div class="col-md-9 mx-auto">
            <h5 class="text-left lead fontHomeBody3">- Si se encuentra en una PC debe seleccionar COMENZAR y luego seleccionar en el panel de opciones "Mis Turnos".</h5><br>
            <h5 class="text-left lead fontHomeBody3">- Si se encuentra en un celular debe seleccionar COMENZAR y luego hacer click en el botón ubicado arriba a la derecha, alli se va a desplegar un menú con opciones y le permitirá seleccionar "Mis Turnos".</h5> <br>
            <h5 class="text-left lead fontHomeBody3">- Luego debe ingresar el DNI del paciente con el que solicito el turno y click en CONSULTAR. Ahi podrá ver sus turnos registrados y cancelarlo si asi lo desea.</h5><br>
           </div> 
        </div>  
        <br>
        <h5 class="lead fontHomeBody">Pantallas con vista desde un celular:</h5>
        <div class="row mobile-screens-row">
            <img src="images/iconos/click_menu.png" class="img_home_2 mobile-screen-shot">
            <img src="images/iconos/click_mis_turnos.png" class="img_home_2 mobile-screen-shot">
            <img src="images/iconos/click_consultar.png" class="img_home_2 mobile-screen-shot"> 
        </div> 
          
        <br>
       <div class="row">
           <div class="col-md-9 mx-auto">
            <h5 class="text-left lead fontHomeBody3">En caso de NO ver el botón ubicado arriba a la derecha desde un celular, puede consultar o cancelar sus turnos haciendo <a href="/mis_turnos">click aqui</a>.</h5><br>
           </div> 
      </div>     

      <hr class="style4">
        <h3 class="lead fontHomeSubtitulo"><b>¿Cómo solicitar una receta?</b></h3>

        <hr class="style4">
        <div class="row">
           <div class="col-md-9 mx-auto">
            <h5 class="text-left lead fontHomeBody3">- Haga click en COMENZAR y luego seleccione una especialidad.</h5><br>
            <h5 class="text-left lead fontHomeBody3">- Haga click sobre el especialista e ingrese los datos del paciente.</h5> <br>
            <h5 class="text-left lead fontHomeBody3">- Selecciono la opción "Recetas" (hay especialistas que no tienen esta funcionalidad).</h5><br>
           </div> 
        </div>  
        <br>

        <h3 class="lead fontHomeSubtitulo"><b>¿Cómo consultar una receta?</b></h3>
        <hr class="style4">
        <div class="row">
           <div class="col-md-9 mx-auto">
            <h5 class="text-left lead fontHomeBody3">- Si se encuentra en una PC debe seleccionar COMENZAR y luego seleccionar en el panel de opciones "Mis Recetas".</h5><br>
            <h5 class="text-left lead fontHomeBody3">- Si se encuentra en un celular debe seleccionar COMENZAR y luego hacer click en el botón ubicado arriba a la derecha, alli se va a desplegar un menú con opciones y le permitirá seleccionar "Mis Recetas".</h5> <br>
            <h5 class="text-left lead fontHomeBody3">- Luego debe ingresar el DNI del paciente con el que solicito la receta y click en CONSULTAR. Ahi podrá ver el estado de la receta o cancelarla si asi lo desea.</h5><br>
            <h5 class="text-left lead fontHomeBody3">- Una vez cargada la receta por el profesional, deberá ingresar a mis recetas, ingresar su dni, hacer click en Ver, y ahi tendra la opcion de descargar la receta.</h5><br>
           </div> 
        </div>  
        <br>
        <h5 class="lead fontHomeBody">Pantallas con vista desde un celular:</h5>
        <div class="row mobile-screens-row">
            <img src="images/iconos/click_menu.png" class="img_home_2 mobile-screen-shot">
            <img src="images/iconos/click_mis_turnos.png" class="img_home_2 mobile-screen-shot">
            <img src="images/iconos/click_consultar.png" class="img_home_2 mobile-screen-shot"> 
        </div> 
          
        <br>
       <div class="row">
           <div class="col-md-9 mx-auto">
            <h5 class="text-left lead fontHomeBody3">En caso de NO ver el botón ubicado arriba a la derecha desde un celular, puede consultar o cancelar sus recetas haciendo <a href="/mis_recetas">click aqui</a>.</h5><br>
           </div> 
      </div>   
      
          

  </div>   <!-- /.container --> 

    <!-- seleccionarmedicoget  seleccionarespecialidad-->
   <form method="GET" action="{{ route('seleccionarespecialidad') }}">
  <div>

    <button class="flotante rodri_button">COMENZAR</button>
  </div>           
  </form>           
                     
   <br>
  <!-- Footer -->
@include('turnos.terminos_condiciones_modal')
  <!-- Footer -->
  <footer class="py-5 bg-dark footerBackground widthImageCarousel">
@yield('contenedorFooter')
    <div class="container">
      <p class="m-0 text-center text-white createdby">
    Created by &copy; Rodrigo Banegas 
    <span id="screen-resolution" style="font-size: 0.8em; opacity: 0.7;"></span>
</p>

<script>
    // Función para obtener y mostrar la resolución
    function mostrarResolucion() {
        const resolucion = document.getElementById('screen-resolution');
        const ancho = window.innerWidth;
        const alto = window.innerHeight;
        resolucion.textContent = `| 📱 ${ancho}x${alto}`;
    }
    
    // Mostrar al cargar
    mostrarResolucion();
    
    // Actualizar si la pantalla cambia de tamaño (útil para móviles al girar)
    window.addEventListener('resize', mostrarResolucion);
</script>
      <p class="text-center">
        <a data-target=".bd-example-modal-xl" data-toggle="modal" class="letrasblancas" id="MainNavHelp" 
         href="#modalAyuda"><u>Términos y Condiciones</u></a>
        <span class="text-white mx-2">|</span>
        <a href="{{ url('/politica-privacidad') }}" class="letrasblancas"><u>Política de Privacidad</u></a>
        <span class="text-white mx-2">|</span>
        <a href="{{ url('/condiciones-servicio') }}" class="letrasblancas"><u>Condiciones del Servicio</u></a>
      </p>
      <!--<button type="button" class="sinBackgroundAzul editText text-center" data-toggle="modal" data-target=".bd-example-modal-xl">Términos y Condiciones </button>-->
    </div>
    <!-- /.container -->
  </footer>

  <!-- Bootstrap core JavaScript -->
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>

</html>
