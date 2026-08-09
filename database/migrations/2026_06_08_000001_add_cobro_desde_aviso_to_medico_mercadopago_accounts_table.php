<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCobroDesdeAvisoToMedicoMercadopagoAccountsTable extends Migration
{
    public function up()
    {
        Schema::table('medico_mercadopago_accounts', function (Blueprint $table) {
            $table->date('cobro_desde')->nullable()->after('cobro_activo');
            $table->string('mensaje_aviso_cobro_titulo', 255)->nullable()->after('cobro_desde');
            $table->text('mensaje_aviso_cobro')->nullable()->after('mensaje_aviso_cobro_titulo');
        });
    }

    public function down()
    {
        Schema::table('medico_mercadopago_accounts', function (Blueprint $table) {
            $table->dropColumn(['cobro_desde', 'mensaje_aviso_cobro_titulo', 'mensaje_aviso_cobro']);
        });
    }
}
