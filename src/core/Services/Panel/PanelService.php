<?php
declare(strict_types=1);

namespace Core\Services\Panel;

use Core\Support\Panel\Monitoring;
use Core\Support\Panel\PanelService as PanelContract;
use Core\Support\ViewRender\Contracts\ViewRendererInterface;

class PanelService implements PanelContract
{
    public function data(): array
    {
        return Monitoring::check();
    }

    public function render(ViewRendererInterface $renderer): string
    {
        return $renderer->render('panel', [
            'monitoring' => $this->data(),
        ]);
    }
}
