<?php
namespace App\Operations;

use \Core\Request;
use \Core\Response;
use App\Models\Check;
use App\Models\Account;
use Core\ApiException;
use Symfony\Component\Yaml\Yaml;

class CollectCheckOperation extends Operation implements IOperation
{
    public function __construct() {
        parent::__construct();
        $this->action = 'collect_check';
    }

    public function process() {
        try {
            $this->validateRequest();
            $this->findCheck();
            $this->withdrawFromTransit();
            $this->depositIntoAccount();
            $this->updateCheckStatus();
            $this->prepareResponse();
        } catch (ApiException $e) {
            $this->response = $this->errors;
        }
    }

    public function findCheck() {
        try {
            $this->check = Check::find([$this->payload['check_id']]);
        } catch (\ActiveRecord\RecordNotFound $e) {
            $this->errors['check']="Check was not found";
            $this->fail(400, "Check was not found");
        }
    }

    public function validateRequest() {
        $params =  Yaml::parse(file_get_contents('../Config/params.yaml'));
        $action = $params[$this->action];
        if (!empty($action['payload'])) {
            foreach ($action['payload'] as $param) {
                if (!array_key_exists($param, $this->payload)) {
                    $this->errors['request']=$param.' is Missing';
                    $this->fail(400, 'Invalid Params');
                }
            }
        }
        if (count($this->errors)>0) {
            $this->statusCode = 403;
            throw new ApiException("Bad Request");
        }
    }

    public function withdrawFromTransit() {
        $check = $this->check;
        $account = Account::find_by_name("Cheques en Transito");
        $account->create_expense([
            'user_id'    => Request::instance()->user->id,
            'bill_number'=> 'S/N',
            'description'=> "Cobro de Cheque ".$check->client->first_name." #$check->id",
            'currency'   => $check->currency,
            'amount'     => $check->amount,
            'account_id' => 9,
            'category_id'=> 73,
            'date'       => date('Y-m-d H:i:s'),
        ]);
    }

    public function depositIntoAccount() {
        $check = $this->check;
        $account = Account::find([$this->payload['account_id']]);
        $account->create_income([
            'user_id'    => Request::instance()->user->id,
            'description'=> "Cobro de Cheque ".$check->client->first_name." #$check->id",
            'currency'   => $check->currency,
            'amount'     => $check->amount,
            'account_id' => $account->id,
            'category_id'=> 73,
            'date'       => date('Y-m-d H:i:s'),
        ]);
    }

    public function updateCheckStatus() {
        $this->check->update_attributes(['account_id'=>$this->payload['account_id'],'status'=>"Abonado en cuenta"]);
    }

    public function prepareResponse() {
        if (count($this->errors)>0) {
            $this->connection->rollback();
            $this->response = $this->errors;
            return;
        }
        $this->connection->commit();
        $this->done = true;
        $this->statusCode = 201;
        $this->response="Cheque Abonado a Cuenta";
    }
}
