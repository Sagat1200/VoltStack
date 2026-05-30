<?php

declare(strict_types=1);

namespace VoltStack\Facades;

use Quantum\Container\Container as ContainerManager;

final class Container extends Facade
{
    protected static function accessor(): string|object
    {
        return ContainerManager::class;
    }
}
