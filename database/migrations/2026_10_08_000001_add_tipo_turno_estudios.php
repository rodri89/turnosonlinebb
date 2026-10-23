<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/** Nuevo tipo de turno para el caso "no es primera consulta". */
class AddTipoTurnoEstudios extends Migration
{
    const ID = 28;

    public function up()
    {
        if (DB::table('tipo_turnos')->where('id', self::ID)->exists()) {
            return;
        }
        DB::table('tipo_turnos')->insert([
            'id' => self::ID,
            'nombre' => 'Estudios',
            'activo' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        DB::table('medico_tipo_turnos')->where('tipo_turno', self::ID)->delete();
        DB::table('tipo_turnos')->where('id', self::ID)->delete();
    }
}
