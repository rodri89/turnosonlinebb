<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesTurnoRegistradosFeriados extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('turno_registrados', function (Blueprint $table) {
            $table->index(
                ['medico', 'consultorio', 'fechaTurno', 'activo'],
                'turno_reg_med_cons_fecha_activo'
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('turno_registrados', function (Blueprint $table) {
            $table->dropIndex('turno_reg_med_cons_fecha_activo');
        });
    }
}
