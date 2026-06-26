# Changelog

## v0.2.0 — unreleased

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
