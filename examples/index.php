<?php

include_once __DIR__ . "/../vendor/autoload.php";

use Phalcon\Application;
use Phalcon\ApplicationContext;
use Phalcon\Router;

$app = new Application();

$router = new Router();

$router->get("/:userId", function (ApplicationContext $ctx, $next) {
    $userId = $ctx->request->params['userId'];
    $ctx->response->status(200);
    $ctx->response->write("Your userId is $userId");
});

$router->post("/data", function (ApplicationContext $ctx, $next) {
    $data = $ctx->request->body;
    $ctx->response->status(200);
    $ctx->response->json($data);
});

$router->all(".*", function (ApplicationContext $ctx, $next) {
    $ctx->response->status(404);
    $ctx->response->write("404 Not Found");
});

$app->use([$router, "routes"]);

$app->run();