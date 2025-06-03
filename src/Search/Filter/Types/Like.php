<?php

namespace Bakirov\Protokit\Search\Filter\Types;

use Bakirov\Protokit\Search\Filter\SearchFilterType;
use Illuminate\Support\Facades\DB;

class Like extends SearchFilterType
{
    public function process(): void
    {
        $this->query->{$this->condition}(
            DB::raw("LOWER($this->field::TEXT)"),
            'LIKE',
            '%' . mb_strtolower((string)$this->value) . '%',
        );
    }
}
