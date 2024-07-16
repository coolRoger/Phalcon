<?php

namespace Phalcon;

use JsonSerializable;

class Session implements JsonSerializable {
    public function __construct(){
        session_start();
    }
    
    public function __get($name){
        if(session_status() !== PHP_SESSION_ACTIVE) return null;
        return $_SESSION[$name] ?? null;
    }

    public function __set($name, $value){
        if(session_status() === PHP_SESSION_ACTIVE){
            $_SESSION[$name] = $value;
        }
    }

    public function jsonSerialize():mixed
    {
        if(session_status() !== PHP_SESSION_ACTIVE) return null;

        return $_SESSION;
    }
}