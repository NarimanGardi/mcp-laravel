# Client configs

Drop-in MCP server configs for popular clients. They all launch the same thing —
`php artisan mcp:serve` — so the only thing that changes per client is **where the
config file lives** and whether you need an absolute `cwd`.

> `php artisan mcp:install` prints the snippet for your app (with the right `cwd`)
> if you'd rather not copy by hand.

| Client | Config location | File here |
|--------|-----------------|-----------|
| Claude Code | `.mcp.json` in your project root | [`claude-code.json`](claude-code.json) |
| Cursor | `.cursor/mcp.json` in your project | [`cursor.json`](cursor.json) |
| Windsurf | `~/.codeium/windsurf/mcp_config.json` | [`windsurf.json`](windsurf.json) |
| Cline (VS Code) | `cline_mcp_settings.json` | [`cline.json`](cline.json) |

## `cwd` matters

`php artisan mcp:serve` must run **from your Laravel app's directory**. Clients
that launch the server from the project you have open (Claude Code, Cursor)
usually don't need `cwd`. Clients with their own working directory (Windsurf,
Cline) do — replace `/absolute/path/to/your/laravel/app` with your real path.

If `php` isn't on the client's PATH, use an absolute path to the binary (e.g.
`/usr/bin/php` or your Herd/Valet PHP).
