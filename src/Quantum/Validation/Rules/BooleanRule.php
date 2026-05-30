<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\Contracts\RuleInterface;
use Quantum\Validation\ValidationRuleContext;

final class BooleanRule implements RuleInterface
{
    public function name(): string
    {
        return 'boolean';
    }

    public function validate(ValidationRuleContext $context): void
    {
        $value = $context->value();

        if (!$context->present() || $value === null) {
            return;
        }

        if (!in_array($value, [true, false, 0, 1, '0', '1'], true)) {
            $context->fail();
        }
    }
}
