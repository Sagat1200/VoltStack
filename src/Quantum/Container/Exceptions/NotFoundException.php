<?php

declare(strict_types=1);

namespace Quantum\Container\Exceptions;

use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

final class NotFoundException extends RuntimeException implements NotFoundExceptionInterface
{
}
