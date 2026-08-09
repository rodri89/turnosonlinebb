<?php

namespace App\Services;

use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventDateTime;
use Carbon\Carbon;

class GoogleCalendarService
{
    private $client;
    private $clientId;
    private $clientSecret;
    private $redirectUri;

    public function __construct()
    {
        $this->clientId = env('GOOGLE_CALENDAR_CLIENT_ID', '');
        $this->clientSecret = env('GOOGLE_CALENDAR_CLIENT_SECRET', '');
        $this->redirectUri = env('GOOGLE_CALENDAR_REDIRECT_URI', url('/google-calendar/callback'));
        
        $this->client = new Google_Client();
        $this->client->setClientId($this->clientId);
        $this->client->setClientSecret($this->clientSecret);
        $this->client->setRedirectUri($this->redirectUri);
        $this->client->setScopes([
            Google_Service_Calendar::CALENDAR,
            Google_Service_Calendar::CALENDAR_EVENTS
        ]);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
    }

    /**
     * Obtener URL de autorización
     */
    public function getAuthUrl($state = null)
    {
        // Validar que las credenciales estén configuradas
        if (empty($this->clientId) || empty($this->clientSecret)) {
            \Log::error('Google Calendar OAuth: Faltan credenciales. CLIENT_ID: ' . (!empty($this->clientId) ? 'configurado' : 'FALTANTE') . ', CLIENT_SECRET: ' . (!empty($this->clientSecret) ? 'configurado' : 'FALTANTE'));
            throw new \Exception('Google Calendar no está configurado correctamente. Por favor, contacta al administrador.');
        }
        
        // Log de las credenciales que se están usando
        \Log::info('Google Calendar OAuth - Generando URL de autorización', [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'client_id_starts_with' => substr($this->clientId, 0, 20)
        ]);
        
        if ($state) {
            $this->client->setState($state);
        }
        
        $authUrl = $this->client->createAuthUrl();
        
        // Log de la URL generada para verificar
        \Log::info('Google Calendar OAuth - URL de autorización generada', [
            'url_length' => strlen($authUrl),
            'url_preview' => substr($authUrl, 0, 300),
            'contains_client_id' => strpos($authUrl, $this->clientId) !== false
        ]);
        
        return $authUrl;
    }

