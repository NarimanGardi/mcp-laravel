<?php

use Gardi\McpLaravel\Tools\ExplainQueryTool;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->string('title');
    });
});

afterEach(function () {
    Schema::dropIfExists('posts');
});

it('returns a query plan without running the query', function () {
    $out = json_decode((new ExplainQueryTool)->handle(['query' => 'select * from posts where id = 1']), true);

    expect($out['driver'])->toBe('sqlite')
        ->and($out['plan'])->not->toBeEmpty();
});

it('rejects writes', function () {
    expect(fn () => (new ExplainQueryTool)->handle(['query' => 'delete from posts']))
        ->toThrow(InvalidArgumentException::class);
});
