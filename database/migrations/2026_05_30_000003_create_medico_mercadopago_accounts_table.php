<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMedicoMercadopagoAccountsTable extends Migration
{
    public function up()
    {
        Schema::create('medico_mercadopago_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('medico_id')->unique();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('collector_id')->nullable();
            $table->string('mp_user_id')->nullable();
            $table->tinyInteger('cobro_activo')->default(0);
            $table->decimal('importe_reserva', 10, 2)->default(0);
            $table->timestamp('linked_at')->nullable();
            $table->string('mode', 20)->nullable();
            $table->timestamps();

            $table->foreign('medico_id')->references('id')->on('medicos')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('medico_mercadopago_accounts');
    }
}
