<?php

declare(strict_types=1);

namespace Quantum\Validation\Conditions\Contracts;

use Quantum\Validation\ValidationRuleContext;

interface DeclarativeConditionInterface
{
    public function __invoke(ValidationRuleContext $context): bool;

    public function messageOther(ValidationRuleContext $context): string;

    public function messageValue(): string;
}
