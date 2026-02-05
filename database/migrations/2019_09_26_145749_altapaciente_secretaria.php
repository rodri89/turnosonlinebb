<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AltapacienteSecretaria extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('paciente_secretarias', function (Blueprint $table) {
            $table->bigIncrements('id');            
            $table->unsignedInteger('paciente');                    
            $table->foreign('paciente')->references('id')->on('pacientes');                             
            $table->unsignedInteger('consultorio');
            $table->foreign('consultorio')->references('id')->on('consultorios');                 
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
        Schema::dropIfExists('paciente_secretarias');
    }
}
