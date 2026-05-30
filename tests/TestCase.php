<?php

declare(strict_types=1);

namespace VoltStack\Framework\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use VoltStack\Platform\Application;
use VoltStack\Platform\VoltStack;

abstract class TestCase extends PHPUnitTestCase
{
    protected function createApplication(array $appConfig = [], array $providers = []): Application
    {
        $basePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'voltstack-tests-' . bin2hex(random_bytes(8));
        $configPath = $basePath . DIRECTORY_SEPARATOR . 'config';
        $bootstrapPath = $basePath . DIRECTORY_SEPARATOR . 'bootstrap';

        if (!is_dir($configPath)) {
            mkdir($configPath, 0777, true);
        }

        if (!is_dir($bootstrapPath)) {
            mkdir($bootstrapPath, 0777, true);
        }

        $config = array_replace_recursive([
            'name' => 'VoltStack Test',
            'env' => 'testing',
            'debug' => true,
        ], $appConfig);

        $export = var_export($config, true);
        $contents = "<?php\n\ndeclare(strict_types=1);\n\nreturn {$export};\n";
        file_put_contents($configPath . DIRECTORY_SEPARATOR . 'app.php', $contents);

        $providersExport = var_export($providers, true);
        $providersContents = "<?php\n\ndeclare(strict_types=1);\n\nreturn {$providersExport};\n";
        file_put_contents($bootstrapPath . DIRECTORY_SEPARATOR . 'providers.php', $providersContents);

        $app = VoltStack::make($basePath);
        voltstack_set_application($app);

        return $app;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
