<?php

namespace Gardi\McpLaravel\Tools\Concerns;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Finder\Finder;

/**
 * Finds Eloquent models by scanning a directory. Assumes PSR-4 mapping of the
 * given namespace to that directory (the Laravel default: App\Models => app/Models).
 */
trait DiscoversModels
{
    /** @return list<class-string<Model>> */
    protected function discoverModels(string $path, string $namespace): array
    {
        if (! is_dir($path)) {
            return [];
        }

        $models = [];

        foreach (Finder::create()->files()->in($path)->name('*.php') as $file) {
            $class = $this->modelClassFromFile($file->getRealPath(), $path, $namespace);

            if ($class !== null && is_subclass_of($class, Model::class)) {
                $models[] = $class;
            }
        }

        return $models;
    }

    protected function modelClassFromFile(string $filePath, string $basePath, string $namespace): ?string
    {
        $relative = str_replace(
            [rtrim($basePath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR, '.php'],
            '',
            $filePath
        );

        $class = $namespace.'\\'.str_replace(DIRECTORY_SEPARATOR, '\\', $relative);

        return class_exists($class) ? $class : null;
    }
}
