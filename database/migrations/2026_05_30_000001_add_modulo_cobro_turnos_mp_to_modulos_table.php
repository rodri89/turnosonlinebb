<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class AddModuloCobroTurnosMpToModulosTable extends Migration
{
    /** Debe coincidir con TurnoController::MODULO_COBRO_TURNOS_MP */
    const MODULO_ID = 12;

    public function up()
    {
        if (DB::table('modulos')->where('id', self::MODULO_ID)->exists()) {
            return;
        }

        DB::table('modulos')->insert([
            'id' => self::MODULO_ID,
            'descripcion' => 'Cobro turnos MercadoPago',
            'activo' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        DB::table('modulo_medicos')->where('modulo', self::MODULO_ID)->delete();
        DB::table('modulos')->where('id', self::MODULO_ID)->delete();
    }
}
