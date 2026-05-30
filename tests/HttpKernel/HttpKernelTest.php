<?php

declare(strict_types=1);

namespace VoltStack\Framework\Tests\HttpKernel;

use Closure;
use Quantum\Http\Request;
use Quantum\Http\Response;
use Quantum\HttpKernel\Contracts\MiddlewareInterface;
use VoltStack\Framework\Tests\TestCase;

final class HttpKernelTest extends TestCase
{
    public function test_kernel_dispatches_closure_route_and_injects_request(): void
    {
        $app = $this->createApplication();
        $app->router()->get('/hello/{name}', function (Request $request, string $name): string {
            return 'Hello ' . $name . ' via ' . $request->method();
        });

        $response = $app->kernel()->handle(Request::create('GET', '/hello/VoltStack'));

        self::assertSame(200, $response->status());
        self::assertSame('Hello VoltStack via GET', $response->content());
    }

    public function test_kernel_dispatches_controller_string_through_container(): void
    {
        $app = $this->createApplication();
        $app->router()->get('/controller/{id}', ControllerHandler::class . '@show');

        $response = $app->kernel()->handle(Request::create('GET', '/controller/7'));

        self::assertSame(200, $response->status());
        self::assertSame('controller:7', $response->content());
    }

    public function test_kernel_runs_middleware_stack(): void
    {
        $app = $this->createApplication();
        $app->container()->instance(UppercaseMiddleware::class, new UppercaseMiddleware());

        $app->router()
            ->get('/middleware', static fn (): string => 'ok')
            ->middleware(UppercaseMiddleware::class);

        $response = $app->kernel()->handle(Request::create('GET', '/middleware'));

        self::assertSame('OK', $response->content());
    }

    public function test_kernel_returns_not_found_response_for_unknown_route(): void
    {
        $app = $this->createApplication();

        $response = $app->kernel()->handle(Request::create('GET', '/missing'));

        self::assertSame(404, $response->status());
        self::assertSame('Not Found', $response->content());
    }

    public function test_kernel_returns_not_found_when_route_binding_cannot_be_resolved(): void
    {
        $app = $this->createApplication();
        $app->bindRouteType(BoundPost::class, static fn (string $value): ?BoundPost => null);
        $app->router()->get('/posts/{post}', BoundPostController::class . '@show');

        $response = $app->kernel()->handle(Request::create('GET', '/posts/999'));

        self::assertSame(404, $response->status());
        self::assertSame('Route binding [post] could not be resolved.', $response->content());
    }
}

final class ControllerHandler
{
    public function show(string $id): string
    {
        return 'controller:' . $id;
    }
}

final class UppercaseMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        return Response::make(strtoupper($response->content()), $response->status(), $response->headers());
    }
}

final class BoundPostController
{
    public function show(BoundPost $post): string
    {
        return $post->id;
    }
}

final class BoundPost
{
    public function __construct(
        public string $id,
    ) {
    }
}
