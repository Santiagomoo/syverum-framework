<?php
declare(strict_types=1);

namespace Core\Support\ViewRender;

use Core\Support\DI\Contracts\ContainerInterface;
use Core\Support\DI\Contracts\ServiceProviderInterface;
use Core\Support\ViewRender\Contracts\ViewRendererInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerInterface $container): void
    {
        $container->singleton(ViewRendererInterface::class, function () {
            $paths = [];
            $projectRoot = dirname(__DIR__, 4);
            if (!is_dir($projectRoot)) {
                $cwd = getcwd();
                $projectRoot = is_string($cwd) ? $cwd : __DIR__;
            }

            $appViews = $projectRoot . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views';
            if (is_dir($appViews)) {
                $paths[] = $appViews;
            }

            $panelViews = $projectRoot . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Panel' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views';
            if (is_dir($panelViews)) {
                $paths[] = $panelViews;
            }

            $cache = $projectRoot . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'blade';
            if (!is_dir($cache)) {
                @mkdir($cache, 0777, true);
            }

            if (class_exists(BladeRenderer::class)) {
                return new BladeRenderer($paths, $cache);
            }

            return new SimplePhpRenderer($paths);
        });
    }
}
