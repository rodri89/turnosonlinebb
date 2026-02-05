<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVideollamadaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('videollamadas', function (Blueprint $table) {
            $table->bigIncrements('id');            
            $table->unsignedInteger('medico');                    
            $table->foreign('medico')->references('id')->on('medicos');  
            $table->unsignedInteger('consultorio');                    
            $table->foreign('consultorio')->references('id')->on('consultorios');
            $table->string('link');
            $table->string('link_pago');
            $table->string('key');
            $table->string('secret');
            $table->integer('importe');            
            $table->integer('perfil');
            $table->integer('disponible'); 
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
        Schema::dropIfExists('videollamadas');
    }
}
