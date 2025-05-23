<?php

namespace Core\Boot\Services;

use Core\Panel\Monitoring;

class PanelService
{
    public function boot(): void
    {
        Monitoring::register('routes', \Core\Routing\RouteManager::class);
        Monitoring::register('http', \Core\Http\Globals::class);
        Monitoring::register('database', \Core\Database\Connection::class);
    }
}


?>