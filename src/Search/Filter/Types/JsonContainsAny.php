<?php

namespace Bakirov\Protokit\Search\Filter\Types;

use Bakirov\Protokit\Search\Filter\SearchFilterType;

class JsonContainsAny extends SearchFilterType
{
    public function process(): void
    {
        $this->value = (array)$this->value;

        $this->query->{$this->condition}(function ($query) {
            foreach ($this->value as $v) {
                $query->orWhereJsonContains($this->field, $v);
            }
        });
    }
}
