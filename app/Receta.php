<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Receta extends Model
{
    protected $fillable = ['paciente', 'medico' ,'consultorio', 'motivo', 'estado','foto','retira_consultorio', 'comentario', 'sms_enviado','activo'];

}
