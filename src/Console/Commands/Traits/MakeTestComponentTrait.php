<?php

namespace Bakirov\Protokit\Console\Commands\Traits;

use Illuminate\Support\Str;

trait MakeTestComponentTrait
{
    private function createHttpComponents(string $name, array|null $models = null): void
    {
        $this->getTestsStub();
        $name  = Str::replace(['/', '|'], '\\', $name);
        $dashName = $this->httpComponentPath ? $this->httpComponentPath . '\\' . $name : $name;
        $slashName = $this->httpComponentPath ? $this->httpComponentPath . '/' . $name : $name;
        $slashName = Str::replace(['\\', '|'], '/', $slashName);

        if ($models) {
            //this loop for each model
            foreach ($models as $model) {

                
            }
        } else {
            //this is for creating by name only, without models

            
        }
    }
    
    //creating routes for http component
    public function getTestsStub()
    {
        foreach (glob(__DIR__."/../../Stubs/Module/Http/Test/*.stub") as $file) {
            dump($file);
        }
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
