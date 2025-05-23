<?php

use Core\Support\Controllers\Factory;


if (!function_exists('view')) {
    function view($name, $data = []) {
        echo Factory::renderView($name, $data);
    }
}

?>
