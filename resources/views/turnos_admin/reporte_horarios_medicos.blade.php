@extends('turnos_admin/modelo_plantilla_admin')

@section('titulo_header', $titulo)

@section('contenedor')
<div class="container-fluid py-3">
  <p class="text-muted mb-2">Generado: {{ $generado }} · Misma lógica que <code>MedicoController::createJson</code> (próxima ocurrencia de cada día de la semana).</p>
  <p class="mb-3">
    <a href="{{ route('admin.reporte.horarios.medicos.csv') }}" class="btn btn-secondary btn-sm">Descargar CSV</a>
  </p>
  <pre class="bg-light border p-3 small" style="max-height: 85vh; overflow: auto; white-space: pre-wrap;">{{ $contenido }}</pre>
</div>
@endsection
