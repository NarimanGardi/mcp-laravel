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

    /** @return list<array<string, mixed>> */
    public function schemas(): array
    {
        return array_map(function (Tool $tool) {
            $schema = [
                'name' => $tool->name(),
                'description' => $tool->description(),
                'inputSchema' => $tool->inputSchema(),
            ];

            if (($annotations = $tool->annotations()) !== []) {
                $schema['annotations'] = $annotations;
            }

            return $schema;
        }, array_values($this->tools));
    }
}
