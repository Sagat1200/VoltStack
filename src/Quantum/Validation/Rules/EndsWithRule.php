<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\Contracts\RuleInterface;
use Quantum\Validation\ValidationRuleContext;

final class EndsWithRule implements RuleInterface
{
    /**
     * @param array<int, string> $needles
     */
    public function __construct(protected array $needles) {}

    public function name(): string
    {
        return 'ends_with';
    }

    public function validate(ValidationRuleContext $context): void
    {
        $value = $context->value();

        if (!$context->present() || $value === null) {
            return;
        }

        if (!is_string($value)) {
            $context->fail(null, [
                'values' => implode(',', $this->needles),
            ]);
            return;
        }

        foreach ($this->needles as $needle) {
            if ($needle !== '' && str_ends_with($value, $needle)) {
                return;
            }
        }

        $context->fail(null, [
            'values' => implode(',', $this->needles),
        ]);
    }
}
