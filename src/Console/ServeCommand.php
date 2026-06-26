<?php

namespace Gardi\McpLaravel\Console;

use Gardi\McpLaravel\Server\StdioServer;
use Illuminate\Console\Command;

class ServeCommand extends Command
{
    protected $signature = 'mcp:serve';

    protected $description = 'Run the MCP server over stdio so AI agents can inspect this Laravel app.';

    public function handle(StdioServer $server): int
    {
        // stdout is the MCP transport channel — nothing else may write to it,
        // so this command intentionally prints nothing of its own.
        $server->run();

        return self::SUCCESS;
    }
}
