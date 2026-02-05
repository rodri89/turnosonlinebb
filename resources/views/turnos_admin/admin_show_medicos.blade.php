
@extends('turnos_admin/modelo_plantilla_admin')

@section('titulo_header','Admin Medicos')

@section('body_titulo','')

@section('contenedor')

<div class="row">
    
      <div class="col-md-8 col-center">
      <h4>Listado de Medicos:</h4>
      <table class="table">
       <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Especialidad</th>
            <th scope="col">Medico</th>                     
          </tr>
        </thead>
        <tbody>
          <?php $cont = 1 ?>
          @foreach($users as $tr)
          <tr>
            <th scope="row">{{$cont++}}</th>
            <td>{{$tr->especialidad}}</td>                     
            <!--<td><img class="card-img-top" src="images/medicos/{{$tr->foto}}" alt=""> {{$tr->apellidom.', '.$tr->nombrem}}</td>                     -->
            <td>{{$tr->apellidom.', '.$tr->nombrem}}</td>                     
            <form method="POST" action="{{ route('adminmedico') }}">
            <input type="hidden" name="medico_id" value="{{$tr->medico_id}}"  />            
            <input type="hidden" name="consultorio" value="{{$tr->consultorio}}"  />
            @csrf                         
            <td><button>></button></td>
            </form>
          </tr>
          @endforeach
      </tbody>
    </table>
    </div>
      
</div>

@endsection