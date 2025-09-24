<?php
declare(strict_types=1);

namespace Core\Support\Http;

final class JsonResponse extends Response
{
    /** @param array<string,string> $headers */
    public function __construct(mixed $data, int $status = 200, array $headers = [])
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            $json = 'null';
        }
        $headers = ['content-type' => 'application/json; charset=UTF-8'] + $headers;
        parent::__construct($status, $headers, $json);
    }
}

