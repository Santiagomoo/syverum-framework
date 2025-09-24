<?php
declare(strict_types=1);

namespace Core\Application\Middleware;

use Core\Application\Contracts\KernelInterface;
use Core\Services\Application;

class Kernel implements KernelInterface
{
    public function boot(Application $app): void
    {
        // Aquí se registrarían middlewares enrutables/ globales usando el contenedor si aplica.
    }
}
