<?php

use Gardi\McpLaravel\Tools\TailLogsTool;

beforeEach(function () {
    $this->dir = sys_get_temp_dir().'/mcp-logs-'.uniqid();
    mkdir($this->dir);
    file_put_contents(
        $this->dir.'/laravel.log',
        implode("\n", array_map(fn (int $i) => "line {$i}", range(1, 100)))."\n"
    );
});

afterEach(function () {
    @unlink($this->dir.'/laravel.log');
    @rmdir($this->dir);
});

it('returns the last N lines of the newest log', function () {
    $out = (new TailLogsTool($this->dir))->handle(['lines' => 10]);

    expect($out)->toContain('line 100')
        ->and($out)->toContain('line 91')
        ->and($out)->not->toContain('line 90');
});

it('caps at the configured max lines', function () {
    $out = (new TailLogsTool($this->dir, defaultLines: 50, maxLines: 5))->handle(['lines' => 999]);

    expect(substr_count($out, 'line '))->toBe(5);
});

it('errors when the logs directory has no log file', function () {
    $empty = sys_get_temp_dir().'/mcp-empty-'.uniqid();
    mkdir($empty);

    expect(fn () => (new TailLogsTool($empty))->handle([]))
        ->toThrow(InvalidArgumentException::class);

    rmdir($empty);
});
