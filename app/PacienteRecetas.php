<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PacienteRecetas extends Model
{
    protected $fillable = ['receta', 'foto','activo'];
}
