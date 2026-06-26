<?php

namespace Gardi\McpLaravel\Prompts;

use InvalidArgumentException;

/**
 * A prompt built from a text template with {placeholder} tokens that are filled
 * from the declared arguments.
 */
class TemplatePrompt implements Prompt
{
    /** @param list<array{name: string, description: string, required: bool}> $arguments */
    public function __construct(
        protected string $name,
        protected string $description,
        protected array $arguments,
        protected string $template,
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function arguments(): array
    {
        return $this->arguments;
    }

    public function render(array $arguments): string
    {
        $text = $this->template;

        foreach ($this->arguments as $argument) {
            $name = $argument['name'];
            $value = $arguments[$name] ?? null;

            if (($argument['required'] ?? false) && ($value === null || $value === '')) {
                throw new InvalidArgumentException("Missing required argument: {$name}");
            }

            $text = str_replace('{'.$name.'}', (string) ($value ?? ''), $text);
        }

        return $text;
    }
}
