<?php

namespace Gardi\McpLaravel\Tools;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

class DescribeModelTool implements Tool
{
    public function __construct(protected string $modelsNamespace)
    {
    }

    public function name(): string
    {
        return 'describe_model';
    }

    public function description(): string
    {
        return 'Describe an Eloquent model: its table, columns, fillable/hidden, casts and relationships.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'model' => [
                    'type' => 'string',
                    'description' => 'Model class — short name (e.g. "User") or fully-qualified (e.g. "App\\\\Models\\\\User").',
                ],
            ],
            'required' => ['model'],
        ];
    }

    public function handle(array $arguments): string
    {
        $input = trim((string) ($arguments['model'] ?? ''));

        if ($input === '') {
            throw new InvalidArgumentException('The "model" argument is required.');
        }

        $class = $this->resolveClass($input);

        if ($class === null) {
            throw new InvalidArgumentException("Model not found: {$input}");
        }

        /** @var Model $model */
        $model = new $class;
        $table = $model->getTable();

        return json_encode([
            'class' => $class,
            'table' => $table,
            'columns' => $this->columns($model, $table),
            'fillable' => $model->getFillable(),
            'hidden' => $model->getHidden(),
            'casts' => $model->getCasts(),
            'relations' => $this->relations($class),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    protected function resolveClass(string $input): ?string
    {
        if (class_exists($input)) {
            return $input;
        }

        $guess = $this->modelsNamespace.'\\'.$input;

        return class_exists($guess) ? $guess : null;
    }

    /** @return list<array{name: string, type: ?string, nullable: ?bool}> */
    protected function columns(Model $model, string $table): array
    {
        $schema = Schema::connection($model->getConnectionName());

        return array_map(fn (array $column) => [
            'name' => $column['name'],
            'type' => $column['type_name'] ?? $column['type'] ?? null,
            'nullable' => $column['nullable'] ?? null,
        ], $schema->getColumns($table));
    }

    /**
     * Detect relationships by reflecting public, zero-argument methods whose
     * return type is an Eloquent Relation. Relations without a return type
     * hint are not detected (calling every method to find out is unsafe).
     *
     * @return list<array{name: string, type: string}>
     */
    protected function relations(string $class): array
    {
        $relations = [];

        foreach ((new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->class !== $class || $method->getNumberOfParameters() > 0) {
                continue;
            }

            $type = $method->getReturnType();

            if ($type instanceof ReflectionNamedType
                && ! $type->isBuiltin()
                && is_subclass_of($type->getName(), Relation::class)) {
                $relations[] = ['name' => $method->getName(), 'type' => class_basename($type->getName())];
            }
        }

        return $relations;
    }
}
