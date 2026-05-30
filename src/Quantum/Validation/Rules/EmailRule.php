<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\Contracts\RuleInterface;
use Quantum\Validation\ValidationRuleContext;

final class EmailRule implements RuleInterface
{
    public function name(): string
    {
        return 'email';
    }

    public function validate(ValidationRuleContext $context): void
    {
        $value = $context->value();

        if (!$context->present() || $value === null) {
            return;
        }

        if (!is_string($value) || filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $context->fail();
        }
    }
}
