<?php

namespace Gardi\McpLaravel\Tools;

use Gardi\McpLaravel\Tools\Concerns\IsReadOnly;
use Illuminate\Support\Facades\Schema;

class DatabaseSchemaTool implements Tool
{
    use IsReadOnly;

    public function __construct(protected ?string $connection = null)
    {
    }

    public function name(): string
    {
        return 'database_schema';
    }

    public function description(): string
    {
        return 'List every table in the database with its columns (name and type) — a whole-schema overview.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'connection' => ['type' => 'string', 'description' => 'Optional database connection name.'],
            ],
        ];
    }

    public function handle(array $arguments): string
    {
        $schema = Schema::connection($arguments['connection'] ?? $this->connection);

        $tables = [];

        // false => unqualified table names ("posts", not "main.posts"); Laravel 12
        // qualifies by default, older versions ignore the extra argument.
        foreach ($schema->getTableListing(null, false) as $name) {
            $tables[] = [
                'name' => $name,
                'columns' => array_map(fn (array $c) => [
                    'name' => $c['name'],
                    'type' => $c['type_name'] ?? $c['type'] ?? null,
                ], $schema->getColumns($name)),
            ];
        }

        return json_encode([
            'tableCount' => count($tables),
            'tables' => $tables,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
