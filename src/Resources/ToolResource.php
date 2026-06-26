<?php

namespace Gardi\McpLaravel\Resources;

use Gardi\McpLaravel\Tools\Tool;

/**
 * Adapts a read-only Tool into a Resource: reading the resource runs the tool
 * with a fixed set of arguments. This lets the schema, routes and model graph be
 * both callable (tools) and attachable (resources) without duplicating logic.
 */
class ToolResource implements Resource
{
    public function __construct(
        protected string $uri,
        protected string $name,
        protected string $description,
        protected Tool $tool,
        protected array $arguments = [],
        protected string $mimeType = 'application/json',
    ) {
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function mimeType(): string
    {
        return $this->mimeType;
    }

    public function read(): string
    {
        return $this->tool->handle($this->arguments);
    }
}
