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
            $this->commands([
                CreateModelCommand::class,
                ProtokitInitCommand::class,
            ]);
        }
    }
}