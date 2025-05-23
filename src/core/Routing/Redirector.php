<?php

class Redirector {
    public function to($url) {
        echo "Redirigiendo a $url\n";
        return $this;  
    }

    public function with($key, $value) {
        echo "Con $key => $value\n";
        return $this; 
    }
}