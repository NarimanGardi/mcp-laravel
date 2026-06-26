<?php

namespace Gardi\McpLaravel\Server;

use Gardi\McpLaravel\Resources\Resource;

class ResourceRegistry
{
    /** @var array<string, Resource> */
    protected array $resources = [];

    public function register(Resource $resource): void
    {
        $this->resources[$resource->uri()] = $resource;
    }

    public function get(string $uri): ?Resource
    {
        return $this->resources[$uri] ?? null;
    }

    /** @return list<array{uri: string, name: string, description: string, mimeType: string}> */
    public function list(): array
    {
        return array_map(fn (Resource $resource) => [
            'uri' => $resource->uri(),
            'name' => $resource->name(),
            'description' => $resource->description(),
            'mimeType' => $resource->mimeType(),
        ], array_values($this->resources));
    }
}
