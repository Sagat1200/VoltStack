<?php

declare(strict_types=1);

namespace Quantum\Routing;

use Quantum\Exceptions\NotFoundHttpException;
use Quantum\Http\Request;
use ReflectionNamedType;
use ReflectionParameter;

final class RouteBindingRegistry
{
    /** @var array<string, callable> */
    protected array $parameterBindings = [];

    /** @var array<string, callable> */
    protected array $typeBindings = [];

    public function bind(string $parameter, callable $resolver): self
    {
        $this->parameterBindings[$parameter] = $resolver;

        return $this;
    }

    public function bindType(string $type, callable $resolver): self
    {
        $this->typeBindings[$type] = $resolver;

        return $this;
    }

    public function resolve(ReflectionParameter $parameter, mixed $value, Request $request, ?Route $route = null): mixed
    {
        $name = $parameter->getName();

        if (isset($this->parameterBindings[$name])) {
            return $this->resolveBinding($this->parameterBindings[$name], $name, $value, $request, $route);
        }

        $type = $parameter->getType();

        if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
            $typeName = $type->getName();

            if (isset($this->typeBindings[$typeName])) {
                return $this->resolveBinding($this->typeBindings[$typeName], $name, $value, $request, $route);
            }
        }

        return $value;
    }

    protected function resolveBinding(
        callable $resolver,
        string $parameter,
        mixed $value,
        Request $request,
        ?Route $route,
    ): mixed {
        $resolved = $resolver($value, $request, $route, $parameter);

        if ($resolved === null) {
            throw new NotFoundHttpException(
                sprintf('Route binding [%s] could not be resolved.', $parameter)
            );
        }

        return $resolved;
    }
}