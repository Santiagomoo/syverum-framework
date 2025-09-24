<?php
declare(strict_types=1);

namespace Core\Support\Routing;

use Core\Support\Routing\Contracts\RouterInterface;

class UrlGenerator
{
    public function __construct(private readonly RouterInterface $router)
    {
    }

    public function route(string $name, array $params = []): string
    {
        foreach ($this->router->all() as $r) {
            if (($r['name'] ?? null) === $name) {
                return $this->substitute($r['path'], $params);
            }
        }
        throw new \RuntimeException('Route not found by name: ' . $name);
    }

    private function substitute(string $pattern, array $params): string
    {
        $url = $pattern;
        foreach ($params as $k => $v) {
            $url = str_replace('{' . $k . '}', rawurlencode((string) $v), $url);
        }
        return $url;
    }
}

