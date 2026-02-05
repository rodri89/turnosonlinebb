<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Sitio web para turnos online de medicos en Bahia Blanca.">
  <meta name="author" content="Rodrigo Banegas">
  <meta name="keywords" content="turnos online, medicos, pediatria, bahia blanca, turnos, profesionales">
  <link rel="shortcut icon" type="image/x-icon" href="/images/iconos/turnosonlinebb_icon.png" />
  <title>@yield('title','Turnos Online')</title>
  
  <!-- Bootstrap core CSS -->
  {{ Html::style('vendor/bootstrap/css/bootstrap.min.css') }}  

  @include('layouts.rodri_style_css')

  <!-- Google Add Sense -->
  <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-3758335156050794"
     crossorigin="anonymous"></script>
</head>

<body onLoad="setInterval('timerFunction()',15000);">

  <!-- Navigation -->
   <nav class="navbar navbar-expand-lg fontNav fixed-top fondoNav">
    <div class="container">
      <a class="textheader navbar-brand text-white" href="homes">Turnos Online</a>
      <button class="navbar-toggler ml-auto buttonMenuSizeMarco" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav ml-auto fondoNavMenu">
          <li class="nav-item active">
            <a class="nav-link text-white" href="/homes">Home
              <span class="sr-only">(current)</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white menuAlineacion" href="/plantilla_medica">Medicos</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white menuAlineacion" href="/mis_turnos">Mis Turnos</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white menuAlineacion" href="/mis_recetas">Mis Recetas</a>
          </li>
          @if(Auth::user() == null)
          <li class="nav-item">
            <a href="/login" class="text-white menuAlineacion visibleLoginCel">Login</a>
          </li>
          @endif
          <!--<li class="nav-item">
            <a class="nav-link text-white" href="/contacto">Contacto</a>
          </li>-->
          @if(Auth::user() != null)
            <li class="nav-item dropdown">
            <a id="navbarDropdown" class="nav-link dropdown-toggle text-white" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                {{ Auth::user()->name }} <span class="caret"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                @if(Auth::user()->usuario_tipo==1)
                  <a class="dropdown-item" href="{{ route('adminshowmedicos') }}">
                    {{ __('Mi Panel') }}
                </a> 
                @endif
                @if(Auth::user()->usuario_tipo==2)
                 <a class="dropdown-item" href="{{ route('medicohome') }}">
                    {{ __('Mi Panel') }}
                </a>
                @endif
                @if(Auth::user()->usuario_tipo==3)
                <a class="dropdown-item" href="secretaria_home">
                  {{ __('Mi Panel') }}
                </a>
                @endif

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
            @endif
        </ul>        
      </div>  
    </div>
    @if(Auth::user() == null)
    <a href="/login" class="text-white textheaderLogin hiddenLoginCel">Login</a>
    @endif
  </nav>  
  <!-- Header -->  
    <header class="fondoHeader py-5 mb-5 sizeHeader">  
    <div class="container h-100">
      <div class="row h-100 align-items-center">
        <div class="col-lg-12">
          <h4 class="display-4 mt-5 mb-2 fontColorHeader">@yield('titulo_header','Turnos Online')</h4>
           <p class="lead mb-2 fontColorHeader">@yield('descripcion_header','')</p>          
            @yield('headerContainer')      
        </div>
      </div>
    </div>
  </header>

  <!-- Page Content -->
  <div class="container ">

    <div class="row">
      <div class="col-md-12 mb-5">
        @if('body_titulo')
        <h3 class="lead fontHomeTitulo"> @yield('body_titulo')</h3>
        <br>
        @endif
        
        @yield('contenedor')

      </div>
    </div>
  </div>
  <!-- /.container -->

  <!-- Footer -->
  <footer class="py-5 bg-dark footerBackground">
@yield('contenedorFooter')
    <div class="container">
      <p class="m-0 text-center text-white createdby ">Created by &copy; Rodrigo Banegas</p>
    </div>
    <!-- /.container -->
  </footer>

  <!-- Bootstrap core JavaScript -->
  {{ Html::script('vendor/jquery/jquery.min.js') }}
  {{ Html::script('vendor/bootstrap/js/bootstrap.bundle.min.js') }}    

</body>

</html>
