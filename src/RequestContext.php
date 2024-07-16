<?php

namespace Phalcon;

class RequestContext{
    public string $path;

    public string $method;

    public array $query;

    public array $params;

    public array $body;

    public array $header;

    public string $ip;

    public string $domain;

    public int $timestamp;

    public array $cookie;

    public function __construct(){

        $this->params = [];
        
        $this->method = $_SERVER['REQUEST_METHOD'];

        $this->path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $this->query = $_GET;

        $this->body = $_POST;

        $this->header = getallheaders();

        $this->ip = $_SERVER['REMOTE_ADDR'];

        $this->domain = $_SERVER["HTTP_ORIGIN"] ?? "";

        $this->timestamp = $_SERVER["REQUEST_TIME"];

        $this->cookie = $_COOKIE;
    }
}