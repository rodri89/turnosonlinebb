<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Recordatorio Turno</title>
</head>
<body>
<h3><strong>Recordatorio Turno</strong></h3>
<p>Buenas tardes {{$data['nombre']}}! Queremos recordarte que mañana a las {{$data['horario']}} hs tenes un turno reservado con el médico {{$data['medico']}} en el consultorio ubicado en {{$data['direccion']}}.</p>
<p>En caso de no poder asistir por favor comunicarse a {{$data['telefono']}}.</p>
<br>
<p>Saludos,</p>
<p>Turnos Online BB</p>
</body>
</html>