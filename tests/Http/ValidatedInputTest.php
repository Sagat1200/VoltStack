<?php

declare(strict_types=1);

namespace VoltStack\Framework\Tests\Http;

use LogicException;
use Quantum\Http\ValidatedInput;
use VoltStack\Framework\Tests\TestCase;

final class ValidatedInputTest extends TestCase
{
    public function test_validated_input_supports_array_access_and_dot_notation(): void
    {
        $input = new ValidatedInput([
            'email' => 'user@example.com',
            'profile' => [
                'name' => 'VoltStack',
            ],
        ]);

        self::assertTrue(isset($input['email']));
        self::assertTrue(isset($input['profile.name']));
        self::assertSame('user@example.com', $input['email']);
        self::assertSame('VoltStack', $input['profile.name']);
        self::assertNull($input['missing']);
    }

    public function test_validated_input_is_iterable_and_json_serializable(): void
    {
        $input = new ValidatedInput([
            'email' => 'user@example.com',
            'profile' => [
                'name' => 'VoltStack',
            ],
        ]);

        self::assertSame([
            'email' => 'user@example.com',
            'profile' => [
                'name' => 'VoltStack',
            ],
        ], iterator_to_array($input));

        self::assertSame(
            '{"email":"user@example.com","profile":{"name":"VoltStack"}}',
            json_encode($input, JSON_THROW_ON_ERROR)
        );
    }

    public function test_validated_input_supports_presence_helpers_and_counting(): void
    {
        $input = new ValidatedInput([
            'email' => 'user@example.com',
            'profile' => [
                'name' => 'VoltStack',
                'nickname' => '',
            ],
            'active' => false,
            'score' => 0,
            'tags' => [],
        ]);

        self::assertTrue($input->has('email'));
        self::assertTrue($input->has(['email', 'profile.name']));
        self::assertFalse($input->has(['email', 'missing']));
        self::assertTrue($input->missing('missing'));
        self::assertFalse($input->missing('profile.name'));

        self::assertTrue($input->filled(['email', 'profile.name']));
        self::assertTrue($input->filled(['active', 'score']));
        self::assertFalse($input->filled('profile.nickname'));
        self::assertFalse($input->filled('tags'));
        self::assertFalse($input->filled('missing'));

        self::assertSame(5, $input->count());
        self::assertFalse($input->isEmpty());
        self::assertSame(0, (new ValidatedInput([]))->count());
        self::assertTrue((new ValidatedInput([]))->isEmpty());
    }

    public function test_validated_input_supports_merge_to_array_and_collect(): void
    {
        $input = new ValidatedInput([
            'email' => 'user@example.com',
            'profile' => [
                'name' => 'VoltStack',
                'role' => 'admin',
            ],
        ]);

        $merged = $input->merge([
            'profile' => [
                'role' => 'owner',
            ],
            'active' => true,
        ]);

        self::assertNotSame($input, $merged);
        self::assertSame([
            'email' => 'user@example.com',
            'profile' => [
                'name' => 'VoltStack',
                'role' => 'admin',
            ],
        ], $input->toArray());
        self::assertSame([
            'email' => 'user@example.com',
            'profile' => [
                'name' => 'VoltStack',
                'role' => 'owner',
            ],
            'active' => true,
        ], $merged->toArray());
        self::assertSame($merged->toArray(), iterator_to_array($merged->collect()));
    }

    public function test_validated_input_supports_map_filter_and_first(): void
    {
        $input = new ValidatedInput([
            'email' => 'user@example.com',
            'active' => false,
            'score' => 0,
            'nickname' => '',
            'tags' => [],
        ]);

        $mapped = $input->map(static fn(mixed $value, string $key): string => $key . ':' . get_debug_type($value));
        $filtered = $input->filter(static fn(mixed $value, string $key): bool => in_array($key, ['email', 'score'], true));
        $filled = $input->filter();

        self::assertSame([
            'email' => 'email:string',
            'active' => 'active:bool',
            'score' => 'score:int',
            'nickname' => 'nickname:string',
            'tags' => 'tags:array',
        ], $mapped->toArray());

        self::assertSame([
            'email' => 'user@example.com',
            'score' => 0,
        ], $filtered->toArray());

        self::assertSame([
            'email' => 'user@example.com',
            'active' => false,
            'score' => 0,
        ], $filled->toArray());

        self::assertSame('user@example.com', $input->first());
        self::assertSame(0, $input->first(static fn(mixed $value, string $key): bool => $key === 'score'));
        self::assertSame('fallback', $input->first(static fn(mixed $value, string $key): bool => $key === 'missing', 'fallback'));
    }

