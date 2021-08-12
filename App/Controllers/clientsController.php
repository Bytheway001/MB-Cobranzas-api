<?php

namespace App\Controllers;

// UPDATED
use App\Models\Client;
use App\Models\User;
use App\Models\Agent;
use Core\Response;
use Core\Request;
use App\Libs\Time;

class clientsController extends Controller
{
    public function create() {
        $operation = new \App\Operations\Client\CreateClientOperation();
        $operation->process();
        if($operation->done){
            Response::send($operation->statusCode,$operation->response);
        }  
        else{
            Response::crash($operation->statusCode,$operation->errors);
        }
    }
    public function update($id) {
        $operation = new \App\Operations\Client\UpdateClientOperation();
        $operation->process();
        if($operation->done){
            Response::send($operation->statusCode,$operation->response);
        }  
        else{
            Response::crash($operation->statusCode,$operation->errors);
        }
    }
    public function index() {
        try {
            $clients = [];
            $result = [];
            if (isset($_GET['q'])) {
                $clients = Client::all(['conditions'=>['first_name LIKE ? OR h_id LIKE ?', '%'.$_GET['q'].'%', '%'.$_GET['q'].'%']]);
            } else {
                $clients = Client::all();
            }
            foreach ($clients as $client) {
                $result[] = $client->serialized();
            }
            Response::send(200, $result);
        } catch (\ActiveRecord\DatabaseException $e) {
            Response::crash(500, $e->getMessage());
        }
    }
    public function show($id) {
        $client = Client::find_by_id([$id]);
        if ($client) {
            $result = $client->serialized();
            Response::send(200, $result);
        } else {
            Response::crash(404, "Couldn`t Find Client");
        }
    }
    public function profile($id) {
        $client = Client::find_by_id($id);
        $result = $client->serialized();
        $result['payments'] = [];
        foreach ($client->payments as $payment) {
            $p = $payment->to_array();
            $p['date'] = $payment->payment_date->format('d-m-Y');
            $p['user'] = $payment->user->name;
            $result['payments'][] = $p;
        }
        $result['policy_payments'] = [];
        foreach ($client->policy_payments as $p) {
            $pp = $p->to_array();
            $pp['date'] = $p->created_at->format('d-m-y');
            $pp['user'] = $p->user->name;
            $result['policy_payments'][] = $pp;
        }
        Response::send(200, $result);
    }
}
