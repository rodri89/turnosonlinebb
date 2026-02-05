<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMedicoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medicos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre');            
            $table->string('apellido');
            $table->unsignedInteger('especialidad'); 
            $table->foreign('especialidad')->references('id')->on('especialidads');                       
            $table->unsignedInteger('consultorio'); 
            $table->foreign('consultorio')->references('id')->on('consultorios');
            $table->bigInteger('telefono');            
            $table->string('mail');
            $table->integer('castigo_automatico');
            $table->string('foto');
            $table->integer('perfil');
            $table->integer('activo');
            $table->unsignedBigInteger('user_id'); 
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('sexo');
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
        Schema::dropIfExists('medicos');
    }
}
