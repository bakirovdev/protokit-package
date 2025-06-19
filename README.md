<div align="center">
  <h1>laravel-backend protokit package</h1>
</div>

<div align="center">
  <img src="https://img.shields.io/packagist/dependency-v/bakirov/protokit/php">
  <img src="https://img.shields.io/packagist/dt/bakirov/protokit">
  <img src="https://img.shields.io/packagist/v/bakirov/protokit">
  <img src="https://img.shields.io/packagist/dt/bakirov/protokit">
</div>

The package for using Laravel framework easly. It has starter configurations and own dependency structure. 
## Key Features
- **Halpers**: Helpers for validation, file management , locoliziation
- **Confugured Testing**:  Classes for application tests
- **Http Structure**:  Structure based on SOLID princips
- **Model Structure**:  Structure based on SOLID princips
- **Configured Base Controller**:  Base controller that has default CRUD wich common for all models
- **Filter System**:  Filter structure for queryies, It will give convenience for Frontend

<div align="center">
  <h2>Installation</h2>
</div>

```bash
composer require bakirov/protokit
```

Next step you must publish config and action register files:

```bash
php artisan vendor:publish --provider="Bakirov\Protokit\ProtokitServiceProvider"
```
After successfully run this command, you will have a configuration file `config/protokit.php`. This file is containing configuration variables for the package.

Next step you need to run init command for configuring app structure. 
```bash
php artisan protokit:init
```
- It will create Kernel files to Console and Http folders.
- Add RouteServiceProvider if you want. Wit this you can register your routes inside http-component
- Add new autoloads to package.json
- Bind App's ExceptionHandler , ResourceRegistrar and router to Protokit Base classes
Last installation stap is autoload command fo composer . You need to run it like this.
```bash
composer dump-autoload
```

