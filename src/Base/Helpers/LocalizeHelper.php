<?php

namespace Bakirov\Protokit\Base\Helpers;

class LocalizeHelper {

    public static function localizeString(string $string): string
    {
        $languages = config('protokit.languages');

        $locales = array_keys($languages);
        $locales = array_combine($locales, $locales);

        $result = array_map(fn ($locale) => "$string $locale", $locales);

        return json_encode($result);
    }

}
