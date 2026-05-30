<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\Contracts\RuleInterface;
use Quantum\Validation\ValidationRuleContext;

final class AcceptedRule implements RuleInterface
{
    public function name(): string
    {
        return 'accepted';
    }

    public function validate(ValidationRuleContext $context): void
    {
        $value = $context->value();

        if (!$context->present() || $value === null) {
            return;
        }

        if (!in_array($value, ['yes', 'on', 1, '1', true, 'true'], true)) {
            $context->fail();
        }
    }
}