    public function test_validated_input_supports_keys_values_contains_and_pluck(): void
    {
        $input = new ValidatedInput([
            'first' => 'user@example.com',
            'second' => false,
            'third' => 0,
        ]);

        $users = new ValidatedInput([
            [
                'id' => 10,
                'profile' => [
                    'name' => 'VoltStack',
                ],
            ],
            [
                'id' => 20,
                'profile' => [
                    'name' => 'Quantum',
                ],
            ],
            [
                'profile' => [
                    'name' => 'Platform',
                ],
            ],
            'ignored',
        ]);

        self::assertSame(['first', 'second', 'third'], $input->keys());
        self::assertSame(['user@example.com', false, 0], $input->values());

        self::assertTrue($input->contains(false));
        self::assertFalse($input->contains('0'));
        self::assertTrue($input->contains(static fn(mixed $value, string $key): bool => $key === 'third' && $value === 0));
        self::assertFalse($input->contains(static fn(mixed $value, string $key): bool => $key === 'missing'));

        self::assertSame(['VoltStack', 'Quantum', 'Platform'], $users->pluck('profile.name'));
        self::assertSame([
            10 => 'VoltStack',
            20 => 'Quantum',
            21 => 'Platform',
        ], $users->pluck('profile.name', 'id'));
    }

    public function test_validated_input_supports_reduce_every_some_and_partition(): void
    {
        $input = new ValidatedInput([
            'first' => 2,
            'second' => 4,
            'third' => 5,
        ]);

        $sum = $input->reduce(
            static fn(int $carry, int $value, string $key): int => $carry + $value,
            0,
        );

        [$even, $odd] = $input->partition(
            static fn(int $value, string $key): bool => $value % 2 === 0,
        );

        self::assertSame(11, $sum);
        self::assertFalse($input->every(static fn(int $value, string $key): bool => $value % 2 === 0));
        self::assertTrue($input->every(static fn(int $value, string $key): bool => $value > 1));
        self::assertTrue($input->some(static fn(int $value, string $key): bool => $value % 2 !== 0));
        self::assertFalse($input->some(static fn(int $value, string $key): bool => $value > 10));

        self::assertInstanceOf(ValidatedInput::class, $even);
        self::assertInstanceOf(ValidatedInput::class, $odd);
        self::assertSame([
            'first' => 2,
            'second' => 4,
        ], $even->toArray());
        self::assertSame([
            'third' => 5,
        ], $odd->toArray());
    }

    public function test_validated_input_supports_sort_sort_by_keys_reverse_and_slice(): void
    {
        $input = new ValidatedInput([
            'b' => 3,
            'c' => 1,
            'a' => 2,
        ]);

        self::assertSame([
            'c' => 1,
            'a' => 2,
            'b' => 3,
        ], $input->sort()->toArray());

        self::assertSame([
            'b' => 3,
            'a' => 2,
            'c' => 1,
        ], $input->sort(static fn(int $left, int $right): int => $right <=> $left)->toArray());

        self::assertSame([
            'a' => 2,
            'b' => 3,
            'c' => 1,
        ], $input->sortByKeys()->toArray());

        self::assertSame([
            'c' => 1,
            'b' => 3,
            'a' => 2,
        ], $input->sortByKeys(static fn(string $left, string $right): int => $right <=> $left)->toArray());

        self::assertSame([
            'a' => 2,
            'c' => 1,
            'b' => 3,
        ], $input->reverse()->toArray());

        self::assertSame([
            'c' => 1,
            'b' => 3,
        ], $input->reverse()->slice(1, 2)->toArray());
    }

    public function test_validated_input_is_read_only(): void
    {
        $input = new ValidatedInput([
            'email' => 'user@example.com',
        ]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('ValidatedInput is read-only.');

        $input['email'] = 'other@example.com';
    }
}
