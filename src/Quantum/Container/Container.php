<?php

declare(strict_types=1);

namespace Quantum\Container;

use Closure;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Quantum\Container\Contracts\ContainerInterface;
use Quantum\Container\Exceptions\ContainerException;
use Quantum\Container\Exceptions\NotFoundException;
use ReflectionClass;
use ReflectionNamedType;
use Throwable;

final class Container implements ContainerInterface, PsrContainerInterface
{
    /** @var array<string, Binding> */
    protected array $bindings = [];

    /** @var array<string, mixed> */
    protected array $instances = [];

    public function bind(string $abstract, mixed $concrete): void
    {
        $this->bindings[$abstract] = new Binding($concrete, false);
    }

    public function singleton(string $abstract, mixed $concrete): void
    {
        $this->bindings[$abstract] = new Binding($concrete, true);
    }

    public function instance(string $abstract, mixed $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->instances)
            || array_key_exists($id, $this->bindings)
            || class_exists($id);
    }

    public function get(string $id): mixed
    {
        return $this->make($id);
    }

    public function make(string $abstract, array $parameters = []): mixed
    {
        if (array_key_exists($abstract, $this->instances)) {
            return $this->instances[$abstract];
        }

        if (array_key_exists($abstract, $this->bindings)) {
            $binding = $this->bindings[$abstract];
            $resolved = $this->resolveConcrete($binding->concrete, $parameters);

            if ($binding->shared) {
                $this->instances[$abstract] = $resolved;
            }

            return $resolved;
        }

        if (!class_exists($abstract)) {
            throw new NotFoundException("Service [$abstract] is not registered.");
        }

        return $this->build($abstract, $parameters);
    }

    protected function resolveConcrete(mixed $concrete, array $parameters = []): mixed
    {
        if ($concrete instanceof Closure) {
            return $concrete($this, $parameters);
        }

        if (is_string($concrete)) {
            return $this->build($concrete, $parameters);
        }

        return $concrete;
    }

    protected function build(string $class, array $parameters = []): object
    {
        try {
            $reflector = new ReflectionClass($class);

            if (!$reflector->isInstantiable()) {
                throw new ContainerException("Class [$class] is not instantiable.");
            }

            $constructor = $reflector->getConstructor();

            if ($constructor === null) {
                return new $class();
            }

            $dependencies = [];

            foreach ($constructor->getParameters() as $parameter) {
                $name = $parameter->getName();

                if (array_key_exists($name, $parameters)) {
                    $dependencies[] = $parameters[$name];
                    continue;
                }

                $type = $parameter->getType();

                if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                    $dependencies[] = $this->make($type->getName());
                    continue;
                }

                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                    continue;
                }

                throw new ContainerException(
                    "Unable to resolve parameter [$name] while building [$class]."
                );
            }

            return $reflector->newInstanceArgs($dependencies);
        } catch (Throwable $exception) {
            if ($exception instanceof ContainerException || $exception instanceof NotFoundException) {
                throw $exception;
            }

            throw new ContainerException($exception->getMessage(), 0, $exception);
        }
    }
}
