<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Permite acotar un tipo de turno habilitado para un médico a un caso de
 * "primera consulta" puntual: NULL = se ofrece siempre, 1 = solo si el
 * paciente respondió que es primera consulta, 0 = solo si no lo es.
 */
class AddPrimerControlToMedicoTipoTurnosTable extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('medico_tipo_turnos', 'primer_control')) {
            return;
        }
        Schema::table('medico_tipo_turnos', function (Blueprint $table) {
            $table->tinyInteger('primer_control')->nullable()->after('tipo_turno');
        });

        // Entrevista con padres (26) y Consulta y evaluación (27) quedan acotados a
        // "primera consulta = sí" para los médicos que ya los tengan cargados.
        DB::table('medico_tipo_turnos')
            ->whereIn('tipo_turno', [26, 27])
            ->whereNull('primer_control')
            ->update(['primer_control' => 1]);
    }

    public function down()
    {
        if (!Schema::hasColumn('medico_tipo_turnos', 'primer_control')) {
            return;
        }
        Schema::table('medico_tipo_turnos', function (Blueprint $table) {
            $table->dropColumn('primer_control');
        });
    }
}
