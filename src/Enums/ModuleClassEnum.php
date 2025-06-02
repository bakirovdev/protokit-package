<?php

namespace Bakirov\Protokit\Enums;

enum ModuleClassEnum: string
{
    case Model = 'Model';
    case Service = 'Service';
    case Observer = 'Observer';
    case Resource = 'Resource';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }
}
