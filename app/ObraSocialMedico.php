<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ObraSocialMedico extends Model
{
 	 protected $fillable = ['medico', 'obra_social', 'importe', 'importe_reserva', 'activo'];
}
