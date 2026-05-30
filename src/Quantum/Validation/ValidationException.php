<?php

declare(strict_types=1);

namespace Quantum\Validation;

use Quantum\Exceptions\HttpException;

final class ValidationException extends HttpException
{
    public function __construct(
        protected array $errors,
        string $message = 'The given data was invalid.',
        array $headers = [],
    ) {
        parent::__construct(422, $message, $headers);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
