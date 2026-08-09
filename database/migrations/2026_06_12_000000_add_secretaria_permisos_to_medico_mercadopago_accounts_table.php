<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSecretariaPermisosToMedicoMercadopagoAccountsTable extends Migration
{
    public function up()
    {
        Schema::table('medico_mercadopago_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('medico_mercadopago_accounts', 'secretaria_puede_reembolso')) {
                $table->unsignedTinyInteger('secretaria_puede_reembolso')->default(0)->after('reembolso_cancelacion_dias_previos');
            }
            if (!Schema::hasColumn('medico_mercadopago_accounts', 'secretaria_puede_ver_panel_mp')) {
                $table->unsignedTinyInteger('secretaria_puede_ver_panel_mp')->default(0)->after('secretaria_puede_reembolso');
            }
        });
    }

    public function down()
    {
        Schema::table('medico_mercadopago_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('medico_mercadopago_accounts', 'secretaria_puede_ver_panel_mp')) {
                $table->dropColumn('secretaria_puede_ver_panel_mp');
            }
            if (Schema::hasColumn('medico_mercadopago_accounts', 'secretaria_puede_reembolso')) {
                $table->dropColumn('secretaria_puede_reembolso');
            }
        });
    }
}
