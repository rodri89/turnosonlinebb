<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\MercadoPagoPlatformSetting;
use App\Services\MercadoPago\MercadoPagoOAuthService;
use Illuminate\Http\Request;

class MercadoPagoSettingsController extends Controller
{
    public function edit()
    {
        $settings = MercadoPagoPlatformSetting::current();
        $redirectUri = (new MercadoPagoOAuthService($settings))->redirectUri();

        return view('turnos_admin.admin_mercadopago_settings', compact('settings', 'redirectUri'));
    }

    public function update(Request $request)
    {
        $settings = MercadoPagoPlatformSetting::current();
        if (!$settings) {
            $settings = new MercadoPagoPlatformSetting();
        }

        $request->validate([
            'platform_commission_percent' => 'nullable|numeric|min:0|max:100',
            'mp_app_id' => 'nullable|string|max:255',
            'mode' => 'nullable|in:sandbox,production',
            'integrator_access_token' => 'nullable|string',
            'integrator_public_key' => 'nullable|string|max:255',
            'mp_commission_fallback_percent' => 'nullable|numeric|min:0|max:100',
            'checkout_description' => 'nullable|string|max:255',
        ]);

        $settings->platform_commission_percent = $request->input('platform_commission_percent', 0);
        $settings->mp_app_id = $request->input('mp_app_id');
        $settings->mode = $request->input('mode', 'sandbox');
        $settings->integrator_public_key = $request->input('integrator_public_key');
        $settings->mp_commission_fallback_percent = $request->input('mp_commission_fallback_percent', 0.8);
        $settings->checkout_description = $request->input('checkout_description');

        if ($request->filled('mp_client_secret')) {
            $settings->mp_client_secret = $request->input('mp_client_secret');
        }
        if ($request->filled('integrator_access_token')) {
            $settings->integrator_access_token = $request->input('integrator_access_token');
        }

        $settings->save();

        return redirect()->route('admin.mercadopago.settings')
            ->with('success', 'Configuración de Mercado Pago guardada.');
    }
}
