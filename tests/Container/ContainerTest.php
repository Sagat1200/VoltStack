<?php

declare(strict_types=1);

namespace VoltStack\Framework\Tests\Container;

use Quantum\Container\Container;
use Quantum\Container\Exceptions\NotFoundException;
use VoltStack\Framework\Tests\TestCase;

final class ContainerTest extends TestCase
{
    public function test_it_resolves_a_concrete_class_without_binding(): void
    {
        $container = new Container();
        $service = $container->make(SimpleService::class);

        self::assertInstanceOf(SimpleService::class, $service);
    }

    public function test_it_resolves_constructor_dependencies(): void
    {
        $container = new Container();
        $service = $container->make(ServiceWithDependency::class);

        self::assertInstanceOf(ServiceWithDependency::class, $service);
        self::assertInstanceOf(SimpleService::class, $service->dependency);
    }

    public function test_singleton_returns_same_instance(): void
    {
        $container = new Container();
        $container->singleton(SimpleService::class, SimpleService::class);

        $first = $container->make(SimpleService::class);
        $second = $container->make(SimpleService::class);

        self::assertSame($first, $second);
    }

    public function test_it_throws_for_unknown_service(): void
    {
        $this->expectException(NotFoundException::class);

        (new Container())->make('Missing\\Service');
    }
}

final class SimpleService
{
}

final class ServiceWithDependency
{
    public function __construct(public SimpleService $dependency)
    {
    }
}
