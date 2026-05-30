<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\ValidationRuleContext;

final class AcceptedIfRule extends AbstractConditionalRule
{
    public function name(): string
    {
        return 'accepted_if';
    }

    public function validate(ValidationRuleContext $context): void
    {
        if (!$this->conditionMatches($context)) {
            return;
        }

        if (!in_array($context->value(), ['yes', 'on', 1, '1', true, 'true'], true)) {
            $context->fail(null, $this->dependentMessageReplacements($context));
        }
    }
}
