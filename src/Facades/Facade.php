<?php

declare(strict_types=1);

namespace VoltStack\Facades;

use RuntimeException;
use VoltStack\Platform\Application;

abstract class Facade
{
    protected static ?Application $application = null;

    public static function setApplication(Application $app): void
    {
        static::$application = $app;
    }

    protected static function app(): Application
    {
        if (static::$application === null) {
            throw new RuntimeException('Facade application has not been set.');
        }

        return static::$application;
    }

    abstract protected static function accessor(): string|object;

    public static function __callStatic(string $method, array $arguments): mixed
    {
        $accessor = static::accessor();
        $instance = is_object($accessor) ? $accessor : static::app()->container()->make($accessor);

        return $instance->{$method}(...$arguments);
    }
}
