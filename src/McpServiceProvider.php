<?php

namespace Gardi\McpLaravel;

use Gardi\McpLaravel\Console\ServeCommand;
use Gardi\McpLaravel\Server\StdioServer;
use Gardi\McpLaravel\Server\ToolRegistry;
use Gardi\McpLaravel\Tools\DatabaseQueryTool;
use Gardi\McpLaravel\Tools\DescribeModelTool;
use Gardi\McpLaravel\Tools\ListModelsTool;
use Gardi\McpLaravel\Tools\ListRoutesTool;
use Illuminate\Support\ServiceProvider;

class McpServiceProvider extends ServiceProvider
{
    public const VERSION = '0.1.0';

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/mcp.php', 'mcp');

        $this->app->singleton(ToolRegistry::class, function ($app) {
            $config = $app['config']->get('mcp');
            $enabled = $config['tools'] ?? [];

            $factories = [
                'list_routes' => fn () => new ListRoutesTool($app['router']),
                'list_models' => fn () => new ListModelsTool($config['models_path'], $config['models_namespace']),
                'describe_model' => fn () => new DescribeModelTool($config['models_namespace']),
                'database_query' => fn () => new DatabaseQueryTool(
                    $config['database']['connection'],
                    $config['database']['default_limit'],
                    $config['database']['max_limit'],
                ),
            ];

            $registry = new ToolRegistry;

            foreach ($factories as $name => $factory) {
                if (($enabled[$name] ?? false) === true) {
                    $registry->register($factory());
                }
            }

            return $registry;
        });

        $this->app->singleton(StdioServer::class, fn ($app) => new StdioServer(
            $app->make(ToolRegistry::class),
            'mcp-laravel',
            self::VERSION,
        ));
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([ServeCommand::class]);

            $this->publishes([
                __DIR__.'/../config/mcp.php' => $this->app->configPath('mcp.php'),
            ], 'mcp-config');
        }
    }
}
