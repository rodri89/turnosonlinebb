<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGoogleCalendarEventIdToTurnoRegistradosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('turno_registrados', function (Blueprint $table) {
            $table->string('google_calendar_event_id', 255)->nullable()->default('')->after('activo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('turno_registrados', function (Blueprint $table) {
            $table->dropColumn('google_calendar_event_id');
        });
    }
}
