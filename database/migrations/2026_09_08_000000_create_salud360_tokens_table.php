<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tokens de acceso de la API de Salud 360.
 * La tabla también se crea sola en el primer ingreso (App\Services\Salud360\TokenService::asegurarTabla),
 * por eso la migración es idempotente.
 */
class CreateSalud360TokensTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('salud360_tokens')) {
            return;
        }
        Schema::create('salud360_tokens', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->unsignedBigInteger('user_id')->index();
            $t->string('nombre', 100)->default('Salud360');
            $t->string('token_hash', 64)->unique();
            $t->timestamp('expira_en')->nullable();
            $t->timestamp('ultimo_uso_en')->nullable();
            $t->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('salud360_tokens');
    }
}
