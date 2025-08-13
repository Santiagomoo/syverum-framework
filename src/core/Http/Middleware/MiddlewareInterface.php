<?php

namespace Core\Http\Middleware;

interface MiddlewareInterface
{
    /**
     * Procesa la petición antes de llegar al controlador.
     *
     * @param callable $next Callback que continúa con la ejecución.
     * @return mixed
     */
    
    public function handle(callable $next);
}
?>