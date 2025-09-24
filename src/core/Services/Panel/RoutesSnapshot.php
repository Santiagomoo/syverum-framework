<?php
declare(strict_types=1);

namespace Core\Services\Panel;

use Core\Support\Panel\Attributes\Debuggable;

final class RoutesSnapshot
{
    #[Debuggable]
    protected static array $routes = [];

    /** @param array<int, array{method:string,path:string,handler:mixed,name:?string,middleware:array<int,string>}> $routes */
    public static function update(array $routes): void
    {
        $grouped = [];
        foreach ($routes as $r) {
            $handler = $r['handler'];
            $controller = 'closure';
            $function = '';
            if (is_array($handler)) {
                $controller = is_object($handler[0]) ? $handler[0]::class : (string) $handler[0];
                $function = (string) $handler[1];
            } elseif (is_string($handler)) {
                if (str_contains($handler, '@')) {
                    [$controller, $function] = explode('@', $handler, 2);
                } else {
                    $controller = $handler;
                }
            }

            $grouped[$r['method']][] = [
                'endPoint' => $r['path'],
                'controller' => $controller,
                'function' => $function,
                'onUse' => 'off',
                'routeName' => $r['name'] ?? null,
                'middleware' => implode(',', $r['middleware'] ?? []),
            ];
        }

        self::$routes = $grouped;
    }

    public static function markActive(string $method, string $path): void
    {
        $method = strtoupper($method);
        if (!isset(self::$routes[$method])) {
            return;
        }
        foreach (self::$routes[$method] as &$route) {
            if (($route['endPoint'] ?? '') === $path) {
                $route['onUse'] = 'on';
                break;
            }
        }
    }
}
