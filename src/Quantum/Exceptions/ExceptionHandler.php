<?php

declare(strict_types=1);

namespace Quantum\Exceptions;

use Quantum\Exceptions\Contracts\ExceptionHandlerInterface;
use Quantum\Http\Request;
use Quantum\Http\Response;
use Quantum\Http\ResponseFactory;
use Quantum\Validation\ValidationException;
use Throwable;

final class ExceptionHandler implements ExceptionHandlerInterface
{
    public function __construct(
        protected ResponseFactory $responses = new ResponseFactory(),
    ) {}

    public function report(Throwable $throwable): void
    {
        // Reporting hooks can be added later without changing the kernel contract.
    }

    public function render(Request $request, Throwable $throwable): Response
    {
        $status = 500;
        $headers = [];
        $message = 'Server Error';

        if ($throwable instanceof HttpException) {
            $status = $throwable->statusCode();
            $headers = $throwable->headers();
            $message = $throwable->getMessage();
        }

        if ($this->expectsJson($request)) {
            $payload = [
                'message' => $message,
                'status' => $status,
            ];

            if ($throwable instanceof ValidationException) {
                $payload['errors'] = $throwable->errors();
            }

            return $this->responses->json($payload, $status, $headers);
        }

        return $this->responses->make($message, $status, $headers);
    }

    protected function expectsJson(Request $request): bool
    {
        $accept = (string) $request->header('accept', '');

        return str_contains(strtolower($accept), 'application/json');
    }
}
