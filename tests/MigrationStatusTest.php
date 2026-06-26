<?php

use Gardi\McpLaravel\Tools\MigrationStatusTool;

it('lists migration files with their run state', function () {
    $tool = new MigrationStatusTool(app('migrator'), __DIR__.'/Fixtures/migrations');

    $out = json_decode($tool->handle([]), true);

    $demo = collect($out['migrations'])->firstWhere('migration', '2026_01_01_000000_create_demo_table');

    expect($demo)->not->toBeNull()
        ->and($demo['ran'])->toBeFalse()
        ->and($out['pendingCount'])->toBeGreaterThanOrEqual(1);
});

it('is read-only', function () {
    $tool = new MigrationStatusTool(app('migrator'), __DIR__.'/Fixtures/migrations');

    expect($tool->annotations())->toMatchArray(['readOnlyHint' => true]);
});
