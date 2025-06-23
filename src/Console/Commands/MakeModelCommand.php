<?php

namespace Bakirov\Protokit\Console\Commands;

use Illuminate\Support\Str;
use Bakirov\Protokit\Enums\ModuleClassEnum;
use Bakirov\Protokit\Console\Commands\Abstractions\ModuleCommand;

class MakeModelCommand extends ModuleCommand
{
    protected $isModule = true;
    use Traits\ModelHttpComponentTrait;
    use Traits\MakeTestComponentTrait;

    protected $signature = 'protokit:make-module {name}  {--models=}'; 
    protected $description = 'This will create new Module by the name and create models';
    
    public function handle(): void
    {
        $httpComponent = false;
        $name = $this->argument('name');
        $models = $this->option('models');

        if ($models)
            $models = explode(',', $models);

        $askHttpComponents = $this->ask("Do you want add HttpComponents such as Controllers, Requests, routes (Y/n)?");

        if (in_array(strtolower($askHttpComponents), ['y', 'yes', ''])) {
            $httpComponent = true;
            $askHttpComponentPath = $this->ask("Which folder inside http you want to create. (if you don't just press enter)?");
            $this->httpComponentPath = $askHttpComponentPath;
            $this->checkHttpPath($name);
        }

        $this->checkPath($name);
        $this->createModuleClasses($name, $models, $httpComponent);

        $this->info("Model class '$name' created successfully.");
    }

    private function createModuleClasses(string $name, array|null $models = null, $httpComponent = null): void
    {
        $this->configureDatabase($name);
        if ($models) {
            foreach ($models as $model) {
                foreach (ModuleClassEnum::values() as $class) {
                    $classPlural = Str::plural($class);
                    $className = $model;
                    if ($class !== ModuleClassEnum::Model->value)                        
                        $className = $model.$class;

                    $classTemplate = $this->getStub($class);
                    $classTemplate = str_replace("{{MODULE_NAME}}", "$name", $classTemplate);
                    $classTemplate = str_replace("{{CLASS_NAME}}", "$className", $classTemplate);
                    
                    $path  = "modules/{$name}/{$classPlural}";
                    $this->checkEachFile($path);

                    file_put_contents(base_path("modules/{$name}/{$classPlural}/{$className}.php"), $classTemplate);
                    $this->info("✅ Modules/{$name}/{$classPlural}/{$className}.php is created!");
                }

                $this->createDatabase($name, $model);
            }
            if ($httpComponent) {
                $this->createHttpComponents($name, $models);
            }
            return;
        }

        foreach (ModuleClassEnum::values() as $class) {
            $classPlural = Str::plural($class);
            $className = $name;
            if ($class !== ModuleClassEnum::Model->value)
                $className = $name.$class;
            
            $classTemplate = $this->getStub($class);
            $classTemplate = str_replace("{{MODULE_NAME}}", "$name", $classTemplate);
            $classTemplate = str_replace("{{CLASS_NAME}}", "$className", $classTemplate);
            
            $path  = "modules/{$name}/$classPlural";
            $this->checkEachFile($path);

            file_put_contents(base_path("modules/{$name}/{$classPlural}/{$className}.php"), $classTemplate);

            
            if ($httpComponent) {
                $this->createHttpComponents($name, null);
            }
            $this->info("✅ Modules/{$name}/{$classPlural}/{$className}.php is created!");
        }
        $this->createDatabase($name, $name);
    }

    public function configureDatabase(string $moduleName)
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
         
        $fileName = Str::plural(Str::snake($moduleName)).'-relations';
        if (file_exists(base_path("modules/$moduleName/Database/$fileName.php"))){
            $this->error("❗️  $moduleName-relations.php  relation is already exists");
        }else {
            $relationTemplate = $this->getStub('relation');
            file_put_contents(base_path("modules/$moduleName/Database/$fileName.php"), $relationTemplate);
        }
    }

    private function createDatabase($moduleName, $model):void
    {
        $fileName = Str::plural(Str::snake($model));

        // create migration
        if (file_exists(base_path("modules/$moduleName/Database/Migrations/$fileName.php"))){
            $this->error("❗️  $fileName.php migration is already exists");
        }else {
            $migrationTemplate = $this->getStub('migration');
            $migrationTemplate = str_replace("{{TABLE_NAME}}", "$fileName", $migrationTemplate);
            file_put_contents(base_path("modules/$moduleName/Database/Migrations/$fileName.php"), $migrationTemplate);
        }

        // createSeeder
        if (file_exists(base_path("modules/$moduleName/Database/Seeders/$fileName-seeder.php"))){
            $this->error("❗️  $fileName-seeder.php seeder is already exists");
        }else {
            $seederTemplate = $this->getStub('seeder');
            $seederTemplate = str_replace("{{TABLE_NAME}}", "$fileName", $seederTemplate);
            file_put_contents(base_path("modules/$moduleName/Database/Seeders/$fileName-seeder.php"), $seederTemplate);
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