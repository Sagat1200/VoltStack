<?php

declare(strict_types=1);

namespace Quantum\Container\Contracts;

interface ContainerInterface extends \Psr\Container\ContainerInterface
{
    public function bind(string $abstract, mixed $concrete): void;

    public function singleton(string $abstract, mixed $concrete): void;

    public function instance(string $abstract, mixed $instance): void;

    public function make(string $abstract, array $parameters = []): mixed;
}