    /**
     * Intercambiar código de autorización por tokens
     */
    public function getAccessTokenFromCode($code)
    {
        try {
            $token = $this->client->fetchAccessTokenWithAuthCode($code);
            return $token;
        } catch (\Exception $e) {
            \Log::error('Error obteniendo token de Google Calendar: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Configurar tokens de acceso
     */
    public function setAccessToken($accessToken, $refreshToken = null, $expiresAt = null)
    {
        $tokenData = [
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => $expiresAt ? (strtotime($expiresAt) - time()) : 3600,
            'created' => time()
        ];

        if ($refreshToken) {
            $tokenData['refresh_token'] = $refreshToken;
        }

        $this->client->setAccessToken($tokenData);
    }

    /**
     * Refrescar token de acceso
     */
    public function refreshAccessToken($refreshToken)
    {
        try {
            $this->client->refreshToken($refreshToken);
            $token = $this->client->getAccessToken();
            return $token;
        } catch (\Exception $e) {
            \Log::error('Error refrescando token de Google Calendar: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Crear evento en el calendario
     */
    public function createEvent($title, $description, $location, $startDate, $startTime, $endTime = null, $reminderDate = null, $reminderTime = '09:00')
    {
        try {
            $service = new Google_Service_Calendar($this->client);

            // Crear evento principal del turno
            $event = new Google_Service_Calendar_Event();
            $event->setSummary($title);
            $event->setDescription($description);
            $event->setLocation($location);

            // Configurar fecha/hora de inicio
            $startDateTime = new Google_Service_Calendar_EventDateTime();
            $startDateTimeStr = date('Y-m-d\TH:i:s', strtotime($startDate . ' ' . $startTime));
            $startDateTime->setDateTime($startDateTimeStr);
            $startDateTime->setTimeZone('America/Argentina/Buenos_Aires');
            $event->setStart($startDateTime);

            // Configurar fecha/hora de fin
            $endDateTime = new Google_Service_Calendar_EventDateTime();
            if (!$endTime) {
                $endTime = date('H:i:s', strtotime($startDate . ' ' . $startTime . ' +30 minutes'));
            }
            $endDateTimeStr = date('Y-m-d\TH:i:s', strtotime($startDate . ' ' . $endTime));
            $endDateTime->setDateTime($endDateTimeStr);
            $endDateTime->setTimeZone('America/Argentina/Buenos_Aires');
            $event->setEnd($endDateTime);

            // Agregar recordatorios
            $reminders = new \Google_Service_Calendar_EventReminders();
            $reminders->setUseDefault(false);
            $reminderItems = [
                new \Google_Service_Calendar_EventReminder([
                    'method' => 'email',
                    'minutes' => 1440 // 24 horas antes
                ]),
                new \Google_Service_Calendar_EventReminder([
                    'method' => 'popup',
                    'minutes' => 60 // 1 hora antes
                ])
            ];
            $reminders->setOverrides($reminderItems);
            $event->setReminders($reminders);

            // Insertar evento
            $createdEvent = $service->events->insert('primary', $event);
            
            // Si hay recordatorio del día previo, crear evento separado
            if ($reminderDate) {
                $this->createReminderEvent($reminderDate, $reminderTime, $title, $description, $location, $startDate, $startTime);
            }

            return [
                'success' => true,
                'event_id' => $createdEvent->getId(),
                'html_link' => $createdEvent->getHtmlLink()
            ];
        } catch (\Exception $e) {
            \Log::error('Error creando evento en Google Calendar: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Crear evento de recordatorio (día previo)
     * Método público para crear solo el recordatorio sin el evento del turno
     */
    public function createReminderEvent($reminderDate, $reminderTime, $originalTitle, $originalDescription, $location, $turnoDate, $turnoTime)
    {
        try {
            $service = new Google_Service_Calendar($this->client);

            $event = new Google_Service_Calendar_Event();
            $event->setSummary("Recordatorio: " . $originalTitle);
            
            // Mejorar la descripción del recordatorio
            $description = "Recordatorio: Tienes un turno mañana a las {$turnoTime} con el Dr. " . str_replace("Turno con Dr. ", "", $originalTitle);
            $description .= "\n\n📅 Fecha del turno: " . date('d/m/Y', strtotime($turnoDate));
            $description .= "\n🕐 Horario: {$turnoTime}";
            $description .= "\n📍 Consultorio: {$location}";
            if (!empty($originalDescription)) {
                $description .= "\n\n" . $originalDescription;
            }
            
            $event->setDescription($description);
            $event->setLocation($location);

            $startDateTime = new Google_Service_Calendar_EventDateTime();
            $startDateTimeStr = date('Y-m-d\TH:i:s', strtotime($reminderDate . ' ' . $reminderTime));
            $startDateTime->setDateTime($startDateTimeStr);
            $startDateTime->setTimeZone('America/Argentina/Buenos_Aires');
            $event->setStart($startDateTime);

            $endDateTime = new Google_Service_Calendar_EventDateTime();
            $endDateTimeStr = date('Y-m-d\TH:i:s', strtotime($reminderDate . ' ' . $reminderTime . ' +15 minutes'));
            $endDateTime->setDateTime($endDateTimeStr);
            $endDateTime->setTimeZone('America/Argentina/Buenos_Aires');
            $event->setEnd($endDateTime);

            $createdEvent = $service->events->insert('primary', $event);
            return $createdEvent->getId();
        } catch (\Exception $e) {
            \Log::error('Error creando evento de recordatorio: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verificar si el token es válido
     */
    public function isTokenValid($accessToken, $expiresAt)
    {
        if (!$accessToken || !$expiresAt) {
            return false;
        }

        // Verificar si el token expiró (con margen de 5 minutos)
        return strtotime($expiresAt) > (time() + 300);
    }

    /**
     * Eliminar evento de Google Calendar
     */
    public function deleteEvent($eventId, $accessToken = null, $refreshToken = null, $expiresAt = null)
    {
        try {
            // Si se proporcionan tokens, configurarlos
            if ($accessToken) {
                $this->setAccessToken($accessToken, $refreshToken, $expiresAt);
            }
            
            $service = new Google_Service_Calendar($this->client);
            
            // Eliminar el evento
            $service->events->delete('primary', $eventId);
            
            \Log::info("Evento de Google Calendar eliminado exitosamente: {$eventId}");
            return true;
        } catch (\Exception $e) {
            \Log::error('Error eliminando evento de Google Calendar: ' . $e->getMessage());
            \Log::error('Event ID: ' . $eventId);
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }
}

