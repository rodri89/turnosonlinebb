
@extends('turnos_admin/modelo_plantilla_admin')

@section('titulo_header','Alta medico')

@section('body_titulo','')

@section('contenedor')

<head>            
    <script src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{asset('js/jquery.min.js')}}"> </script>
</head>

<div class="row">
    
      <div class="col-md-6">
        
        <h2> Ingrese los datos del medico:</h2>
        @if($especialidad == null)
        <form method="POST" action="{{ route('altamedico') }}" enctype="multipart/form-data">        
        @else
        <form method="POST" action="{{ route('actualizarmedico') }}" enctype="multipart/form-data">
        <p>Los campos con (*) son los que se pueden actualizar</p>     
        <input type="hidden" class="form-control" name="medico_id"  value="{{$medico->id}}"  />
        @endif

          @csrf
          <input type="hidden" class="form-control" name="user_id"  value="{{$user->id}}"  />          
          @if($medico != null)
          <input type="hidden" class="form-control" id="consultorio_id"  value="{{$medico->consultorio}}"  />
          <label for="text" class="col-sm-0 control-label">Id</label>     
          <input type="text" class="form-control" name="m_id"  value="{{$medico->id}}"  />
          <label for="text" class="col-sm-0 control-label">(*) Nombre</label>     
          <input type="text" class="form-control" name="nombre"  value="{{$medico->nombre}}"  />
          <label for="text" class="col-sm-0 control-label">(*) Apellido</label>   
          <input type="text" class="form-control" name="apellido" placeholder="apellido" value="{{$medico->apellido}}"  />
          <label for="text" class="col-sm-0 control-label">(*) Mail</label>                
          <input type="text" class="form-control" name="mail" value="{{$medico->mail}}"  />

          <label for="text" class="col-sm-0 control-label">(*) Sexo</label>                
          <input type="text" class="form-control" name="sexo" value="{{$medico->sexo}}"  />
          @else
          <label for="text" class="col-sm-0 control-label">Nombre</label>     
          <input type="text" class="form-control" name="nombre"  value="{{$user->name}}"  />
          <label for="text" class="col-sm-0 control-label">Apellido</label>                                
          <input type="text" class="form-control" name="apellido" placeholder="" value="{{$surname}}"  />                  
          <label for="text" class="col-sm-0 control-label">Mail</label>                
          <input type="text" class="form-control" name="mail" value="{{$user->email}}"  />

          <label for="text" class="col-sm-0 control-label">Sexo</label>                
          <input type="text" class="form-control" name="sexo" value="M/F"  />
          @endif          
          
          @if($especialidad != null)
          <label for="text" class="col-sm-0 control-label">Especialidad</label>                
          <input type="text" class="form-control" name="especialidad_aux" value="{{$especialidad->nombre}}"  />
          @else
          <div class="form-group">
          <label for="sel1">Especialidades:</label>
          <select class="form-control" id="sel1" name="especialidad">
            <option>N/A</option>            
            @foreach($especialidades as $especialidad)
            <option>{{$especialidad->id.'-'.$especialidad->nombre}}</option>            
            @endforeach
          </select>
          </div>
          @endif

          <div class="form-group">
          <label for="sel1">Consultorio:</label>
          <select class="form-control" id="sel1" name="consultorio">
            <option>N/A</option>            
            @foreach($consultorios as $consultorio)
            <option>{{$consultorio->id.'-'.$consultorio->direccion}}</option>            
            @endforeach
          </select>
          </div>
          @if($medico != null)
          <label for="text" class="col-sm-0 control-label">(*) Perfil</label>      
          <input type="text" class="form-control" name="perfil"  value="{{$medico->perfil}}"  />          
          @else
          <label for="text" class="col-sm-0 control-label">Perfil</label>      
          <input type="text" class="form-control" name="perfil"  placeholder="Numero Perfil"  />
          @endif

          @if($medico != null)
            <label for="text" class="col-sm-0 control-label">(*) Telefono</label>      
            <input type="text" class="form-control" name="telefono"  value="{{$medico->telefono}}"  />          
            <label for="text" class="col-sm-0 control-label">(*) Mostrar Medico</label>      <br>

            @if($medico->castigo_automatico == 1)                       
            <div class="form-check"> 
              <label class="form-check-label" for="materialUnchecked">Si</label>
              <input type="radio" id="materialUnchecked" name="radio" value="1" checked>
                          
              <label class="form-check-label" for="materialChecked">No</label>
              <input type="radio" id="materialChecked" name="radio" value="0">          
            </div>
            @else
              <div class="form-check"> 
                <label class="form-check-label" for="materialUnchecked">Si</label>
                <input type="radio" id="materialUnchecked" name="radio" value="1">
                            
                <label class="form-check-label" for="materialChecked">No</label>
                <input type="radio" id="materialChecked" name="radio" value="0" checked="">
              </div>
            @endif

          @else
          <label for="text" class="col-sm-0 control-label">Telefono</label>      
          <input type="text" class="form-control" name="telefono"  placeholder="Telefono solo numeros (EJ: 2915050050)"  />          
          <label for="text" class="col-sm-0 control-label">Mostrar Medico</label>      <br>

          <div class="form-check">                        
            <label class="form-check-label" for="materialUnchecked">Si</label>
            <input type="radio" id="materialUnchecked" name="radio" value="1" checked>
                        
            <label class="form-check-label" for="materialChecked">No</label>
            <input type="radio" id="materialChecked" name="radio" value="0">
          </div>
          @endif          
          
          
          <label for="text" class="col-sm-0 control-label">Foto</label><br>
          <input type="file" name="foto" placeholder="foto"  />

          <br>
          <br>
          <div>
             @if($especialidad == null)
                <button>Registrar</button>     
             @else
                <button>Actualizar</button>     
            @endif            
          </div>
        </form>
      </div>
      @if($medico!=null)
      <div class="col-md-6">
        
       <h2> Actualizar Foto</h2>
        <form method="POST" action="{{ route('actualizarFoto') }}" enctype="multipart/form-data">
          @csrf          
          <input type="hidden" class="form-control" name="medico_id"  value="{{$medico->id}}"  />
          <label for="text" class="col-sm-0 control-label">Foto</label><br>
          <input type="file" name="foto" placeholder="foto" />
        
          <br>
          <br>
          <div>
            <button>Actualizar</button>     
          </div>
        </form>
        <br>
        <br>
        
       <h2>Asignar Modulos</h2>        
          <input type="hidden" class="form-control" id="medico_id" name="medico_id"  value="{{$medico->id}}"  />          
          <div class="form-check">
            @foreach($moduloMedicos as $modulo)
    
              @if($modulo["activo"]==1)
                <input checked type="checkbox" class="form-check-input" id="checkModulo{{$modulo['modulo']}}" name="checkModulo{{$modulo['modulo']}}">
              @else
                <input type="checkbox" class="form-check-input" id="checkModulo{{$modulo['modulo']}}" name="checkModulo{{$modulo['modulo']}}">
              @endif
              <label class="form-check-label" for="materialUnchecked">{{$modulo["descripcion"]}}</label><br>                           
          @endforeach
          </div>
          <br>
          <div>
            <button type="button" onclick="actualizarModuloMedico()">Actualizar</button>     
          </div>
      
      <h2>Ventana Dias</h2>
      <div>
      @if($ventanaDias != null)
        <input type="text" class="form-control mercadopago_collapse_70px" id="valor_string" name="valor_string" value="{{$ventanaDias->valor_string}}">
        <input type="text" class="form-control mercadopago_collapse_70px" id="valor_integer" name="valor_integer" value="{{$ventanaDias->valor_integer}}">
      @else
        <input type="text" class="form-control mercadopago_collapse_70px" id="valor_string" name="valor_string" placeholder="+5d">
        <input type="text" class="form-control mercadopago_collapse_70px" id="valor_integer" name="valor_integer" placeholder="5">
      @endif      
          <br>
          <button type="button" onclick="actualizarVentanaDiasMedico()">Actualizar</button>     
      </div>

      @if($moduloMercadoPago == 1)
      <h2>Asignar credenciales MercadoPago</h2>        
          <input type="hidden" class="form-control" id="mp_medico_id" name="mp_medico_id"  value="{{$medico->id}}"  />
          <form>
              @if($videollamada->key)
                <input type="text" class="form-control" id="mp_key" name="mp_key" value="{{$videollamada->key}}">
              @else
                <input type="text" class="form-control" id="mp_key" name="mp_key" placeholder="Key">
              @endif              
              @if($videollamada->secret)
                <input type="text" class="form-control" id="mp_secret" name="mp_secret" value="{{$videollamada->secret}}">
              @else
                <input type="text" class="form-control" id="mp_secret" name="mp_key" placeholder="Secret">
              @endif
              @if($videollamada->perfil)
                <input type="text" class="form-control" id="mp_perfil" name="mp_perfil" value="{{$videollamada->perfil}}">
              @else
                <input type="text" class="form-control" id="mp_perfil" name="mp_perfil" placeholder="Perfil">
              @endif
          
          <br>
          <div>
            <button type="button" onclick="actualizarCredencialesMP()">Actualizar</button>     
          </div>
        </form>
      
      @endif
      @endif
    </div>
