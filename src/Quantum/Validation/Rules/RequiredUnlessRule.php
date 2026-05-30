<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\ValidationRuleContext;

final class RequiredUnlessRule extends AbstractConditionalRule
{
    public function name(): string
    {
        return 'required_unless';
    }

    public function validate(ValidationRuleContext $context): void
    {
        if ($this->shouldSkipRequirement($context)) {
            return;
        }

        if (!$context->present() || $this->isEmpty($context->value())) {
            $context->fail(null, $this->dependentMessageReplacements($context, 'values'));
        }
    }

    protected function shouldSkipRequirement(ValidationRuleContext $context): bool
    {
        return $this->conditionMatches($context);
    }
}
