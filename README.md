# mcp-laravel

An MCP server that lets an AI coding agent inspect a Laravel application — its
routes, models, database schema, and (optionally) read-only query results —
over stdio.

I kept watching agents guess at a Laravel codebase: inventing model attributes,
half-remembering route names, assuming a column exists. They were working from
the training set, not the app in front of them. This exposes the real thing as
a handful of tools the agent can call, so it reads your schema instead of
hallucinating it.

It's deliberately small. Four tools, no daemon, no extra services — just an
artisan command that speaks the Model Context Protocol on stdin/stdout.

## Requirements

- PHP 8.2+
- Laravel 11 or 12

## Install

```bash
composer require gardi/mcp-laravel --dev
```

The service provider is auto-discovered. Run the installer to publish the config
and print the client snippet for you:

```bash
php artisan mcp:install
```

(Or just `php artisan vendor:publish --tag=mcp-config` if you only want the config.)

## Connect an agent

The server runs over stdio, so any MCP client launches it the same way — point
it at this app's directory and have it run `php artisan mcp:serve`. For example,
in an MCP client's server config:

```json
{
  "mcpServers": {
    "laravel": {
      "command": "php",
      "args": ["artisan", "mcp:serve"],
      "cwd": "/absolute/path/to/your/laravel/app"
    }
  }
}
```

That's it — the agent can now list and call the tools below.

## Tools

| Tool | What it returns |
|------|-----------------|
| `list_routes` | Methods, URI, name, action and middleware (optional substring filter). |
| `list_models` | Eloquent models found under `app/Models`, with their tables. |
| `describe_model` | A model's table, columns, fillable/hidden, casts and relationships. |
| `relationship_graph` | Every model and its relationships as a graph (nodes + edges). |
| `describe_table` | Any table's columns, indexes and foreign keys (not just models). |
| `database_schema` | Every table with its columns — a whole-schema overview. |
| `explain_query` | The query plan (EXPLAIN) for a read-only SELECT — without running it. |
| `tail_logs` | The last lines of a log file (newest in `storage/logs` by default). |
| `model_query` | Rows from a read-only Eloquent query (filters, columns, relations). Opt-in. |
| `database_query` | Rows from a single **read-only** SELECT. Opt-in (see below). |

## A note on the data tools

`model_query` and `database_query` both return row data, so both are **off by
default** — enable them with `MCP_MODEL_QUERY=true` / `MCP_DATABASE_QUERY=true`.
`model_query` can't run raw SQL (it builds an Eloquent query from structured
arguments), which makes it the safer of the two.

For `database_query` specifically: when enabled, every query is validated as a
single read-only statement *and* run inside a transaction that is always rolled back.
That's defense in depth, not a guarantee — the right way to run it is against a
least-privilege, read-only database user. Enable it with:

```env
MCP_DATABASE_QUERY=true
MCP_DB_CONNECTION=readonly   # optional: a read-only connection
```

## Limitations

Worth knowing before you rely on it:

- `list_models` assumes the Laravel default PSR-4 mapping (`App\Models` →
  `app/Models`). Models elsewhere need the `models_path` / `models_namespace`
  config changed.
- `describe_model` detects relationships only when the relation method declares
  a return type (e.g. `public function posts(): HasMany`). Untyped relation
  methods aren't listed — calling every method to find out would be unsafe.
- The `database_query` / `explain_query` read-only check is a keyword/structure
  heuristic, not a SQL parser. Pair it with a read-only DB user; don't treat the
  heuristic as your only line of defense.
- `tail_logs` returns whatever is in your log files, which can include sensitive
  data (exception payloads, tokens). Disable it or scrub logs if that matters.

## Development

```bash
composer install
./vendor/bin/pest
```

## License

MIT — see [LICENSE](LICENSE).
