# Changelog

## Unreleased

- Add MCP **prompts** (`prompts/list` / `prompts/get`): `explain_app`,
  `review_model`, `write_test` and `debug_recent_error` — parameterised templates
  that point the agent at the tools. Toggle in the `prompts` config block.
- README: add CI / Packagist / license badges.

## v0.5.1

- Support Laravel 13 alongside Laravel 12 (and Pest 4 / Testbench 11). Laravel 13
  requires PHP 8.3+, so the CI matrix excludes the PHP 8.2 × Laravel 13 combination.

## v0.5.0

- Add `config_get` — read config values with sensitive keys redacted (errs toward
  over-redaction); omit the key to list namespaces.
- Add `migration_status` — migration files with ran/pending state and batch numbers.
- Add `list_commands` — registered Artisan commands with descriptions and usage.
- Drop Laravel 11 support (it has reached end-of-life for security fixes); require
  Laravel 12. CI tests Laravel 12 on PHP 8.2–8.4.

## v0.4.0

- Add MCP **resources** (`resources/list` / `resources/read`): `laravel://schema`,
  `laravel://routes` and `laravel://models`, served as adapters over the existing
  read-only tools. Toggle them in the `resources` config block.
- Add an **HTTP transport**: a bearer-authenticated `POST /{path}` endpoint, off by
  default (set `MCP_HTTP_ENABLED` + `MCP_HTTP_TOKEN`). Protocol handling is now
  shared by the stdio and HTTP transports via a transport-agnostic `Dispatcher`.

## v0.3.1

- Make model discovery path/namespace configurable via `MCP_MODELS_PATH` /
  `MCP_MODELS_NAMESPACE` (or the `models_path` / `models_namespace` config).
- Docs: add `demo/` (a reproducible live session via Testbench, with a VHS tape
  for recording a GIF) and `examples/` (client configs for Claude Code, Cursor,
  Windsurf, Cline).

## v0.3.0

- Add `relationship_graph` — maps every model and its relationships as a graph
  (nodes = models, edges = relations).
- Add `tail_logs` — return the last lines of a log file (newest in `storage/logs`
  by default), reading only the file's tail.
- Add `explain_query` — return the query plan (EXPLAIN) for a read-only SELECT
  without executing it.

## v0.2.0

- Add `describe_table` (columns, indexes, foreign keys) and `database_schema`
  (whole-schema overview) tools.
- Add `model_query` — read-only Eloquent queries with equality filters, column
  selection, eager-loaded relations, ordering and a row limit. Opt-in.
- Advertise MCP tool annotations (`readOnlyHint`) on `tools/list`.
- Add `php artisan mcp:install` to publish the config and print the MCP client
  configuration snippet.

## v0.1.0

- Initial release: a stdio MCP server exposing `list_routes`, `list_models`,
  `describe_model` and a read-only `database_query` tool.
