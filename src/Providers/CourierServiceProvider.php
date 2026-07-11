<?php

namespace Rajibbinalam\BagistoCourier\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Rajibbinalam\BagistoCourier\Console\Commands\SyncCourierStatusCommand;
use Rajibbinalam\BagistoCourier\Events\CourierOrderCreated;
use Rajibbinalam\BagistoCourier\Events\CourierStatusUpdated;
use Rajibbinalam\BagistoCourier\Listeners\LogCourierActivity;
use Rajibbinalam\BagistoCourier\Services\CourierManager;

class CourierServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/courier.php', 'courier');

        // Registers "Courier Settings" under Bagisto's Configure > Sales.
        // Must happen in register(), not boot() — Bagisto reads the 'core'
        // config key early during its own boot sequence.
        $this->mergeConfigFrom(__DIR__ . '/../../config/system.php', 'core');

        $this->app->singleton(CourierManager::class, fn ($app) => new CourierManager());
        $this->app->alias(CourierManager::class, 'courier');
    }

    public function boot(): void
    {
        $this->configureLogging();
        $this->publishAssets();
        $this->loadMigrations();
        $this->loadRoutes();
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'bagisto-courier');
        $this->registerEvents();
        $this->registerCommands();
    }

    /**
     * Adds a dedicated "courier" log channel writing to
     * storage/logs/courier-*.log, without requiring the host app to edit
     * config/logging.php manually.
     */
    protected function configureLogging(): void
    {
        $this->app['config']->set('logging.channels.courier', [
            'driver' => 'daily',
            'path'   => storage_path('logs/courier.log'),
            'level'  => env('COURIER_LOG_LEVEL', 'info'),
            'days'   => 14,
        ]);
    }

    protected function publishAssets(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/courier.php' => config_path('courier.php'),
        ], 'courier-config');

        $this->publishes([
            __DIR__ . '/../../database/migrations' => database_path('migrations'),
        ], 'courier-migrations');

        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/bagisto-courier'),
        ], 'courier-views');
    }

    protected function loadMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }

    /**
     * The routes file defines its own middleware groups (['web', 'admin']
     * for admin routes, no group for public webhook routes) — matching
     * Bagisto's own package-routing convention.
     */
    protected function loadRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/admin-routes.php');
    }

    protected function registerEvents(): void
    {
        Event::listen(CourierOrderCreated::class, [LogCourierActivity::class, 'handleOrderCreated']);
        Event::listen(CourierStatusUpdated::class, [LogCourierActivity::class, 'handleStatusUpdated']);
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncCourierStatusCommand::class,
            ]);
        }
    }
}
