<?php

namespace Bakirov\Protokit\Base;

use Bakirov\Protokit\Base\Model\Model;

class Service
{
    public function __construct(protected Model $model)
    {
    }
}
