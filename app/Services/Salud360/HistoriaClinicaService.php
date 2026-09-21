<?php

namespace App\Services\Salud360;

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Historias clínicas de Salud 360 habilitadas a cada médico (tabla `salud360_medico_hc`).
 *
 * turnosonlinebb es la identidad única de Salud 360: el administrador decide acá qué historia clínica
 * (pediatría, clínica, gineco...) puede abrir cada médico, y el perfil que devuelve la API
 * (`auth/login`, `auth/perfil`) lo informa en `historias_clinicas`. Cada historia clínica vive en su
 * propio sistema Laravel; esas APIs validan el mismo token contra `auth/perfil` y ubican al usuario
 * local por `users.medico_id_tobb`.
 *
 * Compatible con PHP 7.1 / Laravel 5.8.
 */
class HistoriaClinicaService
{
    const TABLA = 'salud360_medico_hc';

    /** Códigos de las historias clínicas compiladas en la app (mismos códigos que `EspecialidadDefinition.codigo`). */
    const CATALOGO = [
        'pediatria' => 'Pediatría',
        'clinica' => 'Clínica médica',
        'hepatologia' => 'Hepatología',
        'gineco' => 'Ginecología y obstetricia',
        'cardiologia' => 'Cardiología',
        'endocrinologia' => 'Endocrinología',
        'hematologia' => 'Hematología',
        'desarrollo_infantil' => 'Desarrollo infantil',
    ];

    /** Crea la tabla si todavía no existe (por si el hosting no corre migraciones). */
    public function asegurarTabla()
    {
        if (Schema::hasTable(self::TABLA)) {
            return;
        }
        Schema::create(self::TABLA, function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->unsignedBigInteger('medico_id')->index();
            $t->string('hc_codigo', 40);
            $t->tinyInteger('activo')->default(1);
            $t->timestamps();
            $t->unique(['medico_id', 'hc_codigo']);
        });
    }

    /** Códigos habilitados (activos) para un médico, en el orden del catálogo. */
    public function habilitadas($medicoId)
    {
        $this->asegurarTabla();
        $codigos = DB::table(self::TABLA)
            ->where('medico_id', (int) $medicoId)
            ->where('activo', 1)
            ->pluck('hc_codigo')
            ->all();
        $orden = array_keys(self::CATALOGO);
        usort($codigos, function ($a, $b) use ($orden) {
            $ia = array_search($a, $orden);
            $ib = array_search($b, $orden);
            return ($ia === false ? 999 : $ia) - ($ib === false ? 999 : $ib);
        });
        return array_values($codigos);
    }

    /** Catálogo con el estado de cada historia clínica para un médico (para la pantalla del administrador). */
    public function estadoPara($medicoId)
    {
        $activas = $this->habilitadas($medicoId);
        $out = [];
        foreach (self::CATALOGO as $codigo => $nombre) {
            $out[] = ['codigo' => $codigo, 'nombre' => $nombre, 'activo' => in_array($codigo, $activas, true) ? 1 : 0];
        }
        return $out;
    }

    /** Deja habilitadas exactamente las historias clínicas indicadas (las demás quedan inactivas). */
    public function guardar($medicoId, array $codigos)
    {
        $this->asegurarTabla();
        $medicoId = (int) $medicoId;
        $validos = array_values(array_intersect(array_map('strval', $codigos), array_keys(self::CATALOGO)));
        $ahora = Carbon::now();
        DB::table(self::TABLA)->where('medico_id', $medicoId)->whereNotIn('hc_codigo', $validos)->update(['activo' => 0, 'updated_at' => $ahora]);
        foreach ($validos as $codigo) {
            $existe = DB::table(self::TABLA)->where('medico_id', $medicoId)->where('hc_codigo', $codigo)->first();
            if ($existe === null) {
                DB::table(self::TABLA)->insert([
                    'medico_id' => $medicoId, 'hc_codigo' => $codigo, 'activo' => 1,
                    'created_at' => $ahora, 'updated_at' => $ahora,
                ]);
            } elseif ((int) $existe->activo !== 1) {
                DB::table(self::TABLA)->where('id', $existe->id)->update(['activo' => 1, 'updated_at' => $ahora]);
            }
        }
        return $validos;
    }
}
