<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Closure;
use Quantum\Validation\Contracts\RuleInterface;
use Quantum\Validation\Rules\Concerns\ResolvesDependentFields;
use Quantum\Validation\ValidationRuleContext;

abstract class AbstractConditionalRule implements RuleInterface
{
    use ResolvesDependentFields;

    /**
     * @param array<int, scalar|null> $expectedValues
     */
    public function __construct(
        protected bool|Closure|string $condition,
        protected array $expectedValues = [],
    ) {}

    protected function conditionMatches(ValidationRuleContext $context): bool
    {
        if (is_bool($this->condition)) {
            return $this->condition;
        }

        if ($this->condition instanceof Closure) {
            return (bool) ($this->condition)($context);
        }

        $otherValue = $this->otherValue($context);

        foreach ($this->expectedValues as $expectedValue) {
            if ((string) $otherValue === (string) $expectedValue) {
                return true;
            }
        }

        return false;
    }

    protected function otherField(ValidationRuleContext $context): ?string
    {
        if (!is_string($this->condition)) {
            return null;
        }

        return $this->resolveDependentField($context, $this->condition);
    }

    protected function otherValue(ValidationRuleContext $context): mixed
    {
        $otherField = $this->otherField($context);

        if ($otherField === null) {
            return null;
        }

        return $this->getValueByPath($context->data(), $otherField);
    }

    protected function expectedValuesAsString(): string
    {
        return implode(', ', array_map(static fn(mixed $value): string => (string) $value, $this->expectedValues));
    }

    protected function dependentMessageReplacements(ValidationRuleContext $context, string $valueKey = 'value'): array
    {
        $otherField = $this->otherField($context);

        if ($otherField === null) {
            return [];
        }

        return [
            'other' => $this->attributeLabel($context, $otherField),
            $valueKey => $this->expectedValuesAsString(),
        ];
    }

    protected function isEmpty(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [];
    }
}
