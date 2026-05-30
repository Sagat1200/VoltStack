<?php

declare(strict_types=1);

namespace Quantum\Validation;

use Quantum\Validation\Rules\AlphaDashRule;
use Quantum\Validation\Rules\AsciiRule;
use Quantum\Validation\Rules\ArrayRule;
use Quantum\Validation\Rules\BetweenRule;
use Quantum\Validation\Rules\BooleanRule;
use Quantum\Validation\Rules\CallbackRule;
use Quantum\Validation\Rules\DateRule;
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
use Quantum\Validation\Rules\NotRegexRule;
use Quantum\Validation\Rules\NumericRule;
use Quantum\Validation\Rules\RegexRule;
use Quantum\Validation\Rules\RequiredRule;
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
     * @param array<int, string|int|float|bool> $allowed
     */
    public static function in(array $allowed): InRule
    {
        return new InRule($allowed);
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

    public static function required(): RequiredRule
    {
        return new RequiredRule();
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
}
