<?php
namespace arif853\LicenseGuard;

use Illuminate\Support\ServiceProvider;
use LicenseGuard\Middleware\CheckLicense;

class LicenseGuardServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Publish config (optional)
        $this->publishes([
            __DIR__.'/../config/license-guard.php' => config_path('license-guard.php'),
        ], 'config');

        // Register middleware globally
        $router = $this->app['router'];
        $router->pushMiddlewareToGroup('web', CheckLicense::class);
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/license-guard.php', 'license-guard');
    }
}
