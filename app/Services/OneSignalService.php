<?php
namespace App\Services;

//use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class OneSignalService
{
    public function sendToUser($playerId, $message, $url = null, $data = null)
    {
        return $this->sendNotification([
            'include_player_ids' => is_array($playerId) ? $playerId : [$playerId],
            'contents' => ['en' => $message],
            'url' => $url,
            'data' => $data
        ]);
    }

    public function sendToAll($message, $url = null)
    {
        return $this->sendNotification([
            'included_segments' => ['All'],
            'contents' => ['en' => $message],
            'url' => $url
        ]);
    }

    protected function sendNotification($params)
    {
        $default = [
            'app_id' => '87602532-8d15-4f44-9888-faec4e96673a',
            'headings' => ['en' => config('app.name')]
        ];

        $client = new Client();
        
        try {
            $response = $client->post('https://onesignal.com/api/v1/notifications', [
                'headers' => [
                    'Authorization' => 'Basic os_v2_app_q5qckmuncvhujgei7lwe5fthhlke2lrnnaguatnexrpbkrjthscju32rabhsb2ligkjthwynu6mafcgtpe7bm6jlnyvgtm7n6t6gina',
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'body' => json_encode(array_merge($default, $params))
            ]);

            // Decodificar la respuesta manualmente
            return json_decode($response->getBody()->getContents(), true);
            
        } catch (GuzzleException $e) {
            // Manejo de errores
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }
}

?>