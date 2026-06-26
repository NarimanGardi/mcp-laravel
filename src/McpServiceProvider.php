<?php

namespace Gardi\McpLaravel;

use Gardi\McpLaravel\Console\InstallCommand;
use Gardi\McpLaravel\Console\ServeCommand;
use Gardi\McpLaravel\Http\McpController;
use Gardi\McpLaravel\Resources\ToolResource;
use Gardi\McpLaravel\Server\Dispatcher;
use Gardi\McpLaravel\Server\ResourceRegistry;
use Gardi\McpLaravel\Server\StdioServer;
use Gardi\McpLaravel\Server\ToolRegistry;
use Gardi\McpLaravel\Tools\ConfigGetTool;
use Gardi\McpLaravel\Tools\DatabaseQueryTool;
use Gardi\McpLaravel\Tools\DatabaseSchemaTool;
use Gardi\McpLaravel\Tools\DescribeModelTool;
use Gardi\McpLaravel\Tools\DescribeTableTool;
use Gardi\McpLaravel\Tools\ExplainQueryTool;
use Gardi\McpLaravel\Tools\ListCommandsTool;
use Gardi\McpLaravel\Tools\ListModelsTool;
use Gardi\McpLaravel\Tools\ListRoutesTool;
use Gardi\McpLaravel\Tools\MigrationStatusTool;
use Gardi\McpLaravel\Tools\ModelQueryTool;
use Gardi\McpLaravel\Tools\RelationshipGraphTool;
use Gardi\McpLaravel\Tools\TailLogsTool;
use Illuminate\Support\ServiceProvider;

class McpServiceProvider extends ServiceProvider
{
    public const VERSION = '0.5.0';

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
                'config_get' => fn () => new ConfigGetTool($config['redact_keys'] ?? []),
                'migration_status' => fn () => new MigrationStatusTool($app->make('migrator'), $config['migrations_path']),
                'list_commands' => fn () => new ListCommandsTool($app->make(\Illuminate\Contracts\Console\Kernel::class)),
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

        $this->app->singleton(ResourceRegistry::class, function ($app) {
            $config = $app['config']->get('mcp');
            $enabled = $config['resources'] ?? [];

            $factories = [
                'schema' => fn () => new ToolResource(
                    'laravel://schema',
                    'Database schema',
                    'Every table with its columns — the whole database schema.',
                    new DatabaseSchemaTool($config['database']['connection']),
                ),
                'routes' => fn () => new ToolResource(
                    'laravel://routes',
                    'HTTP routes',
                    'Every registered route: method, URI, name, action and middleware.',
                    new ListRoutesTool($app['router']),
                ),
                'models' => fn () => new ToolResource(
                    'laravel://models',
                    'Model relationship graph',
                    'Every Eloquent model and its relationships, as a graph.',
                    new RelationshipGraphTool($config['models_path'], $config['models_namespace']),
                ),
            ];

            $registry = new ResourceRegistry;

            foreach ($factories as $key => $factory) {
                if (($enabled[$key] ?? false) === true) {
                    $registry->register($factory());
                }
            }

            return $registry;
        });

        $this->app->singleton(Dispatcher::class, fn ($app) => new Dispatcher(
            $app->make(ToolRegistry::class),
            $app->make(ResourceRegistry::class),
            'mcp-laravel',
            self::VERSION,
        ));

        $this->app->singleton(StdioServer::class, fn ($app) => new StdioServer($app->make(Dispatcher::class)));
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([ServeCommand::class, InstallCommand::class]);

            $this->publishes([
                __DIR__.'/../config/mcp.php' => $this->app->configPath('mcp.php'),
            ], 'mcp-config');
        }

        $config = $this->app['config'];

        // HTTP transport is opt-in and refuses to register without a token.
        if ($config->get('mcp.http.enabled') && $config->get('mcp.http.token')) {
            $this->app['router']->post(
                $config->get('mcp.http.path', 'mcp'),
                McpController::class,
            )->name('mcp.http');
        }
    }
}
