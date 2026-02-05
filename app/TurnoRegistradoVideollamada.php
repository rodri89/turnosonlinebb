<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TurnoRegistradoVideollamada extends Model
{
    protected $fillable = ['paciente','medico', 'consultorio', 'dia', 'horario','fechaTurno','asistio','sobreturno','primerControl','comentario','disponible','disponible_medico','pago','pago_ticket','cargado','activo'];
}
