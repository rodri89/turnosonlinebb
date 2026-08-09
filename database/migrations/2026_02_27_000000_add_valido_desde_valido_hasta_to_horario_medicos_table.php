<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddValidoDesdeValidoHastaToHorarioMedicosTable extends Migration
{
    /**
     * Run the migrations.
     * Vigencia del horario: válido desde/hasta estas fechas (null = sin límite).
     *
     * @return void
     */
    public function up()
    {
        Schema::table('horario_medicos', function (Blueprint $table) {
            $table->date('valido_desde')->nullable()->after('activo');
            $table->date('valido_hasta')->nullable()->after('valido_desde');
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
            $table->dropColumn(['valido_desde', 'valido_hasta']);
        });
    }
}
