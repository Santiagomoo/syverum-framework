<?php
declare(strict_types=1);

namespace Core\Support\Http;

class Response
{
    /** @param array<string,string> $headers */
    public function __construct(
        protected int $status = 200,
        protected array $headers = [],
        protected string $body = ''
    ) {
    }

    public static function text(string $content, int $status = 200, array $headers = []): self
    {
        $headers = ['content-type' => 'text/plain; charset=UTF-8'] + $headers;
        return new self($status, $headers, $content);
    }

    public static function html(string $content, int $status = 200, array $headers = []): self
    {
        $headers = ['content-type' => 'text/html; charset=UTF-8'] + $headers;
        return new self($status, $headers, $content);
    }

    public static function json(mixed $data, int $status = 200, array $headers = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }

    public function status(): int { return $this->status; }
    /** @return array<string,string> */
    public function headers(): array { return $this->headers; }
    public function body(): string { return $this->body; }

    public function withStatus(int $status): static
    {
        $this->status = $status;
        return $this;
    }

    /** @param array<string,string> $headers */
    public function withHeaders(array $headers): static
    {
        $this->headers = $headers + $this->headers;
        return $this;
    }

    public function withBody(string $body): static
    {
        $this->body = $body;
        return $this;
    }
}

