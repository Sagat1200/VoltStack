<?php

declare(strict_types=1);

namespace Quantum\Validation;

use Closure;
use Quantum\Validation\Conditions\CompositeCondition;
use Quantum\Validation\Conditions\Contracts\DeclarativeConditionInterface;
use Quantum\Validation\Conditions\WhenFieldBuilder;
use Quantum\Validation\Rules\AcceptedIfRule;
use Quantum\Validation\Rules\AcceptedRule;
use Quantum\Validation\Rules\AlphaDashRule;
use Quantum\Validation\Rules\AsciiRule;
use Quantum\Validation\Rules\ArrayRule;
use Quantum\Validation\Rules\BetweenRule;
use Quantum\Validation\Rules\BooleanRule;
use Quantum\Validation\Rules\CallbackRule;
use Quantum\Validation\Rules\ConfirmedRule;
use Quantum\Validation\Rules\DateRule;
use Quantum\Validation\Rules\DeclinedIfRule;
use Quantum\Validation\Rules\DeclinedRule;
use Quantum\Validation\Rules\DigitsRule;
use Quantum\Validation\Rules\EmailRule;
use Quantum\Validation\Rules\EndsWithRule;
use Quantum\Validation\Rules\InRule;
use Quantum\Validation\Rules\IntegerRule;
use Quantum\Validation\Rules\IpRule;
use Quantum\Validation\Rules\Ipv4Rule;
use Quantum\Validation\Rules\Ipv6Rule;
use Quantum\Validation\Rules\JsonRule;
use Quantum\Validation\Rules\LowercaseRule;
use Quantum\Validation\Rules\MaxRule;
use Quantum\Validation\Rules\MinRule;
use Quantum\Validation\Rules\NullableRule;
use Quantum\Validation\Rules\NotRegexRule;
use Quantum\Validation\Rules\NumericRule;
use Quantum\Validation\Rules\PresentRule;
use Quantum\Validation\Rules\ProhibitedRule;
use Quantum\Validation\Rules\ProhibitedIfRule;
use Quantum\Validation\Rules\RegexRule;
use Quantum\Validation\Rules\RequiredRule;
use Quantum\Validation\Rules\RequiredIfRule;
use Quantum\Validation\Rules\RequiredUnlessRule;
use Quantum\Validation\Rules\RequiredWithRule;
use Quantum\Validation\Rules\RequiredWithoutRule;
use Quantum\Validation\Rules\SameRule;
use Quantum\Validation\Rules\SizeRule;
use Quantum\Validation\Rules\StartsWithRule;
use Quantum\Validation\Rules\StringRule;
use Quantum\Validation\Rules\UppercaseRule;
use Quantum\Validation\Rules\UrlRule;
use Quantum\Validation\Rules\UuidRule;

final class Rule
{
    public static function callback(string $name, callable $callback): CallbackRule
    {
        return new CallbackRule($name, $callback);
    }

    public static function ascii(): AsciiRule
    {
        return new AsciiRule();
    }

    public static function alphaDash(): AlphaDashRule
    {
        return new AlphaDashRule();
    }

    public static function array(): ArrayRule
    {
        return new ArrayRule();
    }

    public static function boolean(): BooleanRule
    {
        return new BooleanRule();
    }

    public static function accepted(): AcceptedRule
    {
        return new AcceptedRule();
    }

    public static function acceptedIf(bool|callable|string|DeclarativeConditionInterface $condition, mixed ...$expectedValues): AcceptedIfRule
    {
        if ($condition instanceof DeclarativeConditionInterface) {
            return new AcceptedIfRule($condition);
        }

        if (is_callable($condition) && !is_string($condition)) {
            $condition = Closure::fromCallable($condition);
        }

        return new AcceptedIfRule(
            $condition,
            is_string($condition) ? self::normalizeListArguments($expectedValues) : [],
        );
    }

    public static function declined(): DeclinedRule
    {
        return new DeclinedRule();
    }

    public static function declinedIf(bool|callable|string|DeclarativeConditionInterface $condition, mixed ...$expectedValues): DeclinedIfRule
    {
        if ($condition instanceof DeclarativeConditionInterface) {
            return new DeclinedIfRule($condition);
        }

        if (is_callable($condition) && !is_string($condition)) {
            $condition = Closure::fromCallable($condition);
        }

        return new DeclinedIfRule(
            $condition,
            is_string($condition) ? self::normalizeListArguments($expectedValues) : [],
        );
    }

    public static function email(): EmailRule
    {
        return new EmailRule();
    }

    public static function date(): DateRule
    {
        return new DateRule();
    }

    public static function digits(int $digits): DigitsRule
    {
        return new DigitsRule($digits);
    }

    /**
     * @param array<int, string> $needles
     */
    public static function endsWith(array $needles): EndsWithRule
    {
        return new EndsWithRule($needles);
    }

    public static function between(int $minimum, int $maximum): BetweenRule
    {
        return new BetweenRule($minimum, $maximum);
    }

    /**
     * @param mixed ...$allowed
     */
    public static function in(mixed ...$allowed): InRule
    {
        return new InRule(self::normalizeListArguments($allowed));
    }

    public static function integer(): IntegerRule
    {
        return new IntegerRule();
    }

    public static function ip(): IpRule
    {
        return new IpRule();
    }

    public static function ipv4(): Ipv4Rule
    {
        return new Ipv4Rule();
    }

    public static function ipv6(): Ipv6Rule
    {
        return new Ipv6Rule();
    }

