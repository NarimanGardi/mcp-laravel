<?php

namespace Gardi\McpLaravel\Server;

/**
 * The stdio transport: reads newline-delimited JSON-RPC messages from stdin,
 * hands each to the Dispatcher, and writes responses to stdout. Notifications
 * (no id) produce no output.
 */
class StdioServer
{
    public function __construct(protected Dispatcher $dispatcher)
    {
    }

    /**
     * @param  resource  $in
     * @param  resource  $out
     */
    public function run($in = STDIN, $out = STDOUT): void
    {
        while (($line = fgets($in)) !== false) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $response = $this->dispatcher->dispatchLine($line);

            if ($response !== null) {
                fwrite($out, json_encode($response, JSON_UNESCAPED_SLASHES)."\n");
                fflush($out);
            }
        }
    }
}
