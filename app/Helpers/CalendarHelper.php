<?php

namespace App\Helpers;

class CalendarHelper
{
    /**
     * Genera un enlace de Google Calendar
     */
    public static function generateGoogleCalendarLink($title, $description, $location, $startDate, $startTime, $endTime = null)
    {
        // Normalizar formato de fecha si viene en formato d/m/Y
        if (strpos($startDate, '/') !== false) {
            $fechaParts = explode('/', $startDate);
            if (count($fechaParts) == 3) {
                $startDate = $fechaParts[2] . '-' . $fechaParts[1] . '-' . $fechaParts[0];
            }
        }
        
        // Asegurar que la fecha esté en formato Y-m-d
        $timestamp = strtotime($startDate);
        if ($timestamp === false) {
            // Si falla, intentar con formato diferente
            $timestamp = strtotime(str_replace('/', '-', $startDate));
        }
        $startDate = date('Y-m-d', $timestamp);
        
        // Configurar zona horaria de Argentina
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        
        // Calcular fecha/hora de inicio y fin
        // Google Calendar acepta formato local (sin Z) o UTC (con Z)
        // Usaremos formato local para que se ajuste automáticamente a la zona horaria del usuario
        $startDateTime = date('Ymd\THis', strtotime($startDate . ' ' . $startTime));
        
        // Si no se especifica hora de fin, agregar 30 minutos por defecto
        if (!$endTime) {
            $endDateTime = date('Ymd\THis', strtotime($startDate . ' ' . $startTime . ' +30 minutes'));
        } else {
            $endDateTime = date('Ymd\THis', strtotime($startDate . ' ' . $endTime));
        }
        
        // URL encode los parámetros
        $title = urlencode($title);
        $description = urlencode($description);
        $location = urlencode($location);
        
        // Generar URL de Google Calendar
        // Removemos &sf=true&output=xml para que abra directamente el formulario de creación
        $url = "https://calendar.google.com/calendar/render?action=TEMPLATE";
        $url .= "&text={$title}";
        $url .= "&dates={$startDateTime}/{$endDateTime}";
        $url .= "&details={$description}";
        $url .= "&location={$location}";
        
        return $url;
    }
    
    /**
     * Genera un archivo .ics (iCalendar) con recordatorio para el día previo
     */
    public static function generateICSFile($title, $description, $location, $startDate, $startTime, $endTime = null, $reminderDaysBefore = 1, $reminderTime = '09:00')
    {
        // Normalizar formato de fecha
        if (strpos($startDate, '/') !== false) {
            $fechaParts = explode('/', $startDate);
            if (count($fechaParts) == 3) {
                $startDate = $fechaParts[2] . '-' . $fechaParts[1] . '-' . $fechaParts[0];
            }
        }
        
        $timestamp = strtotime($startDate);
        if ($timestamp === false) {
            $timestamp = strtotime(str_replace('/', '-', $startDate));
        }
        $startDate = date('Y-m-d', $timestamp);
        
        // Configurar zona horaria
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        
        // Calcular fecha/hora de inicio y fin
        $startDateTime = date('Ymd\THis', strtotime($startDate . ' ' . $startTime));
        
        if (!$endTime) {
            $endDateTime = date('Ymd\THis', strtotime($startDate . ' ' . $startTime . ' +30 minutes'));
        } else {
            $endDateTime = date('Ymd\THis', strtotime($startDate . ' ' . $endTime));
        }
        
        // Generar contenido del archivo .ics
        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//TurnosOnlineBB//NONSGML v1.0//ES\r\n";
        $ics .= "CALSCALE:GREGORIAN\r\n";
        $ics .= "METHOD:PUBLISH\r\n";
        $ics .= "BEGIN:VEVENT\r\n";
        $ics .= "DTSTART:{$startDateTime}\r\n";
        $ics .= "DTEND:{$endDateTime}\r\n";
        $ics .= "DTSTAMP:" . date('Ymd\THis\Z') . "\r\n";
        $ics .= "SUMMARY:" . str_replace(["\r", "\n"], ['', ' '], $title) . "\r\n";
        $ics .= "DESCRIPTION:" . str_replace(["\r", "\n"], ['', ' '], $description) . "\r\n";
        $ics .= "LOCATION:" . str_replace(["\r", "\n"], ['', ' '], $location) . "\r\n";
        $ics .= "UID:" . uniqid() . "@turnosonlinebb.com\r\n";
        $ics .= "SEQUENCE:0\r\n";
        $ics .= "STATUS:CONFIRMED\r\n";
        $ics .= "TRANSP:OPAQUE\r\n";
        
        // Agregar recordatorio (alarma) para el día previo
        // TRIGGER:-P1D significa 1 día antes (P1D = Period 1 Day)
        // ACTION:DISPLAY muestra una notificación
        $ics .= "BEGIN:VALARM\r\n";
        $ics .= "TRIGGER:-P{$reminderDaysBefore}D\r\n"; // Días antes (ej: -P1D = 1 día antes)
        $ics .= "ACTION:DISPLAY\r\n";
        $ics .= "DESCRIPTION:Recordatorio: " . str_replace(["\r", "\n"], ['', ' '], $title) . "\r\n";
        $ics .= "END:VALARM\r\n";
        
        $ics .= "END:VEVENT\r\n";
        $ics .= "END:VCALENDAR\r\n";
        
        return $ics;
    }
    
