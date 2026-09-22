<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNotaToPacientesTable extends Migration
{
    /**
     * Nota interna del consultorio sobre el paciente (la ve el equipo de salud, no el paciente).
     * Nullable: los altas de paciente de la web asignan campo por campo y no la conocen.
     */
    public function up()
    {
        if (Schema::hasColumn('pacientes', 'nota')) {
            return;
        }
        Schema::table('pacientes', function (Blueprint $table) {
            $table->text('nota')->nullable()->after('localidad');
        });
    }

    public function down()
    {
        if (!Schema::hasColumn('pacientes', 'nota')) {
            return;
        }
        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropColumn('nota');
        });
    }
}
