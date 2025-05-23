<?php

namespace Core\Boot;

use Core\Boot\Services\PanelService;
use Core\Boot\Services\DatabaseService;

use Dotenv\Dotenv;

use Core\Routing\RouteResolver;
use Core\Facades\Request;

class Application
{
    public function __construct()
    {
        $this->loadEnv();
        $this->loadHelpers();
        $this->loadServices();
        $this->loadRoutes();
    }

    private function loadEnv(): void
    {
            if (file_exists('.env')) {
                $dotenv = Dotenv::createImmutable(BASE_PATH);
                $dotenv->load();
            }
    }
    private function loadRoutes(): void
    {
        require_once BASE_PATH .'/routes/web.php';
    }

    private function loadHelpers(): void
    {
        require_once __DIR__ . '/../Support/Controllers/Helpers.php';
        require_once __DIR__ . '/../Support/Views/Helpers.php';
    }

    private function loadServices(): void
    {
        $database = new DatabaseService();
        $database->boot();

        $panel = new PanelService();
        $panel->boot();
    }

    
    public function run(): void
    {
        $response = Request::capture();
        RouteResolver::resolveRoute($response['method'], $response['endpoint']);
    }
}
