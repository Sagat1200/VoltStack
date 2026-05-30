<?php

declare(strict_types=1);

namespace Quantum\Exceptions;

final class NotFoundHttpException extends HttpException
{
    public function __construct(string $message = 'Not Found', array $headers = [])
    {
        parent::__construct(404, $message, $headers);
    }
}
