<?php
declare(strict_types=1);

namespace Core\Application\Routing;

use Core\Support\DI\Contracts\ContainerInterface;
use Core\Support\DI\ContainerRegistry;
use Core\Support\Routing\Contracts\RouterInterface;

class Handler
{
    public function __construct(private readonly ?ContainerInterface $container = null)
    {
    }

    private function container(): ContainerInterface
    {
        return $this->container ?? ContainerRegistry::get();
    }

    private function router(): RouterInterface
    {
        /** @var RouterInterface $router */
        $router = $this->container()->make(RouterInterface::class);
        return $router;
    }

    // Route registration API
    public function get(string $path, callable|array|string $handler, ?string $name = null): self
    {
        $this->router()->get($path, $handler, $name);
        \Core\Services\Panel\RoutesSnapshot::update($this->router()->all());
        return $this;
    }

    public function post(string $path, callable|array|string $handler, ?string $name = null): self
    {
        $this->router()->post($path, $handler, $name);
        \Core\Services\Panel\RoutesSnapshot::update($this->router()->all());
        return $this;
    }

    public function put(string $path, callable|array|string $handler, ?string $name = null): self
    {
        $this->router()->put($path, $handler, $name);
        \Core\Services\Panel\RoutesSnapshot::update($this->router()->all());
        return $this;
    }

    public function patch(string $path, callable|array|string $handler, ?string $name = null): self
    {
        $this->router()->patch($path, $handler, $name);
        \Core\Services\Panel\RoutesSnapshot::update($this->router()->all());
        return $this;
    }

    public function delete(string $path, callable|array|string $handler, ?string $name = null): self
    {
        $this->router()->delete($path, $handler, $name);
        \Core\Services\Panel\RoutesSnapshot::update($this->router()->all());
        return $this;
    }

    public function middleware(string ...$ids): self
    {
        $this->router()->middleware(...$ids);
        \Core\Services\Panel\RoutesSnapshot::update($this->router()->all());
        return $this;
    }

    public function dispatch(string $method, string $path, array $extraParameters = []): mixed
    {
        $match = $this->router()->match($method, $path);
        \Core\Services\Panel\RoutesSnapshot::markActive($method, $path);
        $handler = $match['handler'];

        // normalize 'Controller@method' => [Controller::class, 'method']
        if (is_string($handler) && str_contains($handler, '@')) {
            [$class, $method] = explode('@', $handler, 2);
            $handler = [$class, $method];
        }

        $params = array_merge($match['vars'], $extraParameters);

        // Run middleware pipeline before invoking the controller action
        $middlewareIds = $match['middleware'] ?? [];
        $mwHandler = $this->container()->make(\Core\Application\Middleware\Handler::class);
        $controllerInvoker = $this->container()->make(\Core\Application\Controller\Handler::class);

        return $mwHandler->run(
            $middlewareIds,
            fn() => $controllerInvoker->call($handler, $params)
        );
    }
}


