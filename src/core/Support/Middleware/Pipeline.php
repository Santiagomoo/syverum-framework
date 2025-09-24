<?php
declare(strict_types=1);

namespace Core\Support\Middleware;

use Core\Support\DI\Contracts\ContainerInterface;
use Core\Support\Middleware\Contracts\MiddlewareInterface;

class Pipeline
{
    public function __construct(private readonly ContainerInterface $container)
    {
    }

    /**
     * @param array<int, callable|string|object> $middlewares
     * @param callable $destination Final callable to execute after middleware.
     */
    public function process(array $middlewares, callable $destination): mixed
    {
        $next = $destination;
        for ($i = count($middlewares) - 1; $i >= 0; $i--) {
            $mw = $middlewares[$i];
            $next = $this->wrap($mw, $next);
        }
        return $next();
    }

    private function wrap(callable|string|object $middleware, callable $next): callable
    {
        if (is_string($middleware)) {
            $instance = $this->container->make($middleware);
            return $this->asCallable($instance, $next);
        }

        if (is_object($middleware)) {
            return $this->asCallable($middleware, $next);
        }

        $callable = $middleware; // already callable
        return static fn() => $callable($next);
    }

    private function asCallable(object $middleware, callable $next): callable
    {
        if ($middleware instanceof MiddlewareInterface) {
            return static fn() => $middleware->process($next);
        }

        if (is_callable($middleware)) {
            return static fn() => $middleware($next);
        }

        return static fn() => $next();
    }
}

