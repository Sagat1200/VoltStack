<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\Contracts\RuleInterface;
use Quantum\Validation\Rules\Concerns\MeasuresValue;
use Quantum\Validation\ValidationRuleContext;

final class SizeRule implements RuleInterface
{
    use MeasuresValue;

    public function __construct(protected int $size) {}

    public function name(): string
    {
        return 'size';
    }

    public function validate(ValidationRuleContext $context): void
    {
        $value = $context->value();

        if (!$context->present() || $value === null) {
            return;
        }

        $measuredSize = $this->valueSize($value);

        if ($measuredSize === null || $measuredSize != $this->size) {
            $context->fail(null, [
                'size' => (string) $this->size,
            ]);
        }
    }
}
