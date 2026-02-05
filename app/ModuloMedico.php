<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ModuloMedico extends Model
{
	protected $fillable = ['medico', 'modulo','activo'];
}
