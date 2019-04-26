<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->resource('/user',PostController::class);
    $router->resource('/goods_select',GoodsController::class);
    $router->resource('/order_select',OrderController::class);
    $router->resource('/sucai_insert',SucaiController::class);
    $router->resource('/sucai_select',SucaiController::class);
});
