<?php

declare(strict_types=1);

namespace Quantum\HttpKernel;

use Closure;
use Psr\Container\ContainerInterface;
use Quantum\Exceptions\Contracts\ExceptionHandlerInterface;
use Quantum\Exceptions\NotFoundHttpException;
use Quantum\Http\Request;
use Quantum\Http\Response;
use Quantum\Http\ResponseFactory;
use Quantum\HttpKernel\Contracts\HttpKernelInterface;
use Quantum\HttpKernel\Contracts\MiddlewareInterface;
use Quantum\Routing\ResolvedRoute;
use Quantum\Routing\Route;
use Quantum\Routing\Router;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use RuntimeException;

final class HttpKernel implements HttpKernelInterface
{
    /** @var array<int, mixed> */
    protected array $middleware = [];

    public function __construct(
        protected Router $router,
        protected ContainerInterface $container,
        protected ResponseFactory $responses = new ResponseFactory(),
        protected ?ExceptionHandlerInterface $exceptions = null,
        protected ?MiddlewareRegistry $middlewareRegistry = null,
    ) {}

    public function handle(Request $request): Response
    {
        try {
            $resolved = $this->router->resolve($request);

            if ($resolved === null) {
                throw new NotFoundHttpException();
            }

            $request = $request
                ->withAttribute('route', $resolved->route())
                ->withAttribute('route_parameters', $resolved->parameters());

            foreach ($resolved->parameters() as $key => $value) {
                $request = $request->withAttribute($key, $value);
            }

            $destination = fn(Request $currentRequest): Response => $this->dispatchResolvedRoute($currentRequest, $resolved);
            $stack = $this->middlewareRegistry()->resolve(
                array_merge($this->middleware, $resolved->route()->middlewares())
            );

            foreach (array_reverse($stack) as $middleware) {
                $next = $destination;
                $destination = fn(Request $currentRequest): Response => $this->runMiddleware(
                    $middleware,
                    $currentRequest,
                    $next,
                );
            }

            return $destination($request);
        } catch (\Throwable $throwable) {
            return $this->exceptionHandler()->render($request, $throwable);
        }
    }

    public function pushMiddleware(mixed $middleware): self
    {
        $this->middleware[] = $middleware;

        return $this;
    }

    public function aliasMiddleware(string $name, mixed $middleware): self
    {
        $this->middlewareRegistry()->alias($name, $middleware);

        return $this;
    }

    public function middlewareGroup(string $name, array $middleware): self
    {
        $this->middlewareRegistry()->group($name, $middleware);

        return $this;
    }

    protected function runMiddleware(mixed $middleware, Request $request, Closure $next): Response
    {
        if (is_string($middleware)) {
            $middleware = $this->container->get($middleware);
        }

        if ($middleware instanceof MiddlewareInterface) {
            return $middleware->handle($request, $next);
        }

        if (is_callable($middleware)) {
            $response = $middleware($request, $next);

            return $response instanceof Response ? $response : $this->responses->from($response);
        }

        throw new RuntimeException('Invalid middleware provided to HttpKernel.');
    }

    protected function dispatchResolvedRoute(Request $request, ResolvedRoute $resolved): Response
    {
        $result = $this->dispatchHandler(
            $resolved->route()->handler(),
            $request,
            $resolved->parameters(),
        );

        return $this->responses->from($result);
    }

    protected function exceptionHandler(): ExceptionHandlerInterface
    {
        if ($this->exceptions instanceof ExceptionHandlerInterface) {
            return $this->exceptions;
        }

        if ($this->container->has(ExceptionHandlerInterface::class)) {
            /** @var ExceptionHandlerInterface $handler */
            $handler = $this->container->get(ExceptionHandlerInterface::class);

            return $handler;
        }

        return new \Quantum\Exceptions\ExceptionHandler($this->responses);
    }

    protected function middlewareRegistry(): MiddlewareRegistry
    {
        if ($this->middlewareRegistry instanceof MiddlewareRegistry) {
            return $this->middlewareRegistry;
        }

        if ($this->container->has(MiddlewareRegistry::class)) {
            /** @var MiddlewareRegistry $registry */
            $registry = $this->container->get(MiddlewareRegistry::class);

            return $registry;
        }

        return new MiddlewareRegistry();
    }

    protected function dispatchHandler(mixed $handler, Request $request, array $parameters): mixed
    {
        if (is_string($handler) && str_contains($handler, '@')) {
            [$class, $method] = explode('@', $handler, 2);
            $instance = $this->container->get($class);

            return $this->invokeCallable([$instance, $method], $request, $parameters);
        }

        if (is_string($handler) && class_exists($handler)) {
            $instance = $this->container->get($handler);

            if (!is_callable($instance)) {
                throw new RuntimeException("Handler [$handler] is not invokable.");
            }

            return $this->invokeCallable($instance, $request, $parameters);
        }

        if (is_array($handler) && count($handler) === 2) {
            [$target, $method] = $handler;
            $instance = is_string($target) ? $this->container->get($target) : $target;

            return $this->invokeCallable([$instance, $method], $request, $parameters);
        }

        if (is_callable($handler)) {
            return $this->invokeCallable($handler, $request, $parameters);
        }

        throw new RuntimeException('Route handler could not be dispatched.');
    }

    protected function invokeCallable(callable $callable, Request $request, array $routeParameters): mixed
    {
        $reflection = is_array($callable)
            ? new ReflectionMethod($callable[0], (string) $callable[1])
            : new ReflectionFunction(Closure::fromCallable($callable));

        $arguments = [];

        foreach ($reflection->getParameters() as $parameter) {
            $arguments[] = $this->resolveArgument($parameter, $request, $routeParameters);
        }

        return $callable(...$arguments);
    }

    protected function resolveArgument(
        ReflectionParameter $parameter,
        Request $request,
        array $routeParameters,
    ): mixed {
        $name = $parameter->getName();

        if (array_key_exists($name, $routeParameters)) {
            return $routeParameters[$name];
        }

        $type = $parameter->getType();

        if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
            $typeName = $type->getName();

            if ($typeName === Request::class) {
                return $request;
            }

            if ($typeName === Route::class) {
                return $request->attribute('route');
            }

            return $this->container->get($typeName);
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new RuntimeException(
            sprintf('Unable to resolve argument [%s] for route handler.', $name)
        );
    }
}
