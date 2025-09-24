<?php
declare(strict_types=1);

namespace Core\Application\Http;

use Core\Application\Contracts\KernelInterface;
use Core\Services\Application;
use Core\Support\Http\ServiceProvider as HttpServiceProvider;

class Kernel implements KernelInterface
{
    public function boot(Application $app): void
    {
        // Por ahora no hay inicialización compleja; el ServiceProvider de HTTP
        // se añade en el Kernel de DI para exponer Request/Emitter vía contenedor.
    }
}
