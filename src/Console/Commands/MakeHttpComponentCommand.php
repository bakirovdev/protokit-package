<?php

namespace Bakirov\Protokit\Console\Commands;

use Illuminate\Support\Str;
use Bakirov\Protokit\Console\Commands\Abstractions\ModuleCommand;
class MakeHttpComponentCommand extends ModuleCommand
{
    use Traits\ModelHttpComponentTrait;

    protected $signature = 'protokit:make-http-component {name}  {--models=}';
    protected $description = 'This will create new Http Component by the name and create models. Inside component there are controllers, requests, and tests.';

    public function handle(): void
    {
        $name = $this->argument('name');
        $name = Str::replace(['\\', '|' ], '/', $name);
        $models = $this->option('models');
        if ($models)
            $models = explode(',', $models);

        $this->checkHttpPath($name);
        $this->createHttpComponents($name, $models);

        $this->info("Http Component '$name' created successfully.");
    }
}
