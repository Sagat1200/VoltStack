<?php

declare(strict_types=1);

namespace Quantum\Validation\Conditions;

use Quantum\Validation\Conditions\Contracts\DeclarativeConditionInterface;

final class ChainedWhenBuilder
{
    public function __construct(
        protected DeclarativeConditionInterface $baseCondition,
        protected string $mode,
        protected string $field,
    ) {}

    public function is(mixed ...$expectedValues): CompositeCondition
    {
        return $this->compose((new WhenFieldBuilder($this->field))->is(...$expectedValues));
    }

    public function isNot(mixed ...$expectedValues): CompositeCondition
    {
        return $this->compose((new WhenFieldBuilder($this->field))->isNot(...$expectedValues));
    }

    public function in(mixed ...$expectedValues): CompositeCondition
    {
        return $this->compose((new WhenFieldBuilder($this->field))->in(...$expectedValues));
    }

    public function notIn(mixed ...$expectedValues): CompositeCondition
    {
        return $this->compose((new WhenFieldBuilder($this->field))->notIn(...$expectedValues));
    }

    public function exists(): CompositeCondition
    {
        return $this->compose((new WhenFieldBuilder($this->field))->exists());
    }

    public function missing(): CompositeCondition
    {
        return $this->compose((new WhenFieldBuilder($this->field))->missing());
    }

    public function empty(): CompositeCondition
    {
        return $this->compose((new WhenFieldBuilder($this->field))->empty());
    }

    public function filled(): CompositeCondition
    {
        return $this->compose((new WhenFieldBuilder($this->field))->filled());
    }

    protected function compose(DeclarativeConditionInterface $condition): CompositeCondition
    {
        return new CompositeCondition($this->mode, [$this->baseCondition, $condition]);
    }
}
