<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\Contracts\RuleInterface;
use Quantum\Validation\Rules\Concerns\ResolvesDependentFields;
use Quantum\Validation\ValidationRuleContext;

final class SameRule implements RuleInterface
{
    use ResolvesDependentFields;

    public function __construct(protected string $otherField) {}

    public function name(): string
    {
        return 'same';
    }

    public function validate(ValidationRuleContext $context): void
    {
        $value = $context->value();

        if (!$context->present() || $value === null) {
            return;
        }

        $otherField = $this->resolveDependentField($context, $this->otherField);
        $otherValue = $this->getValueByPath($context->data(), $otherField);

        if ($value !== $otherValue) {
            $context->fail(null, [
                'other' => $this->attributeLabel($context, $otherField),
            ]);
        }
    }
}
