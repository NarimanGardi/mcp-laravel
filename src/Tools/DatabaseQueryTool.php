<?php

namespace Gardi\McpLaravel\Tools;

use Gardi\McpLaravel\Tools\Concerns\IsReadOnly;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Runs a single read-only SELECT and returns the rows.
 *
 * Two layers of protection: the query is validated as a single read-only
 * statement before it runs, and it executes inside a transaction that is
 * always rolled back. This is still not a substitute for pointing the tool
 * at a least-privilege, read-only database user — do that in production.
 */
class DatabaseQueryTool implements Tool
{
    use IsReadOnly;

    private const FORBIDDEN = [
        'insert', 'update', 'delete', 'drop', 'alter', 'truncate',
        'create', 'replace', 'grant', 'revoke', 'attach', 'pragma', 'into',
    ];

    public function __construct(
        protected ?string $connection = null,
        protected int $defaultLimit = 100,
        protected int $maxLimit = 1000,
    ) {
    }

    public function name(): string
    {
        return 'database_query';
    }

    public function description(): string
    {
        return 'Run a single read-only SELECT query and return the rows. Writes are rejected.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => [
                    'type' => 'string',
                    'description' => 'A single SELECT (or WITH ... SELECT) statement. No semicolons, no writes.',
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => "Max rows to return (default {$this->defaultLimit}, max {$this->maxLimit}).",
                ],
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

        $this->assertReadOnly($sql);

        $limit = (int) ($arguments['limit'] ?? $this->defaultLimit);
        $limit = max(1, min($limit, $this->maxLimit));

        $connection = DB::connection($this->connection);

        $connection->beginTransaction();

        try {
            $rows = $connection->select($sql);
        } finally {
            $connection->rollBack();
        }

        $rows = array_slice($rows, 0, $limit);

        return json_encode([
            'rowCount' => count($rows),
            'truncated' => count($rows) === $limit,
            'rows' => $rows,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    protected function assertReadOnly(string $sql): void
    {
        if (str_contains(rtrim($sql, "; \t\n\r"), ';')) {
            throw new InvalidArgumentException('Only a single statement is allowed (no semicolons).');
        }

        if (! preg_match('/^\s*(select|with)\b/i', $sql)) {
            throw new InvalidArgumentException('Only SELECT (or WITH ... SELECT) queries are allowed.');
        }

        if (preg_match('/\b('.implode('|', self::FORBIDDEN).')\b/i', $sql)) {
            throw new InvalidArgumentException('Query contains a forbidden keyword; only read-only queries are allowed.');
        }
    }
}
