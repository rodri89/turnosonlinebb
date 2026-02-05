<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MedicoConfig extends Model
{
    protected $fillable = ['modulo', 'medico', 'valor_string', 'valor_integer', 'valor_consulta','activo'];
}
