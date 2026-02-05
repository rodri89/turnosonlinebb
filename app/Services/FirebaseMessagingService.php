<?php
namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Google\Auth\ApplicationDefaultCredentials;
use Google\Auth\CredentialsLoader;
use Google\Auth\OAuth2;

class FirebaseMessagingService
{
    private $serverKey;
    private $serviceAccountPath;
    private $projectId;
    private $client;

    public function __construct()
    {
        // Intentar usar Server Key legacy primero (más simple)
        $this->serverKey = env('FIREBASE_SERVER_KEY', '');
        
        // Si no hay Server Key, usar cuenta de servicio (API v1)
        $serviceAccountPath = env('FIREBASE_SERVICE_ACCOUNT_PATH', 'storage/app/firebase-service-account.json');
        
        // Resolver la ruta: si es relativa, convertir a absoluta usando base_path de Laravel
        if (substr($serviceAccountPath, 0, 1) !== '/') {
            // Es ruta relativa, convertir a absoluta
            $this->serviceAccountPath = base_path($serviceAccountPath);
        } else {
            // Ya es ruta absoluta
            $this->serviceAccountPath = $serviceAccountPath;
        }
        
        // Verificar que el archivo existe, si no, intentar con storage_path
        if (!file_exists($this->serviceAccountPath)) {
            $this->serviceAccountPath = storage_path('app/firebase-service-account.json');
        }
        
        $this->projectId = env('FIREBASE_PROJECT_ID', 'turnosonlinebb-8406b');
        $this->client = new Client();
    }

