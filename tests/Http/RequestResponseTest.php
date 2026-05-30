<?php

declare(strict_types=1);

namespace VoltStack\Framework\Tests\Http;

use Quantum\Http\Request;
use Quantum\Http\Response;
use VoltStack\Framework\Tests\TestCase;

final class RequestResponseTest extends TestCase
{
    public function test_request_keeps_immutable_attributes(): void
    {
        $request = Request::create('GET', '/users', ['page' => 2], [], ['Accept' => 'application/json']);
        $updated = $request->withAttribute('tenant', 'acme');

        self::assertNull($request->attribute('tenant'));
        self::assertSame('acme', $updated->attribute('tenant'));
        self::assertSame('/users', $request->path());
        self::assertSame(2, $request->query('page'));
        self::assertSame('application/json', $request->header('accept'));
    }

    public function test_response_json_sets_content_and_header(): void
    {
        $response = Response::json(['ok' => true], 201);

        self::assertSame(201, $response->status());
        self::assertSame('application/json', $response->header('Content-Type'));
        self::assertSame('{"ok":true}', $response->content());
    }
}
