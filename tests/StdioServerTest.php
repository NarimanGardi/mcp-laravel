<?php

use Gardi\McpLaravel\Server\Dispatcher;
use Gardi\McpLaravel\Server\StdioServer;
use Gardi\McpLaravel\Server\ToolRegistry;

it('reads requests from stdin and writes responses to stdout', function () {
    $in = fopen('php://memory', 'r+');
    fwrite($in, json_encode(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'ping'])."\n");
    fwrite($in, json_encode(['jsonrpc' => '2.0', 'method' => 'notifications/initialized'])."\n");
    rewind($in);

    $out = fopen('php://memory', 'r+');

    (new StdioServer(new Dispatcher(new ToolRegistry)))->run($in, $out);

    rewind($out);
    $lines = array_values(array_filter(explode("\n", trim(stream_get_contents($out)))));

    // The request (ping) gets exactly one response; the notification stays silent.
    expect($lines)->toHaveCount(1)
        ->and(json_decode($lines[0], true)['id'])->toBe(1);
});
