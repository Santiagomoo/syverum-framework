<?php
declare(strict_types=1);

namespace Core\Application\ViewRender;

use Core\Application\Contracts\KernelInterface;
use Core\Services\Application;

class Kernel implements KernelInterface
{
    public function boot(Application $app): void
    {
        // Inicialización de motor de vistas / DI para renderizadores, etc.
    }
}
