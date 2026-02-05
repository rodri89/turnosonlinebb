
@extends('turnos_admin/modelo_plantilla_admin')

@section('titulo_header','Admin Obra Social')

@section('body_titulo','')

@section('contenedor')
<script src = "https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="{{asset('js/jquery.min.js')}}"> </script>

<div class="row">
    
      <div class="col-md-6">
        
        <h2> Ingrese el nombre de una obra social</h2>
        <form method="POST" action="{{ route('altaobrasocial') }}">
          @csrf
          <label for="text" class="col-sm-0 control-label">Nombre</label>      
          <input type="text" class="form-control" id="nombre" name="nombre"  placeholder="Nueva Obra Social" value="" required />
          
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
                  <th id="head1" scope="col"><b>Obra Sociales Activas</b></th>                              
              </tr>
            </thead>         
             <tbody id="pacientes-list1" name="pacientes-list1">        
              @foreach($obraSociales as $f)
                <tr>            
                <td><button class="rodri_button_agenda" onclick="eliminarObraSocial('{{$f->id}}')" >{{$f->nombre}}</button></td>            
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

function eliminarObraSocial(obra_social_id){        
 
}


</script>

@endsection