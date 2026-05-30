<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\Contracts\RuleInterface;
use Quantum\Validation\ValidationRuleContext;
use Quantum\Validation\Rules\Concerns\MeasuresValue;

final class MinRule implements RuleInterface
{
    use MeasuresValue;

    public function __construct(protected int $minimum) {}

    public function name(): string
    {
        return 'min';
    }

    public function validate(ValidationRuleContext $context): void
    {
        $value = $context->value();

        if (!$context->present() || $value === null) {
            return;
        }

        $size = $this->valueSize($value);

        if ($size === null || $size < $this->minimum) {
            $context->fail(null, [
                'min' => (string) $this->minimum,
            ]);
        }
    }
}
