<?php

namespace Bakirov\Protokit;

use Illuminate\Support\ServiceProvider;
use Bakirov\Protokit\Console\Commands\CreateModelCommand;

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
            ]);
        }
    }
}