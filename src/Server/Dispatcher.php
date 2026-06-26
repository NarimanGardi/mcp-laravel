<?php

namespace Gardi\McpLaravel\Server;

use InvalidArgumentException;
use Throwable;

/**
 * Transport-agnostic MCP message handling (JSON-RPC 2.0). Both the stdio server
 * and the HTTP controller feed messages through here.
 *
 * Only the methods a tools-and-resources server needs are implemented:
 * initialize, tools/list, tools/call, resources/list, resources/read and ping.
 * Notifications (messages without an id) get no reply, per the JSON-RPC spec.
 */
class Dispatcher
{
    public function __construct(
        protected ToolRegistry $tools,
        protected ResourceRegistry $resources = new ResourceRegistry,
        protected string $serverName = 'mcp-laravel',
        protected string $serverVersion = '0.1.0',
    ) {
    }

    /** Decode one JSON-RPC message and handle it. Null = notification (no reply). */
    public function dispatchLine(string $line): ?array
    {
        $message = json_decode($line, true);

        if (! is_array($message)) {
            return $this->error(null, -32700, 'Parse error');
        }

        return $this->dispatch($message);
    }

    /** Handle a decoded JSON-RPC message. Null = notification (no reply). */
    public function dispatch(array $message): ?array
    {
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
                'resources/list' => $this->success($id, ['resources' => $this->resources->list()]),
                'resources/read' => $this->success($id, $this->readResource($params)),
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
            'capabilities' => [
                'tools' => ['listChanged' => false],
                'resources' => ['listChanged' => false],
            ],
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

    protected function readResource(array $params): array
    {
        $uri = (string) ($params['uri'] ?? '');
        $resource = $this->resources->get($uri);

        if ($resource === null) {
            throw new InvalidArgumentException("Unknown resource: {$uri}");
        }

        return [
            'contents' => [[
                'uri' => $resource->uri(),
                'mimeType' => $resource->mimeType(),
                'text' => $resource->read(),
            ]],
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
