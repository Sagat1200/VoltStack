<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\Contracts\RuleInterface;
use Quantum\Validation\ValidationRuleContext;

final class NullableRule implements RuleInterface
{
    public function name(): string
    {
        return 'nullable';
    }

    public function validate(ValidationRuleContext $context): void
    {
        if ($context->value() === null) {
            $context->skipRemainingRules();
        }
    }
}
