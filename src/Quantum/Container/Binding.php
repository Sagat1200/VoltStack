<?php

declare(strict_types=1);

namespace Quantum\Container;

final class Binding
{
    public function __construct(
        public mixed $concrete,
        public bool $shared = false,
    ) {
    }
}
