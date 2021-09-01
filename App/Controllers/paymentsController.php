<?php

namespace App\Controllers;

use App\Models\Payment;
use App\Models\PolicyPayment;
use App\Models\Policy;
use App\Models\Check;
use Core\Request;
use Core\Response;

function clientHasDebt($client) {
    $amount_to_pay = $client->prima;
    $payed_amount = 0;
    $payments = $client->payments;
    foreach ($payments as $payment) {
        if ($payment->currency === 'BOB') {
            $payed_amount = $payed_amount + ($payment->amount / $payment->change_rate);
            $amount_to_pay = $amount_to_pay - $payment->calculateDiscount();
        } else {
            $payed_amount = $payed_amount + $payment->amount;
        }
    }

    return  $payed_amount < $client->prima;
}

class paymentsController extends Controller
{
    public function create() {
        $operation = new \App\Operations\CreatePaymentOperation();
        $operation->process();
        if ($operation->done) {
            Response::send($operation->statusCode, $operation->response);
        } else {
            Response::crash($operation->statusCode, $operation->errors);
        }
    }

    public function index() {
        $payments = Payment::list(['order'=>'processed ASC,payment_date DESC', 'limit'=>50], [
            'include'=> [
                'account',
                'policy'=> [
                    'include'=> [
                        'client',
                        'plan'=> [
                            'include'=> [
                                'company',
                            ],
                        ],

                    ],
                ],
            ],
        ]);

        Response::send(200, $payments);
    }

    public function validate($id) {
        $payment = Payment::find([$id]);
        $payment->processed = 1;
        if ($payment->save()) {
            Response::send(200, "Validacion Exitosa");
        } else {
            Response::crash(400, 'No se pudo validar esta cobranza');
        }
    }
}
