<?php

declare(strict_types=1);

namespace Quantum\HttpKernel;

final class MiddlewareRegistry
{
    /** @var array<string, mixed> */
    protected array $aliases = [];

    /** @var array<string, array<int, mixed>> */
    protected array $groups = [];

    public function alias(string $name, mixed $middleware): self
    {
        $this->aliases[$name] = $middleware;

        return $this;
    }

    public function group(string $name, array $middleware): self
    {
        $this->groups[$name] = $middleware;

        return $this;
    }

    public function hasAlias(string $name): bool
    {
        return array_key_exists($name, $this->aliases);
    }

    public function hasGroup(string $name): bool
    {
        return array_key_exists($name, $this->groups);
    }

    public function resolve(mixed $middleware): array
    {
        if (is_array($middleware)) {
            $resolved = [];

            foreach ($middleware as $item) {
                $resolved = [...$resolved, ...$this->resolve($item)];
            }

            return $resolved;
        }

        if (is_string($middleware) && $this->hasGroup($middleware)) {
            return $this->resolve($this->groups[$middleware]);
        }

        if (is_string($middleware) && $this->hasAlias($middleware)) {
            return $this->resolve($this->aliases[$middleware]);
        }

        return [$middleware];
    }
}
