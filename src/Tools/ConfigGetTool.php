<?php

namespace Gardi\McpLaravel\Tools;

use Gardi\McpLaravel\Tools\Concerns\IsReadOnly;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

/**
 * Reads configuration values with sensitive ones redacted. Redaction is by key
 * name and errs toward over-redacting: any key containing a sensitive substring
 * (password, secret, token, key, …) has its value replaced with "[redacted]".
 * It's a safety net, not a guarantee — review what you expose.
 */
class ConfigGetTool implements Tool
{
    use IsReadOnly;

    private const SENSITIVE = [
        'password', 'passwd', 'pwd', 'secret', 'token', 'key', 'salt',
        'credential', 'cipher', 'signature', 'private', 'auth', 'dsn', 'cert',
    ];

    /** @param list<string> $extraSensitive additional key substrings to redact */
    public function __construct(protected array $extraSensitive = [])
    {
    }

    public function name(): string
    {
        return 'config_get';
    }

    public function description(): string
    {
        return 'Read configuration values (sensitive values redacted). Omit "key" to list the top-level config namespaces.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'key' => [
                    'type' => 'string',
                    'description' => 'Dot-notation config key, e.g. "database.default". Omit to list namespaces.',
                ],
            ],
        ];
    }

    public function handle(array $arguments): string
    {
        $key = trim((string) ($arguments['key'] ?? ''));

        if ($key === '') {
            return json_encode(['namespaces' => array_keys(Config::all())], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        if (! Config::has($key)) {
            throw new InvalidArgumentException("Config key not found: {$key}");
        }

        return json_encode([
            'key' => $key,
            'value' => $this->redact($this->leaf($key), Config::get($key)),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    protected function redact(string $leafKey, mixed $value): mixed
    {
        if ($this->isSensitive($leafKey)) {
            return '[redacted]';
        }

        if (is_array($value)) {
            $out = [];
            foreach ($value as $childKey => $childValue) {
                $out[$childKey] = $this->redact((string) $childKey, $childValue);
            }

            return $out;
        }

        return $value;
    }

    protected function leaf(string $key): string
    {
        $parts = explode('.', $key);

        return (string) end($parts);
    }

    protected function isSensitive(string $key): bool
    {
        $key = strtolower($key);

        foreach (array_merge(self::SENSITIVE, $this->extraSensitive) as $needle) {
            if ($needle !== '' && str_contains($key, strtolower($needle))) {
                return true;
            }
        }

        return false;
    }
}
