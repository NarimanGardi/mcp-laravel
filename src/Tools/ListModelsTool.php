<?php

namespace Gardi\McpLaravel\Tools;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Finder\Finder;

/**
 * Discovers Eloquent models by scanning the configured models directory.
 * Assumes PSR-4 mapping of the configured namespace to that directory
 * (the Laravel default: App\Models => app/Models).
 */
class ListModelsTool implements Tool
{
    public function __construct(
        protected string $modelsPath,
        protected string $modelsNamespace,
    ) {
    }

    public function name(): string
    {
        return 'list_models';
    }

    public function description(): string
    {
        return 'List Eloquent models in the application with their database table names.';
    }

    public function inputSchema(): array
    {
        return ['type' => 'object', 'properties' => (object) []];
    }

    public function handle(array $arguments): string
    {
        if (! is_dir($this->modelsPath)) {
            return json_encode([
                'models' => [],
                'note' => "Models directory not found: {$this->modelsPath}",
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        $models = [];

        foreach (Finder::create()->files()->in($this->modelsPath)->name('*.php') as $file) {
            $class = $this->classFromFile($file->getRealPath());

            if ($class === null || ! is_subclass_of($class, Model::class)) {
                continue;
            }

            /** @var Model $instance */
            $instance = new $class;

            $models[] = ['class' => $class, 'table' => $instance->getTable()];
        }

        return json_encode(['models' => $models], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    protected function classFromFile(string $path): ?string
    {
        $relative = str_replace(
            [rtrim($this->modelsPath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR, '.php'],
            '',
            $path
        );

        $class = $this->modelsNamespace.'\\'.str_replace(DIRECTORY_SEPARATOR, '\\', $relative);

        return class_exists($class) ? $class : null;
    }
}
