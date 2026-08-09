<?php

namespace App\Services\MercadoPago;

use App\MercadoPagoPlatformSetting;
use App\MedicoMercadoPagoAccount;
use App\TurnoPagoIntent;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;

class MercadoPagoMarketplaceService
{
    protected $settings;

    public function __construct(MercadoPagoPlatformSetting $settings = null)
    {
        $this->settings = $settings ?: MercadoPagoPlatformSetting::current();
    }

    public function calculatePlatformFee($grossAmount)
    {
        $gross = (float) $grossAmount;
        $mpPercent = (float) ($this->settings->mp_commission_fallback_percent ?? 0.8);
        $platformPercent = (float) ($this->settings->platform_commission_percent ?? 0);

        $mpFee = round($gross * ($mpPercent / 100), 2);
        $subtotal = max(0, $gross - $mpFee);
        $platformFee = round($subtotal * ($platformPercent / 100), 2);

        return [
            'gross' => $gross,
            'mp_fee_estimate' => $mpFee,
            'subtotal' => $subtotal,
            'platform_fee' => $platformFee,
        ];
    }

    public function createCheckoutPreference(TurnoPagoIntent $intent, MedicoMercadoPagoAccount $account)
    {
        if (!$this->settings) {
            throw new \RuntimeException('Falta la configuración global de Mercado Pago.');
        }

        if (!$account->isLinked()) {
            throw new \RuntimeException('El médico no tiene Mercado Pago vinculado.');
        }

        $account = $this->ensureSellerAccessToken($account);

        $feeData = $this->calculatePlatformFee($intent->amount);
        $intent->platform_fee = $feeData['platform_fee'];
        $intent->save();

        $baseUrl = rtrim(config('app.url'), '/');
        $description = $this->settings->checkout_description ?: 'Reserva de turno online';

        $payload = [
            'items' => [[
                'title' => $description,
                'quantity' => 1,
                'currency_id' => 'ARS',
                'unit_price' => (float) $intent->amount,
            ]],
            'payer' => [
                'email' => optional(\App\Paciente::find($intent->paciente_id))->mail,
            ],
            'external_reference' => (string) $intent->id,
            'notification_url' => $baseUrl . '/api/webhooks/mercadopago',
            'back_urls' => [
                'success' => $baseUrl . '/turno/pago/exito?intent=' . $intent->id,
                'failure' => $baseUrl . '/turno/pago/error?intent=' . $intent->id,
                'pending' => $baseUrl . '/turno/pago/pendiente?intent=' . $intent->id,
            ],
            'auto_return' => 'approved',
            'marketplace_fee' => (float) $feeData['platform_fee'],
        ];

        $client = new Client(['base_uri' => 'https://api.mercadopago.com']);

        try {
            $response = $client->post('/checkout/preferences', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $account->access_token,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);
        } catch (ClientException $e) {
            $this->logMercadoPagoError('createCheckoutPreference', $e, [
                'medico_id' => $account->medico_id,
                'intent_id' => $intent->id,
            ]);
            throw new \RuntimeException($this->friendlyMercadoPagoMessage($e));
        }

        $data = json_decode($response->getBody()->getContents(), true);

        $intent->preference_id = $data['id'] ?? null;
        $intent->save();

        $initPoint = $data['init_point'] ?? null;
        if ($this->settings && !$this->settings->isProduction()) {
            $initPoint = $data['sandbox_init_point'] ?? $initPoint;
        }

        return [
            'preference_id' => $data['id'] ?? null,
            'init_point' => $initPoint,
            'sandbox_init_point' => $data['sandbox_init_point'] ?? null,
        ];
    }

    /**
     * Split Payments OAuth: la preferencia se crea con el token del vendedor (médico).
     */
    protected function ensureSellerAccessToken(MedicoMercadoPagoAccount $account): MedicoMercadoPagoAccount
    {
        if (empty($account->access_token)) {
            throw new \RuntimeException(
                'El médico no tiene token de Mercado Pago. Debe reconectar su cuenta desde Pagos / Mercado Pago.'
            );
        }

        if ($this->settings && $account->mode && $account->mode !== $this->settings->mode) {
            throw new \RuntimeException(
                'La cuenta de Mercado Pago del médico no coincide con el modo de la plataforma (' .
                $this->settings->mode . '). Desconecte y vuelva a vincular la cuenta.'
            );
        }

        $needsRefresh = $account->expires_at && $account->expires_at->lte(now()->addHour());
        if ($needsRefresh) {
            if (empty($account->refresh_token)) {
                throw new \RuntimeException(
                    'El token de Mercado Pago expiró. Desconecte y vuelva a vincular la cuenta desde Pagos / Mercado Pago.'
                );
            }

            try {
                (new MercadoPagoOAuthService($this->settings))->refreshToken($account);
                $account->refresh();
            } catch (\Exception $e) {
                Log::warning('MP OAuth refresh on-demand failed for medico ' . $account->medico_id . ': ' . $e->getMessage());
                throw new \RuntimeException(
                    'No se pudo renovar la sesión de Mercado Pago. Desconecte y vuelva a vincular la cuenta.'
                );
            }
        }

        return $account;
    }