</div>

<script type="text/javascript">  

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  function actualizarModuloMedico(){  
    var medico_id = document.getElementById("medico_id").value;
    var consultorio_id = document.getElementById("consultorio_id").value;
    
    var activarPacienteCheck = document.getElementById("checkModulo1").checked;
    var cajaComentarioCheck = document.getElementById("checkModulo2").checked;
    var primerControlDobleCheck = document.getElementById("checkModulo3").checked;
    var soloUnTurnoCheck = document.getElementById("checkModulo4").checked;
    var recetasCheck = document.getElementById("checkModulo5").checked;
    var videollamadasCheck = document.getElementById("checkModulo6").checked;
    var mercadoPagoCheck = document.getElementById("checkModulo7").checked;
    var afiliadoObligatorioCheck = document.getElementById("checkModulo8").checked;
    var ventanaDiasCheck = document.getElementById("checkModulo9").checked;
    var extraTurnoCheck = document.getElementById("checkModulo10").checked;

    var activarPaciente = 0;
    var cajaComentario = 0;
    var primerControlDoble = 0;
    var soloUnTurno = 0;
    var recetas = 0;
    var videollamadas = 0;
    var mercadopago = 0;
    var afiliadoObligatorio = 0;
    var ventanaDias = 0;
    var extraTurno = 0;

    if(activarPacienteCheck)
      activarPaciente = 1;
    if(cajaComentarioCheck)
      cajaComentario = 1;
    if(primerControlDobleCheck)
      primerControlDoble = 1;
    if(soloUnTurnoCheck)
      soloUnTurno = 1;
    if(recetasCheck)
      recetas = 1;
    if(videollamadasCheck)
      videollamadas = 1;
    if(mercadoPagoCheck)
      mercadopago = 1;
    if(afiliadoObligatorioCheck)
      afiliadoObligatorio = 1;
    if(ventanaDiasCheck)
      ventanaDias = 1;
    if(extraTurnoCheck)
      extraTurno = 1;

    $.ajax({
         type:'POST',
         dataType:'JSON',
         url:'/admin_modulo_medico',
         data:{medico_id:medico_id,consultorio_id:consultorio_id,activarPaciente:activarPaciente, cajaComentario:cajaComentario, primerControlDoble:primerControlDoble, soloUnTurno:soloUnTurno, recetas:recetas, videollamadas:videollamadas, mercadopago:mercadopago, afiliadoObligatorio:afiliadoObligatorio, ventanaDias:ventanaDias,extraTurno:extraTurno, _token: '{{csrf_token()}}'},
         success:function(data){                          
              alert("Los modulos fueron actualizados");
           }
      });
  }

  function actualizarCredencialesMP(){
    var medico_id = document.getElementById("medico_id").value;
    var key = document.getElementById("mp_key").value;
    var secret = document.getElementById("mp_secret").value;
    var perfil = document.getElementById("mp_perfil").value;
    var consultorio = document.getElementById("consultorio_id").value;
    $.ajax({
         type:'POST',
         dataType:'JSON',
         url:'/guardar_mp_config',
         data:{medico_id:medico_id,key:key,secret:secret,consultorio:consultorio,perfil:perfil, _token: '{{csrf_token()}}'},
         success:function(data){                          
              alert("Las credenciales han sido guardadas");
           }
      });  
  }

  function actualizarVentanaDiasMedico(){
    var medico_id = document.getElementById("medico_id").value;
    var valor_string = document.getElementById("valor_string").value;
    var valor_integer = document.getElementById("valor_integer").value;

    $.ajax({
         type:'POST',
         dataType:'JSON',
         url:'/guardar_ventana_dias',
         data:{medico_id:medico_id, valor_string:valor_string, valor_integer:valor_integer,_token: '{{csrf_token()}}'},
         success:function(data){                          
              alert("Los valores han sido guardadas");
           }
      });
  }

</script>

@endsection