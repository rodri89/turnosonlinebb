
@extends('turnos/modelo_plantilla')

@section('titulo_header','El turno ha sido registrado!')

@section('body_titulo','Informacion del turno')

@section('contenedor')

<p>Un mensaje será enviado un dia previo para recordarle que tiene que asistir con el médico.</p>

<p>Si desea cancelar el turno, recuerde hacerlo con 24 hs de anticipacion de lo contrario será penalizado.</p>

<p>El turno registrado fue para el dia {{$fechaSolicitada}} a las {{$horario}} hs.</p>

@if(!empty($pagoOnline) && !empty($importe))
<p>Se registró el pago online de reserva por <strong>${{ number_format($importe, 2, ',', '.') }}</strong>.</p>
@endif

@if(session('mp_prueba_medico') && !session('mp_prueba_compartida') && session('mp_prueba_volver_url') && Auth::check() && Auth::user()->usuario_tipo == 2)
<p class="mt-3"><a href="{{ session('mp_prueba_volver_url') }}" class="button">Volver a Pagos / Mercado Pago</a></p>
@else
	<a href="/homes" class="button">Finalizar</a>
@endif

@if(!empty($pagoOnline))
<script>
sessionStorage.removeItem('mp_pending_intent');
sessionStorage.removeItem('mp_pending_paciente');
</script>
@endif

@endsection