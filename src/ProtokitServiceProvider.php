<?php

namespace Bakirov\Protokit;

use Illuminate\Support\ServiceProvider;
use Bakirov\Protokit\Console\Commands\CreateModelCommand;
use Bakirov\Protokit\Console\Commands\ProtokitInitCommand;

class ProtokitServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Optional: bind services here
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {

            $this->publishes([__DIR__.'/../config/protokit.php' => config_path('protokit.php')], 'protokit');

            $this->commands([
                CreateModelCommand::class,
                ProtokitInitCommand::class,
            ]);
        }
    }
}