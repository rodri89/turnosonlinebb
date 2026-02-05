
@extends('turnos_admin/modelo_plantilla_admin')

@section('titulo_header','Admin Consultorios')

@section('body_titulo','')

@section('contenedor')

<div class="row">
    
      <div class="col-md-6">
        
        <h2> Ingrese los datos del nuevo consultorio:</h2>
        <form method="POST" action="{{ route('altaconsultorio') }}" enctype="multipart/form-data">
          @csrf

           <label for="text" class="col-sm-0 control-label">Nombre</label>      
          <input type="text" class="form-control" name="nombre"  placeholder=""  />
          
          <label for="text" class="col-sm-0 control-label">Direccion</label>      
          <input type="text" class="form-control" name="direccion"  placeholder=""  />
          
          <label for="text" class="col-sm-0 control-label">Telefono</label>      
          <input type="text" class="form-control" name="telefono"  placeholder=""  />

          <label for="text" class="col-sm-0 control-label">Foto</label><br>
          <input type="file" name="foto" placeholder="foto"  />

          <br> <br>
          <div>
            <button>Registar</button>
            <!--<a href="turno_registrado" class="btn btn-primary">Registrar</a>-->
          </div>
        </form>
      </div>

      <div class="col-md-6">
        
       <h2> Actualizar Foto</h2>
        <form method="POST" action="{{ route('actualizarFotoConsultorio') }}" enctype="multipart/form-data">
          @csrf          
          <select class="form-control" id="sel1" name="consultorio">
            <option>N/A</option>            
            @foreach($consultorios as $consultorio)
            <option>{{$consultorio->id.'-'.$consultorio->direccion}}</option>            
            @endforeach
          </select>
          <label for="text" class="col-sm-0 control-label">Foto</label><br>
          <input type="file" name="foto" placeholder="foto" />
        
          <br>
          <br>
          <div>
            <button>Actualizar</button>     
          </div>
        </form>
      </div>
      
</div>

@endsection