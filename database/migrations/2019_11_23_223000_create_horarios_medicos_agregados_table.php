<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHorariosMedicosAgregadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('horarios_medicos_agregados', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('fecha_agregada_id');                        
            $table->unsignedInteger('medico');                    
            $table->foreign('medico')->references('id')->on('medicos');                
            $table->unsignedInteger('consultorio');
            $table->foreign('consultorio')->references('id')->on('consultorios');
            $table->integer('dia');                        
            $table->string('horario');
            $table->integer('doble');                                    
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
        Schema::dropIfExists('horarios_medicos_agregados');
    }
}
