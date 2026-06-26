<?php

use Gardi\McpLaravel\Prompts\TemplatePrompt;
use Gardi\McpLaravel\Server\Dispatcher;
use Gardi\McpLaravel\Server\PromptRegistry;
use Gardi\McpLaravel\Server\ResourceRegistry;
use Gardi\McpLaravel\Server\ToolRegistry;

function promptDispatcher(): Dispatcher
{
    $prompts = new PromptRegistry;
    $prompts->register(new TemplatePrompt(
        'review_model',
        'Review a model',
        [['name' => 'model', 'description' => 'Model class', 'required' => true]],
        'Review the {model} Eloquent model.',
    ));

    return new Dispatcher(new ToolRegistry, new ResourceRegistry, $prompts);
}

function promptCall(Dispatcher $dispatcher, string $method, array $params = [], int $id = 1): array
{
    return $dispatcher->dispatchLine(json_encode(array_filter([
        'jsonrpc' => '2.0',
        'id' => $id,
        'method' => $method,
        'params' => $params ?: null,
    ], fn ($v) => $v !== null)));
}

it('advertises the prompts capability', function () {
    $res = promptCall(promptDispatcher(), 'initialize', ['protocolVersion' => '2024-11-05']);

    expect($res['result']['capabilities'])->toHaveKey('prompts');
});

it('lists prompts with their arguments', function () {
    $res = promptCall(promptDispatcher(), 'prompts/list', id: 2);

    $prompt = collect($res['result']['prompts'])->firstWhere('name', 'review_model');

    expect($prompt)->not->toBeNull()
        ->and($prompt['arguments'][0]['name'])->toBe('model');
});

it('renders a prompt with its arguments', function () {
    $res = promptCall(promptDispatcher(), 'prompts/get', ['name' => 'review_model', 'arguments' => ['model' => 'User']], id: 3);

    expect($res['result']['messages'][0]['role'])->toBe('user')
        ->and($res['result']['messages'][0]['content']['text'])->toContain('Review the User Eloquent model');
});

it('errors on a missing required argument', function () {
    $res = promptCall(promptDispatcher(), 'prompts/get', ['name' => 'review_model', 'arguments' => []], id: 4);

    expect($res)->toHaveKey('error');
});

it('errors on an unknown prompt', function () {
    $res = promptCall(promptDispatcher(), 'prompts/get', ['name' => 'nope'], id: 5);

    expect($res)->toHaveKey('error');
});
