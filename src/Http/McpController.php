<?php

namespace Gardi\McpLaravel\Http;

use Gardi\McpLaravel\Server\Dispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * The HTTP transport: a single endpoint that accepts a JSON-RPC message, checks
 * a bearer token, dispatches it, and returns the response as JSON (or 202 for a
 * notification). Off by default — enable it, and set a token, in the config.
 */
class McpController
{
    public function __construct(protected Dispatcher $dispatcher)
    {
    }

    public function __invoke(Request $request): Response|JsonResponse
    {
        $token = config('mcp.http.token');

        if (! is_string($token) || $token === '' || ! hash_equals($token, (string) $request->bearerToken())) {
            return new JsonResponse([
                'jsonrpc' => '2.0',
                'id' => null,
                'error' => ['code' => -32001, 'message' => 'Unauthorized'],
            ], 401);
        }

        $response = $this->dispatcher->dispatchLine($request->getContent());

        // Notifications get acknowledged with no body.
        if ($response === null) {
            return new Response('', 202);
        }

        return new JsonResponse($response);
    }
}
