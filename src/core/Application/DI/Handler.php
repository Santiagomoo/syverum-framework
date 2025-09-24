<?php
declare(strict_types=1);

namespace Core\Application\DI;

use Core\Support\DI\Contracts\ContainerInterface;
use Core\Support\DI\ContainerRegistry;

class Handler
{
    public function __construct(private readonly ?ContainerInterface $container = null)
    {
    }

    public function container(): ContainerInterface
    {
        return $this->container ?? ContainerRegistry::get();
    }

    public function resolve(string $controllerClass): object
    {
        return $this->container()->make($controllerClass);
    }

    public function call(string|object $controller, string $method, array $parameters = []): mixed
    {
        if (is_string($controller)) {
            $controller = $this->resolve($controller);
        }

        return $this->container()->call([$controller, $method], $parameters);
    }

    public function bindController(string $id, callable|string|object $concrete, bool $singleton = false): void
    {
        if ($singleton) {
            $this->container()->singleton($id, $concrete);
            return;
        }
        $this->container()->bind($id, $concrete);
    }
}

