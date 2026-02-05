<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Medico extends Model
{
	protected $fillable = ['nombre', 'apellido', 'especialidad','consultorio','telefono','mail','castigo_automatico','foto','activo','user_id','perfil','sexo'];
}
