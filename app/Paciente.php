<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{  
    protected $fillable = ['nombre', 'apellido', 'dni','telefono','domicilio', 'localidad','mail','fecha_nacimiento','fecha_castigo','obra_social','numero_afiliado','obra_social_plan', 'obra_social_foto','afiliado_obligatorio','terminos_condiciones','activo','fcm_token','google_calendar_access_token','google_calendar_refresh_token','google_calendar_token_expires_at'];
}
