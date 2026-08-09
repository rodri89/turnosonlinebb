<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HorarioMedico extends Model
{
	// Doble lo utlizo en caso de que quiera unir dos turnos...si doble es igual a 1 lo puedo hacer unir en caso contrario no.
	// ejemplo el turno de las 12 si el siguiente turno es a las 15 hs doble es 0, no lo puedo unir.
	// valido_desde / valido_hasta: vigencia por fecha (null = sin límite).
    protected $fillable = ['medico', 'consultorio', 'dia', 'horario','doble', 'tipo_turno','activo', 'valido_desde', 'valido_hasta'];

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
}
