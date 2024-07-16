<?php

namespace Phalcon;

use Phalcon\ApplicationContext;

class RouterLayer
{
    public $path;
    public $methods;
    public $stack;
    private $paramNames = [];
    private $regexp;
    private $opts;

    public function __construct($path, $methods, $middleware, $opts = [])
    {
        $this->path = $path;
        $this->methods = array_map('strtoupper', $methods);
        $this->stack = is_array($middleware) ? $middleware : [$middleware];
        $this->opts = $opts;
        $this->regexp = $this->pathToRegexp($path, $this->paramNames, $opts);
    }

    public function match($path)
    {
        return preg_match($this->regexp, $path) === 1;
    }

    public function captures($path)
    {
        if ($this->match($path)) {
            preg_match($this->regexp, $path, $matches);
            return array_slice($matches, 1);
        }
        return [];
    }

    public function params($path, $captures)
    {
        $params = [];
        foreach ($this->paramNames as $index => $name) {
            if (isset($captures[$index])) {
                $params[$name] = $captures[$index];
            }
        }
        return $params;
    }

    private function pathToRegexp($path, &$paramNames, $opts)
    {
        $pattern = preg_replace_callback('/:([^\/]+)/', function ($matches) use (&$paramNames) {
            $paramNames[] = $matches[1];
            return '([^\/]+)';
        }, $path);
        return "#^$pattern\$#";
    }
}

class Router
{
    private $stack = [];

    public function use($path, $middleware = null)
    {
        if (is_string($path) && $middleware instanceof Router) {
            $middleware->prefix($path);
            $this->stack[] = new RouterLayer($path, ['ALL'], [$middleware]);
        } elseif ($middleware === null) {
            $middleware = $path;
            $this->stack[] = new RouterLayer('.*', ['ALL'], $middleware);
        } else {
            $this->stack[] = new RouterLayer($path, ['ALL'], $middleware);
        }
        return $this;
    }

    public function addRoute($method, $path, $middleware)
    {
        $this->stack[] = new RouterLayer($path, [$method], $middleware);
        return $this;
    }

    public function get($path, ...$middleware)
    {
        return $this->addRoute('GET', $path, $middleware);
    }

    public function post($path, ...$middleware)
    {
        return $this->addRoute('POST', $path, $middleware);
    }

    public function put($path, ...$middleware)
    {
        return $this->addRoute('PUT', $path, $middleware);
    }

    public function delete($path, ...$middleware)
    {
        return $this->addRoute('DELETE', $path, $middleware);
    }

    public function all($path, ...$middleware){
        return $this->addRoute('ALL', $path, $middleware);
    }

    public function routes(ApplicationContext $ctx)
    {
        $method = $ctx->request->method;
        $path = $ctx->request->path;
        $layers = $this->stack;

        $next = function () use (&$layers, $ctx, $method, $path, &$next) {
            if (empty($layers)) {
                return;
            }
            
            $layer = array_shift($layers);

            if ($layer->match($path) && (in_array($method, $layer->methods) || in_array('ALL', $layer->methods))) {
                $captures = $layer->captures($path);
                $params = $layer->params($path, $captures);
                $ctx->request->params = $params;
                $middleware = $layer->stack;
                $middleware[] = $next;
                $this->compose($middleware, $ctx);
            } else {
                $next();
            }
        };

        $next();
    }

    private function compose($middleware, $ctx)
    {
        $index = 0;
        $next = function () use (&$index, $middleware, $ctx, &$next) {
            if ($index < count($middleware)) {
                $fn = $middleware[$index++];
                $fn($ctx, $next);
            }
        };
        $next();
    }

    public function prefix($prefix)
    {
        foreach ($this->stack as $layer) {
            $layer->path = "{$prefix}{$layer->path}";
        }
    }
}