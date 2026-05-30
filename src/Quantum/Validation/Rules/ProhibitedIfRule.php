<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\ValidationRuleContext;

final class ProhibitedIfRule extends AbstractConditionalRule
{
    public function name(): string
    {
        return 'prohibited_if';
    }

    public function validate(ValidationRuleContext $context): void
    {
        if (!$this->conditionMatches($context)) {
            return;
        }

        if ($context->present() && !$this->isEmpty($context->value())) {
            $context->fail(null, $this->dependentMessageReplacements($context));
        }
    }
}