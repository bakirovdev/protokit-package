<?php

namespace Bakirov\Protokit\Base\Testing;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreateApplicationTrait {
    public function createApplication(): Application
    {
        $app = require __DIR__ . '.../../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
