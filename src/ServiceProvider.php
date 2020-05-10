<?php

namespace Knowfox\Pocket;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use Knowfox\Pocket\Commands\PocketSyncCommand;
use Illuminate\Console\Scheduling\Schedule;
use Knowfox\Pocket\Commands\PocketSyncCommand;

class ServiceProvider extends IlluminateServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../migrations');
        $this->loadViewsFrom(__DIR__ . '/../views', 'pocket');
        $this->loadRoutesFrom(__DIR__ . '/../routes.php');
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'pocket');

        $this->publishes([
            __DIR__ . '/../views' => resource_path('views/vendor/pocket'),
            __DIR__ . '/../pocket.php' => config_path('pocket.php'),
            __DIR__ . '/../lang' => resource_path('lang/vendor/pocket'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                PocketSyncCommand::class,
            ]);
        }
        
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command(PocketSyncCommand::class)->everyTenMinutes();
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../pocket.php', 'pocket'
        );
    }
}
