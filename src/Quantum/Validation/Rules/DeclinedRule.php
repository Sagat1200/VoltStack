<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\Contracts\RuleInterface;
use Quantum\Validation\ValidationRuleContext;

final class DeclinedRule implements RuleInterface
{
    public function name(): string
    {
        return 'declined';
    }

    public function validate(ValidationRuleContext $context): void
    {
        $value = $context->value();

        if (!$context->present() || $value === null) {
            return;
        }

        if (!in_array($value, ['no', 'off', 0, '0', false, 'false'], true)) {
            $context->fail();
        }
    }
}
