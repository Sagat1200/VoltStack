<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\Contracts\RuleInterface;
use Quantum\Validation\Rules\Concerns\MeasuresValue;
use Quantum\Validation\ValidationRuleContext;

final class MaxRule implements RuleInterface
{
    use MeasuresValue;

    public function __construct(protected int $maximum) {}

    public function name(): string
    {
        return 'max';
    }

    public function validate(ValidationRuleContext $context): void
    {
        $value = $context->value();

        if (!$context->present() || $value === null) {
            return;
        }

        $size = $this->valueSize($value);

        if ($size === null || $size > $this->maximum) {
            $context->fail(null, [
                'max' => (string) $this->maximum,
            ]);
        }
    }
}
