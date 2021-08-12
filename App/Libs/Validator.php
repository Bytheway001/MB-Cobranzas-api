<?php
namespace App\Libs;

use Core\Request;
use Core\Response;

class Validator
{
    public static $actions = [
        'create_client'=>['payload'=>['first_name','agent_id','collector_id','h_id']],
        'client_index'=>['params'=>['q']],
        'create_policy'=>['payload'=>['client_id','plan_id','policy_number','premium','frequency','renovation_date','effective_date','option']],
        'create_policy_payment'=>['payload'=>['account_id','policy_id','amount','currency','payment_date']]
    ];
    public static function validate($action) {
        $errors=[];
        $req = Request::instance();
        $action = static::$actions[$action];
        if (!empty($action['payload'])) {
            foreach ($action['payload'] as $param) {
                if (!array_key_exists($param, $req->payload)) {
                    $errors[]=$param.' is Missing';
                }
            }
        }
        
        if (!empty($action['params'])) {
            foreach ($action['params'] as $param) {
                if (!array_key_exists($param, $req->params)) {
                    $errors[]=$param.' is Missing';
                }
            }
        }

        if (count($errors)>0) {
            Response::crash(400, $errors);
        }
    }
}
