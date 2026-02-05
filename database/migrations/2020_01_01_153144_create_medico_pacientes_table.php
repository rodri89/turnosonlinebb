<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMedicoPacientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medico_pacientes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('paciente');                    
            $table->foreign('paciente')->references('id')->on('pacientes');
            $table->unsignedInteger('medico');                    
            $table->foreign('medico')->references('id')->on('medicos'); 
            $table->integer('bloqueado');
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
        Schema::dropIfExists('medico_pacientes');
    }
}
