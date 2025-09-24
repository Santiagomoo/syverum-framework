<?php
declare(strict_types=1);

namespace Core\Support\Middleware;

use Core\Support\DI\Contracts\ContainerInterface;
use Core\Support\DI\Contracts\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerInterface $container): void
    {
        $container->singleton(Pipeline::class, function (ContainerInterface $c) {
            return new Pipeline($c);
        });
    }
}

