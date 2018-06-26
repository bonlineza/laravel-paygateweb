<?php

namespace MisterBrownRSA\PayGateWeb;

use Illuminate\Support\ServiceProvider;

/**
 * Class PayGateProvider
 * @package MisterBrownRSA\PayGateWeb
 */
class PayGateProvider extends ServiceProvider
{
    protected $defer = true;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes(
            [
            __DIR__ . "/config/paygate.php" => config_path('paygate.php'),
            ]
        );
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            PayGateWeb::class, function ($app) {
                return new PayGateWeb();
            }
        );
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [
            PayGateWeb::class
        ];
    }
}
