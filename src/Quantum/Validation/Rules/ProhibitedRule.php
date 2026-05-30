<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\Contracts\RuleInterface;
use Quantum\Validation\ValidationRuleContext;

final class ProhibitedRule implements RuleInterface
{
    public function name(): string
    {
        return 'prohibited';
    }

    public function validate(ValidationRuleContext $context): void
    {
        $value = $context->value();

        if ($context->present() && $value !== null && $value !== '' && $value !== []) {
            $context->fail();
        }
    }
}
