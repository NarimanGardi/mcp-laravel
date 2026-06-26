<?php

namespace Gardi\McpLaravel\Tools;

use Gardi\McpLaravel\Tools\Concerns\IsReadOnly;
use Illuminate\Contracts\Console\Kernel;
use Throwable;

class ListCommandsTool implements Tool
{
    use IsReadOnly;

    public function __construct(protected Kernel $kernel)
    {
    }

    public function name(): string
    {
        return 'list_commands';
    }

    public function description(): string
    {
        return 'List registered Artisan commands with their descriptions and usage.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'filter' => [
                    'type' => 'string',
                    'description' => 'Optional case-insensitive substring matched against the command name.',
                ],
            ],
        ];
    }

    public function handle(array $arguments): string
    {
        $filter = isset($arguments['filter']) ? strtolower((string) $arguments['filter']) : null;

        $this->kernel->bootstrap();

        $commands = [];

        foreach ($this->kernel->all() as $name => $command) {
            if ($filter !== null && ! str_contains(strtolower($name), $filter)) {
                continue;
            }

            try {
                $synopsis = $command->getSynopsis();
            } catch (Throwable) {
                $synopsis = $name;
            }

            $commands[] = [
                'name' => $name,
                'description' => $command->getDescription(),
                'synopsis' => $synopsis,
            ];
        }

        usort($commands, fn (array $a, array $b) => strcmp($a['name'], $b['name']));

        return json_encode([
            'commandCount' => count($commands),
            'commands' => $commands,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
