<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMedicoMensajesEspecialesTable extends Migration
{
    public function up()
    {
        Schema::create('medico_mensajes_especiales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('medico_id');
            $table->foreign('medico_id')->references('id')->on('medicos');
            $table->string('titulo');
            $table->text('descripcion');
            $table->date('valido_desde')->nullable();
            $table->date('valido_hasta')->nullable();
            $table->integer('activo')->default(1);
            $table->timestamps();

            $table->index(['medico_id', 'activo']);
            $table->index(['valido_desde', 'valido_hasta']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('medico_mensajes_especiales');
    }
}

