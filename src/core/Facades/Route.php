<?php

namespace Core\Facades;
use Core\Routing\RouteManager;

class Route extends RouteManager
{
    
    public static function __callStatic(string $name, $arguments)
    {
        $method = strtoupper($name);
        $endpoint = $arguments[0];
        $action = [
            $arguments[1][0], //controller
            $arguments[1][1]  //function
        ] ;
        
        
        RouteManager::addRoute($method, $endpoint, $action);

        //return instance to allow the methods chaining 
        return new self();
        }

    public function name($name)
    { 
        RouteManager::addNameRoute(RouteManager::$lastMethod, $name);
        return $this;
    }

}

?>
