<?php

namespace Bakirov\Protokit\Console\Commands\Abstractions;

use Illuminate\Console\Command;

abstract class ModuleCommand extends Command
{
    private $isModule = false;
}