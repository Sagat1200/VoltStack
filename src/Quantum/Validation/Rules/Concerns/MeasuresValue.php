<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules\Concerns;

trait MeasuresValue
{
    protected function valueSize(mixed $value): int|float|null
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            return mb_strlen($value);
        }

        if (is_array($value)) {
            return count($value);
        }

        return null;
    }
}
