<?php
declare(strict_types=1);

namespace Core\Support\Middleware\Contracts;

interface MiddlewareInterface
{
    /**
     * Execute middleware logic and call $next to continue the pipeline.
     */
    public function process(callable $next): mixed;
}