    /**
     * Genera archivo .ics para un turno con recordatorio del día previo
     */
    public static function generateTurnoICS($turno, $medico, $consultorio, $paciente = null)
    {
        // Normalizar formato de fecha
        $fechaTurno = is_string($turno->fechaTurno) ? $turno->fechaTurno : $turno->fechaTurno;
        
        if (strpos($fechaTurno, '/') !== false) {
            $fechaParts = explode('/', $fechaTurno);
            if (count($fechaParts) == 3) {
                $fechaTurno = $fechaParts[2] . '-' . $fechaParts[1] . '-' . $fechaParts[0];
            }
        }
        
        $title = "Turno con Dr. {$medico->apellido}";
        $description = "Turno médico con el Dr. {$medico->apellido}, {$medico->nombre}";
        $description .= "\nConsultorio: {$consultorio->direccion}";
        if (isset($consultorio->telefono) && !empty($consultorio->telefono)) {
            $description .= "\nTeléfono: {$consultorio->telefono}";
        }
        if ($paciente) {
            $description .= "\nPaciente: {$paciente->nombre} {$paciente->apellido}";
        }
        
        $location = $consultorio->direccion;
        
        // Generar .ics con recordatorio para 1 día antes a las 9:00 AM
        return self::generateICSFile(
            $title,
            $description,
            $location,
            $fechaTurno,
            $turno->horario,
            null, // endTime (se calcula automáticamente +30 min)
            1,    // reminderDaysBefore: 1 día antes
            '09:00' // reminderTime: 9:00 AM
        );
    }
    
    /**
     * Genera enlace de calendario para recordatorio (día previo)
     */
    public static function generateReminderCalendarLink($turno, $paciente, $medico, $consultorio)
    {
        // Normalizar formato de fecha
        $fechaTurno = is_string($turno->fechaTurno) ? $turno->fechaTurno : $turno->fechaTurno;
        
        // Convertir fecha a formato Y-m-d si viene en otro formato
        if (strpos($fechaTurno, '/') !== false) {
            $fechaParts = explode('/', $fechaTurno);
            if (count($fechaParts) == 3) {
                $fechaTurno = $fechaParts[2] . '-' . $fechaParts[1] . '-' . $fechaParts[0];
            }
        }
        
        // Fecha del recordatorio (día previo al turno)
        $fechaRecordatorio = date('Y-m-d', strtotime($fechaTurno . ' -1 day'));
        $horaRecordatorio = '09:00'; // Hora del recordatorio (9 AM)
        
        $title = "Recordatorio: Turno con Dr. {$medico->apellido}";
        $description = "Recordatorio: Tienes un turno mañana a las {$turno->horario} con el Dr. {$medico->apellido}, {$medico->nombre}";
        $description .= "\n\nFecha del turno: " . date('d/m/Y', strtotime($fechaTurno));
        $description .= "\nHorario: {$turno->horario}";
        $description .= "\nConsultorio: {$consultorio->direccion}";
        if (isset($consultorio->telefono) && !empty($consultorio->telefono)) {
            $description .= "\nTeléfono: {$consultorio->telefono}";
        }
        
        $location = $consultorio->direccion;
        
        return self::generateGoogleCalendarLink(
            $title,
            $description,
            $location,
            $fechaRecordatorio,
            $horaRecordatorio
        );
    }
    
