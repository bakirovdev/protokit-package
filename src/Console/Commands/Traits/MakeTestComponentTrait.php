<?php

namespace Bakirov\Protokit\Console\Commands\Traits;

use Illuminate\Support\Str;

trait MakeTestComponentTrait
{
    private function createTestComponents(string $name, array|null $models = null): void
    {
        $name  = Str::replace(['/', '|'], '\\', $name);
        $dashName = $this->httpComponentPath ? $this->httpComponentPath . '\\' . $name : $name;
        $slashName = $this->httpComponentPath ? $this->httpComponentPath . '/' . $name : $name;
        $slashName = Str::replace(['\\', '|'], '/', $slashName);

        if ($models) {
            //this loop for each model
            foreach ($models as $model) {
                $partsRouteName = preg_split('/[\/\\\\]/', $model);
                $moduleNameLow = Str::plural(Str::snake(Str::replace(['/', '|', '\\'], '', $model), '_'));
                $routeName = Str::plural(Str::snake(array_reverse($partsRouteName)[0]));

                $files = $this->getTestsStubs();
                foreach ($files as $file) {
                    $fileContent = file_get_contents($file);
                    $fileName = basename($file, '.stub');
                                        
                    $fileContent = str_replace('{{MODULE_NAME}}', $dashName, $fileContent);
                    $fileContent = str_replace('{{MODEL_NAME}}', $model, $fileContent);
                    $fileContent = str_replace('{{MODULE_NAME_LOW}}', $moduleNameLow, $fileContent);
                    $fileContent = str_replace('{{ROUTE_NAME}}', $routeName, $fileContent);
                    $fileContent = str_replace('{{SEARCH_CLASS}}', "{$model}Search", $fileContent);

                    $path  = "http/{$slashName}/Tests/$model";
                    $this->checkEachTestFile($path);

                    if (file_exists("http/{$slashName}/Tests/$model/$fileName.php")) {
                        $this->info("⚠️  Http/{$slashName}/Tests/$model/$fileName.php already exists!");
                        continue;
                    }

                    file_put_contents(base_path("$path/{$fileName}.php"), $fileContent);
                    $this->info("✅ Http/{$slashName}/Tests/$model/$fileName.php is created!");
                }                
            }

        } else {
            $partsRouteName = preg_split('/[\/\\\\]/', $name);
            $moduleNameLow = Str::plural(Str::snake(Str::replace(['/', '|', '\\'], '', $name), '_'));
            $routeName = Str::plural(Str::snake(array_reverse($partsRouteName)[0]));

            $files = $this->getTestsStubs();
            foreach ($files as $file) {
                $fileContent = file_get_contents($file);
                $fileName = basename($file, '.stub');                
                
                $fileContent = str_replace('{{MODULE_NAME}}', $name, $fileContent);
                $fileContent = str_replace('{{MODEL_NAME}}', $name, $fileContent);
                $fileContent = str_replace('{{MODULE_NAME_LOW}}', $moduleNameLow, $fileContent);
                $fileContent = str_replace('{{ROUTE_NAME}}', $routeName, $fileContent);
                $fileContent = str_replace('{{SEARCH_CLASS}}', "{$name}Search", $fileContent);

                $path  = "http/{$slashName}/Tests/$name";
                $this->checkEachTestFile($path);

                if (file_exists("http/{$slashName}/Tests/$name/$fileName.php")) {
                    $this->info("⚠️  Http/{$slashName}/Tests/$name/$fileName.php already exists!");
                    continue;
                }

                file_put_contents(base_path("$path/{$fileName}.php"), $fileContent);
                $this->info("✅ Http/{$slashName}/Tests/$name/$fileName.php is created!");
            }      
        }
    }
    
    //creating routes for http component
    public function getTestsStubs()
    {
        return glob(__DIR__."/../../Stubs/Module/Http/Test/*.stub");
    }

    private function checkTestPath($name): void
    {
        $name = $this->httpComponentPath ? $this->httpComponentPath . '/' . $name : $name;
        $paths = preg_split('/[\/\\\\]/', $name);

        $collectedPath = '';
        foreach ($paths as $path) {
            $collectedPath .= ucfirst($path) . '/';
            if (!file_exists(base_path("http/$collectedPath"))) {
                mkdir(base_path("http/$collectedPath"));
                $this->info("✅ $collectedPath folder is created.");
            }
        }
    }

    private function checkEachTestFile(string $path)
    {
        if (!file_exists(base_path("$path"))) {
            mkdir(base_path("$path"));
        }
    }
}
