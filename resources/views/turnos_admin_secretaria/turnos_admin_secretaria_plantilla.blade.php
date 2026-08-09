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
  @include('layouts.rodri_style_css')
  <link rel="stylesheet" type="text/css" href="{{asset('datatable/jquery.dataTables.min.css')}}">
</head>

<body class="d-flex flex-column min-vh-100">

  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg fontNav fixed-top fondoNav flex-shrink-0">
    <div class="container">
      <a class="textheader navbar-brand text-white" href="homes">Turnos Online</a>      
      <button class="navbar-toggler buttonMenuSizeMarco" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarResponsive">
        <ul class="navbar-nav ml-auto fondoNavMenu">
            
          <li class="nav-item dropdown">
            <a id="navbarDropdown" class="nav-link dropdown-toggle text-white" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                Paciente <span class="caret"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
            <form method="POST" action="{{ route('nuevopaciente') }}">                          
              @csrf                                     
              <button class="dropdown-item">Nuevo              
              </button>        
            </form>
            <form method="POST" action="{{ route('actualizarpaciente') }}">                          
              @csrf                                     
              <button class="dropdown-item">Actualizar Datos              
              </button>        
            </form>
             <form method="POST" action="{{ route('listadopacientes') }}">                          
              @csrf                                     
              <button class="dropdown-item">Buscar              
              </button>        
            </form>
            <form method="POST" action="{{ route('listadopacienteshistorial') }}">                          
              @csrf                                     
              <button class="dropdown-item">Historial              
              </button>        
            </form>
            @if(Auth::user()->perfil == 2 || Auth::user()->perfil == 3) 
            <form method="POST" action="{{ route('adminpacientes') }}">                          
              @csrf                                     
              <button class="dropdown-item">Pendientes
              <span class="sr-only">(current)</span>
              </button>        
            </form>           
            @endif   
            </div>
          </li>

          <li class="nav-item">
            <form method="POST" action="{{ route('seleccionarconsultorio') }}">            
              <input type="hidden" name="option" value="3"  />
              @csrf                                     
              <button class="nav-link sinBackground text-white menuAlineacion">Asignar Turnos              
              </button>        
            </form>
          </li>

          <li class="nav-item">
            <form method="POST" action="{{ route('seleccionarconsultorio') }}">            
            <input type="hidden" name="option" value="1"  />
            @csrf                                     
              <button class="nav-link sinBackground text-white menuAlineacion">Turnos Asignados                 
              </button>            
            </form>
          </li>          
          
          <li class="nav-item">
            <form method="POST" action="{{ route('seleccionarconsultorio') }}">            
              <input type="hidden" name="option" value="2"  />
              @csrf                                     
              <button class="nav-link sinBackground text-white menuAlineacion">Sobreturnos</button>        
            </form>
          </li>

          <li class="nav-item">
            <form method="POST" action="{{ route('seleccionarconsultorio') }}">            
              <input type="hidden" name="option" value="6"  />
              @csrf                                     
              <button class="nav-link sinBackground text-white menuAlineacion ">Bloquear</button>        
            </form>
          </li>

          <li class="nav-item">
            <a class="nav-link text-white menuAlineacion" href="{{ route('secretarianuevaobrasocial') }}">Nueva obra social</a>
          </li>

          @if(Auth::user()->perfil == 3 || Auth::user()->perfil == 4)
          <li class="nav-item">
            <form method="POST" action="{{ route('secretariarecetas') }}">            
              <input type="hidden" name="secretaria_id" value="{{Auth::user()->id}}"  />
              @csrf                                                   
              <button class="nav-link sinBackground text-white menuAlineacion">Recetas<div hidden id="seccion_cantidad_recetas_pendientes" class="circulo_rojo_receta float_right">
              <h2 id="cantidad_recetas_pendientes">+9</h2></div></button>        
            </form>
          </li>
          @endif
          
          
        
        </ul>
        
        <li class="nav-item dropdown" style="list-style:none;">
            <a id="navbarDropdown" class="nav-link dropdown-toggle text-white" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                {{ Auth::user()->name }} <span class="caret"></span>
            </a>

            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                @include('turnos_admin_secretaria._dropdown_gestion_medico')
                <div class="dropdown-divider"></div>
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

      </div>
    </div>
  </nav>

  <!-- Page Content (flex-grow para que el footer quede abajo con poco contenido) -->
  <main class="flex-fill pt-5">
  <div class="container pb-4">

    <div class="row">
      <div class="col-md-12 mb-5">
        <h2 id="seleccionar_especialidad_title_id"> @yield('body_titulo','')</h2><br>
        <br><br>
        @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
          <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @yield('contenedor')

      </div>
    </div>

  </div>
  <!-- /.container -->
  </main>

  <!-- Footer -->
  <footer class="py-5 bg-dark fondoNav mt-auto flex-shrink-0">
    <div class="container">
      <p class="m-0 text-center text-white">Created by &copy; Rodrigo Banegas</p>
    </div><br><br>
    <!-- /.container -->
  </footer>

  <!-- Bootstrap core JavaScript -->
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script type="text/javascript" src="{{asset('datatable/jquery.dataTables.min.js')}}"></script>
  <!--<script type="text/javascript" src="http://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>  -->
</body>

<script type="text/javascript">

  window.onload=function() {          
    checkRecetasPendientes();
    if(document.getElementById("seleccionar_medico_screen") != null)
      checkResolution();    
    checkResolutionMobile();
    if(document.getElementById("secretaria_asignar_turno_screen") != null)
      actualizarListado();//onloadAsignarTurnos();
    if(document.getElementById("secretaria_actualizar_paciente_screen") != null)
      onloadActualizarPaciente();
  } 

  function onloadActualizarPaciente() {    
    checkRecetasPendientes();
    var dni_existe = document.getElementById("dni").value;    
    if(dni_existe.localeCompare("") != 0){      
      validarPacienteExiste(dni_existe);      
    }
  }

  function onloadAsignarTurnos() {
    /*checkRecetasPendientes();
    //esto lo utilizo para cuando vengo de buscar paciente.
    var dni_existe = document.getElementById("paciente_id_lpa").value;
    if(dni_existe.localeCompare("")!=0){
      document.getElementById("dni_paciente").value = dni_existe;
      validarPacienteExiste();
    }
    if(document.getElementById("feriadoDescripcion") != null){
      var feriado = document.getElementById("feriadoDescripcion").value;      
        $("#modal_input_texto").val(feriado);
        $("#modalEsFeriado").modal();      
    }

    checkTurnosVirtuales(); */
  }

  function checkResolutionMobile(){
    var w = window.innerWidth;
    var h = window.innerHeight;
    if(document.getElementById("seleccionar_medico_cantidad") != null){
      var cantidad = document.getElementById("seleccionar_medico_cantidad").value;
    }
    
    if(w < 420) {
      if(document.getElementById("seleccionar_especialidad_title_id") != null){
        document.getElementById("seleccionar_especialidad_title_id").hidden = true;    
      }
    }    
  }
  
  function checkRecetasPendientes() {   
     $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/check_recetas_pendientes_secretaria',
           data:{ _token: '{{csrf_token()}}'},
           success:function(data){  
              if(data.response == 1){                 
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
            }
        });    
  }


</script>

</html>
