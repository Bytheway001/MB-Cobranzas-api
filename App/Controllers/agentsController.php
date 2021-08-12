<?php
namespace App\Controllers;

use Core\Response;
use App\Models\Agent;
use App\Models\Payment;
use App\Models\User;

class agentsController extends Controller
{
    public function index() {
        $agents = Agent::list();
        Response::send(200, $agents);
    }

    public function getCollectors() {
        $collectors = User::list();
        Response::send(200, $collectors);
    }
}
