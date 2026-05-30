<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\Contracts\RuleInterface;
use Quantum\Validation\ValidationRuleContext;

final class Ipv4Rule implements RuleInterface
{
    public function name(): string
    {
        return 'ipv4';
    }

    public function validate(ValidationRuleContext $context): void
    {
        $value = $context->value();

        if (!$context->present() || $value === null) {
            return;
        }

        if (!is_string($value) || filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            $context->fail();
        }
    }
}
