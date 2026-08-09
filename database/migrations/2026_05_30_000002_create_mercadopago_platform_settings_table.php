<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMercadopagoPlatformSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('mercadopago_platform_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('platform_commission_percent', 5, 2)->default(0);
            $table->string('mp_app_id')->nullable();
            $table->text('mp_client_secret')->nullable();
            $table->string('mode', 20)->default('sandbox');
            $table->text('integrator_access_token')->nullable();
            $table->string('integrator_public_key')->nullable();
            $table->decimal('mp_commission_fallback_percent', 5, 2)->default(0.8);
            $table->string('checkout_description')->nullable();
            $table->timestamps();
        });

        DB::table('mercadopago_platform_settings')->insert([
            'platform_commission_percent' => 5,
            'mode' => 'sandbox',
            'mp_commission_fallback_percent' => 0.8,
            'checkout_description' => 'Reserva de turno online',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('mercadopago_platform_settings');
    }
}
