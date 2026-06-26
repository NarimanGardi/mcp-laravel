<?php

use Gardi\McpLaravel\Tools\ListCommandsTool;
use Illuminate\Contracts\Console\Kernel;

it('lists registered artisan commands', function () {
    $out = json_decode((new ListCommandsTool(app(Kernel::class)))->handle([]), true);

    expect($out['commandCount'])->toBeGreaterThan(0)
        ->and(array_column($out['commands'], 'name'))->toContain('migrate');
});

it('filters commands by name', function () {
    $out = json_decode((new ListCommandsTool(app(Kernel::class)))->handle(['filter' => 'migrate']), true);

    $names = array_column($out['commands'], 'name');

    expect($names)->not->toBeEmpty();

    foreach ($names as $name) {
        expect($name)->toContain('migrate');
    }
});
