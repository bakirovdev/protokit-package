<?php

namespace Bakirov\Protokit\Console\Commands;

use Bakirov\Protokit\Enums\ModuleClassEnum;
use Illuminate\Console\Command;

class CreateModelCommand extends Command
{
    protected $signature = 'protokit:create-module {name}  {--models=}'; 
    protected $description = 'This will create new Module by the name and create models';
    
    public function handle(): void
    {
        $name = $this->argument('name');
        $models = $this->option('models');
        if ($models)
            $models = explode(',', $models);

        $this->checkPath($name);
        $this->createModuleClasses($name, $models);
        $this->addingAutoload();


        $this->info("Model class '$name' created successfully.");
    }

    private function createModuleClasses(string $name, array $models): void
    {
        if ($models) {
            foreach ($models as $model) {
                foreach (ModuleClassEnum::values() as $class) {
                    if ($class !== ModuleClassEnum::Model->value)
                        $model .= $model.$class;

                    $classTemplate = $this->getStub($class);
                    $classTemplate = str_replace("{{NAME_MODULE}}", "$name", $classTemplate);
                    $classTemplate = str_replace("{{NAME_CLASS}}", "$model", $classTemplate);

                    $path  = "modules/{$name}/{$class}s";
                    $this->checkEachFile($path);

                    file_put_contents(base_path("modules/{$name}/{$class}s/{$model}.php"), $classTemplate);
                    $this->info("✅ Modules/{$name}/{$class}s/{$model}.php is created!");
                }
            }
            return;
        }

        foreach (ModuleClassEnum::values() as $class) {
            if ($class !== ModuleClassEnum::Model->value)
                $name .= $name.$class;
            $classTemplate = $this->getStub($class);
            $classTemplate = str_replace("{{NAME_MODEL}}", "$name", $classTemplate);
            file_put_contents(base_path("modules/{$name}/{$class}s/{$name}.php"), $classTemplate);
            $this->info("✅ Modules/{$name}/{$class}s/{$name}.php is created!");
        }
    }

    public function addingAutoload(): void
    {
        $path = base_path('composer.json');

        if (!file_exists($path))
            $this->error("composer.json not found.");
        

        $json = json_decode(file_get_contents($path), true);

        if (!isset($json['autoload']['psr-4'])) {
            $json['autoload']['psr-4'] = [];
        }

        if (!isset($json['autoload']['psr-4']['Moduels\\'])) {
            $json['autoload']['psr-4']['Moduels\\'] = 'modules/';            
            $this->info("Added autoload: Moduels\\ => modules/");
        }

        if (!isset($json['autoload']['psr-4']['Http\\'])) {
            $json['autoload']['psr-4']['Http\\'] = 'Http/';
            $this->info("Added autoload: Http\\ => http/");
        }

        file_put_contents($path, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->warn("Run `composer dump-autoload` to apply changes.");
    }

    public function getStub(string $stubName)
    {
        return file_get_contents(__DIR__ . "/../Stubs/$stubName.stub");
    }

    private function checkPath($name): void
    {
        if (!file_exists(base_path("modules"))) {
            mkdir(base_path("modules"));
            $this->info("✅ modules folder is created.");
        }

        if (!file_exists(base_path("modules/$name"))) {
            mkdir(base_path("modules/$name"));
            $this->info("✅ modules folder is created.");
        }
    }

    private function checkEachFile(string $path)
    {
        if (!file_exists(base_path("$path"))) {
            mkdir(base_path("$path"));
        }
    }
}