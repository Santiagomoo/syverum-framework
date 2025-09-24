<?php
declare(strict_types=1);

namespace Core\Application\Middleware;

use Core\Support\DI\Contracts\ContainerInterface;
use Core\Support\DI\ContainerRegistry;
use Core\Support\Middleware\Pipeline;

class Handler
{
    /** @var array<string, callable|string> */
    private array $aliases = [];

    public function __construct(private readonly ?ContainerInterface $container = null)
    {
    }

    public function alias(string $name, callable|string $middleware): void
    {
        $this->aliases[$name] = $middleware;
    }

    /**
     * @param array<int, string|callable> $idsOrCallables List of middleware aliases, class names or callables.
     */
    public function run(array $idsOrCallables, callable $destination): mixed
    {
        $middlewares = array_map(fn($id) => $this->resolve($id), $idsOrCallables);
        $pipeline = $this->container()->make(Pipeline::class);
        return $pipeline->process($middlewares, $destination);
    }

    private function resolve(string|callable $id): callable|string
    {
        if (is_string($id) && isset($this->aliases[$id])) {
            return $this->aliases[$id];
        }
        return $id;
    }

    private function container(): ContainerInterface
    {
        return $this->container ?? ContainerRegistry::get();
    }
}
