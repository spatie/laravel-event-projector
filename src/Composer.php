<?php

namespace Spatie\EventProjector;

use Illuminate\Support\Str;

final class Composer
{
    public static function getAutoloadedFiles($composerJsonPath): array
    {
        if (! file_exists($composerJsonPath)) {
            return [];
        }

        $basePath = Str::before($composerJsonPath, 'composer.json');

        $composerContents = json_decode(file_get_contents($composerJsonPath), true);

        $paths = array_merge(
            $composerContents['autoload']['files'] ?? [],
            $composerContents['autoload-dev']['files'] ?? []
        );

        return array_map(function (string $path) use ($basePath) {
            return realpath($basePath.$path);
        }, $paths);
    }
}
