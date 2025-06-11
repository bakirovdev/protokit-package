<?php

namespace Bakirov\Protokit\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Bakirov\Protokit\Enums\ModuleClassEnum;

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

    private function createModuleClasses(string $name, array|null $models = null): void
    {
        $this->createRelation($name);
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

                $this->createDatabase($name, $model);
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
            
            $path  = "modules/{$name}/{$class}s";
            $this->checkEachFile($path);

            file_put_contents(base_path("modules/{$name}/{$class}s/{$className}.php"), $classTemplate);

            $this->createDatabase($name, $name);
            $this->info("✅ Modules/{$name}/{$class}s/{$className}.php is created!");
        }
    }

    private function createDatabase($moduleName, $model):void
    {
        if (!file_exists(base_path("modules/$moduleName/Database"))) {
            mkdir(base_path("modules/$moduleName/Database"));
            mkdir(base_path("modules/$moduleName/Database/Migrations"));
            mkdir(base_path("modules/$moduleName/Database/Seeders"));
            $this->info("✅ modules folder is created.");
        }elseif (!file_exists(base_path("modules/$moduleName/Database/Migrations"))) {
            mkdir(base_path("modules/$moduleName/Database/Migrations"));
        }elseif(!file_exists(base_path("modules/$moduleName/Database/Seeders"))){
            mkdir(base_path("modules/$moduleName/Database/Seeders"));
        }

        $fileName = Str::plural(Str::snake($model));

        // create migration
        if (file_exists(base_path("modules/$moduleName/Database/Migrations/$fileName.php"))){
            $this->error("❗️  $fileName.php  migration is already exists");
        }else {
            $migrationTemplate = $this->getStub('migration');
            $migrationTemplate = str_replace("{{TABLE_NAME}}", "$fileName", $migrationTemplate);
            file_put_contents(base_path("modules/$moduleName/Database/Migrations/$fileName.php"), $migrationTemplate);
        }

        // createSeeder
        if (file_exists(base_path("modules/$moduleName/Database/Seeders/$fileName-seeder.php"))){
            $this->error("❗️  $fileName-seeder.php  migration is already exists");
        }else {
            $seederTemplate = $this->getStub('seeder');
            $seederTemplate = str_replace("{{TABLE_NAME}}", "$fileName", $seederTemplate);
            file_put_contents(base_path("modules/$moduleName/Database/Seeders/$fileName-seeder.php"), $seederTemplate);
        }

    }

    public function createRelation(string $moduleName)
    {
        if (!file_exists(base_path("modules/$moduleName/Database")))
            mkdir(base_path("modules/$moduleName/Database"));
         
        $fileName = Str::plural(Str::snake($moduleName)).'-relations';
        if (file_exists(base_path("modules/$moduleName/Database/$fileName.php"))){
            $this->error("❗️  $moduleName-relations.php  migration is already exists");
        }else {
            $relationTemplate = $this->getStub('relation');
            file_put_contents(base_path("modules/$moduleName/Database/$fileName.php"), $relationTemplate);
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