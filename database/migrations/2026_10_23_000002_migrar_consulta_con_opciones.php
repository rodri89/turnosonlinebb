<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

/**
 * Generaliza, vía TipoTurnoService/tipo_turno_opciones, el mecanismo que antes estaba
 * hardcodeado para el médico 13 (Lucas Sosa), y lo aplica también a la médica 42 (Nadia):
 *
 * - Nadia (42): sus horarios fijos quedaban en 3 tipos distintos (26 Entrevista con padres,
 *   27 Consulta y evaluación, 28 Estudios), obligando al paciente a elegir el tipo ANTES de ver
 *   el calendario. Se crea un tipo padre único "Consulta con opciones", se reasignan sus horarios
 *   fijos a ese padre (conservando vigencia/demás columnas), y 26/27/28 pasan a ser las "opciones"
 *   que el paciente elige al confirmar el turno (mismo horario, cualquiera de las 3 opciones).
 * - Lucas Sosa (13): sus horarios ya son todos tipo_turno=1 ("Consulta") - se configura el tipo 1
 *   como padre. Sus 3 opciones históricas ("Consulta endocrina", "Ecografía", "Consulta endocrina
 *   + Ecografía") no reusan los tipos globales 1/23/25 (esos tienen otro nombre en el catálogo -
 *   "Consulta", "Ecografías", "Consulta + Ecografías" - y 23 además se usa como tipo reservable
 *   normal en otros médicos): se crean 3 tipos nuevos, propios de esta migración, para que el texto
 *   que ve el paciente sea exactamente el mismo que mostraba el radio-group hardcodeado (con el
 *   typo "Consula" corregido a "Consulta"). No hace falta tocar horario_medicos para él.
 */
class MigrarConsultaConOpciones extends Migration
{
    const MEDICO_NADIA = 42;
    const OPCIONES_NADIA = [26, 27, 28]; // Entrevista con padres, Consulta y evaluación, Estudios

    const MEDICO_LUCAS_SOSA = 13;
    const TIPO_PADRE_LUCAS_SOSA = 1; // Consulta
    const OPCIONES_LUCAS_SOSA = ['Consulta endocrina', 'Ecografía', 'Consulta endocrina + Ecografía'];

    public function up()
    {
        $tipoTurnoService = app(\App\Services\TipoTurnoService::class);

        // Nadia: crear el tipo padre (si no existe ya con ese nombre, por si se re-corre a mano).
        $padre = DB::table('tipo_turnos')->where('nombre', 'Consulta con opciones')->first();
        $padreId = $padre ? (int) $padre->id : $tipoTurnoService->crear('Consulta con opciones');

        DB::table('horario_medicos')
            ->where('medico', self::MEDICO_NADIA)
            ->whereIn('tipo_turno', self::OPCIONES_NADIA)
            ->where('activo', 1)
            ->update(['tipo_turno' => $padreId]);

        $tipoTurnoService->guardar(self::MEDICO_NADIA, [$padreId]);
        $tipoTurnoService->guardarOpciones(self::MEDICO_NADIA, $padreId, self::OPCIONES_NADIA);

        // Lucas Sosa: el padre ya es el tipo 1 que usan todos sus horarios, no hace falta crearlo
        // ni tocar horario_medicos. Las opciones sí hay que crearlas (o reusarlas si ya existen,
        // por si se re-corre esta migración a mano).
        $opcionesLucasSosaIds = [];
        foreach (self::OPCIONES_LUCAS_SOSA as $nombre) {
            $existente = DB::table('tipo_turnos')->where('nombre', $nombre)->first();
            $opcionesLucasSosaIds[] = $existente ? (int) $existente->id : $tipoTurnoService->crear($nombre);
        }
        $tipoTurnoService->guardarOpciones(self::MEDICO_LUCAS_SOSA, self::TIPO_PADRE_LUCAS_SOSA, $opcionesLucasSosaIds);
    }

    public function down()
    {
        // No reversible 1:1 (se perdería qué horario de Nadia era 26/27/28 salvo que se guarde un
        // mapeo aparte). Si hace falta revertir, hacerlo a mano revisando tipo_turno_opciones.
    }
}
