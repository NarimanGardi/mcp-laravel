<?php

namespace Gardi\McpLaravel\Tools;

use Gardi\McpLaravel\Tools\Concerns\AssertsReadOnlySql;
use Gardi\McpLaravel\Tools\Concerns\IsReadOnly;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Returns the query plan (EXPLAIN) for a read-only SELECT without running it.
 * Plain EXPLAIN plans the statement; it never executes it (no EXPLAIN ANALYZE).
 */
class ExplainQueryTool implements Tool
{
    use AssertsReadOnlySql;
    use IsReadOnly;

    public function __construct(protected ?string $connection = null)
    {
    }

    public function name(): string
    {
        return 'explain_query';
    }

    public function description(): string
    {
        return 'Return the query plan (EXPLAIN) for a read-only SELECT, without running it.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => ['type' => 'string', 'description' => 'A single SELECT (or WITH ... SELECT) statement.'],
                'connection' => ['type' => 'string', 'description' => 'Optional database connection name.'],
            ],
            'required' => ['query'],
        ];
    }

    public function handle(array $arguments): string
    {
        $sql = trim((string) ($arguments['query'] ?? ''));

        if ($sql === '') {
            throw new InvalidArgumentException('The "query" argument is required.');
        }

        $this->assertReadOnlySql($sql);

        $connection = DB::connection($arguments['connection'] ?? $this->connection);
        $driver = $connection->getDriverName();
        $prefix = $driver === 'sqlite' ? 'EXPLAIN QUERY PLAN ' : 'EXPLAIN ';

        return json_encode([
            'driver' => $driver,
            'plan' => $connection->select($prefix.$sql),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
