<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MedicoPaciente extends Model
{
    protected $fillable = ['medico', 'paciente', 'bloqueado'];
}
