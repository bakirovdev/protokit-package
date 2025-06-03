<?php

namespace Bakirov\Protokit\Search\Filter\Types;

use Bakirov\Protokit\Search\Filter\SearchFilterType;

class JsonContainsAll extends SearchFilterType
{
    public function process(): void
    {
        $this->value = (array)$this->value;

        $this->query->{$this->condition}(function ($query) {
            foreach ($this->value as $v) {
                $query->whereJsonContains($this->field, $v);
            }
        });
    }
}
