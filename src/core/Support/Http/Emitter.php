<?php
declare(strict_types=1);

namespace Core\Support\Http;

final class Emitter
{
    public function emit(Response $response): void
    {
        if (!headers_sent()) {
            http_response_code($response->status());
            foreach ($response->headers() as $name => $value) {
                header($this->normalizeHeaderName($name) . ': ' . $value, true);
            }
        }
        echo $response->body();
    }

    private function normalizeHeaderName(string $name): string
    {
        return implode('-', array_map(static fn($p) => ucfirst($p), explode('-', strtolower($name))));
    }
}

