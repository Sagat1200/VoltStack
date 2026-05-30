<?php

declare(strict_types=1);

namespace VoltStack\Framework\Tests\Http;

use Quantum\Http\Response;
use Quantum\Http\ResponseFactory;
use VoltStack\Framework\Tests\TestCase;

final class ResponseFactoryTest extends TestCase
{
    public function test_factory_creates_text_response(): void
    {
        $factory = new ResponseFactory();
        $response = $factory->text('hello', 202, ['X-Test' => '1']);

        self::assertSame(202, $response->status());
        self::assertSame('hello', $response->content());
        self::assertSame('1', $response->header('X-Test'));
    }

    public function test_factory_converts_payloads_to_response(): void
    {
        $factory = new ResponseFactory();

        self::assertInstanceOf(Response::class, $factory->from('plain'));
        self::assertSame('plain', $factory->from('plain')->content());
        self::assertSame('{"ok":true}', $factory->from(['ok' => true])->content());
        self::assertSame(404, $factory->notFound()->status());
    }
}