    public static function json(): JsonRule
    {
        return new JsonRule();
    }

    public static function lowercase(): LowercaseRule
    {
        return new LowercaseRule();
    }

    public static function max(int $maximum): MaxRule
    {
        return new MaxRule($maximum);
    }

    public static function min(int $minimum): MinRule
    {
        return new MinRule($minimum);
    }

    public static function numeric(): NumericRule
    {
        return new NumericRule();
    }

    public static function notRegex(string $pattern): NotRegexRule
    {
        return new NotRegexRule($pattern);
    }

    public static function regex(string $pattern): RegexRule
    {
        return new RegexRule($pattern);
    }

    public static function nullable(): NullableRule
    {
        return new NullableRule();
    }

    public static function present(): PresentRule
    {
        return new PresentRule();
    }

    public static function prohibited(): ProhibitedRule
    {
        return new ProhibitedRule();
    }

    public static function required(): RequiredRule
    {
        return new RequiredRule();
    }

    public static function requiredIf(bool|callable|string|DeclarativeConditionInterface $condition, mixed ...$expectedValues): RequiredIfRule
    {
        if ($condition instanceof DeclarativeConditionInterface) {
            return new RequiredIfRule($condition);
        }

        if (is_callable($condition) && !is_string($condition)) {
            $condition = Closure::fromCallable($condition);
        }

        return new RequiredIfRule(
            $condition,
            is_string($condition) ? self::normalizeListArguments($expectedValues) : [],
        );
    }

    public static function requiredUnless(bool|callable|string|DeclarativeConditionInterface $condition, mixed ...$expectedValues): RequiredUnlessRule
    {
        if ($condition instanceof DeclarativeConditionInterface) {
            return new RequiredUnlessRule($condition);
        }

        if (is_callable($condition) && !is_string($condition)) {
            $condition = Closure::fromCallable($condition);
        }

        return new RequiredUnlessRule(
            $condition,
            is_string($condition) ? self::normalizeListArguments($expectedValues) : [],
        );
    }

    /**
     * @param mixed ...$fields
     */
    public static function requiredWith(mixed ...$fields): RequiredWithRule
    {
        return new RequiredWithRule(self::normalizeFieldArguments($fields));
    }

    /**
     * @param mixed ...$fields
     */
    public static function requiredWithout(mixed ...$fields): RequiredWithoutRule
    {
        return new RequiredWithoutRule(self::normalizeFieldArguments($fields));
    }

    public static function prohibitedIf(bool|callable|string|DeclarativeConditionInterface $condition, mixed ...$expectedValues): ProhibitedIfRule
    {
        if ($condition instanceof DeclarativeConditionInterface) {
            return new ProhibitedIfRule($condition);
        }

        if (is_callable($condition) && !is_string($condition)) {
            $condition = Closure::fromCallable($condition);
        }

        return new ProhibitedIfRule(
            $condition,
            is_string($condition) ? self::normalizeListArguments($expectedValues) : [],
        );
    }

    public static function same(string $otherField): SameRule
    {
        return new SameRule($otherField);
    }

    public static function when(string $field): WhenFieldBuilder
    {
        return new WhenFieldBuilder($field);
    }

    public static function allOf(DeclarativeConditionInterface ...$conditions): CompositeCondition
    {
        return new CompositeCondition('all', self::normalizeConditions($conditions));
    }

    public static function anyOf(DeclarativeConditionInterface ...$conditions): CompositeCondition
    {
        return new CompositeCondition('any', self::normalizeConditions($conditions));
    }

    public static function confirmed(?string $confirmationField = null): ConfirmedRule
    {
        return new ConfirmedRule($confirmationField);
    }

    public static function size(int $size): SizeRule
    {
        return new SizeRule($size);
    }

    /**
     * @param array<int, string> $needles
     */
    public static function startsWith(array $needles): StartsWithRule
    {
        return new StartsWithRule($needles);
    }

    public static function string(): StringRule
    {
        return new StringRule();
    }

    public static function uppercase(): UppercaseRule
    {
        return new UppercaseRule();
    }

    public static function url(): UrlRule
    {
        return new UrlRule();
    }

    public static function uuid(): UuidRule
    {
        return new UuidRule();
    }

    /**
     * @param array<int, mixed> $arguments
     * @return array<int, scalar|null>
     */
    protected static function normalizeListArguments(array $arguments): array
    {
        if (count($arguments) === 1 && is_array($arguments[0])) {
            $arguments = array_values($arguments[0]);
        }

        return array_values(array_filter(
            $arguments,
            static fn(mixed $value): bool => is_scalar($value) || $value === null,
        ));
    }

    /**
     * @param array<int, mixed> $arguments
     * @return array<int, string>
     */
    protected static function normalizeFieldArguments(array $arguments): array
    {
        if (count($arguments) === 1 && is_array($arguments[0])) {
            $arguments = array_values($arguments[0]);
        }

        return array_values(array_filter(
            array_map(static fn(mixed $value): string => (string) $value, $arguments),
            static fn(string $value): bool => $value !== '',
        ));
    }

    /**
     * @param array<int, mixed> $conditions
     * @return array<int, DeclarativeConditionInterface>
     */
    protected static function normalizeConditions(array $conditions): array
    {
        if (count($conditions) === 1 && is_array($conditions[0])) {
            $conditions = array_values($conditions[0]);
        }

        return array_values(array_filter(
            $conditions,
            static fn(mixed $condition): bool => $condition instanceof DeclarativeConditionInterface,
        ));
    }
}
