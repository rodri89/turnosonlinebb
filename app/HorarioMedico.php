<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HorarioMedico extends Model
{
	// Doble lo utlizo en caso de que quiera unir dos turnos...si doble es igual a 1 lo puedo hacer unir en caso contrario no.
	// ejemplo el turno de las 12 si el siguiente turno es a las 15 hs doble es 0, no lo puedo unir.
    protected $fillable = ['medico', 'consultorio', 'dia', 'horario','doble', 'tipo_turno','activo'];
}
