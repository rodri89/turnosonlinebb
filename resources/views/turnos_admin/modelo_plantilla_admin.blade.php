<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Sitio web para turnos online de medicos en Bahia Blanca.">
  <meta name="author" content="Rodrigo Banegas">
  <meta name="keywords" content="turnos online, medicos, pediatria, bahia blanca, turnos, profesionales">

  <title>@yield('title','Admin Turnos Online')</title>
  <link rel="shortcut icon" type="image/x-icon" href="/images/iconos/turnosonlinebb_icon.png" />

  <!-- Bootstrap core CSS -->
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom styles for this template -->
  <link href="css/business-frontpage.css" rel="stylesheet">
  @include('layouts.rodri_style_css')

  <!-- Google Add Sense -->
  <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-3758335156050794"
     crossorigin="anonymous"></script>
</head>

<body>

  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg fontNav fixed-top fondoNav">
    <div class="container">
      <a class="navbar-brand text-white" href="/homes">Turnos Online</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarResponsive">
        <ul class="navbar-nav ml-auto">
          <li class="nav-item active">
            <a class="nav-link text-white" href="/admin_show_medicos">Medicos
              <span class="sr-only">(current)</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white" href="/admin_consultorios">Consultorios</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white" href="/alta_secretarias">Secretarias</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white" href="/admin_especialidad">Especialidad</a>
          </li>

          <li class="nav-item dropdown">
            <a id="navbarDropdown" class="nav-link dropdown-toggle text-white" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                Turnos <span class="caret"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
              <a class="nav-link " href="/admin_turnos">Admin Turnos</a>
              <a class="nav-link " href="/admin_horarios">Admin Horarios</a>
            </div>                                            
          </li>
          
          <li class="nav-item">
            <a class="nav-link text-white" href="/admin_feriados">Feriados</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white" href="/admin_obra_social">Obra Social</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white" href="/admin_extras">Extras</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white" href="{{ route('register') }}">Registrar Usuario</a>
          </li>
          <li class="nav-item dropdown">
            <a id="navbarDropdown" class="nav-link dropdown-toggle text-white" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                {{ Auth::user()->name }} <span class="caret"></span>
            </a>

            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                
                  <a class="dropdown-item" href="{{ route('listanegra') }}">Lista Negra</a>
                
                <a class="dropdown-item" href="{{ route('logout') }}"
                   onclick="event.preventDefault();
                                 document.getElementById('logout-form').submit();">
                    {{ __('Cerrar Sesión') }}
                </a>



                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Header -->
  <header class="fondoHeader py-5 mb-5">
    <div class="container h-100">
      <div class="row h-100 align-items-center">
        <div class="col-lg-12">
          <h1 class="display-4 fontColorHeader mt-5 mb-2">@yield('titulo_header','Turnos Online')</h1>
          <p class="lead mb-5 fontColorHeader">@yield('descripcion_header','Bienvenidos a turnos online Bahia Blanca.')</p>
        </div>
      </div>
    </div>
  </header>

  <!-- Page Content -->
  <div class="container">

    <div class="row">
      <div class="col-md-12 mb-5">
        <h2> @yield('body_titulo','Seleccionar Especialidad')</h2>
        <br><br>
        @yield('contenedor')

      </div>
    </div>

  </div>
  <!-- /.container -->

  <!-- Footer -->
  <footer class="py-5 bg-dark fondoNav">
    <div class="container">
      <p class="m-0 text-center text-white">Created by &copy; Rodrigo Banegas</p>
    </div>
    <!-- /.container -->
  </footer>

  <!-- Bootstrap core JavaScript -->
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>

</html>
