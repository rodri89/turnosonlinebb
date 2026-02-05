<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSecretariaConsultorioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('secretaria_consultorios', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('secretaria_id');
            $table->foreign('secretaria_id')->references('id')->on('secretarias'); 
            $table->unsignedInteger('consultorio_id');
            $table->foreign('consultorio_id')->references('id')->on('consultorios');
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
        Schema::dropIfExists('secretaria_consultorios');
    }
}
