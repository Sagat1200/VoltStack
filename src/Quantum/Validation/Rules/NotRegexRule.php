<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\Contracts\RuleInterface;
use Quantum\Validation\ValidationRuleContext;

final class NotRegexRule implements RuleInterface
{
    public function __construct(protected string $pattern) {}

    public function name(): string
    {
        return 'not_regex';
    }

    public function validate(ValidationRuleContext $context): void
    {
        $value = $context->value();

        if (!$context->present() || $value === null) {
            return;
        }

        if (!is_string($value) || @preg_match($this->pattern, $value) !== 0) {
            $context->fail();
        }
    }
}
