<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\Contracts\RuleInterface;
use Quantum\Validation\Rules\Concerns\ResolvesDependentFields;
use Quantum\Validation\ValidationRuleContext;

final class ConfirmedRule implements RuleInterface
{
    use ResolvesDependentFields;

    public function __construct(protected ?string $confirmationField = null) {}

    public function name(): string
    {
        return 'confirmed';
    }

    public function validate(ValidationRuleContext $context): void
    {
        $value = $context->value();

        if (!$context->present() || $value === null) {
            return;
        }

        $confirmationField = $this->confirmationField($context);

        if (!$this->pathExists($context->data(), $confirmationField)) {
            $context->fail();
            return;
        }

        if ($value !== $this->getValueByPath($context->data(), $confirmationField)) {
            $context->fail();
        }
    }

    protected function confirmationField(ValidationRuleContext $context): string
    {
        if ($this->confirmationField !== null) {
            return $this->resolveDependentField($context, $this->confirmationField);
        }

        $segments = explode('.', $context->field());
        $lastIndex = array_key_last($segments);

        if ($lastIndex !== null) {
            $segments[$lastIndex] .= '_confirmation';
        }

        return implode('.', $segments);
    }
}
