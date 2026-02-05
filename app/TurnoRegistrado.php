<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TurnoRegistrado extends Model
{
	protected $fillable = ['paciente','medico', 'consultorio', 'dia', 'horario','fechaTurno','asistio','sobreturno','primerControl','caja','comentario', 'otorgado_por', 'msj_enviado','activo'];
}
