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
        'database_query' => env('MCP_DATABASE_QUERY', false),
    ],

    /*
     * Where models live and the namespace they map to (Laravel defaults).
     * Change these if your app keeps models elsewhere.
     */
    'models_path' => app_path('Models'),
    'models_namespace' => 'App\\Models',

    'database' => [
        // null uses the app's default connection.
        'connection' => env('MCP_DB_CONNECTION'),
        'default_limit' => 100,
        'max_limit' => 1000,
    ],

];
