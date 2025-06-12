<?php

namespace Bakirov\Protokit\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Bakirov\Protokit\Enums\HttpComponentClassEnum;
class MakeHttpComponentCommand extends Command
{
    protected $signature = 'protokit:make-http-component {name}  {--models=}';
    protected $description = 'This will create new Http Component by the name and create models. Inside component there are controllers, requests, and tests.';

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
        if ($models) {
            foreach ($models as $model) {
                foreach (HttpComponentClassEnum::values() as $class) {
                    $className = $model . $class;

                    $classTemplate = $this->getStub($class);
                    $classTemplate = str_replace("{{MODULE_NAME}}", "$name", $classTemplate);
                    $classTemplate = str_replace("{{CLASS_NAME}}", "$className", $classTemplate);

                    $path  = "http/{$name}/{$class}s";
                    $this->checkEachFile($path);

                    if (file_exists("http/{$name}/{$class}s/{$className}.php")) {
                        $this->info("⚠️  Http/{$name}/{$class}s/{$className}.php already exists!");
                        continue;
                    }

                    file_put_contents(base_path("http/{$name}/{$class}s/{$className}.php"), $classTemplate);
                    $this->info("✅ Http/{$name}/{$class}s/{$className}.php is created!");
                    
                    if ($class === HttpComponentClassEnum::Controller->value)
                        $this->addRoutes($name, $className, $model);
                }
            }
        } else {
            foreach (HttpComponentClassEnum::values() as $class) {
                $className = $name . $class;

                $classTemplate = $this->getStub($class);
                $classTemplate = str_replace("{{MODULE_NAME}}", "$name", $classTemplate);
                $classTemplate = str_replace("{{CLASS_NAME}}", "$className", $classTemplate);

                $path  = "http/{$name}/{$class}s";
                $this->checkEachFile($path);

                if (file_exists("http/{$name}/{$class}s/{$className}.php")) {
                    $this->info("⚠️  Http/{$name}/{$class}s/{$className}.php already exists!");
                    continue;
                }

                file_put_contents(base_path("http/{$name}/{$class}s/{$className}.php"), $classTemplate);

                $this->info("✅ Http/{$name}/{$class}s/{$className}.php is created!");

                if ($class === HttpComponentClassEnum::Controller->value) {
                    $this->addRoutes($name, $className, $name);
                }
            }
        }
    }

    public function addRoutes(string $moduleName, string $className, string $routeName)
    {
        $path = "http/{$moduleName}/routes.php";
        if (!file_exists(base_path($path))) {
            $routesStub = $this->getStub('routes');

            $parts = preg_split('/[\/\\\\]/', $moduleName);
            $moduleNameLow = Str::plural(Str::lower(array_reverse($parts)[0]));
            $routesStub = str_replace('{{MODULE_NAME_LOW}}', $moduleNameLow, $routesStub);

            $parts = preg_split('/[\/\\\\]/', $routeName);
            $routeName = Str::plural(Str::lower(array_reverse($parts)[0]));
            $routesStub = str_replace('{{ROUTE_NAME}}', $routeName, $routesStub);

            $routesStub = str_replace('{{MODULE_NAME}}', $moduleName, $routesStub);
            $routesStub = str_replace('{{CLASS_NAME}}', $className, $routesStub);
            file_put_contents(base_path($path), $routesStub);
        } else {
            $content = file_get_contents(base_path($path));

            // Match first group block
            $pattern = '/Route::prefix\([\'"][^\'"]+[\'"]\)\s*->group\(function\s*\(\)\s*{([\s\S]*?)}\);/';

            if (!preg_match($pattern, $content, $matches)) {
                $this->error('No group block found.');
                return 1;
            }

            $groupBody = trim($matches[1]);

            // Build new route line
            $newRouteLine = "Route::apiResource('{$routeName}', {$className}::class);";

            if (Str::contains($groupBody, $newRouteLine)) {
                $this->info('Route already exists in group.');
                return 0;
            }

            // Insert new route inside the group block
            $newGroupBody = $groupBody . "\n        " . $newRouteLine;

            $newGroup = str_replace($groupBody, $newGroupBody, $matches[0]);

            // Replace the whole group block in the file content
            $newContent = preg_replace($pattern, $newGroup, $content, 1);

            file_put_contents($path, $newContent);

            $this->info("Route added successfully.");
            return 0;
        }
    }

    public function getStub(string $stubName)
    {
        return file_get_contents(__DIR__ . "/../Stubs/Http/$stubName.stub");
    }

    private function checkPath($name): void
    {
        if (!file_exists(base_path("http"))) {
            mkdir(base_path("http"));
            $this->info("✅ http folder is created.");
        }

        if (!file_exists(base_path("http/$name"))) {
            mkdir(base_path("http/$name"));
            $this->info("✅ $name folder is created.");
        }
    }

    private function checkEachFile(string $path)
    {
        if (!file_exists(base_path("$path"))) {
            mkdir(base_path("$path"));
        }
    }
}
