<?php

namespace Bakirov\Protokit\Search\Filter\Types;

use Bakirov\Protokit\Search\Filter\SearchFilterType;

class EqualRaw extends SearchFilterType
{
    public function process(): void
    {
        $this->query->{$this->condition}(
            $this->field,
            $this->value
        );
    }
}
