<?php
declare(strict_types=1);

namespace Core\Support\Routing;

use Core\Support\DI\Contracts\ContainerInterface;
use Core\Support\DI\Contracts\ServiceProviderInterface;
use Core\Support\Routing\Contracts\RouterInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerInterface $container): void
    {
        $container->singleton(RouterInterface::class, Router::class);
        $container->singleton(UrlGenerator::class, function (ContainerInterface $c) {
            /** @var RouterInterface $router */
            $router = $c->make(RouterInterface::class);
            return new UrlGenerator($router);
        });
    }
}

