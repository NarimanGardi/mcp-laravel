<?php

namespace Gardi\McpLaravel\Tools;

interface Tool
{
    /** The tool name the agent calls (snake_case). */
    public function name(): string;

    /** One sentence the agent reads to decide whether to use the tool. */
    public function description(): string;

    /** JSON Schema describing the tool's arguments. */
    public function inputSchema(): array;

    /**
     * Run the tool and return a text result for the agent.
     *
     * @param  array<string, mixed>  $arguments
     */
    public function handle(array $arguments): string;
}
