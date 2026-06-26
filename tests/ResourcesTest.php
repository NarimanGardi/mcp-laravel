<?php

use Gardi\McpLaravel\Resources\ToolResource;
use Gardi\McpLaravel\Server\Dispatcher;
use Gardi\McpLaravel\Server\ResourceRegistry;
use Gardi\McpLaravel\Server\ToolRegistry;
use Gardi\McpLaravel\Tools\ListRoutesTool;

function resourceDispatcher(): Dispatcher
{
    $resources = new ResourceRegistry;
    $resources->register(new ToolResource(
        'laravel://routes',
        'HTTP routes',
        'Every registered route.',
        new ListRoutesTool(app('router')),
    ));

    return new Dispatcher(new ToolRegistry, $resources);
}

function resourceCall(Dispatcher $dispatcher, string $method, array $params = [], int $id = 1): array
{
    return $dispatcher->dispatchLine(json_encode(array_filter([
        'jsonrpc' => '2.0',
        'id' => $id,
        'method' => $method,
        'params' => $params ?: null,
    ], fn ($v) => $v !== null)));
}

it('advertises the resources capability', function () {
    $res = resourceCall(resourceDispatcher(), 'initialize', ['protocolVersion' => '2024-11-05']);

    expect($res['result']['capabilities'])->toHaveKey('resources');
});

it('lists resources', function () {
    $res = resourceCall(resourceDispatcher(), 'resources/list', id: 2);

    expect(array_column($res['result']['resources'], 'uri'))->toContain('laravel://routes');
});

it('reads a resource', function () {
    $res = resourceCall(resourceDispatcher(), 'resources/read', ['uri' => 'laravel://routes'], id: 3);

    expect($res['result']['contents'][0]['uri'])->toBe('laravel://routes')
        ->and($res['result']['contents'][0]['mimeType'])->toBe('application/json')
        ->and($res['result']['contents'][0])->toHaveKey('text');
});

it('errors on an unknown resource uri', function () {
    $res = resourceCall(resourceDispatcher(), 'resources/read', ['uri' => 'laravel://nope'], id: 4);

    expect($res)->toHaveKey('error');
});
