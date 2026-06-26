<?php

use Gardi\McpLaravel\Tools\ConfigGetTool;

beforeEach(fn () => config()->set('services.demo', [
    'url' => 'https://api.test',
    'secret' => 'sk_live_should_be_hidden',
]));

it('redacts sensitive config values but keeps the rest', function () {
    $out = json_decode((new ConfigGetTool)->handle(['key' => 'services.demo']), true);

    expect($out['value']['secret'])->toBe('[redacted]')
        ->and($out['value']['url'])->toBe('https://api.test');
});

it('lists top-level namespaces when no key is given', function () {
    $out = json_decode((new ConfigGetTool)->handle([]), true);

    expect($out['namespaces'])->toContain('services');
});

it('errors on an unknown key', function () {
    expect(fn () => (new ConfigGetTool)->handle(['key' => 'nope.nope']))
        ->toThrow(InvalidArgumentException::class);
});

it('is read-only', function () {
    expect((new ConfigGetTool)->annotations())->toMatchArray(['readOnlyHint' => true]);
});
