<?php

namespace App\Base;

use App\Routing\Router;
use App\Routing\ResourceRegistrar;
use Illuminate\Foundation\Application as FoundationApplication;
use Illuminate\Routing\ResourceRegistrar as LaravelResourceRegistrar;

class Application extends FoundationApplication
{
    public function __construct($basePath = null)
    {
        parent::__construct($basePath);

        $this->bindRouter();
    }

    private function bindRouter(): void
    {
        $this->singleton('router', function ($app) {
            return new Router($app['events'], $app);
        });

        $this->bind(LaravelResourceRegistrar::class, function ($app) {
            return new ResourceRegistrar($app['router']);
        });
    }
}
