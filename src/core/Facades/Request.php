<?php

namespace Core\Facades;
use Core\Http\Globals;

class Request extends Globals {

    function __construct()
    {
        parent::__construct();
    }

    
    public static function capture(){
       $request = new self;
       return [
            'endpoint' => $request->endpoint,
            'method' => $request->method
       ];
    }

    protected static function all() {
        $request = new self;
        $data = [
            'URL' => $request->url,
            'ENDPOINT' => $request->endpoint,
            'PARAMETERS' => $request->query,
            'BODY_REQUEST' => $request->request,
            'PREVIOUS_URL' => $request->previusUrl, 
            'COOKIES' => $request->cookie,
            'FILES' => $request->file
        ];
    
        return $data;
    }
}