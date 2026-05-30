<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\Contracts\RuleInterface;
use Quantum\Validation\Rules\Concerns\MeasuresValue;
use Quantum\Validation\ValidationRuleContext;

final class BetweenRule implements RuleInterface
{
    use MeasuresValue;

    public function __construct(
        protected int $minimum,
        protected int $maximum,
    ) {}

    public function name(): string
    {
        return 'between';
    }

    public function validate(ValidationRuleContext $context): void
    {
        $value = $context->value();

        if (!$context->present() || $value === null) {
            return;
        }

        $size = $this->valueSize($value);

        if ($size === null || $size < $this->minimum || $size > $this->maximum) {
            $context->fail(null, [
                'min' => (string) $this->minimum,
                'max' => (string) $this->maximum,
            ]);
        }
    }
}
