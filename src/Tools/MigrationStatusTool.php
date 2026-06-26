<?php

namespace Gardi\McpLaravel\Tools;

use Gardi\McpLaravel\Tools\Concerns\IsReadOnly;
use Illuminate\Database\Migrations\Migrator;

/**
 * Lists migration files and whether each has run, by comparing the migrations
 * directory against the migration repository (the `migrations` table).
 */
class MigrationStatusTool implements Tool
{
    use IsReadOnly;

    public function __construct(
        protected Migrator $migrator,
        protected string $migrationsPath,
    ) {
    }

    public function name(): string
    {
        return 'migration_status';
    }

    public function description(): string
    {
        return 'List migrations and whether each has run (ran vs pending), with batch numbers.';
    }

    public function inputSchema(): array
    {
        return ['type' => 'object', 'properties' => (object) []];
    }

    public function handle(array $arguments): string
    {
        $repository = $this->migrator->getRepository();
        $tableExists = $repository->repositoryExists();

        $ran = $tableExists ? $repository->getRan() : [];
        $batches = $tableExists ? $repository->getMigrationBatches() : [];

        $files = array_keys($this->migrator->getMigrationFiles($this->migrationsPath));
        sort($files);

        $migrations = [];
        $ranCount = 0;

        foreach ($files as $name) {
            $hasRun = in_array($name, $ran, true);
            $ranCount += $hasRun ? 1 : 0;

            $migrations[] = [
                'migration' => $name,
                'ran' => $hasRun,
                'batch' => $hasRun ? ($batches[$name] ?? null) : null,
            ];
        }

        return json_encode([
            'tableExists' => $tableExists,
            'ranCount' => $ranCount,
            'pendingCount' => count($files) - $ranCount,
            'migrations' => $migrations,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
