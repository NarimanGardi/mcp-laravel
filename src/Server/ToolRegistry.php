<?php

namespace Gardi\McpLaravel\Server;

use Gardi\McpLaravel\Tools\Tool;

class ToolRegistry
{
    /** @var array<string, Tool> */
    protected array $tools = [];

    public function register(Tool $tool): void
    {
        $this->tools[$tool->name()] = $tool;
    }

    public function get(string $name): ?Tool
    {
        return $this->tools[$name] ?? null;
    }

    /** @return list<array{name: string, description: string, inputSchema: array}> */
    public function schemas(): array
    {
        return array_map(fn (Tool $tool) => [
            'name' => $tool->name(),
            'description' => $tool->description(),
            'inputSchema' => $tool->inputSchema(),
        ], array_values($this->tools));
    }
}
