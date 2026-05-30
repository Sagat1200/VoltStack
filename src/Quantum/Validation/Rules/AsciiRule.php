<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\Contracts\RuleInterface;
use Quantum\Validation\ValidationRuleContext;

final class AsciiRule implements RuleInterface
{
    public function name(): string
    {
        return 'ascii';
    }

    public function validate(ValidationRuleContext $context): void
    {
        $value = $context->value();

        if (!$context->present() || $value === null) {
            return;
        }

        if (!is_string($value) || preg_match('/^[\x00-\x7F]*$/', $value) !== 1) {
            $context->fail();
        }
    }
}
