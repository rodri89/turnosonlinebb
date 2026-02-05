<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecetaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recetas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('paciente');                    
            $table->foreign('paciente')->references('id')->on('pacientes');
            $table->unsignedInteger('medico');                    
            $table->foreign('medico')->references('id')->on('medicos');  
            $table->unsignedInteger('consultorio');                    
            $table->foreign('consultorio')->references('id')->on('consultorios');
            $table->string('motivo');
            $table->unsignedInteger('estado');                    
            $table->foreign('estado')->references('id')->on('receta_estados');
            $table->string('foto');
            $table->integer('retira_consultorio');
            $table->string('comentario');
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
        Schema::dropIfExists('recetas');
    }
}
