<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Qué tipos de turno (tabla tipo_turnos) ofrece cada médico. Análoga a modulo_medicos
 * pero para el catálogo de tipos de turno en vez de módulos on/off.
 */
class CreateMedicoTipoTurnosTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('medico_tipo_turnos')) {
            return;
        }
        Schema::create('medico_tipo_turnos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('medico');
            $table->foreign('medico')->references('id')->on('medicos');
            $table->unsignedInteger('tipo_turno');
            $table->foreign('tipo_turno')->references('id')->on('tipo_turnos');
            $table->integer('activo');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('medico_tipo_turnos');
    }
}
