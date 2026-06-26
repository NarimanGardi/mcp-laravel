<?php

namespace Gardi\McpLaravel\Tools;

use Gardi\McpLaravel\Tools\Concerns\DiscoversModels;
use Gardi\McpLaravel\Tools\Concerns\IsReadOnly;

class ListModelsTool implements Tool
{
    use DiscoversModels;
    use IsReadOnly;

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

        $models = array_map(
            fn (string $class) => ['class' => $class, 'table' => (new $class)->getTable()],
            $this->discoverModels($this->modelsPath, $this->modelsNamespace),
        );

        return json_encode(['models' => $models], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
