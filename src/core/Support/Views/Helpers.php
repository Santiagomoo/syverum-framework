<?php

use Core\Support\Views\Factory;

if (!function_exists('route')) {
    function route($name, $data = []) {
        return Factory::generateURL($name, $data);
    }
}

if(!function_exists('asset')){
    function asset($path){
        return '/' . ltrim($path, '/');
    }
}
?>