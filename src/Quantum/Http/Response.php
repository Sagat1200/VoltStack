<?php

declare(strict_types=1);

namespace Quantum\Http;

use JsonException;

final class Response
{
    public function __construct(
        protected string $content = '',
        protected int $status = 200,
        protected array $headers = [],
    ) {
    }

    public static function make(string $content = '', int $status = 200, array $headers = []): self
    {
        return new self($content, $status, $headers);
    }

    public static function json(mixed $data, int $status = 200, array $headers = []): self
    {
        try {
            $content = json_encode($data, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $content = '{"error":"Unable to encode response."}';
            $status = 500;
        }

        $headers['Content-Type'] = 'application/json';

        return new self($content, $status, $headers);
    }

    public function content(): string
    {
        return $this->content;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function header(string $key, mixed $default = null): mixed
    {
        return $this->headers[$key] ?? $default;
    }

    public function withHeader(string $key, string $value): self
    {
        $clone = clone $this;
        $clone->headers[$key] = $value;

        return $clone;
    }
}
