<?php
declare(strict_types=1);

namespace Core\Application\Http;

use Core\Support\DI\Contracts\ContainerInterface;
use Core\Support\DI\ContainerRegistry;
use Core\Support\Http\Emitter;
use Core\Support\Http\JsonResponse;
use Core\Support\Http\Request;
use Core\Support\Http\Response;

class Handler
{
    public function __construct(private readonly ?ContainerInterface $container = null)
    {
    }

    private function container(): ContainerInterface
    {
        return $this->container ?? ContainerRegistry::get();
    }

    public function handle(?Request $request = null): Response
    {
        // Inicializa Globals para el Panel (si existe la clase legacy)

        $req = $request ?? $this->container()->make(Request::class);

        // Dispatch to routing and controller
        $router = $this->container()->make(\Core\Application\Routing\Handler::class);
        $result = $router->dispatch($req->method(), $req->path());

        $response = $this->toResponse($result);

        // Always expose the debug flag as a response header
        $debug = $this->isDebugEnabled() ? 'on' : 'off';
        $response = (clone $response)->withHeaders(['x-app-debug' => $debug]);

        // Si está activo el panel, tratar de anexarlo al HTML
        if ($this->shouldAttachPanel() && str_contains(($response->headers()['content-type'] ?? ''), 'text/html')) {
            $response = $this->attachPanel($response);
        }

        return $response;
    }

    public function emit(Response $response): void
    {
        $emitter = $this->container()->make(Emitter::class);
        $emitter->emit($response);
    }

    private function toResponse(mixed $result): Response
    {
        if ($result instanceof Response) {
            return $result;
        }
        if ($result === null) {
            return new Response(204);
        }
        if (is_string($result)) {
            return Response::html($result);
        }
        if (is_array($result) || is_object($result)) {
            return new JsonResponse($result);
        }
        if (is_bool($result)) {
            return new Response($result ? 204 : 400);
        }
        // Fallback: cast to string
        return Response::text((string) $result);
    }

    private function shouldAttachPanel(): bool
    {
        return $this->isDebugEnabled();
    }

    private function isDebugEnabled(): bool
    {
        $flag = $this->getEnv('APP_DEBUG');
        if (!is_string($flag)) {
            return false;
        }
        $flag = strtolower($flag);
        if (in_array($flag, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }
        if (in_array($flag, ['0', 'false', 'no', 'off'], true)) {
            return false;
        }
        return false;
    }

    private function getEnv(string $key): ?string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if ($value === false) {
            return null;
        }
        return is_string($value) ? $value : null;
    }

    private function attachPanel(Response $response): Response
    {
        try {
            $renderer = $this->container()->make(\Core\Support\ViewRender\Contracts\ViewRendererInterface::class);
            $panel = $this->container()->make(\Core\Support\Panel\PanelService::class);
            $panelHtml = $panel->render($renderer);
            $body = $response->body() . "\n" . $panelHtml;
            return (clone $response)->withBody($body);
        } catch (\Throwable) {
            // Fallback: append a minimal visible badge indicating debug is enabled
            $badge = '<div style="position:fixed;bottom:8px;right:8px;background:#222;color:#0f0;padding:6px 8px;font:12px/1.2 monospace;z-index:2147483647;border-radius:4px;opacity:.85">DEBUG ON</div>';
            $body = $response->body() . "\n" . $badge;
            return (clone $response)->withBody($body);
        }
    }
}
