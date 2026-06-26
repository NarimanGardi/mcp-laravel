# mcp-laravel — live session

Server: `php artisan mcp:serve` (booted here via Testbench).

## Requests
```json
{"jsonrpc":"2.0","id":1,"method":"initialize","params":{"protocolVersion":"2024-11-05","clientInfo":{"name":"demo","version":"1.0"}}}
{"jsonrpc":"2.0","id":2,"method":"tools/list"}
{"jsonrpc":"2.0","id":3,"method":"ping"}
```

## Responses
```json
{
    "jsonrpc": "2.0",
    "id": 1,
    "result": {
        "protocolVersion": "2024-11-05",
        "capabilities": {
            "tools": {
                "listChanged": false
            }
        },
        "serverInfo": {
            "name": "mcp-laravel",
            "version": "0.3.0"
        }
    }
}

{
    "jsonrpc": "2.0",
    "id": 2,
    "result": {
        "tools": [
            {
                "name": "list_routes",
                "description": "List the application's HTTP routes: methods, URI, name, action and middleware.",
                "inputSchema": {
                    "type": "object",
                    "properties": {
                        "filter": {
                            "type": "string",
                            "description": "Optional case-insensitive substring matched against the URI or route name."
                        }
                    }
                },
                "annotations": {
                    "readOnlyHint": true,
                    "destructiveHint": false
                }
            },
            {
                "name": "list_models",
                "description": "List Eloquent models in the application with their database table names.",
                "inputSchema": {
                    "type": "object",
                    "properties": []
                },
                "annotations": {
                    "readOnlyHint": true,
                    "destructiveHint": false
                }
            },
            {
                "name": "describe_model",
                "description": "Describe an Eloquent model: its table, columns, fillable/hidden, casts and relationships.",
                "inputSchema": {
                    "type": "object",
                    "properties": {
                        "model": {
                            "type": "string",
                            "description": "Model class \u2014 short name (e.g. \"User\") or fully-qualified (e.g. \"App\\\\Models\\\\User\")."
                        }
                    },
                    "required": [
                        "model"
                    ]
                },
                "annotations": {
                    "readOnlyHint": true,
                    "destructiveHint": false
                }
            },
            {
                "name": "relationship_graph",
                "description": "Map every Eloquent model and its relationships as a graph (nodes = models, edges = relations).",
                "inputSchema": {
                    "type": "object",
                    "properties": []
                },
                "annotations": {
                    "readOnlyHint": true,
                    "destructiveHint": false
                }
            },
            {
                "name": "describe_table",
                "description": "Describe a database table: its columns, indexes and foreign keys.",
                "inputSchema": {
                    "type": "object",
                    "properties": {
                        "table": {
                            "type": "string",
                            "description": "Table name."
                        },
                        "connection": {
                            "type": "string",
                            "description": "Optional database connection name."
                        }
                    },
                    "required": [
                        "table"
                    ]
                },
                "annotations": {
                    "readOnlyHint": true,
                    "destructiveHint": false
                }
            },
            {
                "name": "database_schema",
                "description": "List every table in the database with its columns (name and type) \u2014 a whole-schema overview.",
                "inputSchema": {
                    "type": "object",
                    "properties": {
                        "connection": {
                            "type": "string",
                            "description": "Optional database connection name."
                        }
                    }
                },
                "annotations": {
                    "readOnlyHint": true,
                    "destructiveHint": false
                }
            },
            {
                "name": "explain_query",
                "description": "Return the query plan (EXPLAIN) for a read-only SELECT, without running it.",
                "inputSchema": {
                    "type": "object",
                    "properties": {
                        "query": {
                            "type": "string",
                            "description": "A single SELECT (or WITH ... SELECT) statement."
                        },
                        "connection": {
                            "type": "string",
                            "description": "Optional database connection name."
                        }
                    },
                    "required": [
                        "query"
                    ]
                },
                "annotations": {
                    "readOnlyHint": true,
                    "destructiveHint": false
                }
            },
            {
                "name": "tail_logs",
                "description": "Return the last lines of a log file (defaults to the newest log in storage/logs).",
                "inputSchema": {
                    "type": "object",
                    "properties": {
                        "file": {
                            "type": "string",
                            "description": "Log file name within the logs directory (default: the newest *.log)."
                        },
                        "lines": {
                            "type": "integer",
                            "description": "Trailing lines to return (default 50, max 500)."
                        }
                    }
                },
                "annotations": {
                    "readOnlyHint": true,
                    "destructiveHint": false
                }
            }
        ]
    }
}

{
    "jsonrpc": "2.0",
    "id": 3,
    "result": []
}

```

## Calling the relationship_graph tool

Request:
```json
{"jsonrpc":"2.0","id":4,"method":"tools/call","params":{"name":"relationship_graph","arguments":{}}}
```

Result (the text content the agent receives):
```json
{
    "modelCount": 2,
    "relationCount": 2,
    "nodes": [
        {
            "model": "Gardi\\McpLaravel\\Tests\\Fixtures\\Post",
            "table": "posts"
        },
        {
            "model": "Gardi\\McpLaravel\\Tests\\Fixtures\\Comment",
            "table": "comments"
        }
    ],
    "edges": [
        {
            "from": "Gardi\\McpLaravel\\Tests\\Fixtures\\Post",
            "relation": "comments",
            "type": "HasMany",
            "to": "Gardi\\McpLaravel\\Tests\\Fixtures\\Comment"
        },
        {
            "from": "Gardi\\McpLaravel\\Tests\\Fixtures\\Comment",
            "relation": "post",
            "type": "BelongsTo",
            "to": "Gardi\\McpLaravel\\Tests\\Fixtures\\Post"
        }
    ]
}
```
