<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class AddModuloDiferencialObraSocialToModulosTable extends Migration
{
    /** Debe coincidir con AgendaService::MODULO_DIFERENCIAL_OBRA_SOCIAL */
    const MODULO_ID = 13;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (DB::table('modulos')->where('id', self::MODULO_ID)->exists()) {
            return;
        }

        DB::table('modulos')->insert([
            'id' => self::MODULO_ID,
            'descripcion' => 'Diferencial por obra social',
            'activo' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('modulo_medicos')->where('modulo', self::MODULO_ID)->delete();
        DB::table('modulos')->where('id', self::MODULO_ID)->delete();
    }
}
