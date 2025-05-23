<?php

namespace Core\Http;

use Core\Panel\Attributes\Debuggable;

class Globals
{

    //Atributo depurable
    #[Debuggable]
    protected static array $http = [];

    protected $query;
    protected $request;
    protected $cookie;
    protected $file;
    protected $url;
    protected $method;
    protected $endpoint;
    protected $previusUrl;


    public function __construct()
    {

        $this->query = !empty($_GET) ? $_GET : 'Empty';
        $this->request = !empty($_POST) ? $_POST : 'Empty';
        $this->cookie = !empty($_COOKIE) ? $_COOKIE : 'Empty';
        $this->file = !empty($_FILES) ? $_FILES : 'Empty';
        $this->method = $this->getMethod();
        $this->url = $this->getUrl();
        $this->endpoint = $this->getEndPoint();
        $this->previusUrl = $this->getPreviusUrl();

        self::$http = [
            'URL' => $this->url,
            'ENDPOINT' => $this->endpoint,
            'PARAMETERS' => $this->query,
            'BODY_REQUEST' => $this->request,
            'PREVIOUS_URL' => $this->previusUrl,
            'COOKIES' => $this->cookie,
            'FILES' => $this->file
        ];   
    }

    private function getUrl()
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http' . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    private function getEndPoint()
    {
        return $_SERVER['REQUEST_URI'];
    }

    private function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    private function getPreviusUrl()
    {
        return $_SERVER['HTTP_REFERER'] ?? 'No previous URL';
    }
}
