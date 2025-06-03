<?php

namespace Bakirov\Protokit\Search\Enums;

enum SearchFilterConditionEnum: string
{
    case WHERE = 'where';
    case OR_WHERE = 'orWhere';
}
