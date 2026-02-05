<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FechasAgregada extends Model
{
    protected $fillable = ['fecha','dia','medico','consultorio','activo'];
}
