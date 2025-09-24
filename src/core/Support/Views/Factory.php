<?php
declare(strict_types=1);

namespace Core\Support\Views;

use function Core\Support\DI\container;

class Factory
{
    public static function generateURL(string $routeName, array $data = []): string
    {
        $urlGen = container()->make(\Core\Support\Routing\UrlGenerator::class);
        return $urlGen->route($routeName, $data);
    }
}
?>
