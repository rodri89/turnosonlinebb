<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMercadopagoRefundIdToTurnoRegistradosTable extends Migration
{
    public function up()
    {
        Schema::table('turno_registrados', function (Blueprint $table) {
            if (!Schema::hasColumn('turno_registrados', 'mercadopago_refund_id')) {
                $table->string('mercadopago_refund_id')->nullable()->after('mercadopago_preference_id');
            }
        });
    }

    public function down()
    {
        Schema::table('turno_registrados', function (Blueprint $table) {
            if (Schema::hasColumn('turno_registrados', 'mercadopago_refund_id')) {
                $table->dropColumn('mercadopago_refund_id');
            }
        });
    }
}
