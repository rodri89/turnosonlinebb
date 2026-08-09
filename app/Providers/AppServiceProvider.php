<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('onesignal', function() {
            return new \App\Services\OneSignalService();
        });
        
        // COMENTADO: Servicio Firebase FCM - Removido porque no funciona en iOS
        // $this->app->singleton('firebase', function() {
        //     return new \App\Services\FirebaseMessagingService();
        // });
        
        $this->app->singleton('google-calendar', function() {
            return new \App\Services\GoogleCalendarService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
    }
}
