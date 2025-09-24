<?php
declare(strict_types=1);

namespace Core\Application\DI;

use Core\Application\Contracts\KernelInterface;
use Core\Services\Application;
use Core\Support\DI\Contracts\ServiceProviderInterface;
use Core\Support\DI\Contracts\ContainerInterface;
use Core\Support\DI\Factory as DIFactory;
use Core\Support\DI\ContainerRegistry;
use Core\Support\Routing\ServiceProvider as RoutingServiceProvider;
use Core\Support\Middleware\ServiceProvider as MiddlewareServiceProvider;
use Core\Support\Http\ServiceProvider as HttpServiceProvider;
use Core\Support\ViewRender\ServiceProvider as ViewServiceProvider;
use Core\Services\Panel\ServiceProvider as PanelServiceProvider;
use Core\Support\Database\ServiceProvider as DatabaseServiceProvider;

class Kernel implements KernelInterface
{
    /**
     * @param array<int, class-string<ServiceProviderInterface>|ServiceProviderInterface> $providers
     */
    public function __construct(private readonly array $providers = [])
    {
    }

    public function boot(Application $app): void
    {
        // Ensure routing provider is registered by default.
        $providers = $this->mergeDefaultProviders($this->providers);

        // Build container and set it in the registry for global helpers/handlers.
        $container = DIFactory::build($providers);

        // Optionally expose container on the Application facade for quick access.
        $app->setContainer($container);
    }

    /**
     * @param array<int, class-string<ServiceProviderInterface>|ServiceProviderInterface> $providers
     * @return array<int, class-string<ServiceProviderInterface>|ServiceProviderInterface>
     */
    private function mergeDefaultProviders(array $providers): array
    {
        $hasRouting = false;
        $hasMiddleware = false;
        $hasHttp = false;
        $hasView = false;
        $hasPanel = false;
        $hasDatabase = false;
        foreach ($providers as $p) {
            if ((is_string($p) && $p === RoutingServiceProvider::class) || $p instanceof RoutingServiceProvider) {
                $hasRouting = true;
            }
        }
        foreach ($providers as $p) {
            if ((is_string($p) && $p === MiddlewareServiceProvider::class) || $p instanceof MiddlewareServiceProvider) {
                $hasMiddleware = true;
            }
        }
        foreach ($providers as $p) {
            if ((is_string($p) && $p === HttpServiceProvider::class) || $p instanceof HttpServiceProvider) {
                $hasHttp = true;
            }
        }
        foreach ($providers as $p) {
            if ((is_string($p) && $p === ViewServiceProvider::class) || $p instanceof ViewServiceProvider) {
                $hasView = true;
            }
        }
        foreach ($providers as $p) {
            if ((is_string($p) && $p === PanelServiceProvider::class) || $p instanceof PanelServiceProvider) {
                $hasPanel = true;
            }
        }
        foreach ($providers as $p) {
            if ((is_string($p) && $p === DatabaseServiceProvider::class) || $p instanceof DatabaseServiceProvider) {
                $hasDatabase = true;
            }
        }
        if (!$hasRouting) {
            array_unshift($providers, RoutingServiceProvider::class);
        }
        if (!$hasMiddleware) {
            array_unshift($providers, MiddlewareServiceProvider::class);
        }
        if (!$hasHttp) {
            array_unshift($providers, HttpServiceProvider::class);
        }
        if (!$hasView) {
            array_unshift($providers, ViewServiceProvider::class);
        }
        if (!$hasPanel) {
            array_unshift($providers, PanelServiceProvider::class);
        }
        if (!$hasDatabase) {
            array_unshift($providers, DatabaseServiceProvider::class);
        }
        return $providers;
    }
}
