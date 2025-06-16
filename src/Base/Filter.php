<?php

namespace Bakirov\Protokit\Base;

use Illuminate\Database\Eloquent\Builder;

abstract class Filter
{
    abstract public function process(Builder $query): Builder;
}