    /**
     * Enviar notificación a un token FCM específico
     * 
     * @param string $fcmToken El token FCM del dispositivo
     * @param string $title Título de la notificación
     * @param string $body Cuerpo de la notificación
     * @param array $data Datos adicionales (opcional)
     * @return array
     */
    public function sendToToken($fcmToken, $title, $body, $data = [])
    {
        return $this->sendNotification([
            'to' => $fcmToken,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'icon' => '/images/iconos/turnosonlinebb_icon.png',
                'sound' => 'default'
            ],
            'data' => $data
        ]);
    }

    /**
     * Enviar notificación a múltiples tokens
     * 
     * @param array $fcmTokens Array de tokens FCM
     * @param string $title Título de la notificación
     * @param string $body Cuerpo de la notificación
     * @param array $data Datos adicionales (opcional)
     * @return array
     */
    public function sendToMultipleTokens($fcmTokens, $title, $body, $data = [])
    {
        return $this->sendNotification([
            'registration_ids' => $fcmTokens,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'icon' => '/images/iconos/turnosonlinebb_icon.png',
                'sound' => 'default'
            ],
            'data' => $data
        ]);
    }

    /**
     * Enviar notificación de recordatorio de turno
     * 
     * @param string $fcmToken Token FCM del paciente
     * @param array $turnoData Datos del turno (fecha, horario, medico, etc.)
     * @return array
     */
    public function sendTurnoReminder($fcmToken, $turnoData)
    {
        $title = 'Recordatorio de Turno';
        $body = "Tienes un turno el {$turnoData['fecha']} a las {$turnoData['horario']} con {$turnoData['medico']}";
        
        $data = [
            'type' => 'turno_reminder',
            'turno_id' => $turnoData['turno_id'] ?? null,
            'fecha' => $turnoData['fecha'] ?? null,
            'horario' => $turnoData['horario'] ?? null,
            'medico' => $turnoData['medico'] ?? null,
            'url' => '/mis_turnos'
        ];

        return $this->sendToToken($fcmToken, $title, $body, $data);
    }

    /**
     * Obtener token de acceso OAuth2 usando la cuenta de servicio
     * 
     * @return string
     */
    private function getAccessToken()
    {
        if (!file_exists($this->serviceAccountPath)) {
            throw new \Exception('Archivo de cuenta de servicio no encontrado: ' . $this->serviceAccountPath);
        }

        $jsonKey = json_decode(file_get_contents($this->serviceAccountPath), true);
        
        if (!isset($jsonKey['client_email']) || !isset($jsonKey['private_key'])) {
            throw new \Exception('Archivo de cuenta de servicio inválido');
        }

        $oauth = new OAuth2([
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'tokenCredentialUri' => 'https://oauth2.googleapis.com/token',
            'signingKey' => $jsonKey['private_key'],
            'signingAlgorithm' => 'RS256',
        ]);

        $oauth->setIssuer($jsonKey['client_email']);
        $oauth->setAudience('https://oauth2.googleapis.com/token');
        $oauth->setSub($jsonKey['client_email']);

        $token = $oauth->fetchAuthToken();

        return $token['access_token'];
    }

    /**
     * Convertir payload legacy a formato API v1
     * 
     * @param array $payload Payload en formato legacy
     * @return array Payload en formato API v1
     */
    private function convertToV1Format($payload)
    {
        $v1Payload = [
            'message' => []
        ];

        // Token de destino
        if (isset($payload['to'])) {
            $v1Payload['message']['token'] = $payload['to'];
        } elseif (isset($payload['registration_ids']) && !empty($payload['registration_ids'])) {
            // Para múltiples tokens, usar el primero (API v1 requiere un token por request)
            $v1Payload['message']['token'] = $payload['registration_ids'][0];
        }

        // Notificación
        if (isset($payload['notification'])) {
            $v1Payload['message']['notification'] = [
                'title' => $payload['notification']['title'] ?? '',
                'body' => $payload['notification']['body'] ?? ''
            ];
        }

        // Datos
        if (isset($payload['data'])) {
            $v1Payload['message']['data'] = [];
            foreach ($payload['data'] as $key => $value) {
                $v1Payload['message']['data'][$key] = (string)$value;
            }
        }

        // Web push (para icono, sonido, etc.)
        if (isset($payload['notification']['icon']) || isset($payload['notification']['sound'])) {
            $v1Payload['message']['webpush'] = [
                'notification' => []
            ];
            if (isset($payload['notification']['icon'])) {
                $v1Payload['message']['webpush']['notification']['icon'] = $payload['notification']['icon'];
            }
            if (isset($payload['notification']['sound'])) {
                $v1Payload['message']['webpush']['notification']['sound'] = $payload['notification']['sound'];
            }
        }

        return $v1Payload;
    }

    /**
     * Enviar la notificación usando la API de FCM
     * 
     * @param array $payload Payload de la notificación
     * @return array
     */
    protected function sendNotification($payload)
    {
        try {
            // Intentar primero con API legacy si hay Server Key
            if (!empty($this->serverKey) && $this->serverKey !== 'TU_SERVER_KEY_AQUI') {
                try {
                    $response = $this->client->post('https://fcm.googleapis.com/fcm/send', [
                        'headers' => [
                            'Authorization' => 'key=' . $this->serverKey,
                            'Content-Type' => 'application/json'
                        ],
                        'body' => json_encode($payload),
                        'timeout' => 10
                    ]);

                    $result = json_decode($response->getBody()->getContents(), true);
                    
                    if (isset($result['success']) && $result['success'] > 0) {
                        return array_merge($result, ['success' => true]);
                    } elseif (isset($result['failure']) && $result['failure'] > 0) {
                        $errorMessage = 'Error al enviar notificación';
                        if (isset($result['results'][0]['error'])) {
                            $errorMessage = $result['results'][0]['error'];
                        }
                        return [
                            'success' => false,
                            'error' => true,
                            'message' => $errorMessage,
                            'fcm_response' => $result
                        ];
                    }
                    
                    return array_merge($result, ['success' => true]);
                } catch (GuzzleException $e) {
                    // Si falla, continuar con API v1
                }
            }

            // Usar API v1 con cuenta de servicio
            if (!file_exists($this->serviceAccountPath)) {
                // Intentar rutas alternativas
                $alternativePaths = [
                    storage_path('app/firebase-service-account.json'),
                    base_path('storage/app/firebase-service-account.json'),
                    base_path() . '/storage/app/firebase-service-account.json',
                    __DIR__ . '/../../storage/app/firebase-service-account.json'
                ];
                
                $foundPath = null;
                foreach ($alternativePaths as $altPath) {
                    if (file_exists($altPath)) {
                        $foundPath = $altPath;
                        $this->serviceAccountPath = $altPath;
                        break;
                    }
                }
                
                if (!$foundPath) {
                    return [
                        'success' => false,
                        'error' => true,
                        'message' => 'No se encontró el archivo de cuenta de servicio. Verifica que FIREBASE_SERVICE_ACCOUNT_PATH esté configurado correctamente en .env',
                        'path_checked' => $this->serviceAccountPath,
                        'alternative_paths_tried' => $alternativePaths,
                        'base_path' => base_path(),
                        'storage_path' => storage_path('app')
                    ];
                }
            }

            // Obtener token de acceso
            $accessToken = $this->getAccessToken();

            // Convertir payload a formato v1
            $v1Payload = $this->convertToV1Format($payload);

            // Enviar usando API v1
            $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
            
            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode($v1Payload),
                'timeout' => 10
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            
            return [
                'success' => true,
                'message_id' => $result['name'] ?? null,
                'fcm_response' => $result
            ];
            
        } catch (GuzzleException $e) {
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
            $responseBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : '';
            
            return [
                'success' => false,
                'error' => true,
                'message' => $e->getMessage(),
                'status_code' => $statusCode,
                'response' => $responseBody
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => true,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
    }
}

