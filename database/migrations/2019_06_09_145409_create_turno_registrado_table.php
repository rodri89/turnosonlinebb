<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTurnoRegistradoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('turno_registrados', function (Blueprint $table) {
            $table->bigIncrements('id');            
            $table->unsignedInteger('paciente');                    
            $table->foreign('paciente')->references('id')->on('pacientes');         
            $table->unsignedInteger('medico');
            $table->foreign('medico')->references('id')->on('medicos');         
            $table->unsignedInteger('consultorio');
            $table->foreign('consultorio')->references('id')->on('consultorios');         
            $table->integer('dia');                        
            $table->string('horario');
            $table->string('fechaTurno');          
            $table->integer('asistio');
            $table->integer('sobreturno');
            $table->string('primerControl');
            $table->float('caja');
            $table->string('comentario');
            $table->string('otorgado_por');
            $table->integer('msj_enviado');
            $table->integer('activo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('turno_registrados');
    }
}
