<?php

declare(strict_types=1);

namespace VoltStack\Facades;

use Quantum\Config\ConfigRepository;

final class Config extends Facade
{
    protected static function accessor(): string|object
    {
        return ConfigRepository::class;
    }
}
