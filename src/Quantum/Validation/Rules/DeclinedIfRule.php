<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\ValidationRuleContext;

final class DeclinedIfRule extends AbstractConditionalRule
{
    public function name(): string
    {
        return 'declined_if';
    }

    public function validate(ValidationRuleContext $context): void
    {
        if (!$this->conditionMatches($context)) {
            return;
        }

        if (!in_array($context->value(), ['no', 'off', 0, '0', false, 'false'], true)) {
            $context->fail(null, $this->dependentMessageReplacements($context));
        }
    }
}
