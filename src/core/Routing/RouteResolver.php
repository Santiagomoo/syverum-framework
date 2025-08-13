<?php

namespace Core\Routing;

use Core\Http\Middleware\MiddlewareRunner;

class RouteResolver extends RouteManager
{
    public static function resolveRoute(string $method, string $endPoint, string $name = null)
    {
        if (isset(self::$routes[$method])) {
            foreach (self::$routes[$method] as $route => &$value) {
                if ($value['endPoint'] === $endPoint) {

                    $value['onUse'] = "on";

                    $controller = $value['controller'];
                    $function = $value['function'];
                    $middleware = $value['middleware'] ?? null;

                    // Ejecutar middleware y, si pasa, ejecutar el controlador
                    return MiddlewareRunner::run($middleware, function () use ($controller, $function) {
                        if (method_exists($controller, $function)) {
                            return (new $controller)->$function();
                        } else {
                            throw new \Exception("Método o clase no encontrada: " . $controller . " - " . $function);
                        }
                    });
                }
            }
        }

        throw new \Exception("Ruta no encontrada para {$method} {$endPoint}");
    }

    public static function resolveRouteByName($name, $data = [])
    {
        $routes = self::getRoutes();

        foreach ($routes as $method => $route) {
            foreach ($route as $key => $value) {
                if ($value['routeName'] === $name) {
                    return $value['endPoint'];
                }
            }
        }

        throw new \Exception("Ruta no encontrada para {$name}");
    }
}
