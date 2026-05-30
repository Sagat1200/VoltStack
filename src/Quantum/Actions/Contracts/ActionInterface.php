<?php

declare(strict_types=1);

namespace Quantum\Actions\Contracts;

interface ActionInterface
{
    public function handle(mixed ...$arguments): mixed;
}
