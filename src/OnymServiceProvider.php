<?php

namespace Blaspsoft\Onym;

use Illuminate\Support\ServiceProvider;

class OnymServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('onym.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'onym');

        // Register the main class to use with the facade
        $this->bind->singleton('onym', function () {
            return new Onym;
        });
    }
}
