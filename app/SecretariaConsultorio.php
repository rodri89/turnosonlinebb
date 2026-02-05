<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SecretariaConsultorio extends Model
{
    protected $fillable = ['id_secretaria', 'id_consultorio', 'activo'];
}
