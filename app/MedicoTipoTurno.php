<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MedicoTipoTurno extends Model
{
    protected $fillable = ['medico', 'tipo_turno', 'activo'];
}
