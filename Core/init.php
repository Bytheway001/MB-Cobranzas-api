<?php
/* Here we initialize variables */
define("PROJECTPATH", dirname(__DIR__));
define("APPPATH", PROJECTPATH . '/App');
define("CONTROLLER_NAMESPACE", "\App\Controllers\\");
define("DEBUG", true);
define("MIDDLEWARE_NAMESPACE", "\Middleware\\");

date_default_timezone_set('America/La_Paz');

$allowedOrigins = [
    "http://localhost:3000",
    "https://cobranzas.megabrokerslatam.com",
    "https://test.megabrokerslatam.com",
];

if (isset($_SERVER["HTTP_ORIGIN"]) && in_array($_SERVER["HTTP_ORIGIN"], $allowedOrigins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER["HTTP_ORIGIN"]);
}
header('Access-Control-Allow-Headers:Content-type,CALL-TYPE,Authorization,session,u');
header('Access-Control-Allow-Methods:*');
header('Access-Control-Allow-Credentials:true');
