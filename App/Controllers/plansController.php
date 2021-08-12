<?php

namespace App\Controllers;

use App\Models\Plan;
use Core\Response;

class plansController extends Controller
{
    public function list() {
        $result = [];
        $accounts = Plan::all();
        foreach ($accounts as $account) {
            $result[] = $account->to_array();
        }
        Response::send(200, $result);
    }
}
