<?php

namespace Gardi\McpLaravel\Tools\Concerns;

use Illuminate\Database\Eloquent\Model;

trait ResolvesModel
{
    /**
     * Resolve a model class from a short name ("User") or a fully-qualified
     * one ("App\Models\User"). Returns null if it isn't an Eloquent model.
     */
    protected function resolveModelClass(string $input, string $namespace): ?string
    {
        foreach ([$input, $namespace.'\\'.$input] as $candidate) {
            if (class_exists($candidate) && is_subclass_of($candidate, Model::class)) {
                return $candidate;
            }
        }

        return null;
    }
}
