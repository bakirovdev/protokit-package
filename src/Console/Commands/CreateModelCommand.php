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

        $this->info("Model class '$name' created successfully.");
    }

    private function createModuleClasses(string $name, array $models): void
    {
        if ($models) {
            foreach ($models as $model) {
                foreach (ModuleClassEnum::values() as $class) {
                    $className = $model;
                    if ($class !== ModuleClassEnum::Model->value)                        
                        $className = $model.$class;

                    $classTemplate = $this->getStub($class);
                    $classTemplate = str_replace("{{MODULE_NAME}}", "$name", $classTemplate);
                    $classTemplate = str_replace("{{CLASS_NAME}}", "$className", $classTemplate);

                    $path  = "modules/{$name}/{$class}s";
                    $this->checkEachFile($path);

                    file_put_contents(base_path("modules/{$name}/{$class}s/{$className}.php"), $classTemplate);
                    $this->info("✅ Modules/{$name}/{$class}s/{$className}.php is created!");
                }
            }
            return;
        }

        foreach (ModuleClassEnum::values() as $class) {
            $className = $name;
            if ($class !== ModuleClassEnum::Model->value)
                $className = $name.$class;
            
            $classTemplate = $this->getStub($class);
            $classTemplate = str_replace("{{MODULE_NAME}}", "$name", $classTemplate);
            $classTemplate = str_replace("{{CLASS_NAME}}", "$className", $classTemplate);
            file_put_contents(base_path("modules/{$name}/{$class}s/{$className}.php"), $classTemplate);
            $this->info("✅ Modules/{$name}/{$class}s/{$className}.php is created!");
        }
    }

    public function getStub(string $stubName)
    {
        return file_get_contents(__DIR__ . "/../Stubs/Module/$stubName.stub");
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