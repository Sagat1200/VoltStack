<?php

declare(strict_types=1);

namespace Quantum\Container\Exceptions;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

final class ContainerException extends RuntimeException implements ContainerExceptionInterface {}
