<?php

declare(strict_types=1);

namespace Quantum\Bootstrap;

use Psr\Container\ContainerInterface as PsrContainerInterface;
use Quantum\Config\ConfigLoader;
use Quantum\Config\ConfigRepository;
use Quantum\Config\Contracts\RepositoryInterface;
use VoltStack\Platform\Application;

final class BootstrapManager
{
    public function __construct(
        protected Application $app,
    ) {}

    public function bootstrap(): void
    {
        (new EnvironmentLoader())->load($this->app->environmentPath());

        $items = (new ConfigLoader())->loadFromPath($this->app->configPath());
        $config = new ConfigRepository($items);
        $container = $this->app->container();

        $container->instance(ConfigRepository::class, $config);
        $container->instance(RepositoryInterface::class, $config);
        $container->instance(PsrContainerInterface::class, $container);
        $container->instance('config', $config);

        foreach ($this->loadProvidersFromBootstrapFile() as $provider) {
            $this->app->register($provider);
        }

        $this->app->providers()->registerAll();
        $this->app->providers()->bootAll();
    }

    /**
     * @return array<int, string>
     */
    protected function loadProvidersFromBootstrapFile(): array
    {
        $file = $this->app->bootstrapPath('providers.php');

        if (!is_file($file)) {
            return [];
        }

        $providers = require $file;

        if (!is_array($providers)) {
            return [];
        }

        return array_values(
            array_filter(
                $providers,
                static fn(mixed $provider): bool => is_string($provider)
            )
        );
    }
}
