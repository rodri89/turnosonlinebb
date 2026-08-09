<?php

namespace App\Services\MercadoPago;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MercadoPagoWebhookService
{
    protected $marketplace;
    protected $intentService;

    public function __construct(
        MercadoPagoMarketplaceService $marketplace = null,
        TurnoPagoIntentService $intentService = null
    ) {
        $this->marketplace = $marketplace ?: new MercadoPagoMarketplaceService();
        $this->intentService = $intentService ?: new TurnoPagoIntentService($this->marketplace);
    }

    public function handle(Request $request)
    {
        $topic = $request->input('topic') ?: $request->input('type');
        $paymentId = $request->input('data.id') ?: $request->input('id');

        if ($topic === 'payment' && $paymentId) {
            return $this->processPaymentNotification($paymentId);
        }

        if ($request->has('resource') && strpos($request->input('resource'), '/payments/') !== false) {
            $parts = explode('/', trim($request->input('resource'), '/'));
            $paymentId = end($parts);
            return $this->processPaymentNotification($paymentId);
        }

        Log::info('MP webhook ignorado', $request->all());
        return ['ok' => true, 'ignored' => true];
    }

    public function processPaymentNotification($paymentId)
    {
        $payment = $this->marketplace->fetchPayment($paymentId);
        $externalReference = $payment['external_reference'] ?? null;
        $status = $payment['status'] ?? null;

        $intent = $this->intentService->findByPreferenceOrReference($externalReference, $paymentId);
        if (!$intent) {
            Log::warning('MP webhook: intent no encontrado', ['payment_id' => $paymentId, 'external_reference' => $externalReference]);
            return ['ok' => false, 'reason' => 'intent_not_found'];
        }

        if (!$intent->payment_id) {
            $intent->payment_id = (string) $paymentId;
            $intent->save();
        }

        if ($status === 'approved') {
            $this->intentService->approveIntent($intent, (string) $paymentId, 'approved');
            return ['ok' => true, 'status' => 'approved'];
        }

        if (in_array($status, ['rejected', 'cancelled'], true)) {
            $this->intentService->cancelIntent($intent, $status);
            return ['ok' => true, 'status' => $status];
        }

        $intent->status = $status ?: 'pending';
        $intent->save();

        return ['ok' => true, 'status' => $status];
    }
}
