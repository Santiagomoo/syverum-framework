<?php

namespace Core\Routing;

use Core\Panel\Attributes\Debuggable;

class RouteManager
{
    #[Debuggable]
    protected static $routes = [];

    
    protected static $lastMethod; //identify where the last method is registered in order to assign the correct name to each route

    protected static function addRoute(string $method, string $endPoint, array $action)
    {
        self::$lastMethod = $method;

        $methodsTypes = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];

        if (!in_array($method, $methodsTypes)) {
            throw new \Exception("Método HTTP no válido: " . $method);
        }

        self::$routes[$method][] = [
            'endPoint' => $endPoint,
            'controller' => $action[0],
            'function' => $action[1],
            'onUse' => 'off',
            'routeName' => null,
            'middleware' => null
        ];

    }

    protected static function addNameRoute(string $method, string $name)
    {
        $lastIndex = count(self::$routes[$method]) - 1;
        if ($lastIndex >= 0) {
            self::$routes[$method][$lastIndex]['routeName'] = $name;
        } else {
            throw new \Exception("Nombre invalido: " . $method);
        }   
    }

    protected static function addMiddleware(string $method, string $middleware){
        $lastIndex = count(self::$routes[$method]) - 1;
        
        if ($lastIndex >= 0) {
            self::$routes[$method][$lastIndex]['middleware'] = $middleware;
        } else {
            throw new \Exception("Nombre invalido: " . $method);
        }   
    }



    protected static function getRoutes(){
        return self::$routes;
    }

}
