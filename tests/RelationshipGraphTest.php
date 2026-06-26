<?php

use Gardi\McpLaravel\Tests\Fixtures\Comment;
use Gardi\McpLaravel\Tests\Fixtures\Post;
use Gardi\McpLaravel\Tools\RelationshipGraphTool;

function graph(): array
{
    $tool = new RelationshipGraphTool(__DIR__.'/Fixtures', 'Gardi\\McpLaravel\\Tests\\Fixtures');

    return json_decode($tool->handle([]), true);
}

it('lists models as nodes', function () {
    expect(array_column(graph()['nodes'], 'model'))->toContain(Post::class, Comment::class);
});

it('maps a hasMany relation to its target', function () {
    $edge = collect(graph()['edges'])->firstWhere('relation', 'comments');

    expect($edge['from'])->toBe(Post::class)
        ->and($edge['type'])->toBe('HasMany')
        ->and($edge['to'])->toBe(Comment::class);
});

it('maps a belongsTo relation to its target', function () {
    $edge = collect(graph()['edges'])->firstWhere('relation', 'post');

    expect($edge['type'])->toBe('BelongsTo')
        ->and($edge['to'])->toBe(Post::class);
});

it('is read-only', function () {
    expect((new RelationshipGraphTool('/x', 'App\\Models'))->annotations())
        ->toMatchArray(['readOnlyHint' => true]);
});
