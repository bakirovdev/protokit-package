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
        $this->createKernels();

        $addingRouteProvider = $this->ask("Do you want add RouteServiceProvider (Y/n)?", 'y');
        if ($addingRouteProvider == 'y' ||  $addingRouteProvider == 'yes' || $addingRouteProvider == 'Y') {
            $this->createRouteServiceProvider();    
        }

        $this->addingAutoload();
        $this->bindings();

        $this->info("Protokit initals done successfully.");
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

        if (!file_exists(base_path("modules"))) {
            mkdir(base_path("modules"));
            $this->info("✅ Modules folder is created.");
        }

        if (!file_exists(base_path("http"))) {
            mkdir(base_path("http"));
            $this->info("✅ Http folder is created.");
        }
    }

    private function checkFileExists($path): bool
    {
        return file_exists(app_path('Protokit/'.$path.'.php')) ? true : false;
    }

    private function creteModel(): void
    {
        foreach (ModelFilesEnum::values() as $file) {
            $classTemplate = $this->getStub("Model/$file");

            if(!$this->checkFileExists("Model/$file")){
                $path  = "Model";
                $this->checkPath($path);
                file_put_contents(app_path("Protokit/Model/{$file}.php"), $classTemplate);
                $this->info("✅ APP/Protokit/Model/{$file}.php is created!");
            }

            $this->info("☑️ APP/Protokit/Model/{$file}.php is already exists!");
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
        $this->info("☑️ APP/Protokit/QueryBuilder.php is already exists!");
    }

    private function createSearch(): void
    {
        if (!$this->checkFileExists('Search')) {
            $classTemplate = $this->getStub("Search");
            file_put_contents(app_path("Protokit/Search.php"), $classTemplate);
            $this->info("✅ APP/Protokit/Search.php is created!");
            return;
        }
        $this->info("☑️ APP/Protokit/Search.php is already exists!");
    }

    private function createController(): void
    {
        if (!$this->checkFileExists('Controller')) {
            $classTemplate = $this->getStub("Controller");
            file_put_contents(app_path("Protokit/Controller.php"), $classTemplate);
            $this->info("✅ APP/Protokit/Controller.php is created!");
            return;
        }
        $this->info("☑️ APP/Protokit/Controller.php is already exists!");
    }

    private function createService(): void
    {
        if (!$this->checkFileExists('Service')) {
            $classTemplate = $this->getStub("Service");
            file_put_contents(app_path("Protokit/Service.php"), $classTemplate);
            $this->info("✅ APP/Protokit/Service.php is created!");
            return;
        }
        $this->info("☑️ APP/Protokit/Service.php is already exists!");
    }

    private function createKernels(): void
    {
        //http Kernel
        $this->checkPath('../Http');
        if ($this->checkFileExists('Http/Kernel')) {
            $this->info("☑️ APP/Http/Kernel.php is already exists!");
        }else{
            $classTemplate = $this->getStub("Kernel/HttpKernel");
            file_put_contents(app_path("Http/Kernel.php"), $classTemplate);
            $this->info("✅ APP/Http/Kernel.php.php is created!");
        }

        //console Kernel
        $this->checkPath('../Console');
        if ($this->checkFileExists('Console/Kernel')) {
            $this->info("☑️ APP/Console/Kernel.php is already exists!");
        }else{
            $classTemplate = $this->getStub("Kernel/ConsoleKernel");
            file_put_contents(app_path("Console/Kernel.php"), $classTemplate);
            $this->info("✅ APP/Console/Kernel.php.php is created!");
        }
    }

    private function createRouteServiceProvider(): void
    {
        if (file_exists(app_path('Providers/RouteServiceProvider.php'))) {
            $this->info("☑️ APP/Providers/RouteServiceProvider.php is already exists!");
            return;
        };

        $classTemplate = $this->getStub("Provider/RouteServiceProvider");
        file_put_contents(app_path("Providers/RouteServiceProvider.php"), $classTemplate);
        $this->info("✅ APP/Protokit/Service.php is created!");
        
        $providersFile = base_path('bootstrap/providers.php');

        if (!file_exists($providersFile)){
            $this->error("bootstrap/providers.php not found.");
            return;
        }
        $fileContent = file_get_contents($providersFile);

         if (str_contains($fileContent, 'App\Providers\RouteServiceProvider' . '::class')) {
            $this->info("☑️ Provider already registered.");
            return;
        }

        $pattern = '/(\[.*?)(\];)/s';

        $updated = preg_replace_callback($pattern, function ($matches) {
            // Ensure there is a trailing comma if needed
            $existing = rtrim($matches[1]);
            $newEntry = "    App\Providers\RouteServiceProvider::class,\n";
            return $existing . "\n" . $newEntry . $matches[2];
        }, $fileContent);
        file_put_contents($providersFile, $updated);
        $this->info("RouteServiceProvider registered successfully.");
    }

    private function bindings(): void
    {
        $path = app_path('Providers/AppServeProvider.php');

        $bindings = $this->getStub("Bindings");

        if (!file_exists($path))
            $this->error("AppServeProvider.php not found.");
        

        $appServiceProvider = file_get_contents($path);

        $register = 'public function register()'
            . chr(10)
            . '    {' . chr(10)
            . $bindings;

        $appServiceProvider = str_replace('public function register()' . chr(10) . '    {', $register, $appServiceProvider);

        file_put_contents($path, $appServiceProvider);

        $this->info("✅ App/Providers/AppServiceProvider.php is changed!");
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
            $json['autoload']['psr-4']['Modules\\'] = 'modules/';            
            $this->info("Added autoload: Modules\\ => modules/");
        }

        if (!isset($json['autoload']['psr-4']['Http\\'])) {
            $json['autoload']['psr-4']['Http\\'] = 'http/';
            $this->info("Added autoload: Http\\ => http/");
        }

        file_put_contents($path, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->warn("⚠️  ==========> Run `composer dump-autoload` to apply changes. <==========  ⚠️");
    }
}