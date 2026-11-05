<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * «Tiene agenda de turnos» por médico (Salud 360, spec 005), en una columna propia.
 *
 * Es independiente de `castigo_automatico` («Mostrar Médico»: 1 = el paciente lo ve en la web), que
 * sigue decidiendo la visibilidad y no se toca acá.
 *
 * DEFAULT 1: un médico creado por la web antigua se da por médico con agenda. Después de crear la
 * columna se rellena con el mismo criterio con que `catalogos` calcula `tiene_horarios`
 * (hay un `horario_medicos` con `activo = 1`): 1 si tiene horarios cargados, 0 si no. Así ningún
 * médico cambia de estado al activar la función.
 *
 * Se rellenan todas las fichas, también las dadas de baja. Si la columna ya existe no se hace nada
 * (no se vuelve a rellenar: ya pudo editarla el administrador).
 */
class AddTieneAgendaToMedicosTable extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('medicos', 'tiene_agenda')) {
            return;
        }
        Schema::table('medicos', function (Blueprint $t) {
            // boolean() = tinyint(1) NOT NULL.
            $t->boolean('tiene_agenda')->default(1);
        });
        DB::statement(
            'UPDATE medicos SET tiene_agenda = CASE WHEN EXISTS ('
            . 'SELECT 1 FROM horario_medicos h WHERE h.medico = medicos.id AND h.activo = 1'
            . ') THEN 1 ELSE 0 END'
        );
    }

    public function down()
    {
        if (!Schema::hasColumn('medicos', 'tiene_agenda')) {
            return;
        }
        Schema::table('medicos', function (Blueprint $t) {
            $t->dropColumn('tiene_agenda');
        });
    }
}
