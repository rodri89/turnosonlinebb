<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddImporteReservaToObraSocialMedicosTable extends Migration
{
    public function up()
    {
        Schema::table('obra_social_medicos', function (Blueprint $table) {
            if (!Schema::hasColumn('obra_social_medicos', 'importe_reserva')) {
                $table->decimal('importe_reserva', 10, 2)->default(0)->after('importe');
            }
        });
    }

    public function down()
    {
        Schema::table('obra_social_medicos', function (Blueprint $table) {
            if (Schema::hasColumn('obra_social_medicos', 'importe_reserva')) {
                $table->dropColumn('importe_reserva');
            }
        });
    }
}
