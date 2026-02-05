
@extends('turnos_admin/modelo_plantilla_admin')

@section('titulo_header','Alta Especialidad')

@section('body_titulo','')

@section('contenedor')

<div class="row">
    
      <div class="col-md-6">
        
        <h2> Ingrese los datos de la nueva especialidad:</h2>
        <form method="POST" action="{{ route('altaespecialidad') }}" enctype="multipart/form-data">
          @csrf
          
          <label for="text" class="col-sm-0 control-label">Nombre</label>      
          <input type="text" class="form-control" name="nombre"  placeholder="Nombre"  />
          <label for="text" class="col-sm-0 control-label">Color</label>      
          <input type="text" class="form-control" name="color"  placeholder="#FF0000"  />
          <br>
          <label for="integer" class="col-sm-0 control-label">Foto</label><br>
          <input type="file" name="foto" placeholder="foto"  />
          <br>
          <br>
          <div>
            <button>Registar</button>
            <!--<a href="turno_registrado" class="btn btn-primary">Registrar</a>-->
          </div>
        </form>
      </div>
      
</div>

@endsection