    /**
     * Genera enlace de calendario para el turno mismo
     * Retorna un array con dos URLs: una para el recordatorio (día anterior) y otra para el turno
     */
    public static function generateTurnoCalendarLink($turno, $medico, $consultorio)
    {
        try {
            // Normalizar formato de fecha
            $fechaTurno = is_string($turno->fechaTurno) ? $turno->fechaTurno : (string)$turno->fechaTurno;
            
            // Convertir fecha a formato Y-m-d si viene en otro formato
            if (strpos($fechaTurno, '/') !== false) {
                $fechaParts = explode('/', $fechaTurno);
                if (count($fechaParts) == 3) {
                    $fechaTurno = $fechaParts[2] . '-' . $fechaParts[1] . '-' . $fechaParts[0];
                }
            }
            
            // Validar que la fecha sea válida
            $timestamp = strtotime($fechaTurno);
            if ($timestamp === false) {
                \Log::error('Fecha inválida en generateTurnoCalendarLink: ' . $fechaTurno);
                return null;
            }
            
            // Calcular fecha del día anterior (recordatorio)
            $fechaRecordatorio = date('Y-m-d', strtotime($fechaTurno . ' -1 day'));
            if ($fechaRecordatorio === false) {
                \Log::error('Error calculando fecha del recordatorio para: ' . $fechaTurno);
                return null;
            }
            
            $horaRecordatorio = '09:00'; // Recordatorio a las 9:00 AM
            
            // Validar que el horario exista
            if (empty($turno->horario)) {
                \Log::error('Horario vacío en turno ID: ' . (isset($turno->id) ? $turno->id : 'N/A'));
                return null;
            }
            
            // URL para el recordatorio (día anterior)
            $titleRecordatorio = "Recordatorio: Turno con Dr. {$medico->apellido}";
            $descriptionRecordatorio = "Recordatorio: Tienes un turno mañana a las {$turno->horario} con el Dr. {$medico->apellido}, {$medico->nombre}";
            $descriptionRecordatorio .= "\n\nFecha del turno: " . date('d/m/Y', $timestamp);
            $descriptionRecordatorio .= "\nHorario: {$turno->horario}";
            $descriptionRecordatorio .= "\nConsultorio: {$consultorio->direccion}";
            if (isset($consultorio->telefono) && !empty($consultorio->telefono)) {
                $descriptionRecordatorio .= "\nTeléfono: {$consultorio->telefono}";
            }
            
            $urlRecordatorio = self::generateGoogleCalendarLink(
                $titleRecordatorio,
                $descriptionRecordatorio,
                $consultorio->direccion,
                $fechaRecordatorio,
                $horaRecordatorio
            );
            
            // Validar que la URL se haya generado correctamente
            if (empty($urlRecordatorio) || strpos($urlRecordatorio, 'http') !== 0) {
                \Log::error('URL de recordatorio inválida generada: ' . $urlRecordatorio);
                return null;
            }
            
            // URL para el turno (aunque no se use, la generamos por si acaso)
            $title = "Turno con Dr. {$medico->apellido}";
            $description = "Turno médico con el Dr. {$medico->apellido}, {$medico->nombre}";
            $description .= "\nConsultorio: {$consultorio->direccion}";
            if (isset($consultorio->telefono) && !empty($consultorio->telefono)) {
                $description .= "\nTeléfono: {$consultorio->telefono}";
            }
            
            $urlTurno = self::generateGoogleCalendarLink(
                $title,
                $description,
                $consultorio->direccion,
                $fechaTurno,
                $turno->horario
            );
            
            // Retornar ambas URLs: primero el recordatorio, luego el turno
            return [
                'reminder' => $urlRecordatorio,
                'turno' => $urlTurno
            ];
        } catch (\Exception $e) {
            \Log::error('Error en generateTurnoCalendarLink: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return null;
        }
    }
}

