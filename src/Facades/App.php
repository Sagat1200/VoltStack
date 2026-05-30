<?php

declare(strict_types=1);

namespace VoltStack\Facades;

use VoltStack\Platform\Application;

final class App extends Facade
{
    protected static function accessor(): string|object
    {
        return Application::class;
    }
}
