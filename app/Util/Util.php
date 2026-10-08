<?php

namespace App\Util;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Util
{

    /**
     * Nombre legible de un tipo_turno. Busca primero en el catálogo (tabla tipo_turnos,
     * donde viven los tipos nuevos configurables como "Entrevista con padres"); si no está
     * ahí (tabla no migrada todavía, o id no cargado), cae al switch histórico.
     * El catálogo se cachea en memoria por request para no pegarle a la DB en cada llamada
     * (esto se llama una vez por turno al armar una agenda del día).
     */
    public static function getTipoTurno($tipoTurnoId)
    {
        static $catalogo = null;
        if ($catalogo === null) {
            $catalogo = [];
            try {
                if (Schema::hasTable('tipo_turnos')) {
                    $catalogo = DB::table('tipo_turnos')->pluck('nombre', 'id')->all();
                }
            } catch (\Exception $e) {
                $catalogo = [];
            }
        }
        if (isset($catalogo[$tipoTurnoId]) && $catalogo[$tipoTurnoId] !== '') {
            return $catalogo[$tipoTurnoId];
        }

        switch ($tipoTurnoId) {
            case 1:
                return "Consulta";
                break;
            case 2:
                return "Videollamada";
                break;            
            case 22:
                return "Consulta Online";
                break;            
            case 23:
                return "Ecografias";
                break;   
            case 24:
                return "Deportologia";
                break;            
            case 25:
                return "Consulta + Ecografias";
                break;            
            default:
                return "Consulta";
                break;
        }
    }

}
