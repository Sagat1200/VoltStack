<?php

declare(strict_types=1);

namespace VoltStack\Framework\Tests\Validation;

use Quantum\Validation\ValidationException;
use Quantum\Validation\ValidationCallbackContext;
use Quantum\Validation\ValidationRuleContext;
use Quantum\Validation\Rule;
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

    public function test_validator_supports_bail_rule(): void
    {
        $validator = new Validator();

        try {
            $validator->validate([
                'email' => 'not-an-email-address',
            ], [
                'email' => 'bail|email|max:5',
            ]);

            self::fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame([
                'email' => ['The email field must be a valid email address.'],
            ], $exception->errors());
        }
    }

    public function test_validator_supports_stop_on_first_failure(): void
    {
        $validator = new Validator();

        try {
            $validator->stopOnFirstFailure()->validate([
                'email' => 'not-an-email',
                'website' => 'not-a-url',
            ], [
                'email' => ['required', 'email'],
                'website' => ['required', 'url'],
            ]);

            self::fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame([
                'email' => ['The email field must be a valid email address.'],
            ], $exception->errors());
        }

        try {
            $validator->validate([
                'email' => 'not-an-email',
                'website' => 'not-a-url',
            ], [
                'email' => ['required', 'email'],
                'website' => ['required', 'url'],
            ]);

            self::fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame([
                'email' => ['The email field must be a valid email address.'],
                'website' => ['The website field must be a valid URL.'],
            ], $exception->errors());
        }
    }

    public function test_validator_supports_after_callbacks(): void
    {
        $validator = new Validator();

        try {
            $validator
                ->after(static function (ValidationCallbackContext $context): void {
                    if (($context->data()['email'] ?? null) === 'taken@example.com') {
                        $context->addError('email', 'The email has already been taken.');
                    }
                })
                ->validate([
                    'email' => 'taken@example.com',
                ], [
                    'email' => ['required', 'email'],
                ]);

            self::fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame([
                'email' => ['The email has already been taken.'],
            ], $exception->errors());
        }
    }

    public function test_validator_after_callbacks_can_inspect_existing_errors_and_do_not_persist(): void
    {
        $validator = new Validator();

        try {
            $validator
                ->after(static function (ValidationCallbackContext $context): void {
                    if ($context->hasErrors()) {
                        $context->addError('meta', 'Post validation checks failed.');
                    }
                })
                ->validate([
                    'email' => 'not-an-email',
                ], [
                    'email' => ['required', 'email'],
                ]);

            self::fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame([
                'email' => ['The email field must be a valid email address.'],
                'meta' => ['Post validation checks failed.'],
            ], $exception->errors());
        }

        $validated = $validator->validate([
            'email' => 'user@example.com',
        ], [
            'email' => ['required', 'email'],
        ]);

        self::assertSame([
            'email' => 'user@example.com',
        ], $validated);
    }

    public function test_validator_supports_rule_objects_alongside_string_rules(): void
    {
        $validator = new Validator();

        $validated = $validator->validate([
            'email' => 'user@example.com',
            'name' => 'VoltStack',
            'bio' => 'core team',
        ], [
            'email' => [Rule::required(), Rule::email()],
            'name' => [Rule::min(3)],
            'bio' => ['string', 'min:4'],
        ]);

        self::assertSame([
            'email' => 'user@example.com',
            'name' => 'VoltStack',
            'bio' => 'core team',
        ], $validated);
    }

    public function test_validator_supports_custom_messages_with_rule_objects(): void
    {
        $validator = new Validator();

        try {
            $validator->validate([
                'email' => 'not-an-email',
                'name' => 'ab',
            ], [
                'email' => [Rule::required(), Rule::email()],
                'name' => [Rule::min(3)],
            ], [
                'email.email' => 'Debes indicar un :attribute valido.',
                'name.min' => 'El :attribute debe tener al menos :min caracteres.',
            ], [
                'email' => 'correo',
                'name' => 'nombre',
            ]);

            self::fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame([
                'email' => ['Debes indicar un correo valido.'],
                'name' => ['El nombre debe tener al menos 3 caracteres.'],
            ], $exception->errors());
        }
    }

    public function test_validator_supports_callback_rule_objects(): void
    {
        $validator = new Validator();

        try {
            $validator->validate([
                'lucky_number' => 3,
            ], [
                'lucky_number' => [
                    Rule::callback('even', static function (ValidationRuleContext $context): void {
                        if ((int) $context->value() % 2 !== 0) {
                            $context->fail('El campo :attribute debe ser par.');
                        }
                    }),
                ],
            ]);

            self::fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame([
                'lucky_number' => ['El campo lucky_number debe ser par.'],
            ], $exception->errors());
        }
    }

    public function test_validator_supports_additional_builtin_rule_objects(): void
    {
        $validator = new Validator();

        $validated = $validator->validate([
            'status' => 'published',
            'score' => 7,
            'slug' => 'core-api',
            'title' => 'Volt',
        ], [
            'status' => [Rule::in(['draft', 'published', 'archived'])],
            'score' => [Rule::between(5, 10)],
            'slug' => [Rule::regex('/^[a-z-]+$/')],
            'title' => [Rule::max(4)],
        ]);

        self::assertSame([
            'status' => 'published',
            'score' => 7,
            'slug' => 'core-api',
            'title' => 'Volt',
        ], $validated);
    }

    public function test_validator_supports_custom_messages_with_additional_builtin_rule_objects(): void
    {
        $validator = new Validator();

        try {
            $validator->validate([
                'status' => 'pending',
                'score' => 12,
                'slug' => 'Core API',
                'title' => 'VoltStack',
            ], [
                'status' => [Rule::in(['draft', 'published', 'archived'])],
                'score' => [Rule::between(5, 10)],
                'slug' => [Rule::regex('/^[a-z-]+$/')],
                'title' => [Rule::max(4)],
            ], [
                'status.in' => 'El :attribute seleccionado no es valido.',
                'score.between' => 'El :attribute debe estar entre :min y :max.',
                'slug.regex' => 'El :attribute tiene un formato invalido.',
                'title.max' => 'El :attribute no debe superar :max caracteres.',
            ], [
                'status' => 'estado',
                'score' => 'puntaje',
                'slug' => 'slug',
                'title' => 'titulo',
            ]);

            self::fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame([
                'status' => ['El estado seleccionado no es valido.'],
                'score' => ['El puntaje debe estar entre 5 y 10.'],
                'slug' => ['El slug tiene un formato invalido.'],
                'title' => ['El titulo no debe superar 4 caracteres.'],
            ], $exception->errors());
        }
    }

    public function test_validator_supports_text_boundary_and_size_rule_objects(): void
    {
        $validator = new Validator();

        $validated = $validator->validate([
            'code' => '1234',
            'username' => 'voltstack',
            'module' => 'api.users',
            'state' => 'draft',
            'handle' => 'volt-stack',
        ], [
            'code' => [Rule::digits(4)],
            'username' => [Rule::size(9)],
            'module' => [Rule::startsWith(['api.', 'web.'])],
            'state' => [Rule::endsWith(['ft', 'ed'])],
            'handle' => [Rule::notRegex('/\s/')],
        ]);

        self::assertSame([
            'code' => '1234',
            'username' => 'voltstack',
            'module' => 'api.users',
            'state' => 'draft',
            'handle' => 'volt-stack',
        ], $validated);
    }

    public function test_validator_supports_custom_messages_with_text_boundary_and_size_rule_objects(): void
    {
        $validator = new Validator();

        try {
            $validator->validate([
                'code' => '12ab',
                'username' => 'volts',
                'module' => 'admin.users',
                'state' => 'drafting',
                'handle' => 'volt stack',
            ], [
                'code' => [Rule::digits(4)],
                'username' => [Rule::size(9)],
                'module' => [Rule::startsWith(['api.', 'web.'])],
                'state' => [Rule::endsWith(['ft', 'ed'])],
                'handle' => [Rule::notRegex('/\s/')],
            ], [
                'code.digits' => 'El :attribute debe tener :digits digitos.',
                'username.size' => 'El :attribute debe medir exactamente :size caracteres.',
                'module.starts_with' => 'El :attribute debe iniciar con alguno de estos prefijos: :values.',
                'state.ends_with' => 'El :attribute debe terminar con alguno de estos sufijos: :values.',
                'handle.not_regex' => 'El :attribute tiene un formato invalido.',
            ], [
                'code' => 'codigo',
                'username' => 'usuario',
                'module' => 'modulo',
                'state' => 'estado',
                'handle' => 'handle',
            ]);

            self::fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame([
                'code' => ['El codigo debe tener 4 digitos.'],
                'username' => ['El usuario debe medir exactamente 9 caracteres.'],
                'module' => ['El modulo debe iniciar con alguno de estos prefijos: api.,web..'],
                'state' => ['El estado debe terminar con alguno de estos sufijos: ft,ed.'],
                'handle' => ['El handle tiene un formato invalido.'],
            ], $exception->errors());
        }
    }

    public function test_validator_supports_network_and_case_rule_objects(): void
    {
        $validator = new Validator();

        $validated = $validator->validate([
            'handle' => 'volt_stack-01',
            'resource_id' => '550e8400-e29b-41d4-a716-446655440000',
            'client_ip' => '127.0.0.1',
            'public_ipv4' => '8.8.8.8',
            'public_ipv6' => '2001:4860:4860::8888',
            'payload' => '{"team":"core","active":true}',
            'label' => 'coreteam',
            'country' => 'MEXICO',
        ], [
            'handle' => [Rule::ascii()],
            'resource_id' => [Rule::uuid()],
            'client_ip' => [Rule::ip()],
            'public_ipv4' => [Rule::ipv4()],
            'public_ipv6' => [Rule::ipv6()],
            'payload' => [Rule::json()],
            'label' => [Rule::lowercase()],
            'country' => [Rule::uppercase()],
        ]);

        self::assertSame([
            'handle' => 'volt_stack-01',
            'resource_id' => '550e8400-e29b-41d4-a716-446655440000',
            'client_ip' => '127.0.0.1',
            'public_ipv4' => '8.8.8.8',
            'public_ipv6' => '2001:4860:4860::8888',
            'payload' => '{"team":"core","active":true}',
            'label' => 'coreteam',
            'country' => 'MEXICO',
        ], $validated);
    }

    public function test_validator_supports_custom_messages_with_network_and_case_rule_objects(): void
    {
        $validator = new Validator();

        try {
            $validator->validate([
                'handle' => 'volt_stack-cafeñ',
                'resource_id' => 'not-a-uuid',
                'client_ip' => '999.999.999.999',
                'public_ipv4' => '2001:4860:4860::8888',
                'public_ipv6' => '8.8.8.8',
                'payload' => '{"team":',
                'label' => 'CoreTeam',
                'country' => 'Mexico',
            ], [
                'handle' => [Rule::ascii()],
                'resource_id' => [Rule::uuid()],
                'client_ip' => [Rule::ip()],
                'public_ipv4' => [Rule::ipv4()],
                'public_ipv6' => [Rule::ipv6()],
                'payload' => [Rule::json()],
                'label' => [Rule::lowercase()],
                'country' => [Rule::uppercase()],
            ], [
                'handle.ascii' => 'El :attribute solo debe contener caracteres ASCII.',
                'resource_id.uuid' => 'El :attribute debe ser un UUID valido.',
                'client_ip.ip' => 'El :attribute debe ser una IP valida.',
                'public_ipv4.ipv4' => 'El :attribute debe ser una IPv4 valida.',
                'public_ipv6.ipv6' => 'El :attribute debe ser una IPv6 valida.',
                'payload.json' => 'El :attribute debe ser un JSON valido.',
                'label.lowercase' => 'El :attribute debe estar en minusculas.',
                'country.uppercase' => 'El :attribute debe estar en mayusculas.',
            ], [
                'handle' => 'handle',
                'resource_id' => 'recurso',
                'client_ip' => 'ip cliente',
                'public_ipv4' => 'ipv4 publica',
                'public_ipv6' => 'ipv6 publica',
                'payload' => 'payload',
                'label' => 'etiqueta',
                'country' => 'pais',
            ]);

            self::fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame([
                'handle' => ['El handle solo debe contener caracteres ASCII.'],
                'resource_id' => ['El recurso debe ser un UUID valido.'],
                'client_ip' => ['El ip cliente debe ser una IP valida.'],
                'public_ipv4' => ['El ipv4 publica debe ser una IPv4 valida.'],
                'public_ipv6' => ['El ipv6 publica debe ser una IPv6 valida.'],
                'payload' => ['El payload debe ser un JSON valido.'],
                'label' => ['El etiqueta debe estar en minusculas.'],
                'country' => ['El pais debe estar en mayusculas.'],
            ], $exception->errors());
        }
    }

    public function test_validator_supports_core_primitive_rule_objects(): void
    {
        $validator = new Validator();

        $validated = $validator->validate([
            'title' => 'VoltStack',
            'flags' => ['core', 'api'],
            'active' => '1',
            'score' => '19.99',
            'age' => '18',
            'slug' => 'voltstack_core',
            'website' => 'https://voltstack.dev/docs',
            'published_at' => '2026-05-29 10:30:00',
        ], [
            'title' => [Rule::string()],
            'flags' => [Rule::array()],
            'active' => [Rule::boolean()],
            'score' => [Rule::numeric()],
            'age' => [Rule::integer()],
            'slug' => [Rule::alphaDash()],
            'website' => [Rule::url()],
            'published_at' => [Rule::date()],
        ]);

        self::assertSame([
            'title' => 'VoltStack',
            'flags' => ['core', 'api'],
            'active' => '1',
            'score' => '19.99',
            'age' => '18',
            'slug' => 'voltstack_core',
            'website' => 'https://voltstack.dev/docs',
            'published_at' => '2026-05-29 10:30:00',
        ], $validated);
    }

    public function test_validator_supports_custom_messages_with_core_primitive_rule_objects(): void
    {
        $validator = new Validator();

        try {
            $validator->validate([
                'title' => 123,
                'flags' => 'core,api',
                'active' => 'true',
                'score' => 'not-a-number',
                'age' => '18.5',
                'slug' => 'voltstack core',
                'website' => 'not-a-url',
                'published_at' => 'not-a-date',
            ], [
                'title' => [Rule::string()],
                'flags' => [Rule::array()],
                'active' => [Rule::boolean()],
                'score' => [Rule::numeric()],
                'age' => [Rule::integer()],
                'slug' => [Rule::alphaDash()],
                'website' => [Rule::url()],
                'published_at' => [Rule::date()],
            ], [
                'title.string' => 'El :attribute debe ser texto.',
                'flags.array' => 'El :attribute debe ser un arreglo.',
                'active.boolean' => 'El :attribute debe ser verdadero o falso.',
                'score.numeric' => 'El :attribute debe ser numerico.',
                'age.integer' => 'El :attribute debe ser entero.',
                'slug.alpha_dash' => 'El :attribute solo admite letras, numeros, guiones y guion bajo.',
                'website.url' => 'El :attribute debe ser una URL valida.',
                'published_at.date' => 'El :attribute debe ser una fecha valida.',
            ], [
                'title' => 'titulo',
                'flags' => 'banderas',
                'active' => 'activo',
                'score' => 'puntaje',
                'age' => 'edad',
                'slug' => 'slug',
                'website' => 'sitio',
                'published_at' => 'fecha',
            ]);

            self::fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame([
                'title' => ['El titulo debe ser texto.'],
                'flags' => ['El banderas debe ser un arreglo.'],
                'active' => ['El activo debe ser verdadero o falso.'],
                'score' => ['El puntaje debe ser numerico.'],
                'age' => ['El edad debe ser entero.'],
                'slug' => ['El slug solo admite letras, numeros, guiones y guion bajo.'],
                'website' => ['El sitio debe ser una URL valida.'],
                'published_at' => ['El fecha debe ser una fecha valida.'],
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

    public function test_validator_supports_accepted_url_date_and_alpha_dash_rules(): void
    {
        $validator = new Validator();

        $validated = $validator->validate([
            'terms' => 'yes',
            'website' => 'https://voltstack.dev/docs',
            'published_at' => '2026-05-29 10:30:00',
            'slug' => 'voltstack_docs-v1',
        ], [
            'terms' => ['required', 'accepted'],
            'website' => ['required', 'url'],
            'published_at' => ['required', 'date'],
            'slug' => ['required', 'alpha_dash'],
        ]);

        self::assertSame([
            'terms' => 'yes',
            'website' => 'https://voltstack.dev/docs',
            'published_at' => '2026-05-29 10:30:00',
            'slug' => 'voltstack_docs-v1',
        ], $validated);
    }

    public function test_validator_throws_errors_for_accepted_url_date_and_alpha_dash_rules(): void
    {
        $validator = new Validator();

        try {
            $validator->validate([
                'terms' => 'no',
                'website' => 'not-a-url',
                'published_at' => 'not-a-date',
                'slug' => 'voltstack docs',
            ], [
                'terms' => ['required', 'accepted'],
                'website' => ['required', 'url'],
                'published_at' => ['required', 'date'],
                'slug' => ['required', 'alpha_dash'],
            ]);

            self::fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame([
                'terms' => ['The terms field must be accepted.'],
                'website' => ['The website field must be a valid URL.'],
                'published_at' => ['The published_at field must be a valid date.'],
                'slug' => ['The slug field may only contain letters, numbers, dashes, and underscores.'],
            ], $exception->errors());
        }
    }

    public function test_validator_supports_nested_fields_and_wildcards(): void
    {
        $validator = new Validator();

        $validated = $validator->validate([
            'profile' => [
                'name' => 'VoltStack',
            ],
            'items' => [
                ['name' => 'core_api', 'price' => '10.5'],
                ['name' => 'admin-ui', 'price' => 20],
            ],
        ], [
            'profile.name' => ['required', 'string', 'min:3'],
            'items.*.name' => ['required', 'alpha_dash'],
            'items.*.price' => ['required', 'numeric'],
        ]);

        self::assertSame([
            'profile' => [
                'name' => 'VoltStack',
            ],
            'items' => [
                ['name' => 'core_api', 'price' => '10.5'],
                ['name' => 'admin-ui', 'price' => 20],
            ],
        ], $validated);
    }

    public function test_validator_supports_nested_messages_and_attributes_with_wildcards(): void
    {
        $validator = new Validator();

        try {
            $validator->validate([
                'profile' => [],
                'items' => [
                    ['name' => 'core api', 'price' => '10.5'],
                    ['price' => 'oops'],
                ],
            ], [
                'profile.name' => ['required'],
                'items.*.name' => ['required', 'alpha_dash'],
                'items.*.price' => ['required', 'numeric'],
            ], [
                'profile.name.required' => 'Debes completar :attribute.',
                'items.*.name.required' => 'Cada :attribute es obligatorio.',
                'items.*.name.alpha_dash' => 'Cada :attribute solo admite letras, numeros, guiones y guion bajo.',
                'items.*.price.numeric' => 'Cada :attribute debe ser numerico.',
            ], [
                'profile.name' => 'nombre del perfil',
                'items.*.name' => 'nombre del item',
                'items.*.price' => 'precio del item',
            ]);

            self::fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame([
                'profile.name' => ['Debes completar nombre del perfil.'],
                'items.0.name' => ['Cada nombre del item solo admite letras, numeros, guiones y guion bajo.'],
                'items.1.name' => ['Cada nombre del item es obligatorio.'],
                'items.1.price' => ['Cada precio del item debe ser numerico.'],
            ], $exception->errors());
        }
    }

    public function test_validator_supports_sometimes_present_required_if_and_required_unless_rules(): void
    {
        $validator = new Validator();

        $validated = $validator->validate([
            'status' => 'published',
            'published_at' => '2026-05-29',
            'tier' => 'premium',
            'meta' => null,
        ], [
            'nickname' => ['sometimes', 'string', 'min:3'],
            'published_at' => ['required_if:status,published', 'date'],
            'support_code' => ['required_unless:tier,premium'],
            'meta' => ['present'],
        ]);

        self::assertSame([
            'status' => 'published',
            'published_at' => '2026-05-29',
            'tier' => 'premium',
            'meta' => null,
        ], $validated);
    }

    public function test_validator_throws_errors_for_sometimes_present_required_if_and_required_unless_rules(): void
    {
        $validator = new Validator();

        try {
            $validator->validate([
                'status' => 'published',
                'tier' => 'basic',
                'nickname' => 'ab',
            ], [
                'nickname' => ['sometimes', 'string', 'min:3'],
                'published_at' => ['required_if:status,published'],
                'support_code' => ['required_unless:tier,premium'],
                'meta' => ['present'],
            ]);

            self::fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame([
                'nickname' => ['The nickname field must be at least 3.'],
                'published_at' => ['The published_at field is required when status is published.'],
                'support_code' => ['The support_code field is required unless tier is in premium.'],
                'meta' => ['The meta field must be present.'],
            ], $exception->errors());
        }
    }

    public function test_validator_supports_required_with_required_without_prohibited_and_prohibited_if_rules(): void
    {
        $validator = new Validator();

        $validated = $validator->validate([
            'email' => 'user@example.com',
            'phone' => '555-1234',
            'username' => 'voltstack',
            'status' => 'draft',
        ], [
            'phone' => ['required_with:email'],
            'username' => ['required_without:name'],
            'internal_notes' => ['prohibited'],
            'published_at' => ['prohibited_if:status,draft'],
        ]);

        self::assertSame([
            'email' => 'user@example.com',
            'phone' => '555-1234',
            'username' => 'voltstack',
            'status' => 'draft',
        ], $validated);
    }

    public function test_validator_throws_errors_for_required_with_required_without_prohibited_and_prohibited_if_rules(): void
    {
        $validator = new Validator();

        try {
            $validator->validate([
                'email' => 'user@example.com',
                'status' => 'draft',
                'internal_notes' => 'secret',
                'published_at' => '2026-05-29',
            ], [
                'phone' => ['required_with:email'],
                'username' => ['required_without:name'],
                'internal_notes' => ['prohibited'],
                'published_at' => ['prohibited_if:status,draft'],
            ]);

            self::fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame([
                'phone' => ['The phone field is required when any of email are present.'],
                'username' => ['The username field is required when any of name are missing.'],
                'internal_notes' => ['The internal_notes field is prohibited.'],
                'published_at' => ['The published_at field is prohibited when status is draft.'],
            ], $exception->errors());
        }
    }

    public function test_validator_supports_accepted_if_declined_declined_if_and_same_with_resolved_dependency(): void
    {
        $validator = new Validator();

        $validated = $validator->validate([
            'status' => 'published',
            'terms' => 'yes',
            'opt_out' => 'no',
            'cancel_newsletter' => 'off',
            'items' => [
                [
                    'password' => 'secret123',
                    'password_repeat' => 'secret123',
                ],
            ],
        ], [
            'terms' => ['accepted_if:status,published'],
            'opt_out' => ['declined'],
            'cancel_newsletter' => ['declined_if:status,published'],
            'items.*.password' => ['same:items.*.password_repeat'],
        ]);

        self::assertSame([
            'status' => 'published',
            'terms' => 'yes',
            'opt_out' => 'no',
            'cancel_newsletter' => 'off',
            'items' => [
                [
                    'password' => 'secret123',
                    'password_repeat' => 'secret123',
                ],
            ],
        ], $validated);
    }

    public function test_validator_throws_errors_for_accepted_if_declined_declined_if_and_same_with_resolved_dependency(): void
    {
        $validator = new Validator();

        try {
            $validator->validate([
                'status' => 'published',
                'terms' => 'no',
                'opt_out' => 'yes',
                'cancel_newsletter' => 'yes',
                'items' => [
                    [
                        'password' => 'secret123',
                        'password_repeat' => 'different',
                    ],
                ],
            ], [
                'terms' => ['accepted_if:status,published'],
                'opt_out' => ['declined'],
                'cancel_newsletter' => ['declined_if:status,published'],
                'items.*.password' => ['same:items.*.password_repeat'],
            ]);

            self::fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame([
                'terms' => ['The terms field must be accepted when status is published.'],
                'opt_out' => ['The opt_out field must be declined.'],
                'cancel_newsletter' => ['The cancel_newsletter field must be declined when status is published.'],
                'items.0.password' => ['The items.0.password field and items.0.password_repeat must match.'],
            ], $exception->errors());
        }
    }

    public function test_validator_supports_distinct_size_between_and_digits_rules(): void
    {
        $validator = new Validator();

        $validated = $validator->validate([
            'code' => '1234',
            'username' => 'volt',
            'score' => 7,
            'tags' => ['api', 'core'],
            'items' => [
                ['sku' => 'A-100'],
                ['sku' => 'B-200'],
            ],
        ], [
            'code' => ['digits:4'],
            'username' => ['size:4'],
            'score' => ['between:5,10'],
            'tags' => ['between:1,3'],
            'items.*.sku' => ['distinct'],
        ]);

        self::assertSame([
            'code' => '1234',
            'username' => 'volt',
            'score' => 7,
            'tags' => ['api', 'core'],
            'items' => [
                ['sku' => 'A-100'],
                ['sku' => 'B-200'],
            ],
        ], $validated);
    }

    public function test_validator_throws_errors_for_distinct_size_between_and_digits_rules(): void
    {
        $validator = new Validator();

        try {
            $validator->validate([
                'code' => '12ab',
                'username' => 'volts',
                'score' => 12,
                'tags' => ['api', 'core', 'admin', 'web'],
                'items' => [
                    ['sku' => 'A-100'],
                    ['sku' => 'A-100'],
                ],
            ], [
                'code' => ['digits:4'],
                'username' => ['size:4'],
                'score' => ['between:5,10'],
                'tags' => ['between:1,3'],
                'items.*.sku' => ['distinct'],
            ]);

            self::fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame([
                'code' => ['The code field must be 4 digits.'],
                'username' => ['The username field must be 4.'],
                'score' => ['The score field must be between 5 and 10.'],
                'tags' => ['The tags field must be between 1 and 3.'],
                'items.1.sku' => ['The items.1.sku field has a duplicate value.'],
            ], $exception->errors());
        }
    }

    public function test_validator_supports_regex_text_boundaries_and_case_rules(): void
    {
        $validator = new Validator();

        $validated = $validator->validate([
            'slug' => 'volt-stack',
            'username' => 'dev_volt',
            'module' => 'api.users',
            'state' => 'draft',
            'country' => 'MEXICO',
        ], [
            'slug' => ['regex:/^[a-z-]+$/'],
            'username' => ['not_regex:/\s/'],
            'module' => ['starts_with:api.,web.'],
            'state' => ['ends_with:ft,ed'],
            'country' => ['uppercase'],
        ]);

        self::assertSame([
            'slug' => 'volt-stack',
            'username' => 'dev_volt',
            'module' => 'api.users',
            'state' => 'draft',
            'country' => 'MEXICO',
        ], $validated);
    }

    public function test_validator_throws_errors_for_regex_text_boundaries_and_case_rules(): void
    {
        $validator = new Validator();

        try {
            $validator->validate([
                'slug' => 'Volt Stack',
                'username' => 'dev volt',
                'module' => 'admin.users',
                'state' => 'drafting',
                'label' => 'ApiTeam',
                'country' => 'Mexico',
            ], [
                'slug' => ['regex:/^[a-z-]+$/'],
                'username' => ['not_regex:/\s/'],
                'module' => ['starts_with:api.,web.'],
                'state' => ['ends_with:ft,ed'],
                'label' => ['lowercase'],
                'country' => ['uppercase'],
            ]);

            self::fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame([
                'slug' => ['The slug field format is invalid.'],
                'username' => ['The username field format is invalid.'],
                'module' => ['The module field must start with one of the following: api.,web..'],
                'state' => ['The state field must end with one of the following: ft,ed.'],
                'label' => ['The label field must be lowercase.'],
                'country' => ['The country field must be uppercase.'],
            ], $exception->errors());
        }
    }

    public function test_validator_supports_ascii_uuid_ip_and_json_rules(): void
    {
        $validator = new Validator();

        $validated = $validator->validate([
            'handle' => 'volt_stack-01',
            'resource_id' => '550e8400-e29b-41d4-a716-446655440000',
            'client_ip' => '127.0.0.1',
            'public_ipv4' => '8.8.8.8',
            'public_ipv6' => '2001:4860:4860::8888',
            'payload' => '{"team":"core","active":true}',
        ], [
            'handle' => ['ascii'],
            'resource_id' => ['uuid'],
            'client_ip' => ['ip'],
            'public_ipv4' => ['ipv4'],
            'public_ipv6' => ['ipv6'],
            'payload' => ['json'],
        ]);

        self::assertSame([
            'handle' => 'volt_stack-01',
            'resource_id' => '550e8400-e29b-41d4-a716-446655440000',
            'client_ip' => '127.0.0.1',
            'public_ipv4' => '8.8.8.8',
            'public_ipv6' => '2001:4860:4860::8888',
            'payload' => '{"team":"core","active":true}',
        ], $validated);
    }

    public function test_validator_throws_errors_for_ascii_uuid_ip_and_json_rules(): void
    {
        $validator = new Validator();

        try {
            $validator->validate([
                'handle' => 'volt_stack-cafeñ',
                'resource_id' => 'not-a-uuid',
                'client_ip' => '999.999.999.999',
                'public_ipv4' => '2001:4860:4860::8888',
                'public_ipv6' => '8.8.8.8',
                'payload' => '{"team":',
            ], [
                'handle' => ['ascii'],
                'resource_id' => ['uuid'],
                'client_ip' => ['ip'],
                'public_ipv4' => ['ipv4'],
                'public_ipv6' => ['ipv6'],
                'payload' => ['json'],
            ]);

            self::fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame([
                'handle' => ['The handle field must only contain ASCII characters.'],
                'resource_id' => ['The resource_id field must be a valid UUID.'],
                'client_ip' => ['The client_ip field must be a valid IP address.'],
                'public_ipv4' => ['The public_ipv4 field must be a valid IPv4 address.'],
                'public_ipv6' => ['The public_ipv6 field must be a valid IPv6 address.'],
                'payload' => ['The payload field must be a valid JSON string.'],
            ], $exception->errors());
        }
    }

    public function test_validator_supports_semantic_rule_objects_and_expressive_arguments(): void
    {
        $validator = new Validator();

        $validated = $validator->validate([
            'terms' => 'yes',
            'opt_out' => 'off',
            'nickname' => null,
            'email' => 'user@example.com',
            'phone' => '555-1234',
            'username' => 'voltstack',
            'status' => 'published',
            'visibility' => 'public',
            'marketing_opt_in' => 'yes',
            'cancel_newsletter' => 'off',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'pin' => '1234',
            'pin_repeat' => '1234',
            'internal_code' => 'VS-001',
            'support_code' => 'PRM-001',
            'review_notes' => 'checked',
            'items' => [
                [
                    'value' => 'alpha',
                    'value_repeat' => 'alpha',
                ],
            ],
        ], [
            'terms' => [Rule::accepted()],
            'opt_out' => [Rule::declined()],
            'nickname' => [Rule::nullable(), Rule::string(), Rule::max(10)],
            'phone' => [Rule::requiredWith(['email'])],
            'username' => [Rule::requiredWithout('display_name')],
            'visibility' => [Rule::in('public', 'private')],
            'marketing_opt_in' => [Rule::acceptedIf('status', 'published')],
            'cancel_newsletter' => [Rule::declinedIf(static fn(ValidationRuleContext $context): bool => ($context->data()['status'] ?? null) === 'published')],
            'password' => [Rule::confirmed()],
            'pin' => [Rule::same('pin_repeat')],
            'internal_code' => [Rule::requiredIf('status', 'published')],
            'support_code' => [Rule::requiredUnless('visibility', 'public')],
            'review_notes' => [Rule::requiredUnless(static fn(ValidationRuleContext $context): bool => ($context->data()['status'] ?? null) === 'draft')],
            'published_at' => [Rule::prohibitedIf('status', 'draft')],
            'items.*.value' => [Rule::confirmed('items.*.value_repeat')],
        ]);

        self::assertSame([
            'terms' => 'yes',
            'opt_out' => 'off',
            'nickname' => null,
            'email' => 'user@example.com',
            'phone' => '555-1234',
            'username' => 'voltstack',
            'status' => 'published',
            'visibility' => 'public',
            'marketing_opt_in' => 'yes',
            'cancel_newsletter' => 'off',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'pin' => '1234',
            'pin_repeat' => '1234',
            'internal_code' => 'VS-001',
            'support_code' => 'PRM-001',
            'review_notes' => 'checked',
            'items' => [
                [
                    'value' => 'alpha',
                    'value_repeat' => 'alpha',
                ],
            ],
        ], $validated);
    }

    public function test_validator_throws_errors_for_semantic_rule_objects_and_expressive_arguments(): void
    {
        $validator = new Validator();

        try {
            $validator->validate([
                'terms' => 'no',
                'opt_out' => 'yes',
                'nickname' => null,
                'email' => 'user@example.com',
                'status' => 'draft',
                'visibility' => 'friends-only',
                'marketing_opt_in' => 'no',
                'cancel_newsletter' => 'yes',
                'password' => 'secret123',
                'password_confirmation' => 'different',
                'pin' => '1234',
                'pin_repeat' => '0000',
                'flagged' => true,
                'published_at' => '2026-05-29',
                'items' => [
                    [
                        'value' => 'alpha',
                        'value_repeat' => 'beta',
                    ],
                ],
            ], [
                'terms' => [Rule::accepted()],
                'opt_out' => [Rule::declined()],
                'nickname' => [Rule::nullable(), Rule::string(), Rule::max(10)],
                'phone' => [Rule::requiredWith('email')],
                'username' => [Rule::requiredWithout('display_name')],
                'visibility' => [Rule::in('public', 'private')],
                'marketing_opt_in' => [Rule::acceptedIf('status', 'draft', 'published')],
                'cancel_newsletter' => [Rule::declinedIf('status', 'draft', 'published')],
                'password' => [Rule::confirmed()],
                'pin' => [Rule::same('pin_repeat')],
                'internal_code' => [Rule::requiredIf('status', 'draft', 'published')],
                'support_code' => [Rule::requiredUnless('visibility', 'public', 'private')],
                'review_notes' => [Rule::requiredIf(static fn(ValidationRuleContext $context): bool => ($context->data()['flagged'] ?? false) === true)],
                'audit_reason' => [Rule::requiredUnless(static fn(ValidationRuleContext $context): bool => ($context->data()['status'] ?? null) === 'archived')],
                'published_at' => [Rule::prohibitedIf('status', 'draft')],
                'items.*.value' => [Rule::confirmed('items.*.value_repeat')],
            ], [
                'terms.accepted' => 'Debes aceptar :attribute.',
                'opt_out.declined' => 'El campo :attribute debe estar desactivado.',
                'phone.required_with' => 'El campo :attribute es obligatorio cuando alguno de :values esta presente.',
                'username.required_without' => 'El campo :attribute es obligatorio cuando falta alguno de :values.',
                'visibility.in' => 'La :attribute seleccionada no es valida.',
                'marketing_opt_in.accepted_if' => 'Debes aceptar :attribute cuando :other es :value.',
                'cancel_newsletter.declined_if' => 'El campo :attribute debe estar desactivado cuando :other es :value.',
                'password.confirmed' => 'La confirmacion de :attribute no coincide.',
                'pin.same' => 'El campo :attribute debe coincidir con :other.',
                'internal_code.required_if' => 'El campo :attribute es obligatorio cuando :other es :value.',
                'review_notes.required_if' => 'El campo :attribute es obligatorio cuando el registro esta marcado.',
                'support_code.required_unless' => 'El campo :attribute es obligatorio salvo que :other este en :values.',
                'audit_reason.required_unless' => 'El campo :attribute es obligatorio salvo que el estado sea archivado.',
                'published_at.prohibited_if' => 'El campo :attribute esta prohibido cuando :other es :value.',
                'items.*.value.confirmed' => 'La confirmacion de :attribute no coincide.',
            ], [
                'terms' => 'terminos',
                'opt_out' => 'baja',
                'phone' => 'telefono',
                'username' => 'usuario',
                'visibility' => 'visibilidad',
                'marketing_opt_in' => 'suscripcion',
                'cancel_newsletter' => 'cancelacion',
                'password' => 'contrasena',
                'pin' => 'pin',
                'pin_repeat' => 'repeticion del pin',
                'internal_code' => 'codigo interno',
                'review_notes' => 'notas de revision',
                'support_code' => 'codigo de soporte',
                'audit_reason' => 'motivo de auditoria',
                'status' => 'estado',
                'published_at' => 'fecha de publicacion',
                'items.*.value' => 'valor del item',
            ]);

            self::fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame([
                'terms' => ['Debes aceptar terminos.'],
                'opt_out' => ['El campo baja debe estar desactivado.'],
                'phone' => ['El campo telefono es obligatorio cuando alguno de email esta presente.'],
                'username' => ['El campo usuario es obligatorio cuando falta alguno de display_name.'],
                'visibility' => ['La visibilidad seleccionada no es valida.'],
                'marketing_opt_in' => ['Debes aceptar suscripcion cuando estado es draft, published.'],
                'cancel_newsletter' => ['El campo cancelacion debe estar desactivado cuando estado es draft, published.'],
                'password' => ['La confirmacion de contrasena no coincide.'],
                'pin' => ['El campo pin debe coincidir con repeticion del pin.'],
                'internal_code' => ['El campo codigo interno es obligatorio cuando estado es draft, published.'],
                'support_code' => ['El campo codigo de soporte es obligatorio salvo que visibilidad este en public, private.'],
                'review_notes' => ['El campo notas de revision es obligatorio cuando el registro esta marcado.'],
                'audit_reason' => ['El campo motivo de auditoria es obligatorio salvo que el estado sea archivado.'],
                'published_at' => ['El campo fecha de publicacion esta prohibido cuando estado es draft.'],
                'items.0.value' => ['La confirmacion de valor del item no coincide.'],
            ], $exception->errors());
        }
    }
}
