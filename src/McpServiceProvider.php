<?php

namespace Gardi\McpLaravel;

use Gardi\McpLaravel\Console\InstallCommand;
use Gardi\McpLaravel\Console\ServeCommand;
use Gardi\McpLaravel\Server\StdioServer;
use Gardi\McpLaravel\Server\ToolRegistry;
use Gardi\McpLaravel\Tools\DatabaseQueryTool;
use Gardi\McpLaravel\Tools\DatabaseSchemaTool;
use Gardi\McpLaravel\Tools\DescribeModelTool;
use Gardi\McpLaravel\Tools\DescribeTableTool;
use Gardi\McpLaravel\Tools\ExplainQueryTool;
use Gardi\McpLaravel\Tools\ListModelsTool;
use Gardi\McpLaravel\Tools\ListRoutesTool;
use Gardi\McpLaravel\Tools\ModelQueryTool;
use Gardi\McpLaravel\Tools\RelationshipGraphTool;
use Gardi\McpLaravel\Tools\TailLogsTool;
use Illuminate\Support\ServiceProvider;

class McpServiceProvider extends ServiceProvider
{
    public const VERSION = '0.2.0';

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
                'relationship_graph' => fn () => new RelationshipGraphTool($config['models_path'], $config['models_namespace']),
                'describe_table' => fn () => new DescribeTableTool($config['database']['connection']),
                'database_schema' => fn () => new DatabaseSchemaTool($config['database']['connection']),
                'explain_query' => fn () => new ExplainQueryTool($config['database']['connection']),
                'tail_logs' => fn () => new TailLogsTool(
                    $config['logs']['path'],
                    $config['logs']['default_lines'],
                    $config['logs']['max_lines'],
                ),
                'model_query' => fn () => new ModelQueryTool(
                    $config['models_namespace'],
                    $config['query']['default_limit'],
                    $config['query']['max_limit'],
                ),
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
            $this->commands([ServeCommand::class, InstallCommand::class]);

            $this->publishes([
                __DIR__.'/../config/mcp.php' => $this->app->configPath('mcp.php'),
            ], 'mcp-config');
        }
    }
}
