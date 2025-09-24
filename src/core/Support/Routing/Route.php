<?php
declare(strict_types=1);

namespace Core\Support\Routing;

final class Route
{
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly mixed $handler,
        public readonly ?string $name = null,
        public readonly array $middleware = [],
    ) {
    }
}



