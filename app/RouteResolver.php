<?php


namespace app;


use app\lib\controller\HelloController;
use app\lib\controller\PhoneBookController;
use League\Route\RouteGroup;
use League\Route\Router;

class RouteResolver
{
    private Router $router;

    /**
     * RouteResolver constructor.
     * @param Router $router
     */
    public function __construct(
        Router $router
    ) {
        $this->router = $router;
    }


    public function resolve(): void
    {
        $this->router->get('/hello', [HelloController::class, 'getHello']);

        $this->router->group(
            '/phone-book',
            function (RouteGroup $route) {
                $route->get('', [PhoneBookController::class, 'getAll']);
                $route->get('/params', [PhoneBookController::class, 'getAllParams']);
                $route->get('/{id:number}', [PhoneBookController::class, 'getOne']);
                $route->post('', [PhoneBookController::class, 'post']);
                $route->put('/{id:number}', [PhoneBookController::class, 'update']);
                $route->patch('/{id:number}', [PhoneBookController::class, 'update']);
                $route->delete('/{id:number}', [PhoneBookController::class, 'delete']);
            }
        );
    }
}