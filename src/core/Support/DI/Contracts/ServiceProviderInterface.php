<?php
declare(strict_types=1);

namespace Core\Support\DI\Contracts;

interface ServiceProviderInterface
{
    /**
     * Register bindings/services into the container.
     */
    public function register(ContainerInterface $container): void;
}

