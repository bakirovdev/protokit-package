<?php

namespace Bakirov\Protokit\Base\Search\Filter\Types;

use Bakirov\Protokit\Base\Search\Filter\SearchFilterType;
use Illuminate\Support\Facades\DB;

class EqualString extends SearchFilterType
{
    public function process(): void
    {
        $this->query->{$this->condition}(
            DB::raw("LOWER($this->field::VARCHAR)"),
            mb_strtolower((string)$this->value)
        );
    }
}
