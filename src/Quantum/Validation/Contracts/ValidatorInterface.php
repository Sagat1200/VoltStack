<?php

declare(strict_types=1);

namespace Quantum\Validation\Contracts;

use Quantum\Validation\ValidationException;

interface ValidatorInterface
{
    public function after(callable $callback): static;

    public function stopOnFirstFailure(bool $value = true): static;

    /**
     * @param array<string, array<int, string|RuleInterface>|string|RuleInterface> $rules
     *
     * @throws ValidationException
     */
    public function validate(
        array $data,
        array $rules,
        array $messages = [],
        array $attributes = []
    ): array;
}