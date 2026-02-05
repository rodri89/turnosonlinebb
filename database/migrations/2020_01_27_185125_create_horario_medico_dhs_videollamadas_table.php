<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHorarioMedicoDhsVideollamadasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('horario_medico_dhs_videollamadas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('medico');                    
            $table->foreign('medico')->references('id')->on('medicos');                
            $table->unsignedInteger('consultorio');
            $table->foreign('consultorio')->references('id')->on('consultorios');
            $table->integer('dia');                        
            $table->string('desde');
            $table->string('hasta');                                    
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
        Schema::dropIfExists('horario_medico_dhs_videollamadas');
    }
}
