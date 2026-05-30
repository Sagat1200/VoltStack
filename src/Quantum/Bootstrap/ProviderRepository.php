<?php

declare(strict_types=1);

namespace Quantum\Bootstrap;

use Quantum\Container\Exceptions\ContainerException;
use VoltStack\Platform\Application;

final class ProviderRepository
{
    /** @var array<int, string> */
    protected array $providers = [];

    /** @var array<int, ServiceProvider> */
    protected array $instances = [];

    public function __construct(
        protected Application $app,
    ) {}

    public function add(string $providerClass): void
    {
        if (!in_array($providerClass, $this->providers, true)) {
            $this->providers[] = $providerClass;
        }
    }

    public function all(): array
    {
        return $this->providers;
    }

    public function registerAll(): void
    {
        foreach ($this->providers as $providerClass) {
            if (!is_subclass_of($providerClass, ServiceProvider::class)) {
                throw new ContainerException(
                    "Provider [$providerClass] must extend [" . ServiceProvider::class . '].'
                );
            }

            /** @var ServiceProvider $provider */
            $provider = new $providerClass($this->app);
            $provider->register();
            $this->instances[] = $provider;
        }
    }

    public function bootAll(): void
    {
        foreach ($this->instances as $provider) {
            $provider->boot();
        }
    }
}
