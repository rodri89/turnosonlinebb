
@extends('turnos_admin/modelo_plantilla_admin')

@section('titulo_header','Extras')

@section('body_titulo','')

@section('contenedor')

<div class="row">
    
      <div class="col-md-6">
        
        <h2> Esta seccion es para eventualidades</h2>
        <form method="POST" action="{{ route('ejecutarextras') }}" enctype="multipart/form-data">
          @csrf                  
          <p>En este caso es para vincular todas las obras sociales al ID ingresado.</p>
          <input type="text" name="valor_extra" id="valor_extra">
          <br>
          <div>
            <button class="rodri_button">Ejecutar</button>
            <!--<a href="turno_registrado" class="btn btn-primary">Registrar</a>-->
          </div>
        </form>
      </div>

      <div class="col-md-6">
                
        <form method="POST" action="{{ route('ejecutarextras2') }}" enctype="multipart/form-data">
          @csrf                  
          <p>En este caso es para vincular 1 obra social a todos los medicos</p>
          <input type="text" name="valor_extra_2" id="valor_extra_2">
          <br>
          <div>
            <button class="rodri_button">Ejecutar</button>
            <!--<a href="turno_registrado" class="btn btn-primary">Registrar</a>-->
            <small>Ingresar el ID de la obra social</small>
          </div>
        </form>
      </div>
      
</div>

@endsection