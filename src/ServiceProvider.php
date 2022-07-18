<?php

namespace A2Workspace\Stubs;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../publishes/config.php' => config_path('stubs.php'),
            __DIR__ . '/../publishes/resources' => resource_path('stubs'),
        ], '@a2workspace/laravel-stubs');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            Commands\StubGeneratorCommand::class,
        ]);
    }
}
