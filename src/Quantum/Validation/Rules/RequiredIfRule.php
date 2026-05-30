<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\ValidationRuleContext;

final class RequiredIfRule extends AbstractConditionalRule
{
    public function name(): string
    {
        return 'required_if';
    }

    public function validate(ValidationRuleContext $context): void
    {
        if (!$this->shouldRequire($context)) {
            return;
        }

        if (!$context->present() || $this->isEmpty($context->value())) {
            $context->fail(null, $this->dependentMessageReplacements($context));
        }
    }

    protected function shouldRequire(ValidationRuleContext $context): bool
    {
        return $this->conditionMatches($context);
    }
}
