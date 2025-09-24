<?php
declare(strict_types=1);

namespace Core\Application\Controller;

use Core\Support\DI\Contracts\ContainerInterface;
use Core\Support\DI\ContainerRegistry;

class Handler
{
    public function __construct(private readonly ?ContainerInterface $container = null)
    {
    }

    private function container(): ContainerInterface
    {
        return $this->container ?? ContainerRegistry::get();
    }

    public function resolve(string $controllerClass): object
    {
        return $this->container()->make($controllerClass);
    }

    /**
     * @param callable|array|string $callable Controller callable. Supports
     *  - [Controller::class, 'method']
     *  - 'Controller@method'
     *  - 'Controller::method'
     *  - callable
     */
    public function call(callable|array|string $callable, array $parameters = []): mixed
    {
        // Normalize 'Controller@method'
        if (is_string($callable) && str_contains($callable, '@')) {
            [$class, $method] = explode('@', $callable, 2);
            $callable = [$class, $method];
        }

        // Normalize 'Controller::method'
        if (is_string($callable) && str_contains($callable, '::')) {
            [$class, $method] = explode('::', $callable, 2);
            $callable = [$class, $method];
        }

        // If array with class name, resolve controller instance first
        if (is_array($callable) && isset($callable[0], $callable[1]) && is_string($callable[0])) {
            $controller = $this->resolve($callable[0]);
            $callable = [$controller, (string) $callable[1]];
        }

        return $this->container()->call($callable, $parameters);
    }
}

