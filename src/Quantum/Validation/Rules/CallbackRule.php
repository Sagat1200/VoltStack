<?php

declare(strict_types=1);

namespace Quantum\Validation\Rules;

use Quantum\Validation\Contracts\RuleInterface;
use Quantum\Validation\ValidationRuleContext;

final class CallbackRule implements RuleInterface
{
    public function __construct(
        protected string $name,
        protected $callback,
    ) {}

    public function name(): string
    {
        return $this->name;
    }

    public function validate(ValidationRuleContext $context): void
    {
        ($this->callback)($context);
    }
}
