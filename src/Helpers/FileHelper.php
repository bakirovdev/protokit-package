<?php

namespace Bakirov\Protokit\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Http\Testing\File as TestingFile;

class FileHelper
{
    public static function save(string $path, string $content, ?string $extension = null): string
    {
        $path = "storage/uploads/$path/" . date('Y-m-d');
        $name = uniqid() . '_' . Str::uuid();
        $name .= $extension ? ".$extension" : '';

        $fullPath = public_path($path);

        if (!is_dir($fullPath)) {
            mkdir($fullPath);
        }

        file_put_contents("$fullPath/$name", $content);

        return "$path/$name";
    }

    public static function upload(UploadedFile $file, string $path): string
    {
        $path = "storage/uploads/$path/" . date('Y-m-d');
        $extension = $file->getClientOriginalExtension();
        $name = uniqid() . '_' . Str::uuid();
        $name .= $extension ? ".$extension" : '';

        $fullPath = public_path($path);

        if (!is_dir($fullPath)) {
            mkdir($fullPath);
        }

        $file->move($fullPath, $name);
        return "$path/$name";
    }

    public static function delete(string $file): void
    {
        File::delete($file);
    }

    public static function faker(
        string $name = 'image.jpg',
        string $mime = 'image/jpeg',
        int $size = 100,
        int $quantity = 1,
    ): TestingFile|array {
        if ($quantity === 1) {
            return UploadedFile::fake()->create($name, $size, $mime);
        } else {
            return array_map(fn ($value) => UploadedFile::fake()->create($name, $size, $mime), range(1, $quantity));
        }
    }

}
