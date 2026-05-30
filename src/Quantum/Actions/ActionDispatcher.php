<?php

declare(strict_types=1);

namespace Quantum\Actions;

use Psr\Container\ContainerInterface;
use Quantum\Exceptions\HttpException;
use Quantum\Validation\Contracts\ValidatorInterface;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use RuntimeException;

final class ActionDispatcher
{
    public function __construct(
        protected ContainerInterface $container,
        protected ValidatorInterface $validator,
    ) {
    }

    public function dispatch(string|object $action, array $payload = []): mixed
    {
        $instance = is_string($action) ? $this->container->get($action) : $action;

        if (method_exists($instance, 'authorize') && $instance->authorize() === false) {
            throw new HttpException(403, 'This action is unauthorized.');
        }

        if (method_exists($instance, 'rules')) {
            $rules = $instance->rules();

            if (is_array($rules) && $rules !== []) {
                $payload = $this->validator->validate($payload, $rules);
            }
        }

        if (!method_exists($instance, 'handle')) {
            throw new RuntimeException('Action must define a handle() method.');
        }

        return $this->invokeHandle($instance, $payload);
    }

    protected function invokeHandle(object $action, array $payload): mixed
    {
        $method = new ReflectionMethod($action, 'handle');
        $arguments = [];

        foreach ($method->getParameters() as $parameter) {
            $arguments[] = $this->resolveArgument($parameter, $payload);
        }

        return $action->handle(...$arguments);
    }

    protected function resolveArgument(ReflectionParameter $parameter, array $payload): mixed
    {
        $name = $parameter->getName();
        $type = $parameter->getType();

        if (array_key_exists($name, $payload)) {
            return $payload[$name];
        }

        if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
            return $this->container->get($type->getName());
        }

        if ($type instanceof ReflectionNamedType && $type->getName() === 'array') {
            return $payload;
        }

        if (in_array($name, ['payload', 'data', 'input'], true)) {
            return $payload;
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new RuntimeException(
            sprintf('Unable to resolve action argument [%s].', $name)
        );
    }
}
