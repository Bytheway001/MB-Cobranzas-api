<?php

namespace App\Controllers;

use App\Models\Plan;

class plansController extends Controller
{
    public function list()
    {
        $result = [];
        $accounts = Plan::all();
        foreach ($accounts as $account) {
            $result[] = $account->to_array();
        }
        $this->response(['errors'=>false, 'data'=>$result]);
    }
}
