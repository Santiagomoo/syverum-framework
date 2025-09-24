<?php
declare(strict_types=1);

namespace Core\Services;

use Core\Application\Contracts\KernelInterface;
use Core\Application\Controller\Kernel as ControllerKernel;
use Core\Application\DI\Kernel as DIKernel;
use Core\Application\Http\Kernel as HttpKernel;
use Core\Application\Middleware\Kernel as MiddlewareKernel;
use Core\Application\Routing\Kernel as RoutingKernel;
use Core\Application\ViewRender\Kernel as ViewKernel;
use Core\Support\DI\Contracts\ContainerInterface;
use Core\Support\DI\Contracts\ServiceProviderInterface;
use Core\Support\DI\ContainerRegistry;

class Application
{
    /** @var list<KernelInterface> */
    private array $kernels = [];

    private ?ContainerInterface $container = null;

    /**
     * @param array<int, class-string<ServiceProviderInterface>|ServiceProviderInterface> $providers
     */
    public function __construct(private readonly array $providers = [])
    {
    }

    public static function bootDefault(array $providers = []): self
    {
        $app = new self($providers);
        $app->registerDefaultKernels();
        $app->boot();
        return $app;
    }

    public function registerDefaultKernels(): void
    {
        // Orden recomendado: DI primero y luego el resto.
        $this->addKernel(new DIKernel($this->providers));
        $this->addKernel(new RoutingKernel());
        $this->addKernel(new MiddlewareKernel());
        $this->addKernel(new HttpKernel());
        $this->addKernel(new ControllerKernel());
        $this->addKernel(new ViewKernel());
    }

    public function addKernel(KernelInterface $kernel): void
    {
        $this->kernels[] = $kernel;
    }

    public function boot(): void
    {
        foreach ($this->kernels as $kernel) {
            $kernel->boot($this);
        }
    }

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    public function container(): ContainerInterface
    {
        return $this->container ?? ContainerRegistry::get();
    }
}
