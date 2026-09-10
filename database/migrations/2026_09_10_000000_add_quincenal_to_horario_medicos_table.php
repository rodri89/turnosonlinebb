<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQuincenalToHorarioMedicosTable extends Migration
{
    /**
     * Run the migrations.
     * Quincenal: si está en 1, el horario se ofrece una semana sí, una semana no,
     * tomando la semana de `valido_desde` como semana activa (ancla).
     *
     * @return void
     */
    public function up()
    {
        Schema::table('horario_medicos', function (Blueprint $table) {
            $table->boolean('quincenal')->default(false)->after('valido_hasta');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('horario_medicos', function (Blueprint $table) {
            $table->dropColumn(['quincenal']);
        });
    }
}
