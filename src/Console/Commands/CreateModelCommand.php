<?php

namespace Bakirov\Protokit\Console\Commands;
use Illuminate\Console\Command;

class CreateModelCommand extends Command
{
    protected $signature = 'protokit:create-model {name} {--table=} {--force}'; 
    protected $description = 'Create a new model class with a specified name and optional table name.';
    
    public function handle()
    {
        $name = $this->argument('name');
        $table = $this->option('table');
        $force = $this->option('force');

        // Logic to create the model class
        // You can use the Laravel File facade to create the file
        // and write the model class code to it.

        $this->info("Model class '$name' created successfully.");
    }
}