<?php 
date_default_timezone_set('America/La_Paz');
define("PROJECTPATH", dirname(__DIR__));
define("APPPATH", PROJECTPATH . '/App');
define("DEBUG",true);
define("HUBSPOT_API_KEY",'abcb7c3c-c65a-4985-bc11-58892ac09f3f');
require "../vendor/autoload.php";
use PHPRouter\RouteCollection;
use PHPRouter\Router;
use PHPRouter\Route;
use PHPRouter\Config;
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:*');
header('Access-Control-Allow-Headers:*');
$config = Config::loadFromFile(PROJECTPATH.'/Config/routes.yaml');
$router = Router::parseConfig($config);
//$router = Router::parseRafaFile($config);
if (!session_id()) @session_start();
ActiveRecord\Config::initialize(function($cfg)
{
	include('../Config/web.php');
	ActiveRecord\Connection::$datetime_format = 'Y-m-d H:i:s';
	$cfg->set_model_directory(APPPATH.'/Models');
	$cfg->set_connections(array(
	'development' => 'mysql://'.$database['user'].':'.$database['password'].'@'.$database['host'].'/'.$database['name'].';charset=utf8'));
});



if(DEBUG==false){
	try{
		$router->matchCurrentRequest();
	}
	catch(Exception $e){
		die($e->getMessage());
	}
}
else{
	$whoops = new \Whoops\Run;
	$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
	$whoops->register();
	$router->matchCurrentRequest();
}


