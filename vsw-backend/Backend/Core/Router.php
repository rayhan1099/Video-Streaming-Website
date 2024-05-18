<?php

class Router
{
    protected array $route_list = [
        'GET'=>[],
        'POST'=>[],
    ];

    public function __construct(
        public $http_method = null,
        public $route = null
    )
    {
        $this->http_method = $_SERVER['REQUEST_METHOD'];
        $this->route = $_SERVER['REQUEST_URI'];
    }
    public function add($route, $handler, $method = 'GET'){
        $this->route_list[$method][] = [$route, $handler];
    }
    public function handle($route, App $app){

    }
}