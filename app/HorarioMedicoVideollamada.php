<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HorarioMedicoVideollamada extends Model
{
    protected $fillable = ['medico', 'consultorio', 'dia', 'horario','doble','activo'];
}
