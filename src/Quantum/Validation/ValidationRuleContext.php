<?php

declare(strict_types=1);

namespace Quantum\Validation;

final class ValidationRuleContext
{
    protected bool $failed = false;

    protected bool $shouldBreak = false;

    /**
     * @param callable(?string, array): bool $fail
     */
    public function __construct(
        protected string $pattern,
        protected string $field,
        protected mixed $value,
        protected bool $present,
        protected array $data,
        protected array $attributes,
        protected string $rule,
        protected $fail,
    ) {}

    public function pattern(): string
    {
        return $this->pattern;
    }

    public function field(): string
    {
        return $this->field;
    }

    public function value(): mixed
    {
        return $this->value;
    }

    public function present(): bool
    {
        return $this->present;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function attributes(): array
    {
        return $this->attributes;
    }

    public function rule(): string
    {
        return $this->rule;
    }

    public function fail(?string $message = null, array $replacements = []): void
    {
        $this->failed = true;
        $this->shouldBreak = ($this->fail)($message, $replacements);
    }

    public function failed(): bool
    {
        return $this->failed;
    }

    public function skipRemainingRules(): void
    {
        $this->shouldBreak = true;
    }

    public function shouldBreak(): bool
    {
        return $this->shouldBreak;
    }
}
