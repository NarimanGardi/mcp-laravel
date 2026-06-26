<?php

namespace Gardi\McpLaravel\Tools;

use Gardi\McpLaravel\Tools\Concerns\IsReadOnly;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class DescribeTableTool implements Tool
{
    use IsReadOnly;

    public function __construct(protected ?string $connection = null)
    {
    }

    public function name(): string
    {
        return 'describe_table';
    }

    public function description(): string
    {
        return 'Describe a database table: its columns, indexes and foreign keys.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'table' => ['type' => 'string', 'description' => 'Table name.'],
                'connection' => ['type' => 'string', 'description' => 'Optional database connection name.'],
            ],
            'required' => ['table'],
        ];
    }

    public function handle(array $arguments): string
    {
        $table = trim((string) ($arguments['table'] ?? ''));

        if ($table === '') {
            throw new InvalidArgumentException('The "table" argument is required.');
        }

        $schema = Schema::connection($arguments['connection'] ?? $this->connection);

        if (! $schema->hasTable($table)) {
            throw new InvalidArgumentException("Table not found: {$table}");
        }

        return json_encode([
            'table' => $table,
            'columns' => array_map(fn (array $c) => [
                'name' => $c['name'],
                'type' => $c['type_name'] ?? $c['type'] ?? null,
                'nullable' => $c['nullable'] ?? null,
                'default' => $c['default'] ?? null,
            ], $schema->getColumns($table)),
            'indexes' => array_map(fn (array $i) => [
                'name' => $i['name'] ?? null,
                'columns' => $i['columns'] ?? [],
                'unique' => $i['unique'] ?? null,
                'primary' => $i['primary'] ?? null,
            ], $schema->getIndexes($table)),
            'foreignKeys' => array_map(fn (array $f) => [
                'columns' => $f['columns'] ?? [],
                'foreignTable' => $f['foreign_table'] ?? null,
                'foreignColumns' => $f['foreign_columns'] ?? [],
                'onDelete' => $f['on_delete'] ?? null,
                'onUpdate' => $f['on_update'] ?? null,
            ], $schema->getForeignKeys($table)),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
