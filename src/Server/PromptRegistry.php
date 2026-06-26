<?php

namespace Gardi\McpLaravel\Server;

use Gardi\McpLaravel\Prompts\Prompt;

class PromptRegistry
{
    /** @var array<string, Prompt> */
    protected array $prompts = [];

    public function register(Prompt $prompt): void
    {
        $this->prompts[$prompt->name()] = $prompt;
    }

    public function get(string $name): ?Prompt
    {
        return $this->prompts[$name] ?? null;
    }

    /** @return list<array{name: string, description: string, arguments: array}> */
    public function list(): array
    {
        return array_map(fn (Prompt $prompt) => [
            'name' => $prompt->name(),
            'description' => $prompt->description(),
            'arguments' => $prompt->arguments(),
        ], array_values($this->prompts));
    }
}
