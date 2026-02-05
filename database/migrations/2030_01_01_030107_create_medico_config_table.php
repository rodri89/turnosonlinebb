<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMedicoConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medico_configs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('modulo');                    
            $table->foreign('modulo')->references('id')->on('modulos');
            $table->unsignedInteger('medico');                    
            $table->foreign('medico')->references('id')->on('medicos');  
            $table->string('valor_string');
            $table->integer('valor_integer');
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
        Schema::dropIfExists('medico_configs');
    }
}
