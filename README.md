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
  
<h2>Key Features</h2>
- **Halpers**: Helpers for validation, file management , locoliziation
- **Confugured Testing**:  Classes for application tests
- **Http Structure**:  Structure based on SOLID princips
- **Model Structure**:  Structure based on SOLID princips
- **Configured Base Controller**:  Base controller that has default CRUD wich common for all models
- **Filter System**:  Filter structure for queryies, It will give convenience for Frontend

<div align="center">
  <h2>Installation</h2>
</div>

You can install package by this composer command.
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
<div align="center">
  <h2>Usage</h2>
</div>

Package has Base Classes to use inside Bakirov/Protokit/Base folder. You can write your application by extending those environment.
There are
- Helpers
- Model
- Request
- Routing
- Common Rules 
- Search
- Testing

After instalation you will have two new folders that are `modules` and `http`

<h2>modules</h2>
It will have your models such as User, Post, etc...
<div align="center">
  <h5>The sturcuture is</h5>
</div>

```bash
modules/
  └── {{module-name}}/     # Such as User, Post , Setting or etc...
      ├── Database/        # Seeders, migrations, relations specific to User
      ├── Models/          # Eloquent models (e.g., User.php, UserProfile,)
      ├── Searches/        # Filtering logic. It looks like GraphQL
      ├── Resources/       # API Resource collections
      └── Observers/       # Model observers for lifecycle events
      └── Enums/           # Enums that belongs to Models inside this modules. (e,g., GenderEnum, UserTypeEnum)
```

<h2>http</h2>
It will have your http-components  such as Controllers, Rquests and API
<div align="center">
  <h5>The sturcuture is</h5>
</div>

```bash
http/
  └── {{componenet-name}}/        # You can neme it like User, Post, Comment. Sometimes it depends to module.
      ├── Controllers/            # Controllers that working for this component.
      ├── Requests/               # Requests depend Controllers
      └── routes.php/             # api routes depend this Controllers
```

