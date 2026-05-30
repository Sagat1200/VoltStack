<?php

declare(strict_types=1);

namespace Quantum\HttpKernel\Contracts;

use Quantum\Http\Request;
use Quantum\Http\Response;

interface HttpKernelInterface
{
    public function handle(Request $request): Response;
}
