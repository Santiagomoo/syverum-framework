<?php
declare(strict_types=1);

namespace Core\Support\Routing;

use Core\Support\Routing\Contracts\RouterInterface;
use Core\Support\Routing\Exceptions\RouteNotFoundException;

final class Router implements RouterInterface
{
    /** @var array<int, Route> */
    private array $routes = [];
    
    /** Index of last added route, or null if none. */
    private ?int $lastIndex = null;

    public function get(string $path, callable|array|string $handler, ?string $name = null): self
    {
        return $this->add('GET', $path, $handler, $name);
    }

    public function post(string $path, callable|array|string $handler, ?string $name = null): self
    {
        return $this->add('POST', $path, $handler, $name);
    }

    public function put(string $path, callable|array|string $handler, ?string $name = null): self
    {
        return $this->add('PUT', $path, $handler, $name);
    }

    public function patch(string $path, callable|array|string $handler, ?string $name = null): self
    {
        return $this->add('PATCH', $path, $handler, $name);
    }

    public function delete(string $path, callable|array|string $handler, ?string $name = null): self
    {
        return $this->add('DELETE', $path, $handler, $name);
    }

    public function middleware(string ...$middleware): self
    {
        if ($this->lastIndex === null) {
            return $this;
        }
        $current = $this->routes[$this->lastIndex];
        $new = new Route(
            $current->method,
            $current->path,
            $current->handler,
            $current->name,
            array_values(array_unique(array_merge($current->middleware, $middleware)))
        );
        $this->routes[$this->lastIndex] = $new;
        $this->refreshSnapshot();
        return $this;
    }

    public function name(string $name): self
    {
        if ($this->lastIndex === null) {
            return $this;
        }
        $current = $this->routes[$this->lastIndex];
        $this->routes[$this->lastIndex] = new Route(
            $current->method,
            $current->path,
            $current->handler,
            $name,
            $current->middleware
        );
        $this->refreshSnapshot();
        return $this;
    }

    /** @inheritDoc */
    public function all(): array
    {
        $all = [];
        foreach ($this->routes as $r) {
            $all[] = [
                'method' => $r->method,
                'path' => $r->path,
                'handler' => $r->handler,
                'name' => $r->name,
                'middleware' => $r->middleware,
            ];
        }
        return $all;
    }

    /** @inheritDoc */
    public function match(string $method, string $path): array
    {
        foreach ($this->routes as $route) {
            if ($route->method !== strtoupper($method)) {
                continue;
            }
            $params = $this->matchPath($route->path, $path);
            if ($params !== null) {
                return [
                    'handler' => $route->handler,
                    'vars' => $params,
                    'name' => $route->name,
                    'middleware' => $route->middleware,
                ];
            }
        }

        throw new RouteNotFoundException(sprintf('Route not found for %s %s', strtoupper($method), $path));
    }

    private function add(string $method, string $path, callable|array|string $handler, ?string $name): self
    {
        $this->routes[] = new Route($method, $path, $handler, $name, []);
        $this->lastIndex = count($this->routes) - 1;
        $this->refreshSnapshot();
        return $this;
    }

    private function refreshSnapshot(): void
    {
        if (class_exists('Core\\Services\\Panel\\RoutesSnapshot')) {
            try {
                \Core\Services\Panel\RoutesSnapshot::update($this->all());
            } catch (\Throwable) {
                // ignore
            }
        }
    }

    /**
     * Return params if matches, null otherwise. Supports /foo/{id} style.
     * @return array<string,string>|null
     */
    private function matchPath(string $pattern, string $path): ?array
    {
        if ($pattern === $path) {
            return [];
        }

        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if ($regex === null) {
            return null;
        }

        $matches = [];
        if (preg_match($regex, $path, $matches) === 1) {
            $params = [];
            foreach ($matches as $k => $v) {
                if (is_string($k)) {
                    $params[$k] = $v;
                }
            }
            return $params;
        }

        return null;
    }
}
