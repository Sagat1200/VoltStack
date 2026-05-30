<?php

declare(strict_types=1);

namespace Quantum\Routing;

final class ResolvedRoute
{
    public function __construct(
        protected Route $route,
        protected array $parameters = [],
    ) {
    }

    public function route(): Route
    {
        return $this->route;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }
}
