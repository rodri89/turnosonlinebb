@extends('turnos/modelo_plantilla')

@section('titulo_header','Test Google Calendar OAuth')

@section('descripcion_header','Página de prueba para conectar Google Calendar')

@section('contenedor')

<div class="row">
  <div class="col-md-8 mx-auto">
    <div class="card">
      <div class="card-header bg-primary text-white">
        <h4 class="mb-0">🧪 Test Google Calendar OAuth</h4>
      </div>
      <div class="card-body">
        
        @if(session('success'))
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>✅ Éxito:</strong> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        @endif

        @if(session('error'))
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>❌ Error:</strong> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        @endif

        <div class="mb-4">
          <h5>Configuración Actual:</h5>
          <ul class="list-group">
            <li class="list-group-item">
              <strong>Client ID:</strong> 
              <code>{{ substr(env('GOOGLE_CALENDAR_CLIENT_ID', 'NO CONFIGURADO'), 0, 30) }}...</code>
            </li>
            <li class="list-group-item">
              <strong>Redirect URI:</strong> 
              <code>{{ env('GOOGLE_CALENDAR_REDIRECT_URI', 'NO CONFIGURADO') }}</code>
            </li>
            <li class="list-group-item">
              <strong>Estado:</strong> 
              @if(env('GOOGLE_CALENDAR_CLIENT_ID') && env('GOOGLE_CALENDAR_CLIENT_SECRET'))
                <span class="badge badge-success">Configurado</span>
              @else
                <span class="badge badge-danger">No Configurado</span>
              @endif
            </li>
          </ul>
        </div>

        <div class="mb-4">
          <h5>Probar Conexión:</h5>
          <p class="text-muted">
            Haz clic en el botón para probar la conexión con Google Calendar.
            Necesitarás un ID de paciente de prueba.
          </p>
          
          <form method="GET" action="{{ route('test.google.calendar.connect', ['token' => request()->route('token')]) }}" class="mb-3">
            <div class="form-group">
              <label for="paciente_id">ID de Paciente (para pruebas):</label>
              <input type="number" 
                     class="form-control" 
                     id="paciente_id" 
                     name="paciente_id" 
                     value="{{ old('paciente_id', request()->input('paciente_id', 1)) }}" 
                     required
                     placeholder="Ej: 1">
              <small class="form-text text-muted">
                Usa un ID de paciente existente en la base de datos para pruebas.
              </small>
            </div>
            <button type="submit" class="btn btn-primary btn-lg">
              🔗 Conectar Google Calendar
            </button>
          </form>
        </div>

        <div class="alert alert-info">
          <h6><strong>ℹ️ Información:</strong></h6>
          <ul class="mb-0">
            <li>Esta página es solo para pruebas y está protegida con un token secreto.</li>
            <li>Los usuarios normales no pueden acceder a esta página.</li>
            <li>Después de autorizar, serás redirigido de vuelta aquí.</li>
            <li>El token de acceso se guardará en la base de datos para el paciente especificado.</li>
          </ul>
        </div>

        @if(isset($paciente) && $paciente)
          <div class="mt-4">
            <h5>Estado del Paciente de Prueba (ID: {{ $paciente->id }}):</h5>
            <ul class="list-group">
              <li class="list-group-item">
                <strong>Nombre:</strong> {{ $paciente->nombre }} {{ $paciente->apellido }}
              </li>
              <li class="list-group-item">
                <strong>Google Calendar Conectado:</strong> 
                @if($paciente->google_calendar_access_token)
                  <span class="badge badge-success">✅ Sí</span>
                @else
                  <span class="badge badge-warning">❌ No</span>
                @endif
              </li>
              @if($paciente->google_calendar_access_token)
                <li class="list-group-item">
                  <strong>Token Expira:</strong> 
                  {{ $paciente->google_calendar_token_expires_at ? \Carbon\Carbon::parse($paciente->google_calendar_token_expires_at)->format('d/m/Y H:i:s') : 'N/A' }}
                </li>
                <li class="list-group-item">
                  <form method="POST" action="{{ route('test.google.calendar.disconnect', ['token' => request()->route('token')]) }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="paciente_id" value="{{ $paciente->id }}">
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Desconectar Google Calendar para este paciente?')">
                      Desconectar
                    </button>
                  </form>
                </li>
              @endif
            </ul>
          </div>
        @endif

      </div>
    </div>
  </div>
</div>

@endsection

