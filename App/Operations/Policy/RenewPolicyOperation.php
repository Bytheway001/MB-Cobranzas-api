<?php
namespace App\Operations\Policy;

use App\Operations\Operation;
use App\Operations\IOperation;
use Symfony\Component\Yaml\Yaml;
use Core\ApiException;

class RenewPolicyOperation extends Operation implements IOperation
{
    public function __construct() {
        parent::__construct();
        $this->action = 'renew_policy';
    }

    public function process() {
        try {
            $this->validateRequest();
            $this->findPolicy();
            $this->createRenewal();
            $this->prepareResponse();
        } catch (ApiException $e) {
            $this->response = $this->errors;
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

    public function findPolicy() {
        try {
            $this->policy = \App\Models\Policy::find([$this->payload['policy_id']]);
        } catch (\ActiveRecord\RecordNotFound $e) {
            $this->errors['policy']="Policy not found";
            $this->fail(400, "Policy not found");
        }
    }

    public function createRenewal() {
        $this->payload['user_id']=\Core\Request::instance()->user->id;
        $this->renewal = $this->policy->create_renewal($this->payload);
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
        $this->response="Poliza Renovada con exito";
    }
}
