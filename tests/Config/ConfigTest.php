<?php

declare(strict_types=1);

namespace VoltStack\Framework\Tests\Config;

use Quantum\Config\ConfigLoader;
use Quantum\Config\ConfigRepository;
use VoltStack\Framework\Tests\TestCase;

final class ConfigTest extends TestCase
{
    public function test_repository_reads_dot_notation_values(): void
    {
        $repository = new ConfigRepository([
            'app' => [
                'name' => 'VoltStack',
                'meta' => [
                    'env' => 'testing',
                ],
            ],
        ]);

        self::assertTrue($repository->has('app.name'));
        self::assertSame('VoltStack', $repository->get('app.name'));
        self::assertSame('testing', $repository->get('app.meta.env'));
    }

    public function test_repository_sets_dot_notation_values(): void
    {
        $repository = new ConfigRepository();
        $repository->set('app.features.cache', true);

        self::assertTrue($repository->has('app.features.cache'));
        self::assertTrue($repository->get('app.features.cache'));
    }

    public function test_loader_reads_php_config_files(): void
    {
        $basePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'voltstack-config-' . bin2hex(random_bytes(8));
        $configPath = $basePath . DIRECTORY_SEPARATOR . 'config';

        mkdir($configPath, 0777, true);

        file_put_contents(
            $configPath . DIRECTORY_SEPARATOR . 'app.php',
            "<?php\n\nreturn ['name' => 'VoltStack Loader'];\n"
        );

        $items = (new ConfigLoader())->loadFromPath($configPath);

        self::assertSame('VoltStack Loader', $items['app']['name']);
    }
}
