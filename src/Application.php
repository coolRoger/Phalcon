<?php

namespace Phalcon;

use Phalcon\RequestContext;
use Phalcon\Session;
use JsonSerializable;

class ApplicationContext implements JsonSerializable
{
    public RequestContext $request;
    public Session $session;

    public Response $response;

    private array $__properties__ = [];

    public function __construct(RequestContext $request, Session $session, Response $response)
    {
        $this->request = $request;
        $this->session = $session;
        $this->response = $response;
    }

    public function __get($name)
    {
        return $this->__properties__[$name] ?? null;
    }

    public function __set($name, $value)
    {
        $this->__properties__[$name] = $value;
    }

    public function jsonSerialize():mixed
    {
        $output = array_merge(
            get_object_vars($this),
            $this->__properties__
        );

        unset($output["__properties__"]);

        return $output;
    }
}

class Application
{
    private $middlewares = [];

    /**
     * 添加中间件到应用程序
     *
     * @param callable $middleware function(mixed $ctx, callable $next)
     * @return $this
     */

    public function use(...$middlewares)
    {
        foreach ($middlewares as $middleware) {
            $this->middlewares[] = $middleware;
        }

        return $this; // 允许链式调用
    }

    private function handle(ApplicationContext $context)
    {
        $middlewareStack = $this->middlewares;
        $next = function () use (&$middlewareStack, $context, &$next) {
            if (empty($middlewareStack)) {
                return;
            }
            $middleware = array_shift($middlewareStack);
            $middleware($context, $next);
        };
        $next();
    }

    private function buildContext()
    {
        $context = new ApplicationContext(
            new RequestContext(),
            new Session(),
            new Response()
        );

        return $context;
    }

    public function run()
    {
        $this->handle($this->buildContext());
    }
}