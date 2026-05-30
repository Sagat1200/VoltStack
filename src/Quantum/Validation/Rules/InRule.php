<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\Contracts\RuleInterface;
use Quantum\Validation\ValidationRuleContext;

final class InRule implements RuleInterface
{
    /**
     * @param array<int, string|int|float|bool> $allowed
     */
    public function __construct(protected array $allowed) {}

    public function name(): string
    {
        return 'in';
    }

    public function validate(ValidationRuleContext $context): void
    {
        $value = $context->value();

        if (!$context->present() || $value === null) {
            return;
        }

        $allowed = array_map(static fn(mixed $item): string => (string) $item, $this->allowed);

        if (!in_array((string) $value, $allowed, true)) {
            $context->fail();
        }
    }
}
