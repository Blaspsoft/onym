<?php

declare(strict_types=1);

namespace Blaspsoft\Onym;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class OnymServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('onym.php'),
            ], 'onym-config');
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'onym');

        $this->app->singleton(OnymManager::class, fn (Application $app) => new OnymManager($app));

        $this->app->singleton('onym', fn (Application $app) => new Onym(
            $app->make(OnymManager::class),
            $app->make('config'),
            filesystems: fn () => $app->make('filesystem'),
        ));

        $this->app->alias('onym', Onym::class);
    }
}
