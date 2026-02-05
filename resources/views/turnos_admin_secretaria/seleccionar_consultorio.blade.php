@extends('turnos_admin_secretaria/turnos_admin_secretaria_plantilla')

@section('contenedor')
 <div class="row">
  @if($mensaje)
    <div class="col-md-8 col-md-offset-4">
      <h2>{{$mensaje}}</h2>
    </div>
  @else
  @if($consultorios)  
    <div class="table-responsive" style="height:450px">
     <div class="col-md-8 col-center">
      <h4>Listado de Consultorios:</h4>
      <table class="table">
       <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Consultorio</th>                     
          </tr>
        </thead>
        <tbody>
          <?php $cont = 1 ?>
          @foreach($consultorios as $tr)
          <tr>
            <th scope="row">{{$cont++}}</th>
            <td>{{$tr->direccion}}</td>                     
            <form method="POST" action="{{ route('mostrarmedicoconsultorio') }}">            
            <input type="hidden" name="consultorio" value="{{$tr->id}}"  />
            <input type="hidden" name="option" value="{{$option}}"  />
            @csrf                         
            <td><button>></button></td>
            </form>
          </tr>
          @endforeach
      </tbody>
    </table>
    </div>
    </div>
    @else

    @endif
  
  @endif  
</div>

<script type="text/javascript">
  window.onload=function() {          
    checkRecetasPendientes();
  }
</script>

@endsection