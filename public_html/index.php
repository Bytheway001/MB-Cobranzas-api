<?php
require '../Core/init.php';
require '../vendor/autoload.php';
use PHPRouter\Config;
use App\Libs\Router;
$dotenv = Dotenv\Dotenv::createImmutable("../");
$dotenv->load();
$config = Config::loadFromFile(PROJECTPATH.'/Config/routes.yaml');
$router = Router::parseConfig($config);
if (!session_id()) {
    @session_start(); 
}

ActiveRecord\Config::initialize(function ($cfg) {
    ActiveRecord\Connection::$datetime_format = 'Y-m-d H:i:s';
    $cfg->set_model_directory(APPPATH.'/Models');
    $cfg->set_connections(
        [
            'development' => 'mysql://'.$_ENV['DATABASE_USER'].':'.$_ENV['DATABASE_PASSWORD'].'@'.$_ENV['DATABASE_HOST'].'/'.$_ENV['DATABASE_NAME'].';charset=utf8'
        
        ]
    );
    $cfg->set_default_connection('development');
});

if (DEBUG == false) {
    try {
        $router->matchCurrentRequest();
    } catch (Exception $e) {
        exit($e->getMessage());
    }
} else {
    $whoops = new \Whoops\Run();
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
    $whoops->register();
    $router->matchCurrentRequest();
}
