<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\Contracts\RuleInterface;
use Quantum\Validation\Rules\Concerns\ResolvesDependentFields;
use Quantum\Validation\ValidationRuleContext;

abstract class AbstractDependentFieldsRule implements RuleInterface
{
    use ResolvesDependentFields;

    /**
     * @param array<int, string> $fields
     */
    public function __construct(protected array $fields) {}

    /**
     * @return array<int, string>
     */
    protected function dependentFields(ValidationRuleContext $context): array
    {
        return $this->resolveDependentFields($context, $this->fields);
    }

    protected function dependentFieldLabels(ValidationRuleContext $context): string
    {
        return $this->attributeLabels($context, $this->dependentFields($context));
    }

    protected function isEmptyValue(ValidationRuleContext $context, string $field): bool
    {
        $value = $this->getValueByPath($context->data(), $field);

        return !$this->pathExists($context->data(), $field) || $value === null || $value === '' || $value === [];
    }

    protected function currentValueIsEmpty(ValidationRuleContext $context): bool
    {
        $value = $context->value();

        return !$context->present() || $value === null || $value === '' || $value === [];
    }
}
