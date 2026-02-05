<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HorariosMedicosAgregado extends Model
{
    protected $fillable = ['fecha_agregada_id','medico', 'consultorio', 'dia', 'horario','doble','activo'];
}
