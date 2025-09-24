<?php
declare(strict_types=1);

namespace Core\Support\DI;

use Core\Support\DI\Contracts\ContainerInterface;
use Core\Support\DI\Contracts\ServiceProviderInterface;

final class Factory
{
    /**
     * Build and optionally seed the container with service providers.
     *
     * @param array<int, class-string<ServiceProviderInterface>|ServiceProviderInterface> $providers
     */
    public static function build(array $providers = []): ContainerInterface
    {
        $container = new Container();

        foreach ($providers as $provider) {
            $instance = is_string($provider) ? new $provider() : $provider;
            if (!$instance instanceof ServiceProviderInterface) {
                throw new \InvalidArgumentException('Invalid service provider given.');
            }
            $instance->register($container);
        }

        // Set as global container for helpers
        ContainerRegistry::set($container);

        return $container;
    }
}
