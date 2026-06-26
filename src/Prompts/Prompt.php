<?php

namespace Gardi\McpLaravel\Prompts;

/**
 * An MCP prompt: a named, parameterised template a client can surface (often as
 * a slash command) to seed a task. These prompts deliberately point the agent at
 * this package's tools, so a one-click prompt turns into grounded, real-app work.
 */
interface Prompt
{
    public function name(): string;

    public function description(): string;

    /** @return list<array{name: string, description: string, required: bool}> */
    public function arguments(): array;

    /**
     * Render the prompt text for the given arguments.
     *
     * @param  array<string, mixed>  $arguments
     */
    public function render(array $arguments): string;
}
