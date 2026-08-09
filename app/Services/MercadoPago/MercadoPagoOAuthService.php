<?php

namespace App\Services\MercadoPago;

use App\MercadoPagoPlatformSetting;
use App\MedicoMercadoPagoAccount;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MercadoPagoOAuthService
{
    protected $settings;

    public function __construct(MercadoPagoPlatformSetting $settings = null)
    {
        $this->settings = $settings ?: MercadoPagoPlatformSetting::current();
    }

    public function redirectUri()
    {
        return env('MERCADOPAGO_OAUTH_REDIRECT_URI')
            ?: rtrim(config('app.url'), '/') . '/medico/oauth/mercadopago/callback';
    }

    public function buildAuthorizeUrl($medicoId)
    {
        if (!$this->settings || empty($this->settings->mp_app_id)) {
            throw new \RuntimeException('La plataforma no tiene configurado el APP ID de Mercado Pago.');
        }

        $state = base64_encode(json_encode([
            'medico_id' => (int) $medicoId,
            'nonce' => Str::random(32),
        ]));

        session(['mp_oauth_state' => $state]);

        $query = http_build_query([
            'client_id' => $this->settings->mp_app_id,
            'response_type' => 'code',
            'platform_id' => 'mp',
            'state' => $state,
            'redirect_uri' => $this->redirectUri(),
        ]);

        return 'https://auth.mercadopago.com.ar/authorization?' . $query;
    }

    public function exchangeCode($code, $medicoId)
    {
        $client = new Client(['base_uri' => 'https://api.mercadopago.com']);
        $response = $client->post('/oauth/token', [
            'form_params' => [
                'client_id' => $this->settings->mp_app_id,
                'client_secret' => $this->settings->mp_client_secret,
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->redirectUri(),
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        return $this->persistTokens($medicoId, $data);
    }

    public function refreshToken(MedicoMercadoPagoAccount $account)
    {
        if (empty($account->refresh_token)) {
            throw new \RuntimeException('No hay refresh token para el médico ' . $account->medico_id);
        }

        $client = new Client(['base_uri' => 'https://api.mercadopago.com']);
        $response = $client->post('/oauth/token', [
            'form_params' => [
                'client_id' => $this->settings->mp_app_id,
                'client_secret' => $this->settings->mp_client_secret,
                'grant_type' => 'refresh_token',
                'refresh_token' => $account->refresh_token,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        return $this->persistTokens($account->medico_id, $data);
    }

    public function disconnect($medicoId)
    {
        MedicoMercadoPagoAccount::where('medico_id', $medicoId)->update([
            'access_token' => null,
            'refresh_token' => null,
            'expires_at' => null,
            'collector_id' => null,
            'mp_user_id' => null,
            'linked_at' => null,
        ]);
    }

    protected function persistTokens($medicoId, array $data)
    {
        $expiresAt = now()->addSeconds((int) ($data['expires_in'] ?? 15552000));

        return MedicoMercadoPagoAccount::updateOrCreate(
            ['medico_id' => $medicoId],
            [
                'access_token' => $data['access_token'] ?? null,
                'refresh_token' => $data['refresh_token'] ?? null,
                'expires_at' => $expiresAt,
                'collector_id' => isset($data['user_id']) ? (string) $data['user_id'] : null,
                'mp_user_id' => isset($data['user_id']) ? (string) $data['user_id'] : null,
                'linked_at' => now(),
                'mode' => $this->settings ? $this->settings->mode : 'sandbox',
            ]
        );
    }

    public function validateState($state)
    {
        $expected = session('mp_oauth_state');
        session()->forget('mp_oauth_state');

        if (!$expected || !hash_equals($expected, $state)) {
            throw new \RuntimeException('Estado OAuth inválido.');
        }

        $payload = json_decode(base64_decode($state), true);
        if (!is_array($payload) || empty($payload['medico_id'])) {
            throw new \RuntimeException('Estado OAuth corrupto.');
        }

        return (int) $payload['medico_id'];
    }
}
