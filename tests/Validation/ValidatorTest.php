<?php

declare(strict_types=1);

namespace VoltStack\Framework\Tests\Validation;

use Quantum\Validation\ValidationException;
use Quantum\Validation\Validator;
use VoltStack\Framework\Tests\TestCase;

final class ValidatorTest extends TestCase
{
    public function test_validator_accepts_valid_payload(): void
    {
        $validator = new Validator();

        $validated = $validator->validate([
            'email' => 'user@example.com',
            'name' => 'VoltStack',
            'age' => '18',
        ], [
            'email' => ['required', 'email'],
            'name' => ['required', 'string', 'min:3'],
            'age' => ['integer', 'min:18'],
        ]);

        self::assertSame('user@example.com', $validated['email']);
        self::assertSame('VoltStack', $validated['name']);
        self::assertSame('18', $validated['age']);
    }

    public function test_validator_throws_validation_exception_with_errors(): void
    {
        $validator = new Validator();

        try {
            $validator->validate([
                'email' => 'not-an-email',
                'name' => 'ab',
            ], [
                'email' => ['required', 'email'],
                'name' => ['required', 'string', 'min:3'],
                'password' => ['required'],
            ]);

            self::fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame(422, $exception->statusCode());
            self::assertArrayHasKey('email', $exception->errors());
            self::assertArrayHasKey('name', $exception->errors());
            self::assertArrayHasKey('password', $exception->errors());
        }
    }

    public function test_validator_supports_custom_messages_and_attributes(): void
    {
        $validator = new Validator();

        try {
            $validator->validate([
                'email' => 'not-an-email',
            ], [
                'email' => ['required', 'email'],
                'name' => ['required'],
            ], [
                'email.email' => 'Debes indicar un :attribute valido.',
                'required' => 'El campo :attribute es obligatorio.',
            ], [
                'email' => 'correo electronico',
                'name' => 'nombre completo',
            ]);

            self::fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame([
                'email' => ['Debes indicar un correo electronico valido.'],
                'name' => ['El campo nombre completo es obligatorio.'],
            ], $exception->errors());
        }
    }

    public function test_validator_supports_nullable_array_boolean_and_max_rules(): void
    {
        $validator = new Validator();

        $validated = $validator->validate([
            'nickname' => null,
            'flags' => ['a', 'b'],
            'active' => '1',
            'title' => 'Volt',
            'score' => 10,
        ], [
            'nickname' => ['nullable', 'string', 'max:10'],
            'flags' => ['required', 'array', 'max:3'],
            'active' => ['required', 'boolean'],
            'title' => ['required', 'string', 'max:4'],
            'score' => ['required', 'max:10'],
        ]);

        self::assertSame([
            'nickname' => null,
            'flags' => ['a', 'b'],
            'active' => '1',
            'title' => 'Volt',
            'score' => 10,
        ], $validated);
    }

    public function test_validator_throws_errors_for_array_boolean_and_max_rules(): void
    {
        $validator = new Validator();

        try {
            $validator->validate([
                'flags' => 'not-an-array',
                'active' => 'true',
                'title' => 'VoltStack',
                'score' => 11,
            ], [
                'flags' => ['required', 'array'],
                'active' => ['required', 'boolean'],
                'title' => ['required', 'string', 'max:4'],
                'score' => ['required', 'max:10'],
            ]);

            self::fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame([
                'flags' => ['The flags field must be an array.'],
                'active' => ['The active field must be true or false.'],
                'title' => ['The title field may not be greater than 4.'],
                'score' => ['The score field may not be greater than 10.'],
            ], $exception->errors());
        }
    }

    public function test_validator_supports_confirmed_same_in_and_numeric_rules(): void
    {
        $validator = new Validator();

        $validated = $validator->validate([
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'pin' => '1234',
            'pin_repeat' => '1234',
            'status' => 'published',
            'price' => '19.99',
        ], [
            'password' => ['required', 'confirmed'],
            'pin' => ['required', 'same:pin_repeat'],
            'status' => ['required', 'in:draft,published,archived'],
            'price' => ['required', 'numeric'],
        ]);

        self::assertSame([
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'pin' => '1234',
            'pin_repeat' => '1234',
            'status' => 'published',
            'price' => '19.99',
        ], $validated);
    }

    public function test_validator_throws_errors_for_confirmed_same_in_and_numeric_rules(): void
    {
        $validator = new Validator();

        try {
            $validator->validate([
                'password' => 'secret123',
                'password_confirmation' => 'different',
                'pin' => '1234',
                'pin_repeat' => '0000',
                'status' => 'pending',
                'price' => 'not-a-number',
            ], [
                'password' => ['required', 'confirmed'],
                'pin' => ['required', 'same:pin_repeat'],
                'status' => ['required', 'in:draft,published,archived'],
                'price' => ['required', 'numeric'],
            ]);

            self::fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame([
                'password' => ['The password field confirmation does not match.'],
                'pin' => ['The pin field and pin_repeat must match.'],
                'status' => ['The selected status is invalid.'],
                'price' => ['The price field must be a number.'],
            ], $exception->errors());
        }
    }
}
