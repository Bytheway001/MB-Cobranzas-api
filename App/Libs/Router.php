<?php
namespace App\Libs;

use PHPRouter\Router as MainRouter;
use PHPRouter\RouteCollection;
use PHPRouter\Route;

class Router extends MainRouter
{
    public static function parseConfig(array $config) {
        $collection = new RouteCollection();
        foreach ($config['routes'] as $name => $routesController) {
            $controllerName = $name.'Controller';
            foreach ($routesController as $routeName=>$route) {
                $controller = CONTROLLER_NAMESPACE.$controllerName.'::'.$route[1];
                $collection->attachRoute(new Route($route[0], ['_controller' => $controller,'methods' => $route[2],'name' => $name.'_'.$routeName]));
            }
        }
        $router = new Router($collection);
        if (isset($config['base_path'])) {
            $router->setBasePath($config['base_path']);
        }
        return $router;
    }
}
