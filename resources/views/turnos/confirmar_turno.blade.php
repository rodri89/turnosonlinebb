

<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
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
<!-- Para mediaquery debo agregar estos dos. Mediaquery es para que se ve bien en el telefono -->

<meta name="viewport" content="width=device-width"/>
</head>

<style type="text/css">
   .fontHomeBody3{
    color:#303F9F;
    margin-top: 25px;
    font-size: 1.2rem;   
    float: right;
  } 
</style>

<body>

  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg fontNav fixed-top fondoNav">
    <div class="container">      
      
      

    </div>    
    <!--<a href="/login" class="text-white">Login</a>-->
  </nav>  


<h1 align="center">Turno confirmado</h1>

<h3> El turno registrado el día {{$fecha}} en el horario: {{$turnoRegistrado->horario}} con el/la especialista {{$medico->apellido}}, {{$medico->nombre}} ha sido confirmado.</h3>
                               
   <br>
  <!-- Footer -->

</body>

</html>

