<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MedicoPrimerControl extends Model
{
    	protected $fillable = ['medico', 'dia', 'consultorio', 'cantidadPrimerControl','activo'];
}
