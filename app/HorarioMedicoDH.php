<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HorarioMedicoDH extends Model
{
     protected $fillable = ['medico', 'consultorio', 'dia', 'desde', 'hasta','tipo_turno', 'activo'];
}
