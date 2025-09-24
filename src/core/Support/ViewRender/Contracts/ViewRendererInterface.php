<?php
declare(strict_types=1);

namespace Core\Support\ViewRender\Contracts;

interface ViewRendererInterface
{
    /** Render a view into HTML string. */
    public function render(string $view, array $data = []): string;

    /** Add an additional base path to search for views. */
    public function addPath(string $path): void;

    /** Check if a view exists. */
    public function exists(string $view): bool;
}

