<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\Contracts\RuleInterface;
use Quantum\Validation\ValidationRuleContext;

final class DigitsRule implements RuleInterface
{
    public function __construct(protected int $digits) {}

    public function name(): string
    {
        return 'digits';
    }

    public function validate(ValidationRuleContext $context): void
    {
        $value = (string) $context->value();

        if (!$context->present() || $context->value() === null) {
            return;
        }

        if (preg_match('/^\d+$/', $value) !== 1 || strlen($value) !== $this->digits) {
            $context->fail(null, [
                'digits' => (string) $this->digits,
            ]);
        }
    }
}
