<?php

namespace App\Controllers;

use App\Models\Expense;
use App\Models\PolicyPayment;

class expensesController extends Controller
{
    public function create()
    {
        $expense = new Expense($this->payload);
        $expense->user_id = $this->current_id;
        if (!$expense->account->has($expense->amount, $expense->currency)) {
            http_response_code(403);
            $this->response(['errors'=>true, 'data'=>'La cuenta seleccionada no posee el saldo suficiente para registrar esta salida']);
        } else {
            if ($expense->save()) {
                $expense->account->withdraw($expense->amount, $expense->currency);
                $this->response(['errors'=>false, 'data'=>$expense->to_array(['include'=>['account', 'category']])]);
            } else {
                http_response_code(403);
                $this->response(['errors'=>true, 'data'=>'No se pudo crear']);
            }
        }
    }

    public function index()
    {
        $result = [
            'expenses'=> [],
            'payments'=> [],
        ];

        $expenses = Expense::all(['order'=>'date DESC']);
        foreach ($expenses as $expense) {
            $expense = $expense->to_array(['include'=>'category']);
        }

        $policy_payments = PolicyPayment::all();
        foreach ($policy_payments as $payment) {
            $p = $payment->to_array();
            $p['date'] = \App\Libs\Time::format($p['created_at'], 'd-m-Y');
            $p['company'] = \App\Models\Client::find([$payment->client_id])->company;
            $p['client'] = \App\Models\Client::find([$p['client_id']])->first_name;
            $p['account'] = \App\Models\Account::find([$p['account_id']])->name;

            $result['payments'][] = $p;
        }

        $this->response(['errors'=>false, 'data'=>$result]);
    }

    public function createPolicyPayment()
    {
        $policy_payment = new \App\Models\PolicyPayment($this->payload);
        $policy_payment->user_id = $this->current_id;
        if ($policy_payment->account->has($policy_payment->amount, $policy_payment->currency)) {
            if ($policy_payment->save()) {
                $client = $policy_payment->policy->client;
                $policy_payment->policy->financed = $policy_payment->policy->financed + $policy_payment->amount;
                $policy_payment->policy->save();
                $policy_payment->account->withdraw($policy_payment->amount, $policy_payment->currency);
                $this->response(['errors'=>false, 'data'=>'Creado con exito']);
            }
        } else {
            http_response_code(403);
            $this->response(['errors'=>true, 'data'=>'La cuenta seleccionada no posee el saldo suficiente para registrar esta salida']);
        }
    }

    public function getPolicyPayments($id)
    {
        $result = [];
        $policy = \App\Models\Policy::find([$id]);

        $this->response(['errors'=>false, 'data'=>$policy->history()]);
    }
}
