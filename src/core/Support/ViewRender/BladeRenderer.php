<?php
declare(strict_types=1);

namespace Core\Support\ViewRender;

use Core\Support\ViewRender\Contracts\ViewRendererInterface;
use Jenssegers\Blade\Blade;

class BladeRenderer implements ViewRendererInterface
{
    private Blade $blade;

    /**
     * @param array<int, string> $paths
     */
    public function __construct(array $paths = [], ?string $cachePath = null)
    {
        $root = BASE_PATH;

        if ($paths === []) {
            $default = $root . DIRECTORY_SEPARATOR . 'resources';
            if (is_dir($default)) {
                $paths[] = $default;
            }
        }

        $cache = $cachePath ?? ($root . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'blade');
        if (!is_dir($cache)) {
            @mkdir($cache, 0777, true);
        }

        $this->blade = new Blade($paths, $cache);

        // Ensure Illuminate global container instance is set for ViewServiceProvider internals
        try {
            $rc = new \ReflectionClass($this->blade);
            if ($rc->hasProperty('container')) {
                $prop = $rc->getProperty('container');
                $prop->setAccessible(true);
                $app = $prop->getValue($this->blade);
                if ($app instanceof \Illuminate\Container\Container) {
                    \Illuminate\Container\Container::setInstance($app);
                }
            }
        } catch (\Throwable) {
            // ignore; Jenssegers may already have set it via Facades
        }
    }

    public function render(string $view, array $data = []): string
    {
        return $this->blade->render('views/'. $view, $data);
    }

    public function addPath(string $path): void
    {
        if (method_exists($this->blade, 'addPath')) {
            $this->blade->addPath($path);
            return;
        }

        try {
            $factory = (new \ReflectionClass($this->blade))->getProperty('view');
            $factory->setAccessible(true);
            $viewFactory = $factory->getValue($this->blade);
            if (is_object($viewFactory) && method_exists($viewFactory, 'getFinder')) {
                $finder = $viewFactory->getFinder();
                if (is_object($finder) && method_exists($finder, 'addLocation')) {
                    $finder->addLocation($path);
                    return;
                }
            }
        } catch (\Throwable) {
        }
    }

    public function exists(string $view): bool
    {
        try {
            $rc = new \ReflectionClass($this->blade);
            if ($rc->hasMethod('exists')) {
                return (bool) $rc->getMethod('exists')->invoke($this->blade, $view);
            }
            $prop = $rc->getProperty('view');
            $prop->setAccessible(true);
            $factory = $prop->getValue($this->blade);
            if (is_object($factory) && method_exists($factory, 'exists')) {
                return (bool) $factory->exists($view);
            }
        } catch (\Throwable) {
        }

        try {
            $this->blade->render($view, []);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
