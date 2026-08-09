<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReembolsoCancelacionToMedicoMercadopagoAccountsTable extends Migration
{
    public function up()
    {
        Schema::table('medico_mercadopago_accounts', function (Blueprint $table) {
            $table->unsignedTinyInteger('reembolso_cancelacion_activo')->default(0)->after('mensaje_aviso_cobro');
            $table->unsignedInteger('reembolso_cancelacion_dias_previos')->nullable()->after('reembolso_cancelacion_activo');
        });
    }

    public function down()
    {
        Schema::table('medico_mercadopago_accounts', function (Blueprint $table) {
            $table->dropColumn(['reembolso_cancelacion_activo', 'reembolso_cancelacion_dias_previos']);
        });
    }
}
