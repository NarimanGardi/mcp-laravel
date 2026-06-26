<?php

namespace Gardi\McpLaravel\Tools;

use Gardi\McpLaravel\Tools\Concerns\IsReadOnly;
use Gardi\McpLaravel\Tools\Concerns\ResolvesModel;
use InvalidArgumentException;

/**
 * Runs a read-only Eloquent query. There is no raw SQL: the agent supplies a
 * model and structured constraints, so it can only read records the model
 * already exposes. Still returns row data, so it is opt-in via config.
 */
class ModelQueryTool implements Tool
{
    use IsReadOnly;
    use ResolvesModel;

    public function __construct(
        protected string $modelsNamespace,
        protected int $defaultLimit = 50,
        protected int $maxLimit = 500,
    ) {
    }

    public function name(): string
    {
        return 'model_query';
    }

    public function description(): string
    {
        return 'Run a read-only Eloquent query: equality filters, selected columns, eager-loaded relations, ordering and a row limit.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'model' => ['type' => 'string', 'description' => 'Model class — short name (e.g. "User") or fully-qualified.'],
                'filters' => ['type' => 'object', 'description' => 'Column => value pairs, combined with AND and matched with "=".'],
                'columns' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Columns to select (default: all).'],
                'with' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Relations to eager-load.'],
                'order_by' => ['type' => 'string'],
                'direction' => ['type' => 'string', 'enum' => ['asc', 'desc']],
                'limit' => ['type' => 'integer', 'description' => "Max rows (default {$this->defaultLimit}, max {$this->maxLimit})."],
            ],
            'required' => ['model'],
        ];
    }

    public function handle(array $arguments): string
    {
        $input = trim((string) ($arguments['model'] ?? ''));

        if ($input === '') {
            throw new InvalidArgumentException('The "model" argument is required.');
        }

        $class = $this->resolveModelClass($input, $this->modelsNamespace);

        if ($class === null) {
            throw new InvalidArgumentException("Model not found: {$input}");
        }

        $query = $class::query();

        foreach ($this->scalarMap($arguments['filters'] ?? []) as $column => $value) {
            $query->where($column, $value);
        }

        if (($columns = $this->stringList($arguments['columns'] ?? [])) !== []) {
            $query->select($columns);
        }

        if (($with = $this->stringList($arguments['with'] ?? [])) !== []) {
            $query->with($with);
        }

        if (! empty($arguments['order_by'])) {
            $direction = strtolower((string) ($arguments['direction'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
            $query->orderBy((string) $arguments['order_by'], $direction);
        }

        $limit = max(1, min((int) ($arguments['limit'] ?? $this->defaultLimit), $this->maxLimit));

        $records = $query->limit($limit)->get();

        return json_encode([
            'model' => $class,
            'rowCount' => $records->count(),
            'truncated' => $records->count() === $limit,
            'records' => $records->toArray(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /** @return array<string, scalar> */
    protected function scalarMap(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $out = [];

        foreach ($value as $key => $item) {
            if (is_string($key) && is_scalar($item)) {
                $out[$key] = $item;
            }
        }

        return $out;
    }

    /** @return list<string> */
    protected function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, 'is_string'));
    }
}
