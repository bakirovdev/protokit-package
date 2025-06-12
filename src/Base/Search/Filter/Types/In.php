<?php

namespace Bakirov\Protokit\Base\Search\Filter\Types;

use Bakirov\Protokit\Base\Search\Filter\SearchFilterType;

class In extends SearchFilterType
{
    public function process(): void
    {
        $this->value = (array)$this->value;

        $this->query->{$this->condition . 'In'}(
            $this->field,
            $this->value
        );
    }
}
