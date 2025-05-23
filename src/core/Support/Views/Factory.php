<?php

namespace Core\Support\Views;

use Core\Routing\RouteResolver;

class Factory {

    public static function generateURL($routeName, $data = []) {
        return RouteResolver::resolveRouteByName($routeName, $data);
    }
}
?>