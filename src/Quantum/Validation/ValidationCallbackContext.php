<?php

declare(strict_types=1);

namespace Quantum\Validation;

use Closure;

final class ValidationCallbackContext
{
    public function __construct(
        protected array $data,
        protected Closure $addError,
        protected Closure $getErrors,
    ) {}

    public function data(): array
    {
        return $this->data;
    }

    public function errors(): array
    {
        return ($this->getErrors)();
    }

    public function hasErrors(): bool
    {
        return $this->errors() !== [];
    }

    public function addError(string $field, string $message): void
    {
        ($this->addError)($field, $message);
    }
}
