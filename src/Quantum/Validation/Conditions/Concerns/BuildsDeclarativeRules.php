<?php

declare(strict_types=1);

namespace Quantum\Validation\Conditions\Concerns;

use Quantum\Validation\Conditions\ChainedWhenBuilder;
use Quantum\Validation\Conditions\CompositeCondition;
use Quantum\Validation\Conditions\Contracts\DeclarativeConditionInterface;
use Quantum\Validation\Rule;
use Quantum\Validation\Rules\AcceptedIfRule;
use Quantum\Validation\Rules\DeclinedIfRule;
use Quantum\Validation\Rules\ProhibitedIfRule;
use Quantum\Validation\Rules\RequiredIfRule;

trait BuildsDeclarativeRules
{
    public function required(): RequiredIfRule
    {
        return Rule::requiredIf($this);
    }

    public function thenRequired(): RequiredIfRule
    {
        return $this->required();
    }

    public function accepted(): AcceptedIfRule
    {
        return Rule::acceptedIf($this);
    }

    public function thenAccepted(): AcceptedIfRule
    {
        return $this->accepted();
    }

    public function declined(): DeclinedIfRule
    {
        return Rule::declinedIf($this);
    }

    public function thenDeclined(): DeclinedIfRule
    {
        return $this->declined();
    }

    public function prohibited(): ProhibitedIfRule
    {
        return Rule::prohibitedIf($this);
    }

    public function thenProhibited(): ProhibitedIfRule
    {
        return $this->prohibited();
    }

    public function allOf(DeclarativeConditionInterface ...$conditions): CompositeCondition
    {
        return Rule::allOf($this, ...$conditions);
    }

    public function anyOf(DeclarativeConditionInterface ...$conditions): CompositeCondition
    {
        return Rule::anyOf($this, ...$conditions);
    }

    public function andWhen(string $field): ChainedWhenBuilder
    {
        return new ChainedWhenBuilder($this, 'all', $field);
    }

    public function orWhen(string $field): ChainedWhenBuilder
    {
        return new ChainedWhenBuilder($this, 'any', $field);
    }
}