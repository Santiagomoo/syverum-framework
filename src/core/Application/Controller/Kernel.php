<?php
declare(strict_types=1);

namespace Core\Application\Controller;

use Core\Application\Contracts\KernelInterface;
use Core\Services\Application;

class Kernel implements KernelInterface
{
    public function boot(Application $app): void
    {
        // Aquí podrías registrar resolutores de controladores, políticas, etc.
        // El Handler de DI de aplicación usa el contenedor global para invocar actions.
    }
}
