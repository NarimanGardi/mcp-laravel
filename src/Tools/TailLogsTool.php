<?php

namespace Gardi\McpLaravel\Tools;

use Gardi\McpLaravel\Tools\Concerns\IsReadOnly;
use InvalidArgumentException;

/**
 * Returns the tail of a log file. Defaults to the most recently modified *.log
 * in the configured logs directory; an optional file name is taken as a basename
 * within that directory (no path traversal). Reads only the last chunk of the
 * file, so it stays cheap on large logs.
 *
 * Logs can contain sensitive data (exception payloads, tokens). Disable this
 * tool, or scrub your logs, if that's a concern in your environment.
 */
class TailLogsTool implements Tool
{
    use IsReadOnly;

    public function __construct(
        protected string $logsPath,
        protected int $defaultLines = 50,
        protected int $maxLines = 500,
    ) {
    }

    public function name(): string
    {
        return 'tail_logs';
    }

    public function description(): string
    {
        return 'Return the last lines of a log file (defaults to the newest log in storage/logs).';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'file' => ['type' => 'string', 'description' => 'Log file name within the logs directory (default: the newest *.log).'],
                'lines' => ['type' => 'integer', 'description' => "Trailing lines to return (default {$this->defaultLines}, max {$this->maxLines})."],
            ],
        ];
    }

    public function handle(array $arguments): string
    {
        $path = $this->resolvePath($arguments['file'] ?? null);

        if ($path === null) {
            throw new InvalidArgumentException('No matching log file found in '.$this->logsPath);
        }

        $lines = max(1, min((int) ($arguments['lines'] ?? $this->defaultLines), $this->maxLines));
        $tail = $this->tailLines($path, $lines);

        return '── '.basename($path).' (last '.count($tail).' lines) ──'.PHP_EOL.implode(PHP_EOL, $tail);
    }

    protected function resolvePath(?string $file): ?string
    {
        if ($file !== null && $file !== '') {
            // basename() strips any directory components, blocking path traversal.
            $candidate = rtrim($this->logsPath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.basename($file);

            return is_file($candidate) ? $candidate : null;
        }

        $logs = glob(rtrim($this->logsPath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'*.log') ?: [];

        if ($logs === []) {
            return null;
        }

        usort($logs, fn (string $a, string $b) => filemtime($b) <=> filemtime($a));

        return $logs[0];
    }

    /** @return list<string> */
    protected function tailLines(string $path, int $lines): array
    {
        $size = filesize($path);

        if ($size === 0) {
            return [];
        }

        $chunk = (int) min($size, 256 * 1024);
        $handle = fopen($path, 'rb');
        fseek($handle, -$chunk, SEEK_END);
        $data = fread($handle, $chunk) ?: '';
        fclose($handle);

        // If we began mid-file, drop the first (partial) line.
        if ($chunk < $size && ($newline = strpos($data, "\n")) !== false) {
            $data = substr($data, $newline + 1);
        }

        return array_slice(explode("\n", rtrim($data, "\n")), -$lines);
    }
}
