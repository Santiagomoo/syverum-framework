<?php
declare(strict_types=1);

namespace Core\Services\Panel;

use Core\Support\DI\Contracts\ContainerInterface;
use Core\Support\DI\Contracts\ServiceProviderInterface;
use Core\Support\Panel\Monitoring;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerInterface $container): void
    {
        // Alias the public Panel contract to its concrete implementation
        $container->bind(\Core\Support\Panel\PanelService::class, \Core\Services\Panel\PanelService::class);
        Monitoring::register('http', HttpSnapshot::class);
        Monitoring::register('routes', RoutesSnapshot::class);
        Monitoring::register('database', \Core\Services\Database\Connection::class);
    }
}
