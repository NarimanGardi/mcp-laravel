<?php

namespace Gardi\McpLaravel\Tools\Concerns;

/**
 * Every tool in this package only reads from the application, so they all
 * advertise the same MCP annotations. Clients use these hints to decide how
 * to present a tool and whether it needs confirmation before running.
 */
trait IsReadOnly
{
    public function annotations(): array
    {
        return [
            'readOnlyHint' => true,
            'destructiveHint' => false,
        ];
    }
}
