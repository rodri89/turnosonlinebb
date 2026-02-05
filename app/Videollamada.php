<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Videollamada extends Model
{
	// key y secret corresponden a mercado pago.
    protected $fillable = ['medico', 'consultorio', 'link', 'disponible','key','secret','importe','link_pago','perfil', 'activo'];
}
