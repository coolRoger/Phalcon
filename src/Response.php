<?php

namespace Phalcon;

class Response {
    public function status($code) {
        http_response_code($code);
    }

    public function headers(array $headers){
        foreach ($headers as $key => $value) {
            header("$key: $value");
        }
    }

    public function cookies(array $cookies){
        foreach ($cookies as $key => $value) {
            setcookie($key, $value);
        }
    }
    
    public function write($content) {
        header('Content-Type: text/plain');
        echo $content;
    }
    
    public function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function redirect($url) {
        header("Location: $url");
    }
}
