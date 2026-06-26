<?php

namespace Gardi\McpLaravel\Resources;

/**
 * An MCP resource: a named, addressable blob of read-only context a client can
 * attach to a conversation (as opposed to a tool, which the agent calls on demand).
 */
interface Resource
{
    /** Stable URI the client references, e.g. "laravel://schema". */
    public function uri(): string;

    public function name(): string;

    public function description(): string;

    /** MIME type of the content returned by read(). */
    public function mimeType(): string;

    /** The resource's current content. */
    public function read(): string;
}
