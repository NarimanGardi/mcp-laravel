# Changelog

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
