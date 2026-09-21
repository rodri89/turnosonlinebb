<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Historias clínicas de Salud 360 habilitadas por médico.
 * La tabla también se crea sola al usarse (App\Services\Salud360\HistoriaClinicaService::asegurarTabla),
 * por eso la migración es idempotente.
 */
class CreateSalud360MedicoHcTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('salud360_medico_hc')) {
            return;
        }
        Schema::create('salud360_medico_hc', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->unsignedBigInteger('medico_id')->index();
            $t->string('hc_codigo', 40);
            $t->tinyInteger('activo')->default(1);
            $t->timestamps();
            $t->unique(['medico_id', 'hc_codigo']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('salud360_medico_hc');
    }
}
