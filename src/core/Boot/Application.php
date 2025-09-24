<?php

namespace Core\Boot;

use Core\Services\Application as App;
use Core\Application\Routing\Kernel as RoutingKernel;
use Core\Application\Http\Handler as HttpHandler;
use Dotenv\Dotenv;
class Application
{
    public function __construct()
    {
        $this->loadEnvIfAvailable();
    }

    private function loadEnvIfAvailable(): void
    {
        $dotenv = Dotenv::createImmutable(BASE_PATH);
        $dotenv->load();
    }

    public function run(): void
    {
        // Boot the application with default kernels (DI, Routing, Middleware, Http, Controller, ViewRender, Panel, DB)
        // and App-level providers (e.g., middleware aliases)
        $app = App::bootDefault([
            \App\Providers\MiddlewareServiceProvider::class,
        ]);

        // Optionally, you can pass a routes registrar callback instead of relying on routes/web.php
        // $routing = new RoutingKernel(function(\Core\Application\Routing\Handler $r) {
        //     // define routes here if not using routes/web.php
        // });
        // $app->addKernel($routing);

        // Handle the current HTTP request and emit the response
        $http = new HttpHandler();
        $response = $http->handle();
        $http->emit($response);
    }
}
