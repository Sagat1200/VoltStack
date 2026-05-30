<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\Contracts\RuleInterface;
use Quantum\Validation\ValidationRuleContext;

final class JsonRule implements RuleInterface
{
    public function name(): string
    {
        return 'json';
    }

    public function validate(ValidationRuleContext $context): void
    {
        $value = $context->value();

        if (!$context->present() || $value === null) {
            return;
        }

        if (!is_string($value)) {
            $context->fail();
            return;
        }

        json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $context->fail();
        }
    }
}
