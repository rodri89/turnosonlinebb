@extends('turnos/modelo_plantilla')

@section('titulo_header', $status === 'error' ? 'Pago no completado' : 'Pago en proceso')

@section('body_titulo', 'Reserva de turno')

@section('contenedor')
    @if($status === 'error')
        <p>{{ $message }}</p>
    @elseif($status === 'pending')
        <p>{{ $message }}</p>
        @if(!empty($intent))
            <p class="small text-muted">Referencia: #{{ $intent->id }}</p>
        @endif
    @endif
    @if(session('mp_prueba_medico') && !session('mp_prueba_compartida') && session('mp_prueba_volver_url') && Auth::check() && Auth::user()->usuario_tipo == 2)
        <p class="mt-3"><a href="{{ session('mp_prueba_volver_url') }}" class="button">Volver a Pagos / Mercado Pago</a></p>
    @else
    <a href="/homes" class="button">Volver al inicio</a>
    @endif
    <script>
    sessionStorage.removeItem('mp_pending_intent');
    sessionStorage.removeItem('mp_pending_paciente');
    </script>
@endsection
