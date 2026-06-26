<?php

use Gardi\McpLaravel\Http\McpController;
use Gardi\McpLaravel\Server\Dispatcher;
use Gardi\McpLaravel\Server\ToolRegistry;
use Illuminate\Http\Request;

beforeEach(fn () => config()->set('mcp.http.token', 'secret'));

function httpPost(string $body, ?string $token): \Symfony\Component\HttpFoundation\Response
{
    $server = $token !== null ? ['HTTP_AUTHORIZATION' => "Bearer {$token}"] : [];
    $request = Request::create('/mcp', 'POST', [], [], [], $server, $body);

    return (new McpController(new Dispatcher(new ToolRegistry)))($request);
}

it('rejects a missing token with 401', function () {
    expect(httpPost('{"jsonrpc":"2.0","id":1,"method":"ping"}', null)->getStatusCode())->toBe(401);
});

it('rejects a wrong token with 401', function () {
    expect(httpPost('{"jsonrpc":"2.0","id":1,"method":"ping"}', 'nope')->getStatusCode())->toBe(401);
});

it('dispatches an authorized request', function () {
    $res = httpPost('{"jsonrpc":"2.0","id":1,"method":"ping"}', 'secret');

    expect($res->getStatusCode())->toBe(200)
        ->and(json_decode($res->getContent(), true)['id'])->toBe(1);
});

it('returns 202 for a notification', function () {
    $res = httpPost('{"jsonrpc":"2.0","method":"notifications/initialized"}', 'secret');

    expect($res->getStatusCode())->toBe(202);
});
