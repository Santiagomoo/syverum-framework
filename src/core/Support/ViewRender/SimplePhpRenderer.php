<?php
declare(strict_types=1);

namespace Core\Support\ViewRender;

use Core\Support\ViewRender\Contracts\ViewRendererInterface;

class SimplePhpRenderer implements ViewRendererInterface
{
    /** @var array<int, string> */
    private array $paths = [];

    /** @param array<int, string> $paths */
    public function __construct(array $paths = [])
    {
        foreach ($paths as $p) {
            $this->addPath($p);
        }
    }

    public function addPath(string $path): void
    {
        $real = rtrim($path, DIRECTORY_SEPARATOR);
        if ($real !== '' && is_dir($real) && !in_array($real, $this->paths, true)) {
            $this->paths[] = $real;
        }
    }

    public function exists(string $view): bool
    {
        return $this->resolveViewFile($view) !== null;
    }

    public function render(string $view, array $data = []): string
    {
        $file = $this->resolveViewFile($view);
        if ($file === null) {
            throw new \RuntimeException('View not found: ' . $view);
        }

        extract($data, EXTR_SKIP);
        ob_start();
        try {
            /** @psalm-suppress UnresolvableInclude */
            require $file;
        } finally {
            $content = ob_get_clean();
        }
        return (string) $content;
    }

    private function resolveViewFile(string $view): ?string
    {
        $normalized = str_replace(['.', ':'], DIRECTORY_SEPARATOR, $view);
        $candidates = [
            $normalized . '.php',
            $normalized . '.phtml',
            $normalized,
        ];

        foreach ($this->paths as $base) {
            foreach ($candidates as $rel) {
                $path = $base . DIRECTORY_SEPARATOR . ltrim($rel, DIRECTORY_SEPARATOR);
                if (is_file($path)) {
                    return $path;
                }
            }
        }

        return null;
    }
}

