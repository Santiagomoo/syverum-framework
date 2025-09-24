<?php
declare(strict_types=1);

namespace Core\Support\Routing\Contracts;

interface RouterInterface
{
    public function get(string $path, callable|array|string $handler, ?string $name = null): self;
    public function post(string $path, callable|array|string $handler, ?string $name = null): self;
    public function put(string $path, callable|array|string $handler, ?string $name = null): self;
    public function patch(string $path, callable|array|string $handler, ?string $name = null): self;
    public function delete(string $path, callable|array|string $handler, ?string $name = null): self;

    /** @return array<int, array{method:string,path:string,handler:callable|array|string,name:?string,middleware:array<int,string>}> */
    public function all(): array;

    /** @return array{handler: callable|array|string, vars: array<string,string>, name:?string, middleware: array<int,string>} */
    public function match(string $method, string $path): array;

    /** Attach middleware identifiers to the last registered route. */
    public function middleware(string ...$middleware): self;

    /** Set the name on the last registered route (chainable after get/post/etc). */
    public function name(string $name): self;
}
