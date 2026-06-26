<?php

use Gardi\McpLaravel\Tools\DatabaseQueryTool;

it('rejects non-SELECT statements', function (string $sql) {
    expect(fn () => (new DatabaseQueryTool)->handle(['query' => $sql]))
        ->toThrow(InvalidArgumentException::class);
})->with([
    'update users set name = "x"',
    'delete from users',
    'drop table users',
    'insert into users (name) values ("x")',
    'select * from users; drop table users',
    'select * into backup from users',
]);

it('requires a query', function () {
    expect(fn () => (new DatabaseQueryTool)->handle(['query' => '   ']))
        ->toThrow(InvalidArgumentException::class);
});
