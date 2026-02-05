<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModuloMedicoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modulo_medicos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('medico');                    
            $table->foreign('medico')->references('id')->on('medicos');  
            $table->unsignedInteger('modulo');                    
            $table->foreign('modulo')->references('id')->on('modulos');  
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
        Schema::dropIfExists('modulo_medicos');
    }
}
