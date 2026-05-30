<?php

declare(strict_types=1);

namespace Quantum\Validation\Conditions;

use Quantum\Validation\Conditions\Concerns\BuildsDeclarativeRules;
use Quantum\Validation\Conditions\Contracts\DeclarativeConditionInterface;
use Quantum\Validation\Rules\Concerns\ResolvesDependentFields;
use Quantum\Validation\ValidationRuleContext;

final class FieldStateCondition implements DeclarativeConditionInterface
{
    use BuildsDeclarativeRules;
    use ResolvesDependentFields;

    public function __construct(
        protected string $field,
        protected string $state,
    ) {}

    public function __invoke(ValidationRuleContext $context): bool
    {
        $resolvedField = $this->resolveDependentField($context, $this->field);
        $present = $this->pathExists($context->data(), $resolvedField);
        $value = $this->getValueByPath($context->data(), $resolvedField);

        return match ($this->state) {
            'exists' => $present,
            'missing' => !$present,
            'empty' => !$present || $value === null || $value === '' || $value === [],
            'filled' => $present && $value !== null && $value !== '' && $value !== [],
            default => false,
        };
    }

    public function field(): string
    {
        return $this->field;
    }

    public function messageOther(ValidationRuleContext $context): string
    {
        return $this->attributeLabel($context, $this->resolveDependentField($context, $this->field));
    }

    public function messageValue(): string
    {
        return $this->state;
    }
}
