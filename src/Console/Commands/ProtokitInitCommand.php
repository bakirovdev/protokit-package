<?php

namespace Bakirov\Protokit\Console\Commands;

use Illuminate\Console\Command;
use Bakirov\Protokit\Enums\ModelFilesEnum;

class ProtokitInitCommand extends Command
{
    protected $signature = 'protokit:init'; 
    protected $description = 'This will create new Module by the name and create models';
    
    public function handle(): void
    {
        $this->checkPath();
        $this->creteModel();
        $this->creteQueryBuilder();
        $this->createSearch();
        $this->createService();
        $this->createController();
        // $this->createApplication();

        $this->info("Protokit initals done successfully.");
    }

    private function creteModel(): void
    {
        foreach (ModelFilesEnum::values() as $file) {            
            $classTemplate = $this->getStub("Model/$file");

            $path  = "Model";
            $this->checkPath($path);
            file_put_contents(app_path("Protokit/Model/{$file}.php"), $classTemplate);
            $this->info("✅ APP/Protokit/Model/{$file}.php is created!");
        }
    }

    private function creteQueryBuilder(): void
    {
        if (!$this->checkFileExists('QueryBuilder')) {
            $classTemplate = $this->getStub("QueryBuilder");
            file_put_contents(app_path("Protokit/QueryBuilder.php"), $classTemplate);
            $this->info("✅ APP/Protokit/QueryBuilder.php is created!");
            return;
        }
        $this->info("✅ APP/Protokit/QueryBuilder.php is already exists!");
    }

    private function createSearch(): void
    {
        if (!$this->checkFileExists('Search')) {
            $classTemplate = $this->getStub("Search");
            file_put_contents(app_path("Protokit/Search.php"), $classTemplate);
            $this->info("✅ APP/Protokit/Search.php is created!");
            return;
        }
        $this->info("✅ APP/Protokit/Search.php is already exists!");
    }

    private function createController(): void
    {
        if (!$this->checkFileExists('ProtokitController')) {
            $classTemplate = $this->getStub("ProtokitController");
            file_put_contents(app_path("Protokit/ProtokitController.php"), $classTemplate);
            $this->info("✅ APP/Protokit/ProtokitController.php is created!");
            return;
        }
        $this->info("✅ APP/Protokit/ProtokitController.php is already exists!");
    }
    private function createApplication(): void
    {
        if (!$this->checkFileExists('Application')) {
            $classTemplate = $this->getStub("Application");
            file_put_contents(app_path("Protokit/Application.php"), $classTemplate);
            $this->info("✅ APP/Protokit/Application.php is created!");
            return;
        }
        $this->info("✅ APP/Protokit/Application.php is already exists!");
    }

    private function createService(): void
    {
        if (!$this->checkFileExists('Service')) {
            $classTemplate = $this->getStub("Service");
            file_put_contents(app_path("Protokit/Service.php"), $classTemplate);
            $this->info("✅ APP/Protokit/Service.php is created!");
            return;
        }
        $this->info("✅ APP/Protokit/Service.php is already exists!");
    }

    public function getStub(string $stubName)
    {
        return file_get_contents(__DIR__ . "/../Stubs/$stubName.stub");
    }

    private function checkPath($path = null): void
    {
        if ($path && !file_exists(app_path("Protokit/$path"))) {
            mkdir(app_path("Protokit/$path"));
            $this->info("✅ Protokit/$path folder is created.");
            return;
        }

        if (!file_exists(app_path("Protokit"))) {
            mkdir(app_path("Protokit"));
            $this->info("✅ Protokit folder is created.");
        }
    }

    private function checkFileExists($path): bool
    {
        return file_exists(app_path('Protokit/'.$path)) ? true : false;
    }
}