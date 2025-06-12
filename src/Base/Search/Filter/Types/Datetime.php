<?php

namespace Bakirov\Protokit\Base\Search\Filter\Types;

use Bakirov\Protokit\Base\Search\Filter\SearchFilterType;

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
