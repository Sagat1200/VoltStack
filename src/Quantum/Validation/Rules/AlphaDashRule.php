<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\Contracts\RuleInterface;
use Quantum\Validation\ValidationRuleContext;

final class AlphaDashRule implements RuleInterface
{
    public function name(): string
    {
        return 'alpha_dash';
    }

    public function validate(ValidationRuleContext $context): void
    {
        $value = $context->value();

        if (!$context->present() || $value === null) {
            return;
        }

        if (!is_string($value) || preg_match('/^[A-Za-z0-9_-]+$/', $value) !== 1) {
            $context->fail();
        }
    }
}
