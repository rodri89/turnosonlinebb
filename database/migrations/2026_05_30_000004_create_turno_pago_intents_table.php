<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTurnoPagoIntentsTable extends Migration
{
    public function up()
    {
        Schema::create('turno_pago_intents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('paciente_id');
            $table->unsignedInteger('medico_id');
            $table->unsignedInteger('consultorio_id');
            $table->unsignedTinyInteger('dia');
            $table->string('horario', 10);
            $table->date('fecha_turno');
            $table->string('primer_control', 5)->default('NO');
            $table->unsignedTinyInteger('tipo_turno')->default(0);
            $table->string('especialidad')->nullable();
            $table->decimal('amount', 10, 2);
            $table->decimal('platform_fee', 10, 2)->default(0);
            $table->string('preference_id')->nullable();
            $table->string('payment_id')->nullable();
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('turno_registrado_id')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['medico_id', 'consultorio_id', 'dia', 'horario', 'fecha_turno', 'status'], 'turno_pago_intents_slot_status_idx');
            $table->index('preference_id');
            $table->index('payment_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('turno_pago_intents');
    }
}
