
@extends('turnos_admin/modelo_plantilla_admin')

@section('titulo_header','Admin Feriados')

@section('body_titulo','')

@section('contenedor')
<script src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="{{asset('js/jquery.min.js')}}"> </script>

<div class="row">
    
      <div class="col-md-6">
        
        <h2> Ingrese una fecha de un dia feriado:</h2>
        <form method="POST" action="{{ route('altaferiados') }}">
          @csrf

          <label for="text" class="col-sm-0 control-label">Descripcion</label>      
          <input type="text" class="form-control" id="descripcion" name="descripcion"  placeholder="Año Nuevo" value="" required />
          
          <label for="text" class="col-sm-0 control-label">Fecha</label><br>
          <div class="row">
             <div class="col-md-2">
              <input type="text" maxlength="2" class="form-control" id="fecha_dia" name="fecha_dia"  placeholder="dd" required />
            </div>
             <label for="text" class="col-sm-0 control-label">/</label>
            <div class="col-md-2">
              <input type="text" maxlength="2" class="form-control" id="fecha_mes" name="fecha_mes"  placeholder="mm" required />
            </div>
            <label for="text" class="col-sm-0 control-label">/</label>
            <div class="col-md-3">   
              <input type="text" maxlength="4" class="form-control" id="fecha_anio" name="fecha_anio"  placeholder="YYYY" required />
            </div>
         </div>
          <br> <br>
          <div>
            <button class="rodri_button">Registar</button>
            <!--<a href="turno_registrado" class="btn btn-primary">Registrar</a>-->
          </div>
        </form>
      </div>

      <div class="col-md-6">
        <div class="row"> 
          <div class="col-md-5">
          
          <table class="table table-condensed" id="tabla_pacientes1" name="tabla_pacientes1">
           <thead id="pacientes-list-head1" name="pacientes-list-head1">
              <tr>                                    
                  <th id="head1" scope="col"><b>{{$anioActual}}</b></th>                              
              </tr>
            </thead>         
             <tbody id="pacientes-list1" name="pacientes-list1">        
              @foreach($feriados1 as $f)
                <tr>            
                <td><button class="rodri_button_agenda" onclick="eliminarFecha('{{$f->id}}')" >{{$f->fecha}}</button></td>            
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="col-md-5">
          <table class="table table-condensed" id="tabla_pacientes2" name="tabla_pacientes2">
           <thead id="pacientes-list-head2" name="pacientes-list-head2">
              <tr>                                    
                  <th id="head1" scope="col"><b>{{$anioProximo}}</b></th>                              
              </tr>
            </thead>         
             <tbody id="pacientes-list2" name="pacientes-list2">        
              @foreach($feriados2 as $f)
                <tr>            
                <td><button class="rodri_button_agenda" onclick="eliminarFecha('{{$f->id}}')" >{{$f->fecha}}</button></td>            
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      
      </div> <!-- row -->
    </div>
      
</div>

<script type="text/javascript">  

$.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

function eliminarFecha(fecha_id){        
  $.ajax({
       type:'POST',
       dataType:'JSON',
       url:'/eliminar_fecha_feriado',
       data:{fecha_id: fecha_id, _token: '{{csrf_token()}}'},
       success:function(data){      
          $("#tabla_pacientes1").find("tr:gt(0)").remove();                                            
          for (i = 0; i < data.feriados1.length; i++){
              var head = '<tr><td><button class="rodri_button_agenda" onclick=eliminarFecha("'+data.feriados1[i].id+'")>'+data.feriados1[i].fecha+'</button></td></tr>';                
              $('#pacientes-list1').append(head);                                       
           }
           $("#tabla_pacientes2").find("tr:gt(0)").remove(); 
           for (i = 0; i < data.feriados2.length; i++){        
              var head = '<tr><td><button class="rodri_button_agenda" onclick=eliminarFecha("'+data.feriados2[i].id+'")>'+data.feriados2[i].fecha+'</button></td></tr>';                    
              $('#pacientes-list2').append(head);                                       
           }                                                           
        } 
      
  }); 
}


</script>

@endsection