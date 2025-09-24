<?php

use Core\Support\Http\Response;
use Core\Support\Http\JsonResponse;

if (!function_exists('view')) {
    /**
     * Render a Blade/PHP view and return an HTTP response.
     */
    function view(string $name, array $data = [], int $status = 200, array $headers = []): Response
    {
        /** @var \Core\Support\ViewRender\Contracts\ViewRendererInterface $renderer */
        $renderer = \Core\Support\DI\container()->make(\Core\Support\ViewRender\Contracts\ViewRendererInterface::class);
        $html = $renderer->render($name, $data);
        return Response::html($html, $status, $headers);
    }
}

if (!function_exists('json')) {
    /**
     * Build a JSON response.
     */
    function json(mixed $data, int $status = 200, array $headers = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }
}

