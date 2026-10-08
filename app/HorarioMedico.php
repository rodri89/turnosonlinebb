<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HorarioMedico extends Model
{
	// Doble lo utlizo en caso de que quiera unir dos turnos...si doble es igual a 1 lo puedo hacer unir en caso contrario no.
	// ejemplo el turno de las 12 si el siguiente turno es a las 15 hs doble es 0, no lo puedo unir.
	// valido_desde / valido_hasta: vigencia por fecha (null = sin límite).
	// quincenal: si es 1, el horario se ofrece una semana sí, una semana no, usando valido_desde como ancla.
    protected $fillable = ['medico', 'consultorio', 'dia', 'horario','doble', 'tipo_turno','activo', 'valido_desde', 'valido_hasta', 'quincenal'];

    protected $dates = ['valido_desde', 'valido_hasta'];

    /**
     * Scope: solo horarios vigentes en la fecha dada.
     */
    public function scopeVigenciaEnFecha($query, $fecha)
    {
        if ($fecha === null) {
            return $query;
        }
        return $query->where(function ($q) use ($fecha) {
            $q->whereNull('valido_desde')->orWhere('valido_desde', '<=', $fecha);
        })->where(function ($q) use ($fecha) {
            $q->whereNull('valido_hasta')->orWhere('valido_hasta', '>=', $fecha);
        });
    }

    /**
     * Aplica filtro de vigencia a una query builder de horario_medicos (tabla).
     * Uso: HorarioMedico::aplicarVigenciaQuery($query, $fecha)->orderBy(...)->get()
     */
    public static function aplicarVigenciaQuery($query, $fecha)
    {
        if ($fecha === null || $fecha === '' || !\Illuminate\Support\Facades\Schema::hasColumn('horario_medicos', 'valido_desde')) {
            return $query;
        }
        $fecha = str_replace('/', '-', $fecha);
        return $query
            ->where(function ($q) use ($fecha) {
                $q->whereNull('horario_medicos.valido_desde')
                    ->orWhere('horario_medicos.valido_desde', '<=', $fecha);
            })
            ->where(function ($q) use ($fecha) {
                $q->whereNull('horario_medicos.valido_hasta')
                    ->orWhere('horario_medicos.valido_hasta', '>=', $fecha);
            });
    }

    /**
     * Texto "HH:MM a HH:MM" (o varios, separados por " - ") para un día/tipo_turno, calculado
     * directamente de horario_medicos en vez de depender de la tabla horario_medico_d_h_s
     * (mantenida a mano aparte y que se desincroniza del horario real cuando éste cambia).
     * La duración de turno se infiere del menor espacio entre horarios consecutivos del día.
     */
    public static function desdeHastaTexto($medicoId, $dia, $tipoTurno)
    {
        $horarios = \Illuminate\Support\Facades\DB::table('horario_medicos')
            ->where('medico', $medicoId)
            ->where('dia', $dia)
            ->where('tipo_turno', $tipoTurno)
            ->where('activo', 1)
            ->orderBy('horario')
            ->pluck('horario')
            ->unique()
            ->values()
            ->all();

        if (empty($horarios)) {
            return '';
        }

        $minutos = array_map(function ($h) {
            list($hh, $mm) = explode(':', $h);
            return ((int) $hh) * 60 + (int) $mm;
        }, $horarios);

        // Duración de turno: el menor espacio positivo entre horarios consecutivos del día (default 10').
        $duracion = 10;
        $gaps = [];
        for ($i = 1; $i < count($minutos); $i++) {
            $gap = $minutos[$i] - $minutos[$i - 1];
            if ($gap > 0) {
                $gaps[] = $gap;
            }
        }
        if (!empty($gaps)) {
            $duracion = min($gaps);
        }

        // Agrupar en rangos contiguos: un salto mayor a la duración de turno corta el rango
        // (ej. mañana y tarde el mismo día).
        $rangos = [];
        $inicioRango = $minutos[0];
        $finRango = $minutos[0];
        for ($i = 1; $i < count($minutos); $i++) {
            if (($minutos[$i] - $finRango) > $duracion) {
                $rangos[] = [$inicioRango, $finRango];
                $inicioRango = $minutos[$i];
            }
            $finRango = $minutos[$i];
        }
        $rangos[] = [$inicioRango, $finRango];

        $formatear = function ($min) {
            $min = (int) $min;
            return sprintf('%02d:%02d', intdiv($min, 60), $min % 60);
        };

        $textos = array_map(function ($r) use ($duracion, $formatear) {
            return $formatear($r[0]) . ' a ' . $formatear($r[1] + $duracion);
        }, $rangos);

        return implode(' - ', $textos);
    }

    /**
     * true si, para un horario quincenal con ancla $validoDesde, la semana de $fecha es una semana activa.
     * Sin ancla o fecha inválida: falla abierto (true) para no ocultar turnos por datos incompletos.
     */
    public static function esSemanaQuincenalValida($validoDesde, $fecha)
    {
        if (empty($validoDesde) || empty($fecha)) {
            return true;
        }
        try {
            $anchor = new \DateTime(str_replace('/', '-', (string) $validoDesde));
            $target = new \DateTime(str_replace('/', '-', (string) $fecha));
        } catch (\Exception $e) {
            return true;
        }
        if ($target < $anchor) {
            return true;
        }
        $diffWeeks = intdiv((int) $anchor->diff($target)->days, 7);
        return ($diffWeeks % 2) === 0;
    }
}
