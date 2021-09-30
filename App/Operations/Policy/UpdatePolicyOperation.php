<?php
namespace App\Operations\Policy;

use App\Operations\Operation;
use App\Operations\IOperation;
use Symfony\Component\Yaml\Yaml;
use Core\ApiException;
use App\Models\Policy;

class UpdatePolicyOperation extends Operation implements IOperation
{
    public function __construct() {
        parent::__construct();
        $this->action = 'create_policy';
    }

    public function process() {
        try {
            $this->validateRequest();
            $this->findPolicy();
            $this->saveIntoDB();
            $this->prepareResponse();
        } catch (ApiException $e) {
            $this->response = $this->errors;
        }
    }

    public function validateRequest() {
        extract($this->payload);
        if (empty($policy_id)) {
            $this->errors['policy']="Must Provide a policy ID";
            $this->statusCode = 403;
            throw new ApiException("Bad Request");
        }
    }

    public function findPolicy() {
        try {
            $policy = Policy::find([$this->payload['policy_id']]);
            $this->policy = $policy;
        } catch (\ActiveRecord\RecordNotFound $e) {
            $this->errors['policy']="Policy Not found";
            $this->fail(400, "Policy Not found");
        }
    }

    public function saveIntoDB() {
        $policy = $this->policy;
        $payload = $this->payload;
        unset($this->payload['policy_id']);
        if(!$policy->isNew){
            $renewal = $policy->getLastRenewalObject();
            $renewal->update_attributes([
                'plan_id'=>$payload['plan_id'],
                'option'=>$payload['option'],
                'premium'=>$payload['premium'],
                'frequency'=>$payload['frequency'],
            ]);

        }
        else{
             $this->policy->update_attributes($this->payload);
        }
       
    }

    public function prepareResponse() {
        if (count($this->errors)>0) {
            $this->connection->rollback();
            $this->response = $this->errors;
            return;
        }
        $this->connection->commit();
        $this->done = true;
        $this->statusCode = 200;
        $this->policy->reload();
        $this->response=$this->policy->to_array(['include'=>['plan'],'methods'=>['company','totals']]);
        ;
    }
}
