<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\Contracts\RuleInterface;
use Quantum\Validation\ValidationRuleContext;

final class UrlRule implements RuleInterface
{
    public function name(): string
    {
        return 'url';
    }

    public function validate(ValidationRuleContext $context): void
    {
        $value = $context->value();

        if (!$context->present() || $value === null) {
            return;
        }

        if (!is_string($value) || filter_var($value, FILTER_VALIDATE_URL) === false) {
            $context->fail();
        }
    }
}
