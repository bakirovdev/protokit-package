<?php

namespace Bakirov\Protokit;

use Bakirov\Protokit\Console\Commands\MakeHttpComponentCommand;
use Illuminate\Support\ServiceProvider;
use Bakirov\Protokit\Console\Commands\MakeModelCommand;
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

            $this->publishes([__DIR__.'/config/protokit.php' => config_path('protokit.php')], 'protokit');

            $this->commands([
                ProtokitInitCommand::class,
                MakeHttpComponentCommand::class,
                MakeModelCommand::class,
            ]);
        }
    }
}