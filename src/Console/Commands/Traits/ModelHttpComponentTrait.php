<?php

namespace Bakirov\Protokit\Console\Commands\Traits;

use Bakirov\Protokit\Enums\HttpComponentClassEnum;
use Bakirov\Protokit\Enums\ModuleClassEnum;
use Illuminate\Support\Str;

trait ModelHttpComponentTrait
{
    private function createHttpComponents(string $name, array|null $models = null): void
    {
        $name  = Str::replace(['/', '|'], '\\', $name);
        $dashName = $this->httpComponentPath ? $this->httpComponentPath . '\\' . $name : $name;
        $slashName = $this->httpComponentPath ? $this->httpComponentPath . '/' . $name : $name;

        if ($models) {
            //this loop for each model
            foreach ($models as $model) {

                //this loop for httpComponents for each model
                foreach (HttpComponentClassEnum::values() as $class) {
                    $classPlural = Str::plural($class);
                    $httpClassName = $model . $class;
                    $classTemplate = $this->getHttpStub($class);

                    //if components created from module command
                    if ($this->isModule){
                        foreach (ModuleClassEnum::values() as $modelClass){
                            $needle = '{{'.Str::upper($modelClass). '_NAME}}';
                            $modelClassName = $model;
                            if ($modelClass !== ModuleClassEnum::Model->value)
                                $modelClassName = $model.$modelClass;
                            $classTemplate = str_replace($needle, "$modelClassName", $classTemplate);
                        }
                        // replacing dynamic names for module
                        $classTemplate = str_replace("{{REQUEST_NAME}}", "{$model}Request", $classTemplate);
                    }
                    // replacing dynamic names
                    $classTemplate = str_replace("{{HTTP_MODULE_NAME}}", "$dashName", $classTemplate);
                    $classTemplate = str_replace("{{MODULE_NAME}}", "$name", $classTemplate);
                    $classTemplate = str_replace("{{CLASS_NAME}}", "$httpClassName", $classTemplate);

                    $path  = "http/{$slashName}/$classPlural";
                    $this->checkEachHttpFile($path);

                    if (file_exists("http/{$slashName}/$classPlural/{$httpClassName}.php")) {
                        $this->info("⚠️  Http/{$slashName}/$classPlural/{$httpClassName}.php already exists!");
                        continue;
                    }

                    file_put_contents(base_path("http/{$slashName}/$classPlural/{$httpClassName}.php"), $classTemplate);
                    $this->info("✅ Http/{$slashName}/$classPlural/{$httpClassName}.php is created!");

                    if ($class === HttpComponentClassEnum::Controller->value)
                        $this->addRoutes($slashName, $httpClassName, $model);
                }
            }
        } else {
            //this is for creating by name only, without models
            foreach (HttpComponentClassEnum::values() as $class) {
                $classPlural = Str::plural($class);
                $model = preg_split('/[\/\\\\]/', $dashName);
                $model = array_reverse($model)[0];
                $httpClassName = $model . $class;
                $classTemplate = $this->getHttpStub($class);

                //if components created from module command
                if ($this->isModule){
                    foreach (ModuleClassEnum::values() as $modelClass){
                        $needle = '{{'.Str::upper($modelClass). '_NAME}}';
                        $modelClassName = $model;
                        if ($modelClass !== ModuleClassEnum::Model->value)
                            $modelClassName = $model.$modelClass;
                        $classTemplate = str_replace($needle, "$modelClassName", $classTemplate);
                    }
                    // replacing dynamic names for module
                    $classTemplate = str_replace("{{REQUEST_NAME}}", "{$model}Request", $classTemplate);
                }
                // replacing dynamic names
                $classTemplate = str_replace("{{HTTP_MODULE_NAME}}", "$dashName", $classTemplate);
                $classTemplate = str_replace("{{MODULE_NAME}}", "$name", $classTemplate);
                $classTemplate = str_replace("{{CLASS_NAME}}", "$httpClassName", $classTemplate);

                $path  = "http/{$slashName}/$classPlural";
                $this->checkEachHttpFile($path);

                if (file_exists("http/{$slashName}/$classPlural/{$httpClassName}.php")) {
                    $this->info("⚠️  Http/{$slashName}/$classPlural/{$httpClassName}.php already exists!");
                    continue;
                }

                file_put_contents(base_path("http/{$slashName}/$classPlural/{$httpClassName}.php"), $classTemplate);

                $this->info("✅ Http/{$slashName}/$classPlural/{$httpClassName}.php is created!");

                if ($class === HttpComponentClassEnum::Controller->value) {
                    $this->addRoutes($slashName, $httpClassName, $name);
                }
            }
        }
    }

    //creating routes for http component
    public function addRoutes(string $moduleName, string $className, string $routeName)
    {
        // configure names
        $dashModuleName = Str::replace(['/', '|'], '\\', $moduleName);
        $path = "http/{$moduleName}/routes.php";

        $partsRouteName = preg_split('/[\/\\\\]/', $routeName);
        $routeName = Str::plural(Str::snake(array_reverse($partsRouteName)[0]));


        if (!file_exists(base_path($path))) {
            $routesStub = $this->getHttpStub('routes');
            $moduleNameLow = Str::plural(Str::snake(Str::replace(['/', '|', '\\'], '', $moduleName), '_'));

            //replacing dynamic names
            $routesStub = str_replace('{{MODULE_NAME_LOW}}', $moduleNameLow, $routesStub);
            $routesStub = str_replace('{{ROUTE_NAME}}', $routeName, $routesStub);
            $routesStub = str_replace('{{MODULE_NAME}}', $dashModuleName, $routesStub);
            $routesStub = str_replace('{{CLASS_NAME}}', $className, $routesStub);

            file_put_contents(base_path($path), $routesStub);
        } else {
            // if file exists, we will add new route by modifying existing file
            $content = file_get_contents(base_path($path));

            $useStatement = "use Http\\$dashModuleName\\Controllers\\$className;";
            if (!Str::contains($content, $useStatement)) {
                // Add after the last use statement or at the top
                if (preg_match_all('/^use .*;$/m', $content, $matches, PREG_OFFSET_CAPTURE)) {
                    $lastUse = end($matches[0]);
                    $insertPos = $lastUse[1] + strlen($lastUse[0]);
                    $content = substr_replace($content, "\n$useStatement", $insertPos, 0);
                } else {
                    $content = "<?php\n\n$useStatement\n\n" . ltrim($content);
                }
            }

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

    public function getHttpStub(string $stubName)
    {
        if ($this->isModule) return file_get_contents(__DIR__ . "/../../Stubs/Module/Http/$stubName.stub");
        return file_get_contents(__DIR__ . "/../../Stubs/Http/$stubName.stub");
    }

    private function checkHttpPath($name): void
    {
        $name = $this->httpComponentPath ? $this->httpComponentPath . '/' . $name : $name;
        $paths = preg_split('/[\/\\\\]/', $name);

        if (!file_exists(base_path("http"))) {
            mkdir(base_path("http"));
            $this->info("✅ http folder is created.");
        }

        $collectedPath = '';
        foreach ($paths as $path) {
            $collectedPath .= ucfirst($path) . '/';
            if (!file_exists(base_path("http/$collectedPath"))) {
                mkdir(base_path("http/$collectedPath"));
                $this->info("✅ $collectedPath folder is created.");
            }
        }
    }

    private function checkEachHttpFile(string $path)
    {
        if (!file_exists(base_path("$path"))) {
            dump($path);
            mkdir(base_path("$path"));
        }
    }
}
