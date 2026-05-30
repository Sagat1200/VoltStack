<?php

declare(strict_types=1);

namespace Quantum\Http;

final class Request
{
    public function __construct(
        protected string $method,
        protected string $uri,
        protected array $query = [],
        protected array $body = [],
        protected array $headers = [],
        protected array $cookies = [],
        protected array $files = [],
        protected array $server = [],
        protected array $attributes = [],
        protected ?string $rawBody = null,
    ) {
        $this->method = strtoupper($method);
        $this->headers = $this->normalizeHeaders($headers);
    }

    public static function capture(): self
    {
        $headers = function_exists('getallheaders') ? (getallheaders() ?: []) : [];

        return new self(
            $_SERVER['REQUEST_METHOD'] ?? 'GET',
            $_SERVER['REQUEST_URI'] ?? '/',
            $_GET,
            is_array($_POST) ? $_POST : [],
            $headers,
            $_COOKIE,
            $_FILES,
            $_SERVER,
            [],
            file_get_contents('php://input') ?: null,
        );
    }

    public static function create(
        string $method,
        string $uri,
        array $query = [],
        array $body = [],
        array $headers = [],
    ): self {
        return new self($method, $uri, $query, $body, $headers);
    }

    public function method(): string
    {
        return $this->method;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function path(): string
    {
        $path = (string) parse_url($this->uri, PHP_URL_PATH);

        return $path === '' ? '/' : $path;
    }

    public function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->query;
        }

        return $this->query[$key] ?? $default;
    }

    public function input(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->body;
        }

        return $this->body[$key] ?? $default;
    }

    public function header(string $key, mixed $default = null): mixed
    {
        $normalized = strtolower($key);

        return $this->headers[$normalized] ?? $default;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function cookie(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->cookies;
        }

        return $this->cookies[$key] ?? $default;
    }

    public function files(): array
    {
        return $this->files;
    }

    public function server(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->server;
        }

        return $this->server[$key] ?? $default;
    }

    public function rawBody(): ?string
    {
        return $this->rawBody;
    }

    public function attribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function attributes(): array
    {
        return $this->attributes;
    }

    public function withAttribute(string $key, mixed $value): self
    {
        $clone = clone $this;
        $clone->attributes[$key] = $value;

        return $clone;
    }

    public function withMethod(string $method): self
    {
        $clone = clone $this;
        $clone->method = strtoupper($method);

        return $clone;
    }

    public function withHeader(string $key, string $value): self
    {
        $clone = clone $this;
        $clone->headers[strtolower($key)] = $value;

        return $clone;
    }

    protected function normalizeHeaders(array $headers): array
    {
        $normalized = [];

        foreach ($headers as $key => $value) {
            $normalized[strtolower((string) $key)] = $value;
        }

        return $normalized;
    }
}
