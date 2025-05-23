<?php

namespace Core\Routing;

class RouteResolver extends RouteManager
{

    //Find the route by the method and endpoint
    public static function resolveRoute(string $method, string $endPoint, string $name = null)
    {

        //Check the static property to see if the routes are set
        if (isset(self::$routes[$method])) {
            foreach (self::$routes[$method] as $route => &$value) {
                if ($value['endPoint'] === $endPoint) {

                    $value['onUse'] = "on";

                    
                    $controller = $value['controller'];
                    $function = $value['function'];


                    //finding the method and function to create an instance
                    if (method_exists($controller, $function)) {
                        return (new $controller)->$function();
                    } else {
                        throw new \Exception("Metodo o clase no encontrada: " . $controller . " - " . $function);
                    }
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
