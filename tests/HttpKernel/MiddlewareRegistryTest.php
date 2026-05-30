<?php

declare(strict_types=1);

namespace VoltStack\Framework\Tests\HttpKernel;

use Closure;
use Quantum\Http\Request;
use Quantum\Http\Response;
use Quantum\HttpKernel\Contracts\MiddlewareInterface;
use VoltStack\Framework\Tests\TestCase;

final class MiddlewareRegistryTest extends TestCase
{
    public function test_kernel_resolves_middleware_alias(): void
    {
        $app = $this->createApplication();
        $app->container()->instance(AliasMiddleware::class, new AliasMiddleware());
        $app->kernel()->aliasMiddleware('uppercase', AliasMiddleware::class);

        $app->router()
            ->get('/alias', static fn (): string => 'alias-ok')
            ->middleware('uppercase');

        $response = $app->kernel()->handle(Request::create('GET', '/alias'));

        self::assertSame('ALIAS-OK', $response->content());
    }

    public function test_kernel_resolves_middleware_groups(): void
    {
        $app = $this->createApplication();
        $app->container()->instance(PrefixMiddleware::class, new PrefixMiddleware());
        $app->container()->instance(SuffixMiddleware::class, new SuffixMiddleware());

        $app->kernel()->aliasMiddleware('prefix', PrefixMiddleware::class);
        $app->kernel()->aliasMiddleware('suffix', SuffixMiddleware::class);
        $app->kernel()->middlewareGroup('web', ['prefix', 'suffix']);

        $app->router()
            ->get('/group', static fn (): string => 'core')
            ->middleware('web');

        $response = $app->kernel()->handle(Request::create('GET', '/group'));

        self::assertSame('[core]', $response->content());
    }

    public function test_route_groups_share_prefix_and_middleware(): void
    {
        $app = $this->createApplication();
        $app->container()->instance(PrefixMiddleware::class, new PrefixMiddleware());
        $app->container()->instance(SuffixMiddleware::class, new SuffixMiddleware());

        $app->kernel()->aliasMiddleware('prefix', PrefixMiddleware::class);
        $app->kernel()->aliasMiddleware('suffix', SuffixMiddleware::class);
        $app->kernel()->middlewareGroup('web', ['prefix', 'suffix']);

        $app->router()
            ->prefix('api')
            ->middleware('web')
            ->group(function (\Quantum\Routing\Router $router): void {
                $router->get('/status', static fn (): string => 'ok');
            });

        $response = $app->kernel()->handle(Request::create('GET', '/api/status'));

        self::assertSame('[ok]', $response->content());
    }
}

final class AliasMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        return Response::make(strtoupper($response->content()), $response->status(), $response->headers());
    }
}

final class PrefixMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        return Response::make('[' . $response->content(), $response->status(), $response->headers());
    }
}

final class SuffixMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        return Response::make($response->content() . ']', $response->status(), $response->headers());
    }
}
