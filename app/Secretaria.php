<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Secretaria extends Model
{
    protected $fillable = ['nombre', 'apellido', 'user_id'];
}
