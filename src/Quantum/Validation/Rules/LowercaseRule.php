<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\Contracts\RuleInterface;
use Quantum\Validation\ValidationRuleContext;

final class LowercaseRule implements RuleInterface
{
    public function name(): string
    {
        return 'lowercase';
    }

    public function validate(ValidationRuleContext $context): void
    {
        $value = $context->value();

        if (!$context->present() || $value === null) {
            return;
        }

        if (!is_string($value) || mb_strtolower($value) !== $value) {
            $context->fail();
        }
    }
}
