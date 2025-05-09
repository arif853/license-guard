<?php
namespace Arif853\LicenseGuard;

use Illuminate\Support\ServiceProvider;
use Arif853\LicenseGuard\Middleware\CheckLicense;

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
        $router->aliasMiddleware('license.guard', \Arif853\LicenseGuard\Middleware\CheckLicense::class);

        // $router->pushMiddlewareToGroup('web', CheckLicense::class);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/license-guard.php', 'license-guard');
    }
}
