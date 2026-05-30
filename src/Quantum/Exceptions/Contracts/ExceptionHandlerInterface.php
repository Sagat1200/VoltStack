<?php

declare(strict_types=1);

namespace Quantum\Exceptions\Contracts;

use Quantum\Http\Request;
use Quantum\Http\Response;
use Throwable;

interface ExceptionHandlerInterface
{
    public function report(Throwable $throwable): void;

    public function render(Request $request, Throwable $throwable): Response;
}
