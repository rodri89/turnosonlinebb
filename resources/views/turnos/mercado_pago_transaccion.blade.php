<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Sitio web para turnos online de medicos en Bahia Blanca.">
  <meta name="author" content="Rodrigo Banegas">
  <meta name="keywords" content="turnos online, medicos, pediatria, bahia blanca, turnos, profesionales">

  <title>@yield('title','Turnos Online')</title>
  <link rel="shortcut icon" type="image/x-icon" href="/images/iconos/turnosonlinebb_icon.png" />
  <!-- Bootstrap core CSS -->
  {{ Html::style('vendor/bootstrap/css/bootstrap.min.css') }}
    
  <!-- Custom styles for this template -->

  {{ Html::style('css/rodri_style4.css') }}
<!-- Para mediaquery debo agregar estos dos. Mediaquery es para que se ve bien en el telefono -->
<link rel="stylesheet" media="(max-width: 360px)" href="css/rodri_style4.css">
<meta name="viewport" content="width=device-width"/>
</head>

<body>  
  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg fontNav fixed-top fondoNav">
    <div class="container">      
      <a class="textheader navbar-brand text-white" href="homes">Turnos Online</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

    </div>    
    <!--<a href="/login" class="text-white">Login</a>-->
  </nav>  
  <!--</header> -->

  <!-- Page Content -->
  <div class="container">    

    <div class="row contenedor3">
      <div class="col-md-12 mb-5 contenido3"> 
        <br><br><br><br><br><br>                   
          <h3  class="text-center fontHomeTitulo lead">"La transacción resulto exitosa".</h3>
        <br><br>
        <div class="contenedor3">
          <button class="rodri_button contenido3">CONTINUAR</button>         
        </div>             
      </div>             
    </div>    
  </div>
  <!-- /.container --> 
  <form method="GET" action="{{ route('portada') }}">
  <div>
  </div>           
  </form>                   
   <br>
  
  <!-- Bootstrap core JavaScript -->
  {{ Html::script('vendor/jquery/jquery.min.js') }}
  {{ Html::script('vendor/bootstrap/js/bootstrap.bundle.min.js') }}  

</body>

</html>
