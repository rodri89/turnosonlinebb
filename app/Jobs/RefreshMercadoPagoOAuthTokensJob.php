<?php

namespace App\Jobs;

use App\MedicoMercadoPagoAccount;
use App\Services\MercadoPago\MercadoPagoOAuthService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RefreshMercadoPagoOAuthTokensJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $oauth = new MercadoPagoOAuthService();
        $accounts = MedicoMercadoPagoAccount::whereNotNull('refresh_token')->get();

        foreach ($accounts as $account) {
            if ($account->expires_at && $account->expires_at->gt(now()->addDays(7))) {
                continue;
            }

            try {
                $oauth->refreshToken($account);
            } catch (\Exception $e) {
                Log::warning('MP OAuth refresh failed for medico ' . $account->medico_id . ': ' . $e->getMessage());
            }
        }
    }
}
