<?php

namespace App\Controllers;

use App\Models\Expense;
use App\Models\PolicyPayment;
use Core\Request;
use Core\Response;

class expensesController extends Controller
{
    public function create() {
        $operation = new \App\Operations\CreateExpenseOperation();
        $operation->process();
        if($operation->done){
            Response::send($operation->statusCode,$operation->response);
        }  
        else{
            Response::crash($operation->statusCode,$operation->errors);
        }
        /*
        $payload = Request::instance('create_expense')->payload;
        $expense = new Expense(Request::instance()->payload);
        if ($expense->save()) {
            Response::send(201, $expense->to_array(['include'=>['account','category']]));
        } else {
            Response::crash(400, $expense->errors->full_messages());
        }
        */
       
    }
}
