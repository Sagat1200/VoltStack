<?php

declare(strict_types=1);

namespace VoltStack\Framework\Tests\Controllers;

use Quantum\Controllers\Controller;
use Quantum\Http\Request;
use Quantum\Http\ResponseFactory;
use VoltStack\Framework\Tests\TestCase;

final class ControllerTest extends TestCase
{
    public function test_base_controller_can_return_json_using_response_helpers(): void
    {
        $app = $this->createApplication();
        $app->router()->get('/users/{id}', UserController::class . '@show');

        $response = $app->kernel()->handle(Request::create('GET', '/users/9'));

        self::assertSame(200, $response->status());
        self::assertSame('application/json', $response->header('Content-Type'));
        self::assertSame('{"id":"9","resource":"user"}', $response->content());
    }

    public function test_application_exposes_response_factory(): void
    {
        $app = $this->createApplication();

        self::assertInstanceOf(ResponseFactory::class, $app->responses());
    }
}

final class UserController extends Controller
{
    public function show(string $id)
    {
        return $this->json([
            'id' => $id,
            'resource' => 'user',
        ]);
    }
}
