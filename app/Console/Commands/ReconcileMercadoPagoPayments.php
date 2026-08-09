<?php

namespace App\Console\Commands;

use App\TurnoPagoIntent;
use App\Services\MercadoPago\MercadoPagoMarketplaceService;
use App\Services\MercadoPago\MercadoPagoWebhookService;
use App\Services\MercadoPago\TurnoPagoIntentService;
use Illuminate\Console\Command;

class ReconcileMercadoPagoPayments extends Command
{
    protected $signature = 'payments:reconcile-mercadopago';

    protected $description = 'Reconcilia pagos MP pendientes de reserva de turnos';

    public function handle()
    {
        $intentService = new TurnoPagoIntentService();
        $intentService->expireStaleIntents();

        $marketplace = new MercadoPagoMarketplaceService();
        $webhook = new MercadoPagoWebhookService($marketplace, $intentService);

        $pending = TurnoPagoIntent::where('status', 'pending')
            ->whereNotNull('payment_id')
            ->where('created_at', '<', now()->subMinutes(2))
            ->get();

        foreach ($pending as $intent) {
            try {
                $webhook->processPaymentNotification($intent->payment_id);
                $this->info('Reconciled intent #' . $intent->id);
            } catch (\Exception $e) {
                $this->warn('Intent #' . $intent->id . ': ' . $e->getMessage());
            }
        }

        return 0;
    }
}
