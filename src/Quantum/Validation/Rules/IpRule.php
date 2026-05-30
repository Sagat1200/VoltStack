<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\Contracts\RuleInterface;
use Quantum\Validation\ValidationRuleContext;

final class IpRule implements RuleInterface
{
    public function name(): string
    {
        return 'ip';
    }

    public function validate(ValidationRuleContext $context): void
    {
        $value = $context->value();

        if (!$context->present() || $value === null) {
            return;
        }

        if (!is_string($value) || filter_var($value, FILTER_VALIDATE_IP) === false) {
            $context->fail();
        }
    }
}
