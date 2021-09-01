<?php
namespace App\Operations;

use Core\Request;

class Operation
{
    public $done = false;
    protected $connection;
    protected $payload;
    public $response;
    public $statusCode = 200;
    public $errors = [];
    protected function __construct() {
        $this->connection = \ActiveRecord\ConnectionManager::get_connection();
        $this->payload = Request::instance()->payload;
        $this->connection->transaction();
    }
    protected function fail($code, $message) {
        $this->statusCode = $code;
        throw new \Core\ApiException($message);
    }
}