    protected function logMercadoPagoError($operation, ClientException $e, array $context = [])
    {
        $body = $e->getResponse() ? (string) $e->getResponse()->getBody() : '';
        $decoded = json_decode($body, true);

        Log::error('Mercado Pago API error: ' . $operation, array_merge($context, [
            'status' => $e->getResponse() ? $e->getResponse()->getStatusCode() : null,
            'response' => $decoded ?: $body,
        ]));
    }

    protected function friendlyMercadoPagoMessage(ClientException $e): string
    {
        $body = $e->getResponse() ? (string) $e->getResponse()->getBody() : '';
        $decoded = json_decode($body, true);
        $errorCode = is_array($decoded) ? ($decoded['error'] ?? null) : null;

        if ($errorCode === 'invalid_collector_id') {
            return 'Mercado Pago rechazó la vinculación de la cuenta del médico. Desconecte y vuelva a conectar Mercado Pago desde Pagos / Mercado Pago (asegúrese de usar el mismo entorno: producción o sandbox).';
        }

        if (is_array($decoded) && !empty($decoded['message'])) {
            return 'No se pudo iniciar el pago con Mercado Pago: ' . $decoded['message'];
        }

        return 'No se pudo iniciar el pago con Mercado Pago. Intente nuevamente o contacte al consultorio.';
    }

    public function fetchPayment($paymentId)
    {
        if (!$this->settings || empty($this->settings->integrator_access_token)) {
            throw new \RuntimeException('Falta el access token del integrador.');
        }

        $client = new Client(['base_uri' => 'https://api.mercadopago.com']);
        $response = $client->get('/v1/payments/' . $paymentId, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->settings->integrator_access_token,
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function refundPayment($paymentId, MedicoMercadoPagoAccount $account, $amount = null)
    {
        if (!$account->isLinked()) {
            throw new \RuntimeException('El médico no tiene Mercado Pago vinculado.');
        }

        $account = $this->ensureSellerAccessToken($account);

        $payload = [];
        if ($amount !== null) {
            $payload['amount'] = (float) $amount;
        }

        $client = new Client(['base_uri' => 'https://api.mercadopago.com']);

        try {
            $response = $client->post('/v1/payments/' . $paymentId . '/refunds', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $account->access_token,
                    'Content-Type' => 'application/json',
                    'X-Idempotency-Key' => $this->generateIdempotencyKey(),
                ],
                'json' => $payload,
            ]);
        } catch (ClientException $e) {
            $this->logMercadoPagoError('refundPayment', $e, [
                'medico_id' => $account->medico_id,
                'payment_id' => $paymentId,
            ]);
            throw new \RuntimeException($this->friendlyRefundMercadoPagoMessage($e));
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    protected function generateIdempotencyKey()
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    protected function friendlyRefundMercadoPagoMessage(ClientException $e)
    {
        $body = $e->getResponse() ? (string) $e->getResponse()->getBody() : '';
        $decoded = json_decode($body, true);
        $causes = is_array($decoded) ? ($decoded['cause'] ?? []) : [];
        $codes = [];

        if (is_array($causes)) {
            foreach ($causes as $cause) {
                if (is_array($cause) && !empty($cause['code'])) {
                    $codes[] = (string) $cause['code'];
                }
            }
        }

        if (in_array('2024', $codes, true) || in_array('15016', $codes, true)) {
            return 'Mercado Pago no permite reembolsar este pago porque superó el plazo habilitado.';
        }

        if (in_array('4296', $codes, true)) {
            return 'Este pago ya fue reembolsado en Mercado Pago.';
        }

        if (is_array($decoded) && !empty($decoded['message'])) {
            return 'No se pudo reembolsar el pago: ' . $decoded['message'];
        }

        return 'No se pudo reembolsar el pago con Mercado Pago. Intente nuevamente o contacte al consultorio.';
    }
}
