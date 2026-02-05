<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Turno Confirmado</title>
</head>
<body>
<h3><strong>Turno Confirmado</strong></h3>
<p>Hola {{$data['nombre']}}! Confirmamos que su turno ha sido reservado a las {{$data['horario']}} hs el dia {{$data['fecha']}} con el especialista {{$data['medico']}} en el consultorio ubicado en {{$data['direccion']}}.</p>
<p>En caso de no poder asistir por favor comunicarse a {{$data['telefono']}}.</p>
<br>
<p>Saludos,</p>
<p>Turnos Online BB</p>
</body>
</html>