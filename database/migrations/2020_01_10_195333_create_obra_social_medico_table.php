<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateObraSocialMedicoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('obra_social_medicos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('medico');                    
            $table->foreign('medico')->references('id')->on('medicos');
            $table->unsignedInteger('obra_social');                    
            $table->foreign('obra_social')->references('id')->on('obra_social');
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
        Schema::dropIfExists('obra_social_medicos');
    }
}
