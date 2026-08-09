
@extends('turnos_admin/modelo_plantilla_admin')

@section('titulo_header','Admin Consultorios')

@section('body_titulo','')

@section('contenedor')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
  {{ session('success') }}
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  {{ session('error') }}
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
@endif

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

          <hr>
          <h4>Configuración de Estilos</h4>
          <small class="text-muted">Personalice los colores y fuente del consultorio para las pantallas públicas.</small>
          <br><br>

          <label for="text" class="col-sm-0 control-label">Color Primario (Nav y Footer)</label>      
          <input type="color" class="form-control" name="color_primario" value="#1a5276"  />
          
          <label for="text" class="col-sm-0 control-label">Color Secundario (Header)</label>      
          <input type="color" class="form-control" name="color_secundario" value="#2e86c1"  />
          
          <label for="text" class="col-sm-0 control-label">Color Terciario (detalles)</label>      
          <input type="color" class="form-control" name="color_terciario" value="#85c1e9"  />

          <label for="text" class="col-sm-0 control-label">Color Título (texto del header)</label>      
          <input type="color" class="form-control" name="titulo_color" value="#ffffff"  />

          <label for="text" class="col-sm-0 control-label">Color Subtítulo</label>      
          <input type="color" class="form-control" name="subtitulo_color" value="#d4e6f1"  />

          <label for="text" class="col-sm-0 control-label">Tipo de Letra (Nav)</label>      
          <select class="form-control" name="titulo_tipo_letra">
            <option value="Arial, sans-serif">Arial</option>
            <option value="Helvetica, sans-serif">Helvetica</option>
            <option value="'Times New Roman', serif">Times New Roman</option>
            <option value="Georgia, serif">Georgia</option>
            <option value="Verdana, sans-serif">Verdana</option>
            <option value="'Trebuchet MS', sans-serif">Trebuchet MS</option>
            <option value="'Segoe UI', sans-serif">Segoe UI</option>
          </select>

          <br> <br>
          <div>
            <button>Registrar</button>
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

        <hr>
        <h2> Configurar Estilos</h2>
        <form method="POST" action="{{ route('actualizarConfigConsultorio') }}">
          @csrf          
          <label for="text" class="col-sm-0 control-label">Consultorio</label>      
          <select class="form-control" name="consultorio_id" id="select_config_consultorio">
            <option value="">Seleccione...</option>            
            @foreach($consultorios as $consultorio)
            <option value="{{$consultorio->id}}" data-config='@if($consultorio->config){{ json_encode($consultorio->config) }}@endif'>{{$consultorio->nombre.' - '.$consultorio->direccion}}</option>            
            @endforeach
          </select>
          <br>

          <label for="text" class="col-sm-0 control-label">Color Primario (Nav y Footer)</label>      
          <input type="color" class="form-control config-field" name="color_primario" value="#1a5276"  />
          
          <label for="text" class="col-sm-0 control-label">Color Secundario (Header)</label>      
          <input type="color" class="form-control config-field" name="color_secundario" value="#2e86c1"  />
          
          <label for="text" class="col-sm-0 control-label">Color Terciario (detalles)</label>      
          <input type="color" class="form-control config-field" name="color_terciario" value="#85c1e9"  />

          <label for="text" class="col-sm-0 control-label">Color Título (texto del header)</label>      
          <input type="color" class="form-control config-field" name="titulo_color" value="#ffffff"  />

          <label for="text" class="col-sm-0 control-label">Color Subtítulo</label>      
          <input type="color" class="form-control config-field" name="subtitulo_color" value="#d4e6f1"  />

          <label for="text" class="col-sm-0 control-label">Tipo de Letra (Nav)</label>      
          <select class="form-control config-field" name="titulo_tipo_letra">
            <option value="Arial, sans-serif">Arial</option>
            <option value="Helvetica, sans-serif">Helvetica</option>
            <option value="'Times New Roman', serif">Times New Roman</option>
            <option value="Georgia, serif">Georgia</option>
            <option value="Verdana, sans-serif">Verdana</option>
            <option value="'Trebuchet MS', sans-serif">Trebuchet MS</option>
            <option value="'Segoe UI', sans-serif">Segoe UI</option>
          </select>
        
          <br>
          <br>
          <div>
            <button>Guardar Configuración</button>     
          </div>
        </form>
      </div>
      
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectConsultorio = document.getElementById('select_config_consultorio');
    
    selectConsultorio.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const configStr = selectedOption.getAttribute('data-config');
        const fields = document.querySelectorAll('.config-field');
        
        // Resetear a valores por defecto si no hay configuración o se seleccionó "Seleccione..."
        let configValues = null;
        if (configStr && configStr !== '') {
            try {
                configValues = JSON.parse(configStr);
            } catch(e) {
                configValues = null;
            }
        }
        
        fields.forEach(function(field) {
            const fieldName = field.getAttribute('name');
            if (configValues && configValues[fieldName]) {
                if (field.tagName === 'SELECT') {
                    field.value = configValues[fieldName];
                } else {
                    field.value = configValues[fieldName];
                }
            } else {
                // Valores por defecto si no hay configuración guardada
                if (field.tagName === 'SELECT') {
                    field.value = 'Arial, sans-serif';
                } else {
                    switch(fieldName) {
                        case 'color_primario': field.value = '#1a5276'; break;
                        case 'color_secundario': field.value = '#2e86c1'; break;
                        case 'color_terciario': field.value = '#85c1e9'; break;
                        case 'titulo_color': field.value = '#ffffff'; break;
                        case 'subtitulo_color': field.value = '#d4e6f1'; break;
                        default: field.value = '';
                    }
                }
            }
        });
    });
});
</script>

@endsection
