<?php
declare(strict_types=1);

namespace Core\Facades;

use Core\Support\Routing\Contracts\RouterInterface;

final class RouteBuilder
{
    public function __construct(private readonly RouterInterface $router)
    {
    }

    public function name(string $name): self
    {
        $this->router->name($name);
        return $this;
    }

    public function middleware(string ...$ids): self
    {
        $this->router->middleware(...$ids);
        return $this;
    }
}

