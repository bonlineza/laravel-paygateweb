<?php

namespace MisterBrownRSA\PayGateWeb;

use Illuminate\Support\ServiceProvider;

class PayGateProvider extends ServiceProvider
{
    protected $defer = TRUE;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . "/config/paygate.php" => config_path('paygate.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(PayGateWeb::class, function ($app) {
            return new PayGateWeb();
        });
    }

    public function provides()
    {
        return [
            PayGateWeb::class
        ];
    }
}
