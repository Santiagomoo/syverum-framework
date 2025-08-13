<?php

namespace Core\Http\Middleware;

use Exception;

class MiddlewareRunner
{
    public static function run(?string $middlewareName, callable $controllerCallback)
    {
        // Si no hay middleware, continuar directamente con el controlador
        if ($middlewareName === null) {
            return $controllerCallback();
        }

        // Formar el namespace completo (ajústalo si usas otra ruta)
        $middlewareClass = "App\\Http\\Middleware\\" . $middlewareName;

        // Verifica si la clase existe
        if (!class_exists($middlewareClass)) {
            throw new Exception("Middleware '$middlewareClass' no encontrado.");
        }

        // Instancia la clase del middleware
        $middlewareInstance = new $middlewareClass();

        // Verifica si implementa la interfaz correctamente
        if (!($middlewareInstance instanceof MiddlewareInterface)) {
            throw new Exception("El middleware debe implementar MiddlewareInterface.");
        }

        // Ejecuta el middleware pasando el callback del controlador
        return $middlewareInstance->handle($controllerCallback);
    }
}
