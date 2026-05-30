<?php

declare(strict_types=1);

namespace VoltStack\Framework\Tests\Exceptions;

use Quantum\Exceptions\HttpException;
use Quantum\Http\Request;
use Quantum\Validation\ValidationException;
use VoltStack\Framework\Tests\TestCase;

final class ExceptionHandlingTest extends TestCase
{
    public function test_kernel_converts_generic_exception_to_500_response(): void
    {
        $app = $this->createApplication();
        $app->router()->get('/explode', static function (): never {
            throw new \RuntimeException('boom');
        });

        $response = $app->kernel()->handle(Request::create('GET', '/explode'));

        self::assertSame(500, $response->status());
        self::assertSame('Server Error', $response->content());
    }

    public function test_kernel_preserves_http_exception_status_and_message(): void
    {
        $app = $this->createApplication();
        $app->router()->get('/forbidden', static function (): never {
            throw new HttpException(403, 'Forbidden');
        });

        $response = $app->kernel()->handle(Request::create('GET', '/forbidden'));

        self::assertSame(403, $response->status());
        self::assertSame('Forbidden', $response->content());
    }

    public function test_kernel_renders_json_error_when_request_expects_json(): void
    {
        $app = $this->createApplication();
        $app->router()->get('/api/fail', static function (): never {
            throw new HttpException(422, 'Validation failed');
        });

        $response = $app->kernel()->handle(
            Request::create('GET', '/api/fail', [], [], ['Accept' => 'application/json'])
        );

        self::assertSame(422, $response->status());
        self::assertSame('application/json', $response->header('Content-Type'));
        self::assertSame('{"message":"Validation failed","status":422}', $response->content());
    }

    public function test_kernel_renders_validation_errors_in_json_payload(): void
    {
        $app = $this->createApplication();
        $app->router()->get('/api/validation', static function (): never {
            throw new ValidationException([
                'email' => ['The email field is required.'],
            ]);
        });

        $response = $app->kernel()->handle(
            Request::create('GET', '/api/validation', [], [], ['Accept' => 'application/json'])
        );

        self::assertSame(422, $response->status());
        self::assertSame(
            '{"message":"The given data was invalid.","status":422,"errors":{"email":["The email field is required."]}}',
            $response->content()
        );
    }
}
