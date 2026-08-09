<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPagoFieldsToTurnoRegistradosTable extends Migration
{
    public function up()
    {
        Schema::table('turno_registrados', function (Blueprint $table) {
            $table->tinyInteger('pago')->default(0)->after('activo');
            $table->string('pago_estado', 30)->nullable()->after('pago');
            $table->string('mercadopago_payment_id')->nullable()->after('pago_estado');
            $table->string('mercadopago_preference_id')->nullable()->after('mercadopago_payment_id');
            $table->decimal('importe_reserva', 10, 2)->nullable()->after('mercadopago_preference_id');
            $table->unsignedBigInteger('turno_pago_intent_id')->nullable()->after('importe_reserva');
        });
    }

    public function down()
    {
        Schema::table('turno_registrados', function (Blueprint $table) {
            $table->dropColumn([
                'pago',
                'pago_estado',
                'mercadopago_payment_id',
                'mercadopago_preference_id',
                'importe_reserva',
                'turno_pago_intent_id',
            ]);
        });
    }
}
