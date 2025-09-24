<?php
declare(strict_types=1);

namespace Core\Support\Http;

use Core\Support\DI\Contracts\ContainerInterface;
use Core\Support\DI\Contracts\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerInterface $container): void
    {
        // Request is per-dispatch; resolve from globals unless provided.
        $container->bind(Request::class, function () {
            return Request::fromGlobals();
        });

        // Emitter as singleton.
        $container->singleton(Emitter::class, Emitter::class);
    }
}

