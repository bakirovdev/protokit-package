<?php

namespace Bakirov\Protokit\Enums;

enum HttpComponentClassEnum: string
{
    case Controller = 'Controller';
    case Request = 'Request';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }
}
