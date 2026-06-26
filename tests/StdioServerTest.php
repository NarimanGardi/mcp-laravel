<?php

use Gardi\McpLaravel\Server\StdioServer;
use Gardi\McpLaravel\Server\ToolRegistry;
use Gardi\McpLaravel\Tools\ListRoutesTool;

function rpc(string $method, array $params = [], int|string|null $id = 1): string
{
    return json_encode(array_filter([
        'jsonrpc' => '2.0',
        'id' => $id,
        'method' => $method,
        'params' => $params ?: null,
    ], fn ($v) => $v !== null));
}

it('returns serverInfo on initialize', function () {
    $server = new StdioServer(new ToolRegistry);

    $response = $server->handleLine(rpc('initialize', ['protocolVersion' => '2024-11-05']));

    expect($response['result']['serverInfo']['name'])->toBe('mcp-laravel')
        ->and($response['result']['protocolVersion'])->toBe('2024-11-05')
        ->and($response['result']['capabilities'])->toHaveKey('tools');
});

it('stays silent on notifications (no id)', function () {
    $server = new StdioServer(new ToolRegistry);

    expect($server->handleLine(rpc('notifications/initialized', id: null)))->toBeNull();
});

it('lists registered tools', function () {
    $registry = new ToolRegistry;
    $registry->register(new ListRoutesTool(app('router')));

    $server = new StdioServer($registry);
    $response = $server->handleLine(rpc('tools/list', id: 2));

    $names = array_column($response['result']['tools'], 'name');

    expect($names)->toContain('list_routes');
});

it('calls a tool and returns text content', function () {
    $registry = new ToolRegistry;
    $registry->register(new ListRoutesTool(app('router')));

    $server = new StdioServer($registry);
    $response = $server->handleLine(
        rpc('tools/call', ['name' => 'list_routes', 'arguments' => []], id: 3)
    );

    expect($response['result']['content'][0]['type'])->toBe('text')
        ->and($response['result'])->not->toHaveKey('isError');
});

it('reports an unknown tool as an in-band error', function () {
    $server = new StdioServer(new ToolRegistry);

    $response = $server->handleLine(
        rpc('tools/call', ['name' => 'nope', 'arguments' => []], id: 4)
    );

    expect($response['result']['isError'])->toBeTrue();
});

it('returns method-not-found for unknown methods', function () {
    $server = new StdioServer(new ToolRegistry);

    $response = $server->handleLine(rpc('does/not/exist', id: 5));

    expect($response['error']['code'])->toBe(-32601);
});
