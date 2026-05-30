<?php

declare(strict_types=1);

namespace VoltStack\Platform;

final class VoltStack
{
    public static function make(string $basePath): Application
    {
        $app = new Application($basePath);

        if (function_exists('voltstack_set_application')) {
            voltstack_set_application($app);
        }

        return $app;
    }
}
