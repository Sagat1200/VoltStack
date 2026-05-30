<?php

declare(strict_types=1);

namespace Quantum\Exceptions;

use RuntimeException;

class HttpException extends RuntimeException
{
    public function __construct(
        protected int $statusCode = 500,
        string $message = 'Server Error',
        protected array $headers = [],
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function headers(): array
    {
        return $this->headers;
    }
}
