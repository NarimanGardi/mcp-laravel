<?php

use Gardi\McpLaravel\Tests\Fixtures\Post;
use Gardi\McpLaravel\Tools\DatabaseSchemaTool;
use Gardi\McpLaravel\Tools\DescribeTableTool;
use Gardi\McpLaravel\Tools\ModelQueryTool;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->boolean('published')->default(false);
    });

    Post::query()->insert([
        ['title' => 'First', 'published' => true],
        ['title' => 'Second', 'published' => false],
    ]);
});

afterEach(function () {
    Schema::dropIfExists('posts');
});

it('describes a table', function () {
    $out = json_decode((new DescribeTableTool)->handle(['table' => 'posts']), true);

    expect($out['table'])->toBe('posts')
        ->and(array_column($out['columns'], 'name'))->toContain('title', 'published');
});

it('rejects an unknown table', function () {
    expect(fn () => (new DescribeTableTool)->handle(['table' => 'does_not_exist']))
        ->toThrow(InvalidArgumentException::class);
});

it('lists the whole schema', function () {
    $out = json_decode((new DatabaseSchemaTool)->handle([]), true);

    expect(array_column($out['tables'], 'name'))->toContain('posts');
});

it('runs a read-only model query with filters', function () {
    $tool = new ModelQueryTool(modelsNamespace: 'App\\Models');

    $out = json_decode($tool->handle(['model' => Post::class, 'filters' => ['published' => true]]), true);

    expect($out['rowCount'])->toBe(1)
        ->and($out['records'][0]['title'])->toBe('First');
});

it('caps the model query at the max limit', function () {
    $tool = new ModelQueryTool(modelsNamespace: 'App\\Models', maxLimit: 1);

    $out = json_decode($tool->handle(['model' => Post::class, 'limit' => 999]), true);

    expect($out['rowCount'])->toBe(1)
        ->and($out['truncated'])->toBeTrue();
});

it('marks tools as read-only', function () {
    expect((new DatabaseSchemaTool)->annotations())->toMatchArray(['readOnlyHint' => true]);
});
