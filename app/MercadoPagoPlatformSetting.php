<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MercadoPagoPlatformSetting extends Model
{
    protected $table = 'mercadopago_platform_settings';

    protected $fillable = [
        'platform_commission_percent',
        'mp_app_id',
        'mp_client_secret',
        'mode',
        'integrator_access_token',
        'integrator_public_key',
        'mp_commission_fallback_percent',
        'checkout_description',
    ];

    public static function current()
    {
        return static::query()->first();
    }

    public function isProduction()
    {
        return $this->mode === 'production';
    }

    public function oauthBaseUrl()
    {
        return $this->isProduction()
            ? 'https://auth.mercadopago.com.ar'
            : 'https://auth.mercadopago.com.ar';
    }

    public function apiBaseUrl()
    {
        return 'https://api.mercadopago.com';
    }
}
