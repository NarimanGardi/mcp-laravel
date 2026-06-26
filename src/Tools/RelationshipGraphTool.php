<?php

namespace Gardi\McpLaravel\Tools;

use Gardi\McpLaravel\Tools\Concerns\DiscoversModels;
use Gardi\McpLaravel\Tools\Concerns\IsReadOnly;
use Illuminate\Database\Eloquent\Relations\Relation;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use Throwable;

/**
 * Maps every Eloquent model and its relationships as a graph: nodes are models,
 * edges are relations. Relations are detected by reflecting public, zero-argument
 * methods whose return type is an Eloquent Relation; the target model is read from
 * the relation's getRelated() (which builds the relation but runs no query).
 */
class RelationshipGraphTool implements Tool
{
    use DiscoversModels;
    use IsReadOnly;

    public function __construct(
        protected string $modelsPath,
        protected string $modelsNamespace,
    ) {
    }

    public function name(): string
    {
        return 'relationship_graph';
    }

    public function description(): string
    {
        return 'Map every Eloquent model and its relationships as a graph (nodes = models, edges = relations).';
    }

    public function inputSchema(): array
    {
        return ['type' => 'object', 'properties' => (object) []];
    }

    public function handle(array $arguments): string
    {
        $nodes = [];
        $edges = [];

        foreach ($this->discoverModels($this->modelsPath, $this->modelsNamespace) as $class) {
            $model = new $class;
            $nodes[] = ['model' => $class, 'table' => $model->getTable()];

            foreach ($this->relationMethods($class) as $method => $returnType) {
                $edges[] = [
                    'from' => $class,
                    'relation' => $method,
                    'type' => class_basename($returnType),
                    'to' => $this->relatedModel($model, $method),
                ];
            }
        }

        return json_encode([
            'modelCount' => count($nodes),
            'relationCount' => count($edges),
            'nodes' => $nodes,
            'edges' => $edges,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Public zero-argument methods whose return type is an Eloquent Relation.
     * Untyped relation methods aren't detected (calling every method to find
     * out would be unsafe).
     *
     * @return array<string, class-string>  method name => Relation subclass
     */
    protected function relationMethods(string $class): array
    {
        $methods = [];

        foreach ((new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->class !== $class || $method->getNumberOfParameters() > 0) {
                continue;
            }

            $type = $method->getReturnType();

            if ($type instanceof ReflectionNamedType
                && ! $type->isBuiltin()
                && is_subclass_of($type->getName(), Relation::class)) {
                $methods[$method->getName()] = $type->getName();
            }
        }

        return $methods;
    }

    protected function relatedModel(object $model, string $method): ?string
    {
        try {
            return $model->{$method}()->getRelated()::class;
        } catch (Throwable) {
            return null;
        }
    }
}
