<?php

namespace App\Util;

class Util
{
    
    public static function getTipoTurno($tipoTurnoId)
    {        
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
