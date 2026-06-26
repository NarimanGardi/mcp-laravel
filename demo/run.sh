#!/usr/bin/env bash
#
# Reproducible demo of mcp-laravel. Boots the REAL MCP server via Testbench,
# sends the JSON-RPC requests in demo/requests.jsonl, prints the actual
# responses, then calls the relationship_graph tool against the package's
# fixture models. Nothing here is mocked.
#
# Run it:        ./demo/run.sh
# Recapture:     ./demo/run.sh > demo/session.md
#
set -euo pipefail
cd "$(dirname "$0")/.."

# Point model discovery at the package's fixture models so the demo has data.
export MCP_MODELS_PATH="$(pwd)/tests/Fixtures"
export MCP_MODELS_NAMESPACE='Gardi\McpLaravel\Tests\Fixtures'

serve() { vendor/bin/testbench mcp:serve 2>/dev/null; }
pretty() { php -r 'while(($l=fgets(STDIN))!==false){$l=trim($l);if($l==="")continue;echo json_encode(json_decode($l,true),JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),"\n\n";}'; }

echo "# mcp-laravel — live session"
echo
echo "Server: \`php artisan mcp:serve\` (booted here via Testbench)."
echo
echo "## Requests"
echo '```json'
cat demo/requests.jsonl
echo '```'
echo
echo "## Responses"
echo '```json'
serve < demo/requests.jsonl | pretty
echo '```'
echo
echo "## Calling the relationship_graph tool"
echo
echo "Request:"
echo '```json'
echo '{"jsonrpc":"2.0","id":4,"method":"tools/call","params":{"name":"relationship_graph","arguments":{}}}'
echo '```'
echo
echo "Result (the text content the agent receives):"
echo '```json'
printf '%s\n' '{"jsonrpc":"2.0","id":4,"method":"tools/call","params":{"name":"relationship_graph","arguments":{}}}' \
  | serve | php -r '$d=json_decode(stream_get_contents(STDIN),true); echo $d["result"]["content"][0]["text"],"\n";'
echo '```'
