<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HorarioMedicoDhsVideollamada extends Model
{
    protected $fillable = ['medico', 'consultorio', 'dia', 'desde', 'hasta','activo'];
}
