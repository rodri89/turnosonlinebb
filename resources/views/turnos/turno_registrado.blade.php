
@extends('turnos/modelo_plantilla')

@section('titulo_header','El turno ha sido registrado!')

@section('body_titulo','Informacion del turno')

@section('contenedor')

<p>Un mail o mensaje te texto sera enviado un dia previo para recordarle que tiene que asistir con el médico.</p>

<p>Si desea cancelar el turno, recuerde hacerlo con 24 hs de anticipacion de lo contrario será penalizado.</p>

<p>El turno registrado fue para el dia {{$fechaSolicitada}} a las {{$horario}} hs.</p>

	<a href="/homes" class="button">Finalizar</a>


@endsection