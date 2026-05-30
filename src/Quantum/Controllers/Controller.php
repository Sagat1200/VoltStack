<?php

declare(strict_types=1);

namespace Quantum\Controllers;

use Quantum\Actions\ActionDispatcher;
use Quantum\Http\Response;
use Quantum\Http\ResponseFactory;

abstract class Controller
{
    public function __construct(
        protected ResponseFactory $responses,
        protected ActionDispatcher $actions,
    ) {}

    protected function response(string $content = '', int $status = 200, array $headers = []): Response
    {
        return $this->responses->make($content, $status, $headers);
    }

    protected function json(mixed $data, int $status = 200, array $headers = []): Response
    {
        return $this->responses->json($data, $status, $headers);
    }

    protected function text(string $content, int $status = 200, array $headers = []): Response
    {
        return $this->responses->text($content, $status, $headers);
    }

    protected function empty(int $status = 204, array $headers = []): Response
    {
        return $this->responses->empty($status, $headers);
    }

    protected function notFound(string $content = 'Not Found', array $headers = []): Response
    {
        return $this->responses->notFound($content, $headers);
    }

    protected function action(string|object $action, array $payload = []): mixed
    {
        return $this->actions->dispatch($action, $payload);
    }
}
