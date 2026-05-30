<?php

declare(strict_types=1);

namespace Quantum\Routing;

final class Route
{
    protected ?string $name = null;

    /** @var array<int, mixed> */
    protected array $middleware = [];

    public function __construct(
        protected array $methods,
        protected string $uri,
        protected mixed $handler,
    ) {
        $this->methods = array_map(
            static fn(string $method): string => strtoupper($method),
            $methods
        );
        $this->uri = $this->normalizeUri($uri);
    }

    public function methods(): array
    {
        return $this->methods;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function handler(): mixed
    {
        return $this->handler;
    }

    public function name(?string $name = null): string|self|null
    {
        if ($name === null) {
            return $this->name;
        }

        $this->name = $name;

        return $this;
    }

    /**
     * @param array<int, mixed>|mixed $middleware
     */
    public function middleware(mixed $middleware): self
    {
        if (!is_array($middleware)) {
            $middleware = [$middleware];
        }

        $this->middleware = [...$this->middleware, ...$middleware];

        return $this;
    }

    public function middlewares(): array
    {
        return $this->middleware;
    }

    public function allowsMethod(string $method): bool
    {
        return in_array(strtoupper($method), $this->methods, true) || in_array('ANY', $this->methods, true);
    }

    public function matchesPath(string $path): ?array
    {
        $path = $this->normalizeUri($path);
        $pattern = $this->compilePattern();

        $pattern = str_replace('\{', '{', $pattern);
        $pattern = str_replace('\}', '}', $pattern);

        if (!preg_match('#^' . $pattern . '$#', $path, $matches)) {
            return null;
        }

        $parameters = [];

        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $parameters[$key] = $value;
            }
        }

        return $parameters;
    }

    protected function normalizeUri(string $uri): string
    {
        $normalized = '/' . trim($uri, '/');

        return $normalized === '//' ? '/' : $normalized;
    }

    protected function compilePattern(): string
    {
        if ($this->uri === '/') {
            return '\/';
        }

        $segments = explode('/', trim($this->uri, '/'));
        $compiled = array_map(
            static function (string $segment): string {
                if (preg_match('/^\{([A-Za-z_][A-Za-z0-9_]*)\}$/', $segment, $matches) === 1) {
                    return '(?P<' . $matches[1] . '>[^/]+)';
                }

                return preg_quote($segment, '#');
            },
            $segments
        );

        return '\/' . implode('\/', $compiled);
    }
}
