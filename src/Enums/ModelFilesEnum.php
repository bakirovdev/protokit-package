<?php

namespace Bakirov\Protokit\Enums;

enum ModelFilesEnum: string
{
    case Model = 'Model';
    case MRT = 'ModelRelationsTrait';
    case MST = 'ModelSafelyTrait';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }
}
