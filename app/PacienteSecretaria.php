<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PacienteSecretaria extends Model
{
     protected $fillable = ['dni', 'consultorio','activo'];
}
