<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/**
 * tipo_turnos.id se creó como unsignedInteger->primary() sin AUTO_INCREMENT (los ids existentes
 * se insertaron a mano en migraciones). Para que el admin pueda crear tipos de turno nuevos desde
 * la UI (TipoTurnoService::crear()) hace falta que la columna autoincremente.
 */
class MakeTipoTurnosIdAutoincrement extends Migration
{
    public function up()
    {
        // MySQL no permite MODIFY sobre una columna referenciada por FK (error 1833) aunque solo
        // se le agregue AUTO_INCREMENT - hay que soltar la FK, modificar, y recrearla.
        DB::statement('ALTER TABLE medico_tipo_turnos DROP FOREIGN KEY medico_tipo_turnos_tipo_turno_foreign');
        DB::statement('ALTER TABLE tipo_turnos MODIFY id INT UNSIGNED NOT NULL AUTO_INCREMENT');
        DB::statement('ALTER TABLE medico_tipo_turnos ADD CONSTRAINT medico_tipo_turnos_tipo_turno_foreign FOREIGN KEY (tipo_turno) REFERENCES tipo_turnos (id)');
        $max = (int) DB::table('tipo_turnos')->max('id');
        DB::statement('ALTER TABLE tipo_turnos AUTO_INCREMENT = ' . ($max + 1));
    }

    public function down()
    {
        DB::statement('ALTER TABLE medico_tipo_turnos DROP FOREIGN KEY medico_tipo_turnos_tipo_turno_foreign');
        DB::statement('ALTER TABLE tipo_turnos MODIFY id INT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE medico_tipo_turnos ADD CONSTRAINT medico_tipo_turnos_tipo_turno_foreign FOREIGN KEY (tipo_turno) REFERENCES tipo_turnos (id)');
    }
}
