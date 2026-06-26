<?php

namespace Gardi\McpLaravel\Tools\Concerns;

use InvalidArgumentException;

/**
 * Validates that a SQL string is a single read-only statement. This is a
 * keyword/structure heuristic, not a full SQL parser — pair it with a
 * least-privilege, read-only database user.
 */
trait AssertsReadOnlySql
{
    private const FORBIDDEN = [
        'insert', 'update', 'delete', 'drop', 'alter', 'truncate',
        'create', 'replace', 'grant', 'revoke', 'attach', 'pragma', 'into',
    ];

    protected function assertReadOnlySql(string $sql): void
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
