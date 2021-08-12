<?php

namespace App\Controllers;

use Core\Response;

class Controller
{
    public function __construct() {
        $this->authenticateRequest();
    }

    private function authenticateRequest() {
        $uri = strtok($_SERVER['REQUEST_URI'], '?');
        if ($uri !== '/auth') {
            if (!isset($_SERVER['HTTP_U'])) {
                Response::crash(403, "NOT AUTHENTICATED");
            } else {
                $this->current_id = $_SERVER['HTTP_U'];
                $this->current_user = \App\Models\User::find([$this->current_id]);
            }
        }
    }
}
