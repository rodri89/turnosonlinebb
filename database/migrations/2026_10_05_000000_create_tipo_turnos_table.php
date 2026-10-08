<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Catálogo de tipos de turno. Los ids coinciden con los valores de `tipo_turno`
 * usados hoy como "magic numbers" en Util::getTipoTurno() y en horario_medicos/turno_registrados.
 */
class CreateTipoTurnosTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tipo_turnos')) {
            Schema::create('tipo_turnos', function (Blueprint $table) {
                $table->unsignedInteger('id')->primary();
                $table->string('nombre');
                $table->integer('activo');
                $table->timestamps();
            });
        }

        $catalogo = [
            1 => 'Consulta',
            2 => 'Videollamada',
            4 => 'Videollamada Salud360',
            22 => 'Consulta Online',
            23 => 'Ecografías',
            24 => 'Deportología',
            25 => 'Consulta + Ecografías',
            26 => 'Entrevista con padres',
            27 => 'Consulta y evaluación',
        ];

        foreach ($catalogo as $id => $nombre) {
            if (DB::table('tipo_turnos')->where('id', $id)->exists()) {
                continue;
            }
            DB::table('tipo_turnos')->insert([
                'id' => $id,
                'nombre' => $nombre,
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('tipo_turnos');
    }
}
