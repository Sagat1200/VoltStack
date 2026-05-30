<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\Contracts\RuleInterface;
use Quantum\Validation\ValidationRuleContext;

final class PresentRule implements RuleInterface
{
    public function name(): string
    {
        return 'present';
    }

    public function validate(ValidationRuleContext $context): void
    {
        if (!$context->present()) {
            $context->fail();
        }
    }
}
