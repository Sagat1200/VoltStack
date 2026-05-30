<?php

declare(strict_types=1);

namespace Quantum\Validation\Conditions;

final class WhenFieldBuilder
{
    public function __construct(protected string $field) {}

    public function is(mixed ...$expectedValues): FieldValueCondition
    {
        return $this->makeCondition(false, $expectedValues);
    }

    public function isNot(mixed ...$expectedValues): FieldValueCondition
    {
        return $this->makeCondition(true, $expectedValues);
    }

    public function in(mixed ...$expectedValues): FieldValueCondition
    {
        return $this->makeCondition(false, $expectedValues);
    }

    public function notIn(mixed ...$expectedValues): FieldValueCondition
    {
        return $this->makeCondition(true, $expectedValues);
    }

    public function exists(): FieldStateCondition
    {
        return new FieldStateCondition($this->field, 'exists');
    }

    public function missing(): FieldStateCondition
    {
        return new FieldStateCondition($this->field, 'missing');
    }

    public function empty(): FieldStateCondition
    {
        return new FieldStateCondition($this->field, 'empty');
    }

    public function filled(): FieldStateCondition
    {
        return new FieldStateCondition($this->field, 'filled');
    }

    /**
     * @param array<int, mixed> $expectedValues
     */
    protected function makeCondition(bool $negated, array $expectedValues): FieldValueCondition
    {
        if (count($expectedValues) === 1 && is_array($expectedValues[0])) {
            $expectedValues = array_values($expectedValues[0]);
        }

        return new FieldValueCondition(
            $this->field,
            array_values(array_filter(
                $expectedValues,
                static fn(mixed $value): bool => is_scalar($value) || $value === null,
            )),
            $negated,
        );
    }
}