<?php

declare(strict_types=1);

namespace Quantum\Validation\Conditions;

use Quantum\Validation\Conditions\Concerns\BuildsDeclarativeRules;
use Quantum\Validation\Conditions\Contracts\DeclarativeConditionInterface;
use Quantum\Validation\ValidationRuleContext;

final class CompositeCondition implements DeclarativeConditionInterface
{
    use BuildsDeclarativeRules;

    /**
     * @param array<int, DeclarativeConditionInterface> $conditions
     */
    public function __construct(
        protected string $mode,
        protected array $conditions,
    ) {}

    public function __invoke(ValidationRuleContext $context): bool
    {
        if ($this->mode === 'all') {
            foreach ($this->conditions as $condition) {
                if (!$condition($context)) {
                    return false;
                }
            }

            return true;
        }

        foreach ($this->conditions as $condition) {
            if ($condition($context)) {
                return true;
            }
        }

        return false;
    }

    public function messageOther(ValidationRuleContext $context): string
    {
        return implode($this->glue(), array_map(
            static fn(DeclarativeConditionInterface $condition): string => $condition->messageOther($context),
            $this->conditions,
        ));
    }

    public function messageValue(): string
    {
        return implode($this->glue(), array_map(
            static fn(DeclarativeConditionInterface $condition): string => $condition->messageValue(),
            $this->conditions,
        ));
    }

    protected function glue(): string
    {
        return $this->mode === 'all' ? ' and ' : ' or ';
    }
}
