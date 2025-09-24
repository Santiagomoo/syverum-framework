<?php
declare(strict_types=1);

namespace Core\Application\Routing;

use Core\Application\Contracts\KernelInterface;
use Core\Services\Application;
use Core\Support\Routing\Contracts\RouterInterface;
use Core\Support\DI\ContainerRegistry;

class Kernel implements KernelInterface
{
    /** @var null|callable(Handler):void */
    private $routesRegistrar;

    public function __construct(?callable $routesRegistrar = null)
    {
        $this->routesRegistrar = $routesRegistrar;
    }

    public function boot(Application $app): void
    {
        $handler = new Handler();

        // 1) Preferir registrar rutas vía callback inyectada
        if (is_callable($this->routesRegistrar)) {
            ($this->routesRegistrar)($handler);
            return;
        }

        // 2) Si existe un archivo routes/web.php, cargarlo (varios candidatos: cwd, padre de cwd, raíz del proyecto)
        $candidates = [];
        $cwd = getcwd() ?: '';
        if ($cwd !== '') {
            $candidates[] = $cwd . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'web.php';
            $parent = dirname($cwd);
            if ($parent !== '' && $parent !== $cwd) {
                $candidates[] = $parent . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'web.php';
            }
        }
        $projectRoot = realpath(BASE_PATH);
        if ($projectRoot !== '') {
            $candidates[] = $projectRoot . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'web.php';
        }

        foreach ($candidates as $routesFile) {
            if (is_file($routesFile)) {
                /** @var Handler $handlerLocal */
                $handlerLocal = $handler;
                (static function (Handler $router) use ($routesFile) {
                    require $routesFile;
                })($handlerLocal);
                break;
            }
        }
    }
}
