<?php
declare(strict_types=1);

namespace Core\Services\Panel;

use Core\Support\Panel\Attributes\Debuggable;
use Core\Support\Http\Request;

final class HttpSnapshot
{
    #[Debuggable]
    protected static array $http = [];

    public static function update(Request $request): void
    {
        $headers = $request->headers();
        $host = $headers['host'] ?? '';
        $scheme = ($headers['x-forwarded-proto'] ?? '') === 'https' ? 'https' : 'http';
        $url = $host !== '' ? $scheme . '://' . $host . $request->path() : $request->path();

        self::$http = [
            'URL' => $url,
            'ENDPOINT' => $request->path(),
            'PARAMETERS' => $request->query(),
            'BODY_REQUEST' => $request->body(),
            'PREVIOUS_URL' => $headers['referer'] ?? 'N/A',
            'COOKIES' => $request->cookies(),
            'FILES' => $request->files(),
        ];
    }
}
