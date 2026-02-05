<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePacienteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
    {
        Schema::create('pacientes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre');
            $table->string('apellido');
            $table->integer('dni')->unique();
            $table->string('telefono');
            $table->string('domicilio');
            $table->string('localidad');            
            $table->string('mail');
            $table->date('fecha_nacimiento');  
            $table->date('fecha_castigo');              
            $table->string('obra_social');            
            $table->string('numero_afiliado');            
            $table->string('obra_social_plan');
            $table->string('obra_social_foto');   
            $table->integer('afiliado_obligatorio');
            $table->integer('terminos_condiciones');         
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
        Schema::dropIfExists('paciente');
    }
}
