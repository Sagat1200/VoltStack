<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\ValidationRuleContext;

final class RequiredWithRule extends AbstractDependentFieldsRule
{
    public function name(): string
    {
        return 'required_with';
    }

    public function validate(ValidationRuleContext $context): void
    {
        foreach ($this->dependentFields($context) as $field) {
            if ($this->isEmptyValue($context, $field)) {
                continue;
            }

            if ($this->currentValueIsEmpty($context)) {
                $context->fail(null, [
                    'values' => $this->dependentFieldLabels($context),
                ]);
            }

            return;
        }
    }
}