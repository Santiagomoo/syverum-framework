<?php
declare(strict_types=1);

namespace Core\Application\ViewRender;

use Core\Support\DI\Contracts\ContainerInterface;
use Core\Support\DI\ContainerRegistry;
use Core\Support\Http\Response;
use Core\Support\ViewRender\Contracts\ViewRendererInterface;

class Handler
{
    public function __construct(private readonly ?ContainerInterface $container = null)
    {
    }

    private function container(): ContainerInterface
    {
        return $this->container ?? ContainerRegistry::get();
    }

    public function render(string $view, array $data = [], int $status = 200, array $headers = []): Response
    {
        /** @var ViewRendererInterface $renderer */
        $renderer = $this->container()->make(ViewRendererInterface::class);
        $html = $renderer->render($view, $data);
        return Response::html($html, $status, $headers);
    }
}
