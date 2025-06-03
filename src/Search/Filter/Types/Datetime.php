<?php

namespace Bakirov\Protokit\Search\Filter\Types;

use Bakirov\Protokit\Search\Filter\SearchFilterType;

class Datetime extends SearchFilterType
{
    public function process(): void
    {
        $this->query->{$this->condition}(
            $this->field,
            date('Y-m-d H:i:s', strtotime($this->value))
        );
    }
}
