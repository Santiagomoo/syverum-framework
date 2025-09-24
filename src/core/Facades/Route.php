<?php
declare(strict_types=1);

namespace Core\Facades;

use Core\Support\Routing\Contracts\RouterInterface;
use function Core\Support\DI\container;

final class Route
{
    public static function get(string $path, callable|array|string $handler): RouteBuilder
    {
        self::router()->get($path, $handler, null);
        return new RouteBuilder(self::router());
    }

    public static function post(string $path, callable|array|string $handler): RouteBuilder
    {
        self::router()->post($path, $handler, null);
        return new RouteBuilder(self::router());
    }

    public static function put(string $path, callable|array|string $handler): RouteBuilder
    {
        self::router()->put($path, $handler, null);
        return new RouteBuilder(self::router());
    }

    public static function patch(string $path, callable|array|string $handler): RouteBuilder
    {
        self::router()->patch($path, $handler, null);
        return new RouteBuilder(self::router());
    }

    public static function delete(string $path, callable|array|string $handler): RouteBuilder
    {
        self::router()->delete($path, $handler, null);
        return new RouteBuilder(self::router());
    }

    public static function middleware(string ...$ids): RouteBuilder
    {
        self::router()->middleware(...$ids);
        return new RouteBuilder(self::router());
    }

    private static function router(): RouterInterface
    {
        /** @var RouterInterface $router */
        $router = container()->make(RouterInterface::class);
        return $router;
    }
}

