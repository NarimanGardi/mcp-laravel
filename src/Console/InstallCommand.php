<?php

namespace Gardi\McpLaravel\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'mcp:install {--print : Only print the client config, do not publish anything}';

    protected $description = 'Publish the mcp-laravel config and print the MCP client configuration snippet.';

    public function handle(): int
    {
        if (! $this->option('print')) {
            $this->callSilent('vendor:publish', ['--tag' => 'mcp-config']);
            $this->info('Published config to config/mcp.php');
        }

        $snippet = json_encode([
            'mcpServers' => [
                'laravel' => [
                    'command' => 'php',
                    'args' => ['artisan', 'mcp:serve'],
                    'cwd' => $this->laravel->basePath(),
                ],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $this->newLine();
        $this->line('Add this to your MCP client (Claude Code / Cursor / Windsurf):');
        $this->newLine();
        $this->line($snippet);

        return self::SUCCESS;
    }
}
