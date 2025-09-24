<?php
declare(strict_types=1);

namespace Core\Support\Http;

final class Request
{
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $query,
        private readonly array $body,
        private readonly array $headers,
        private readonly array $cookies,
        private readonly array $files,
        private readonly ?string $rawBody = null,
    ) {
    }

    public static function fromGlobals(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace('_', '-', strtolower(substr($key, 5)));
                $headers[$name] = (string) $value;
            }
        }

        $raw = null;
        if (!empty($_SERVER['CONTENT_TYPE'])) {
            $raw = file_get_contents('php://input') ?: null;
        }

        return new self(
            strtoupper($method),
            $path,
            $_GET ?? [],
            $_POST ?? [],
            $headers,
            $_COOKIE ?? [],
            $_FILES ?? [],
            $raw,
        );
    }

    public function method(): string { return $this->method; }
    public function path(): string { return $this->path; }
    /** @return array<string,mixed> */
    public function query(): array { return $this->query; }
    /** @return array<string,mixed> */
    public function body(): array { return $this->body; }
    /** @return array<string,string> */
    public function headers(): array { return $this->headers; }
    /** @return array<string,string> */
    public function cookies(): array { return $this->cookies; }
    /** @return array<string,mixed> */
    public function files(): array { return $this->files; }
    public function rawBody(): ?string { return $this->rawBody; }
}

