<?php
namespace App\Controllers;

use App\Models\Payment;
use App\Models\Policy;
use App\Libs\Validator;
use Core\Request;
use Core\Response;

class policiesController extends Controller
{
    public function create() {
        $operation = new \App\Operations\Policy\CreatePolicyOperation();
        $operation->process();
        if ($operation->done) {
            Response::send($operation->statusCode, $operation->response);
        } else {
            Response::crash($operation->statusCode, $operation->errors);
        }
    }

    public function update($policy_id) {
        \Core\Request::instance()->payload['policy_id']=$policy_id;
        $operation = new \App\Operations\Policy\UpdatePolicyOperation();
        $operation->process();
        if ($operation->done) {
            Response::send($operation->statusCode, $operation->response);
        } else {
            Response::crash($operation->statusCode, $operation->errors);
        }
    }

    public function renew() {
        $operation = new \App\Operations\Policy\RenewPolicyOperation();
        $operation->process();
        if ($operation->done) {
            Response::send($operation->statusCode, $operation->response);
        } else {
            Response::crash($operation->statusCode, $operation->errors);
        }
    }

    public function getHistory($policy_id) {
        $result = ['payments'=>[],'policy_payments'=>[]];
        $policy = \App\Models\Policy::find([$policy_id]);
        if (isset($_GET['type']) && $_GET['type']=='list') {
            $payments = $policy->actual_payments;
            $policy_payments = $policy->actual_policy_payments;
            foreach ($payments as $p) {
                $result['payments'][]=$p->to_array();
            }
            foreach ($policy_payments as $pp) {
                $result['policy_payments'][]=$pp->to_array();
            }
            Response::send(200, $result);
        } else {
            Response::send(200, $policy->history());
        }
    }

    public function show($id) {
        $policy =\App\Models\Policy::find([$id]);
        Response::send(200, $policy->to_array(['methods'=>'company','include'=>'plan']));
    }

    public function pay() {
        $operation = new \App\Operations\Policy\CreatePolicyPaymentOperation();
        $operation->process();
        if ($operation->done) {
            Response::send($operation->statusCode, $operation->response);
        } else {
            Response::crash($operation->statusCode, $operation->errors);
        }
    }

    public function getFinanced() {
        $result=[];
        $policy_payments = \App\Models\PolicyPayment::all(['conditions'=>['payment_type = ?','Finance']]);
        foreach ($policy_payments as $pp) {
            $result[]=$pp->to_array(['include'=>['policy'=>['include'=>['client','plan'=>['include'=>'company']]]]]);
        }
        Response::send(200, $result);
    }

    public function getRenovations() {
        $result = [];
        $policies = \App\Models\Policy::all();
        try {
            foreach ($policies as $policy) {
                if ($policy->renovation_date) {
                    if ($policy->renovation_date->format('Y-m')===$_GET['year'].'-'.$_GET['month']) {
                        $result[] = $policy->to_array(['include'=>'client']);
                    }
                } else {
                    print_r($policy);
                    die();
                }
            }
        } catch (\Exception $e) {
            print_r($policy->id);
        }
        Response::send(200, $result);
    }
}
