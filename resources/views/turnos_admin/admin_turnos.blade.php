
@extends('turnos_admin/modelo_plantilla_admin')

@section('titulo_header','Admin Turnos')

@section('body_titulo','')

@section('contenedor')
<head>
    <!-- Optional theme -->
    <script src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{asset('js/jquery.min.js')}}"> </script>
</head>
<div class="row">
    
      <div class="col-md-6">        
        <h2> Cargar horarios:</h2>
        
        <form method="POST" action="{{ route('crearturnosdia') }}">
          @csrf
          <div class="form-check divMarginCel">
            <input type="checkbox" class="form-check-input" id="videollamadaCheck" name="videollamadaCheck">
            <label class="form-check-label editText" for="videollamadaCheck">Videollamada: </label>            
          </div>
          <div class="form-group">
          <label for="sel1">Medico:</label>
          <select class="form-control" id="medico" name="medico">            
            <option>N/A</option>   
            @foreach($medicos as $medico)
            <option>{{$medico->id.'-'.$medico->apellido.', '.$medico->nombre}}</option>            
            @endforeach
          </select>
          </div>

          <div class="form-group">
          <label for="sel1">Consultorio:</label>
          <select class="form-control" id="consultorio" name="consultorio">
            <option>N/A</option>            
            @foreach($consultorios as $consultorio)
            <option>{{$consultorio->id.'-'.$consultorio->direccion}}</option>            
            @endforeach
          </select>
          </div>
          
          <label for="sel1">Ingrese los dias (Lunes = 1), horarios (08:00), de atencion y doble = 1 en caso de ser ultimo turno:</label><br>
          <label for="sel1">1-08:00-0,1-08:30-1</label>
          <textarea name="dia_horario_doble" rows="10" cols="70">dia-horario-doble,dia-horario-doble</textarea>
          
          <!--<input type="text" class="form-control" name="dia"  value=1 />

          <label for="text" class="col-sm-0 control-label">Horario</label>      
          <input type="text" class="form-control" name="horario"  placeholder="08:00"  />
          -->
          <br>
          <div>
            <button>Registar</button>
            <!--<a href="turno_registrado" class="btn btn-primary">Registrar</a>-->
          </div>
        </form>
      </div>

        <div class="col-md-6">        
        <h2> Cargar Desde Hasta para los dias.</h2>
        
          <label for="sel1">Dia:</label>
          <div class="form-check">                        
            <label class="form-check-label" for="materialUnchecked">Lu</label>
            <input type="radio" id="check1" name="radio" value="1" required>
                        
            <label class="form-check-label" for="materialChecked">Ma</label>
            <input type="radio" id="check2" name="radio" value="2" required>

            <label class="form-check-label" for="materialUnchecked">Mi</label>
            <input type="radio" id="check3" name="radio" value="3" required>
                        
            <label class="form-check-label" for="materialUnchecked">Ju</label>
            <input type="radio" id="check4" name="radio" value="4" required>

            <label class="form-check-label" for="materialUnchecked">Vi</label>
            <input type="radio" id="check5" name="radio" value="5" required>
          </div> <br>
          <label for="sel1">Desde:</label>
          <input id="horario_desde" name="horario_desde" hint="Desde"></input>
          <label for="sel1">Hasta:</label>
          <input id="horario_hasta" name="horario_hasta" hint="Hasta"></input>
          <br><br>
          <div>
            <button onclick="registrarDesdeHasta()">Registar</button>
            <!--<a href="turno_registrado" class="btn btn-primary">Registrar</a>-->
          </div>
        
      </div>
</div>

<script type="text/javascript">      
  
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

 //5-Test, Guillermina
  function registrarDesdeHasta(){
    var dia = document.querySelector('input[name="radio"]:checked').value; 
    var medico = document.getElementById("medico").value;    
    var consul = document.getElementById("consultorio").value;
    var desde = document.getElementById("horario_desde").value;
    var hasta = document.getElementById("horario_hasta").value;
    var videollamadaCheck = document.getElementById("videollamadaCheck").checked;
    var medico_id = medico.split("-")[0];
    var consultorio_id = consul.split("-")[0];
    
     $.ajax({
           type:'POST',
           dataType:'JSON',
           url:'/crear_turnos_dia_dh',
           data:{medico_id: medico_id, consultorio_id:consultorio_id, dia :dia, desde:desde, hasta:hasta, videollamadaCheck:videollamadaCheck, _token: '{{csrf_token()}}'},
           success:function(data){              
              if(data.registrarTurnoDiaDh!=null){
                alert("Los valores fueron cargados");
                document.getElementById("horario_desde").value = '';
                document.getElementById("horario_hasta").value = '';
              }
           }
        }); 
    }
    
  


</script>

@endsection