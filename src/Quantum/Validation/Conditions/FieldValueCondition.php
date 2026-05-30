<?php

declare(strict_types=1);

namespace Quantum\Validation\Conditions;

use Quantum\Validation\Conditions\Concerns\BuildsDeclarativeRules;
use Quantum\Validation\Conditions\Contracts\DeclarativeConditionInterface;
use Quantum\Validation\Rules\Concerns\ResolvesDependentFields;
use Quantum\Validation\ValidationRuleContext;

final class FieldValueCondition implements DeclarativeConditionInterface
{
    use BuildsDeclarativeRules;
    use ResolvesDependentFields;

    /**
     * @param array<int, scalar|null> $expectedValues
     */
    public function __construct(
        protected string $field,
        protected array $expectedValues,
        protected bool $negated = false,
    ) {}

    public function __invoke(ValidationRuleContext $context): bool
    {
        $data = $context->data();
        $resolvedField = $this->resolveDependentField($context, $this->field);
        $current = $data;

        foreach (explode('.', $resolvedField) as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return false;
            }

            $current = $current[$segment];
        }

        foreach ($this->expectedValues as $expectedValue) {
            if ((string) $current === (string) $expectedValue) {
                return !$this->negated;
            }
        }

        return $this->negated;
    }

    public function field(): string
    {
        return $this->field;
    }

    /**
     * @return array<int, scalar|null>
     */
    public function expectedValues(): array
    {
        return $this->expectedValues;
    }

    public function messageValue(): string
    {
        return implode(', ', array_map(
            static fn(mixed $value): string => (string) $value,
            $this->expectedValues,
        ));
    }

    public function messageOther(ValidationRuleContext $context): string
    {
        return $this->attributeLabel($context, $this->resolveDependentField($context, $this->field));
    }
}
