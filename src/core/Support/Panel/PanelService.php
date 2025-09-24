<?php
declare(strict_types=1);

namespace Core\Support\Panel;

use Core\Support\ViewRender\Contracts\ViewRendererInterface;

interface PanelService
{
    /**
     * Return collected monitoring data grouped by module.
     *
     * @return array<string,mixed>
     */
    public function data(): array;

    /**
     * Render the panel HTML using the provided view renderer.
     */
    public function render(ViewRendererInterface $renderer): string;
}

