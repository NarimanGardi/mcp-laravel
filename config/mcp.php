<?php

return [

    /*
     * Which tools the MCP server exposes. database_query is opt-in: it runs
     * read-only SELECTs, but you should still point it at a least-privilege
     * database user before enabling it in any shared environment.
     */
    'tools' => [
        'list_routes' => true,
        'list_models' => true,
        'describe_model' => true,
        'relationship_graph' => true,
        'describe_table' => true,
        'database_schema' => true,
        'explain_query' => true,
        'tail_logs' => true,
        'config_get' => true,
        'migration_status' => true,
        'list_commands' => true,
        'model_query' => env('MCP_MODEL_QUERY', false),
        'database_query' => env('MCP_DATABASE_QUERY', false),
    ],

    /*
     * Where models live and the namespace they map to (Laravel defaults).
     * Change these if your app keeps models elsewhere.
     */
    'models_path' => env('MCP_MODELS_PATH', app_path('Models')),
    'models_namespace' => env('MCP_MODELS_NAMESPACE', 'App\\Models'),
    'migrations_path' => env('MCP_MIGRATIONS_PATH', database_path('migrations')),

    // Extra key substrings config_get should redact, on top of the built-in list.
    'redact_keys' => [],

    'database' => [
        // null uses the app's default connection.
        'connection' => env('MCP_DB_CONNECTION'),
        'default_limit' => 100,
        'max_limit' => 1000,
    ],

    'query' => [
        'default_limit' => 50,
        'max_limit' => 500,
    ],

    'logs' => [
        'path' => storage_path('logs'),
        'default_lines' => 50,
        'max_lines' => 500,
    ],

    /*
     * Read-only resources the server exposes — attachable context for MCP
     * clients that support resources (separate from on-demand tool calls).
     */
    'resources' => [
        'schema' => true,
        'routes' => true,
        'models' => true,
    ],

    /*
     * HTTP transport. Off by default — when enabled, the server is exposed at
     * POST /{path}, guarded by a bearer token. Never enable it without a token.
     */
    'http' => [
        'enabled' => env('MCP_HTTP_ENABLED', false),
        'path' => env('MCP_HTTP_PATH', 'mcp'),
        'token' => env('MCP_HTTP_TOKEN'),
    ],

];
