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
  <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

  <!-- Custom styles for this template -->
  <link href="{{ asset('css/business-frontpage.css') }}" rel="stylesheet">

  @include('layouts.rodri_style_css')
  <!-- Para mediaquery debo agregar estos dos. Mediaquery es para que se ve bien en el telefono -->
  
  <!-- Google Add Sense -->
  <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-3758335156050794"
     crossorigin="anonymous"></script>
  
  <style>
    /* Estilos para que el footer esté siempre pegado abajo */
    html, body {
      height: 100%;
    }
    
    body {
      display: flex;
      flex-direction: column;
    }
    
    .main-content {
      flex: 1 0 auto;
    }
    
    footer {
      flex-shrink: 0;
    }
  </style>

  {{-- Estilos dinámicos del consultorio (si existe config) --}}
  @if(isset($consultorio) && $consultorio && !empty($consultorio->config))
  @php
    $rawConfig = $consultorio->config;
    // Si es string (viene de DB::table), decodificar; si ya es array (vía Eloquent accessor), usarlo directo
    $cfg = is_string($rawConfig) ? json_decode($rawConfig, true) : $rawConfig;
    $colorPrimario   = $cfg['color_primario'] ?? '#1a5276';
    $colorSecundario = $cfg['color_secundario'] ?? '#2e86c1';
    $colorTerciario  = $cfg['color_terciario'] ?? '#85c1e9';
    $tituloColor     = $cfg['titulo_color'] ?? '#ffffff';
    $subtituloColor  = $cfg['subtitulo_color'] ?? '#d4e6f1';
    $tipoLetra       = $cfg['titulo_tipo_letra'] ?? 'Arial, sans-serif';
  @endphp
  <style>
    :root {
      --color-primario: {{ $colorPrimario }};
      --color-secundario: {{ $colorSecundario }};
      --color-terciario: {{ $colorTerciario }};
      --titulo-color: {{ $tituloColor }};
      --subtitulo-color: {{ $subtituloColor }};
      --titulo-tipo-letra: {{ $tipoLetra }};
    }
    .fondoNav,
    .bg-dark.footerBackground,
    footer.py-5.bg-dark {
      background: var(--color-primario) !important;
    }
    .fondoHeader {
      background: var(--color-secundario) !important;
    }
    .fontColorHeader {
      color: var(--titulo-color) !important;
    }
    .fontHomeTitulo {
      color: var(--titulo-color) !important;
    }
    .fontNav {
      font-family: var(--titulo-tipo-letra) !important;
    }
  </style>
  @endif

  {{-- jQuery y Bootstrap una sola vez (en el <head>) para que plugins como bootstrap-datepicker
      cargados en las vistas no se pierdan al volver a incluir jQuery al final del <body>. --}}
  <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</head>

<body>

  <!-- Navigation -->
   <nav class="navbar navbar-expand-lg fontNav fixed-top fondoNav">
    <div class="container">
      <a class="textheader navbar-brand text-white" href="{{ url('/homes') }}">Turnos Online</a>
      <button class="navbar-toggler ml-auto" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
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
            <a href="/login" class="text-white nav-link ">Login</a>
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
                <a class="dropdown-item" href="{{ url('/secretaria_home') }}">
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
    
  </nav>  
  
  <div class="main-content">
    <!-- Header -->  
    <header class="fondoHeader py-5 mb-5 sizeHeader">  
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-12">
            <div class="d-flex flex-column flex-lg-row align-items-center">
              @if(isset($consultorio) && $consultorio && !empty($consultorio->foto) && $consultorio->foto != 'consultorio_sin_foto.png')
                <div class="mb-3 mb-lg-0 mr-lg-4 flex-shrink-0">
                  <img src="{{ asset('images/consultorios/' . $consultorio->foto) }}" 
                       alt="Logo {{ $consultorio->nombre }}" 
                       style="max-width: 100px; max-height: 100px;">
                </div>
              @endif
              <div class="text-center text-lg-left">
                <br>              
                <h4 class="display-4 mt-5 mb-2 fontColorHeader">@yield('titulo_header','Turnos Online')</h4>
                <p class="lead mb-2 fontColorHeader">@yield('descripcion_header','')</p>          
                @yield('headerContainer')
              </div>
            </div>
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
          <h5 hidden id="msj_turno_virtual" class="letrasrojo">Tenga en cuenta que será un turno virtual</h5>
          <br>
          @endif
          
          @yield('contenedor')

        </div>
      </div>
    </div>
    <!-- /.container -->
    @include('turnos.terminos_condiciones_modal')
  </div>
  <!-- Footer -->
  <footer class="py-5 bg-dark footerBackground">
@yield('contenedorFooter')
    <div class="container">
      <p class="m-0 text-center text-white createdby ">Created by &copy; Rodrigo Banegas</p>
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

</body>

</html>
