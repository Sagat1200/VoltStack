<?php

declare(strict_types=1);

namespace Quantum\Validation\Contracts;

use Quantum\Validation\ValidationRuleContext;

interface RuleInterface
{
    public function name(): string;

    public function validate(ValidationRuleContext $context): void;
}
