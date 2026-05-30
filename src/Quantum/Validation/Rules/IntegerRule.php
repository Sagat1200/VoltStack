<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\Contracts\RuleInterface;
use Quantum\Validation\ValidationRuleContext;

final class IntegerRule implements RuleInterface
{
    public function name(): string
    {
        return 'integer';
    }

    public function validate(ValidationRuleContext $context): void
    {
        $value = $context->value();

        if (!$context->present() || $value === null) {
            return;
        }

        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            $context->fail();
        }
    }
}
