<?php

namespace Gardi\McpLaravel\Tools;

use Gardi\McpLaravel\Tools\Concerns\IsReadOnly;
use Illuminate\Routing\Router;

class ListRoutesTool implements Tool
{
    use IsReadOnly;

    public function __construct(protected Router $router)
    {
    }

    public function name(): string
    {
        return 'list_routes';
    }

    public function description(): string
    {
        return "List the application's HTTP routes: methods, URI, name, action and middleware.";
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'filter' => [
                    'type' => 'string',
                    'description' => 'Optional case-insensitive substring matched against the URI or route name.',
                ],
            ],
        ];
    }

    public function handle(array $arguments): string
    {
        $filter = isset($arguments['filter']) ? strtolower((string) $arguments['filter']) : null;

        $routes = [];

        foreach ($this->router->getRoutes() as $route) {
            $uri = $route->uri();
            $name = (string) $route->getName();

            if ($filter !== null
                && ! str_contains(strtolower($uri), $filter)
                && ! str_contains(strtolower($name), $filter)) {
                continue;
            }

            $routes[] = [
                'methods' => implode('|', array_values(array_diff($route->methods(), ['HEAD']))),
                'uri' => $uri,
                'name' => $name !== '' ? $name : null,
                'action' => ltrim($route->getActionName(), '\\'),
                'middleware' => array_values($route->gatherMiddleware()),
            ];
        }

        return json_encode($routes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
