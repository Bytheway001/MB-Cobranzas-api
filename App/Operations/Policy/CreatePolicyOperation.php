<?php
namespace App\Operations\Policy;

use App\Operations\Operation;
use App\Operations\IOperation;
use Symfony\Component\Yaml\Yaml;
use Core\ApiException;
use App\Models\Client;

class CreatePolicyOperation extends Operation implements IOperation
{
    public function __construct() {
        parent::__construct();
        $this->action = 'create_policy';
    }

    public function process() {
        try {
            $this->validateRequest();
            $this->findClient();
            $this->findDuplicates();
            $this->saveIntoDB();
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
                    $this->errors[]=$param.' is Missing';
                    $this->fail(400, 'Invalid Params');
                }
            }
        }
        if (count($this->errors)>0) {
            $this->statusCode = 403;
            throw new ApiException("Bad Request");
        }
    }

    public function findClient() {
        try {
            $client = Client::find([$this->payload['client_id']]);
            $this->client = $client;
        } catch (\ActiveRecord\RecordNotFound $e) {
            $this->errors[]="Cliente No encontrado";
            $this->fail(400, "Client Not found");
        }
    }

    public function findDuplicates() {
        $plan = \App\Models\Plan::find([$this->payload['plan_id']]);
        $company = $plan->company;
        foreach ($this->client->policies as $policy) {
            if ($policy->plan->company->id === $company->id) {
                $this->errors[]="El cliente ya tiene una poliza con esta compañia";
                $this->fail(400, "Client already has a policy with this company");
            }
        }
    }
    public function saveIntoDB() {
        $this->payload['created_by']=\Core\Request::instance()->user->id;
        $this->policy = $this->client->create_policy($this->payload);
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
        $this->response=$this->policy->to_array(['include'=>['plan'],'methods'=>['company','totals']]);
    }
}
