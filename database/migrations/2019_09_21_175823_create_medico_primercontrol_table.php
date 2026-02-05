<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMedicoPrimercontrolTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medico_primer_controls', function (Blueprint $table) {
            $table->bigIncrements('id');            
            $table->unsignedInteger('medico');
            $table->foreign('medico')->references('id')->on('medicos');                     
            $table->integer('dia');         
            $table->unsignedInteger('consultorio');
            $table->foreign('consultorio')->references('id')->on('consultorios');    
            $table->integer('cantidadPrimerControl');            
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
        Schema::dropIfExists('medico_primer_controls');
    }
}
