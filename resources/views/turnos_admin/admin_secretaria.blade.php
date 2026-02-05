
@extends('turnos_admin/modelo_plantilla_admin')

@section('titulo_header','Admin Secretarias')

@section('body_titulo','')

@section('contenedor')

<div class="row">
          
      <div class="col-md-6">
        
        <h2> Vincular secretaria con consultorio:</h2>
        <form method="POST" action="{{ route('vincularsecretariaconsultorio') }}">
          @csrf          
          <div class="form-group">
          <label for="sel1">Secretaria:</label>
          <select class="form-control" id="sel1" name="secretaria">
            <option>N/A</option>            
            @foreach($secretarias as $secretaria)
            <option>{{$secretaria->id.'-'.$secretaria->apellido.', '.$secretaria->nombre}}</option>            
            @endforeach
          </select>
          </div>
          
          <div class="form-group">
          <label for="sel1">Consultorio:</label>
          <select class="form-control" id="sel1" name="consultorio">
            <option>N/A</option>            
            @foreach($consultorios as $consultorio)
            <option>{{$consultorio->id.'-'.$consultorio->direccion}}</option>            
            @endforeach
          </select>
          </div>
          <div>
            <button>Vincular</button>
            <!--<a href="turno_registrado" class="btn btn-primary">Registrar</a>-->
          </div>
        </form>
      </div>
      
</div>

@endsection