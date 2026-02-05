<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="Expires" content="0">
  <meta http-equiv="Last-Modified" content="0">
  <meta http-equiv="Cache-Control" content="no-cache, mustrevalidate">
  <meta http-equiv="Pragma" content="no-cache">

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Sitio web para turnos online de medicos en Bahia Blanca.">
  <meta name="author" content="Rodrigo Banegas">
  <meta name="keywords" content="turnos online, medicos, pediatria, bahia blanca, turnos, profesionales">

  <title>@yield('title','Admin Turnos Online')</title>
  <link rel="shortcut icon" type="image/x-icon" href="/images/iconos/turnosonlinebb_icon.png" />
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.css">
  
  <!-- Bootstrap core CSS -->
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom styles for this template -->
  <link href="css/business-frontpage.css" rel="stylesheet">

  <link rel="stylesheet" type="text/css" href="{{asset('datatable/jquery.dataTables.min.css')}}">
  @include('modal.snackbar')
  @include('layouts.rodri_style_css')    
</head>

<body onLoad="setInterval('recargarListado()',15000);">

  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg fontNav fixed-top fondoNav">
    <div class="container">
      <a class="textheader navbar-brand text-white" href="/homes">Turnos Online</a>      
      
      <button class="navbar-toggler buttonMenuSizeMarco" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav ml-auto fondoNavMenu">
          <li class="nav-item active">            
            <form method="GET" action="{{ route('medicohome') }}">                                                                                
              <button class="nav-link sinBackground text-white menuAlineacion">Turnos              
              </button>        
            </form>                                    
          </li>
           <li class="nav-item dropdown">
            <a id="navbarDropdown" class="nav-link dropdown-toggle text-white" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                Paciente <span class="caret"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
            <form method="POST" action="{{ route('mediconuevopaciente') }}">                          
              @csrf                                     
              <button type="buuton" class="dropdown-item">Nuevo              
              </button>        
            </form>
            <form method="POST" action="{{ route('medicoactualizarpaciente') }}">                          
              @csrf                                     
              <button class="dropdown-item">Actualizar Datos              
              </button>        
            </form>
            <form method="POST" action="{{ route('medicobuscarpacientes') }}">                          
              @csrf                                     
              <button class="dropdown-item">Buscar              
              </button>        
            </form>
            <form method="POST" action="{{ route('pacienteshistoriallistado') }}">                          
              @csrf                                     
              <button class="dropdown-item">Historial              
              </button>        
            </form>
            @if(Auth::user()->perfil == 2 || Auth::user()->perfil == 3 || Auth::user()->perfil == 5) 
            <form method="POST" action="{{ route('medicoadminpacientes') }}">                          
              @csrf                                     
              <button class="dropdown-item">Pendientes
              <span class="sr-only">(current)</span>
              </button>        
            </form>
              @endif    
            </div>
          </li>

          @if(Auth::user()->perfil != 8)          
          <li class="nav-item">
            <form method="POST" action="{{ route('medicoasignarturnos') }}">                          
              @csrf                                     
              <button class="nav-link sinBackground text-white menuAlineacion">Asignar Turnos              
              </button>        
            </form>
          </li>
          @endif
                
          @if(Auth::user()->perfil != 8)                      
          <li class="nav-item">
            <form method="POST" action="{{ route('medicoadminsobreturnos') }}">            
              <input type="hidden" name="option" value="2"  />
              @csrf                                     
              <button class="nav-link sinBackground text-white menuAlineacion">Sobreturnos</button>        
            </form>
          </li>
          @endif

          @if(Auth::user()->perfil != 8)          
          <li class="nav-item">
            <form method="POST" action="{{ route('medicobloquearturnos') }}">                          
              @csrf                                     
              <input type="hidden" name="option" value="4"  />
              <button class="nav-link sinBackground text-white menuAlineacion">Bloquear</button>        
            </form>
          </li>
          @endif

          @if(Auth::user()->perfil == 3 || Auth::user()->perfil == 4 || Auth::user()->perfil == 5 || Auth::user()->perfil == 6)
          <li class="nav-item">
            <form method="POST" action="{{ route('medicorecetas') }}">                          
              @csrf                                     
              <button class="nav-link sinBackground text-white menuAlineacion">Recetas<div hidden id="seccion_cantidad_recetas_pendientes" class="circulo_rojo_receta float_right">
              <h2 id="cantidad_recetas_pendientes">+9</h2></div></button>        
            </form>
          </li>
          @endif

            @if(Auth::user()->perfil == 5 || Auth::user()->perfil == 6 || Auth::user()->perfil == 7 || Auth::user()->perfil == 8)
            <li class="nav-item dropdown">
            <a id="navbarDropdown" class="nav-link dropdown-toggle text-white" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                Videollamadas <span class="caret"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
            <form method="POST" action="{{ route('medicovideollamadas') }}">                          
              @csrf                                     
              <button class="dropdown-item">Turnos              
              </button>        
            </form>
            <form method="POST" action="{{ route('historialvideollamadas') }}">  
                  @csrf                          
                  <button class="dropdown-item">Historial              
                  </button>        
            </form>         
            <!--         
            <form method="POST" action="{{ route('medicovalidarpagos') }}">                          
              @csrf                                     
              <button class="dropdown-item">Validar Pagos              
              </button>        
            </form> -->                    
            </div>
          </li>
          <!--<li class="nav-item">
            <form method="POST" action="{{ route('medicovideollamadas') }}">                          
              @csrf                                     
              <button class="nav-link sinBackground text-white menuAlineacion">Videollamadas</button>        
            </form>
          </li> -->
          @endif
          
          <li class="nav-item dropdown">
            <a id="navbarDropdown" class="nav-link dropdown-toggle text-white" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                {{ Auth::user()->name }} <span class="caret"></span>
            </a>   
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">  
              @if(Auth::user()->perfil == 2 || Auth::user()->perfil == 5 || Auth::user()->perfil == 6 || Auth::user()->perfil == 7 || Auth::user()->perfil == 8 || Auth::user()->perfil == 9)
                  <a class="dropdown-item" href="{{ route('medicoconfiguracion') }}">
                    {{ __('Configuracion') }}
                  </a>

                  <a class="dropdown-item" href="{{ route('adminhorariosm') }}">
                    {{ __('Admin Horarios') }}
                  </a>

                @endif 
                <form method="POST" action="{{ route('medicoadminobrasocial') }}">                          
                  @csrf                                     
                  <button class="dropdown-item">Obra Social              
                  </button>        
                </form>               
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
  

  <!-- Page Content -->
  <div class="container">

    <div class="row">
      <div class="col-md-12 mb-5"><br><br>
        <h2> @yield('body_titulo','')</h2>
        <br><br>
        @yield('contenedor')

      </div>
    </div>

  </div>
  <!-- /.container -->

  <!-- Footer -->
 <footer class="py-5 bg-dark fondoNav">
    <div class="container">
      <p class="m-0 text-center text-white createdby">Created by &copy; Rodrigo Banegas</p>
    </div><br><br>
    <!-- /.container -->
  </footer>

  <!-- Bootstrap core JavaScript -->
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script type="text/javascript" src="{{asset('datatable/jquery.dataTables.min.js')}}"></script>
</body>

<script type="text/javascript">
  
  function checkRecetasPendientes() {            
     $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/check_recetas_pendientes',
           data:{ _token: '{{csrf_token()}}'},
           success:function(data){   
            if(data.cantidadRecetasPendientes > 0){
              document.getElementById("seccion_cantidad_recetas_pendientes").hidden = false;
              if(data.cantidadRecetasPendientes > 9){
                document.getElementById("cantidad_recetas_pendientes").innerHTML = "+9";
              } else {
                document.getElementById("cantidad_recetas_pendientes").innerHTML = data.cantidadRecetasPendientes;
              }
            } else {
              document.getElementById("seccion_cantidad_recetas_pendientes").hidden = true;
            }                    
           }
        });    
  }

</script>

</html>
