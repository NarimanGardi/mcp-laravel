<?php

namespace Gardi\McpLaravel\Server;

use Throwable;

/**
 * A minimal MCP server speaking JSON-RPC 2.0 over newline-delimited stdio.
 *
 * Only the methods a tools-only server needs are implemented: initialize,
 * tools/list, tools/call and ping. Notifications (messages without an id)
 * get no reply, per the JSON-RPC spec.
 */
class StdioServer
{
    public function __construct(
        protected ToolRegistry $tools,
        protected string $serverName = 'mcp-laravel',
        protected string $serverVersion = '0.1.0',
    ) {
    }

    /**
     * @param  resource  $in
     * @param  resource  $out
     */
    public function run($in = STDIN, $out = STDOUT): void
    {
        while (($line = fgets($in)) !== false) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $response = $this->handleLine($line);

            if ($response !== null) {
                fwrite($out, json_encode($response, JSON_UNESCAPED_SLASHES)."\n");
                fflush($out);
            }
        }
    }

    /**
     * Handle one JSON-RPC message. Returns null for notifications (no reply).
     */
    public function handleLine(string $line): ?array
    {
        $message = json_decode($line, true);

        if (! is_array($message)) {
            return $this->error(null, -32700, 'Parse error');
        }

        $id = $message['id'] ?? null;
        $method = $message['method'] ?? null;
        $params = is_array($message['params'] ?? null) ? $message['params'] : [];

        // No id => notification. Acknowledge by staying silent.
        if ($id === null) {
            return null;
        }

        try {
            return match ($method) {
                'initialize' => $this->success($id, $this->initialize($params)),
                'tools/list' => $this->success($id, ['tools' => $this->tools->schemas()]),
                'tools/call' => $this->success($id, $this->callTool($params)),
                'ping' => $this->success($id, (object) []),
                default => $this->error($id, -32601, "Method not found: {$method}"),
            };
        } catch (Throwable $e) {
            return $this->error($id, -32603, $e->getMessage());
        }
    }

    protected function initialize(array $params): array
    {
        return [
            'protocolVersion' => $params['protocolVersion'] ?? '2024-11-05',
            'capabilities' => ['tools' => ['listChanged' => false]],
            'serverInfo' => ['name' => $this->serverName, 'version' => $this->serverVersion],
        ];
    }

    protected function callTool(array $params): array
    {
        $name = (string) ($params['name'] ?? '');
        $arguments = is_array($params['arguments'] ?? null) ? $params['arguments'] : [];

        $tool = $this->tools->get($name);

        if ($tool === null) {
            return $this->toolError("Unknown tool: {$name}");
        }

        try {
            return ['content' => [['type' => 'text', 'text' => $tool->handle($arguments)]]];
        } catch (Throwable $e) {
            // Tool failures are reported in-band so the agent can recover,
            // not as protocol errors.
            return $this->toolError($e->getMessage());
        }
    }

    protected function toolError(string $message): array
    {
        return [
            'content' => [['type' => 'text', 'text' => $message]],
            'isError' => true,
        ];
    }

    protected function success(int|string $id, mixed $result): array
    {
        return ['jsonrpc' => '2.0', 'id' => $id, 'result' => $result];
    }

    protected function error(int|string|null $id, int $code, string $message): array
    {
        return ['jsonrpc' => '2.0', 'id' => $id, 'error' => ['code' => $code, 'message' => $message]];
    }
}
