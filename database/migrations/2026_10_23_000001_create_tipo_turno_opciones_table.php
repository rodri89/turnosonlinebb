<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Opciones de un tipo de turno "padre", por médico. Un horario fijo queda asociado al tipo
 * padre (ej. "Consulta con opciones"), y recién al confirmar el turno el paciente elige una
 * opción concreta (ej. "Estudio 1"), que es la que termina grabada en turno_registrados.tipo_turno.
 * Generaliza el radio-group que antes estaba hardcodeado para el médico 13 (Lucas Sosa).
 */
class CreateTipoTurnoOpcionesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('tipo_turno_opciones')) {
            return;
        }
        Schema::create('tipo_turno_opciones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('medico');
            $table->foreign('medico')->references('id')->on('medicos');
            $table->unsignedInteger('tipo_turno_padre');
            $table->foreign('tipo_turno_padre')->references('id')->on('tipo_turnos');
            $table->unsignedInteger('tipo_turno_opcion');
            $table->foreign('tipo_turno_opcion')->references('id')->on('tipo_turnos');
            $table->integer('activo');
            $table->timestamps();
            $table->unique(['medico', 'tipo_turno_padre', 'tipo_turno_opcion'], 'tipo_turno_opciones_unicas');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tipo_turno_opciones');
    }
}
