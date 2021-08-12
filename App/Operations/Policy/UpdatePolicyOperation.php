<?php 
namespace App\Operations\Policy;
use App\Operations\{Operation,IOperation};
use Symfony\Component\Yaml\Yaml;
use Core\ApiException;
use App\Models\Policy;
class UpdatePolicyOperation extends Operation implements IOperation{
	public function __construct($policy_id){
		parent::__construct();
		$this->action = 'create_policy';
		$this->payload->policy_id = $policy_id;
	}

	public function process(){
		try{
			$this->validateRequest();
			$this->findClient();
			$this->findPolicy();

			$this->saveIntoDB();
			$this->prepareResponse();
		}
		catch(ApiException $e){
			$this->response = $this->errors;
		}
	}

	public function validateRequest(){
		extract($this->payload);
		if(empty($policy_id)){
			$this->errors['policy']="Must Provide a policy ID";
			$this->statusCode = 403;
			throw new ApiException("Bad Request");
		}
	}

	public function findClient(){
		try{
			$this->client = \App\Models\Client::find([$this->payload['client_id']]);
		}
		catch(\ActiveRecord\RecordNotFound $e){
			$this->errors['client']="Client was not found";
			$this->fail(400,"Client Not Found");
		}

	}

	

	public function findPolicy(){
		try{
			$policy = Policy::find([$this->payload['policy_id']]);
			$this->policy = $policy;
			if($this->policy->client->id !== $this->client->id){
				$this->errors['policy']="Policy does not belong to this client";
				$this->fail(404,"Policy Does not belong to client");
			}
		}
		catch(\ActiveRecord\RecordNotFound $e){
			$this->errors['policy']="Policy Not found";
			$this->fail(400,"Policy Not found");
		}
	}

	public function saveIntoDB(){
		unset($this->payload['policy_id']);
		$this->policy->update_attributes($this->payload);
	}

	public function prepareResponse(){
		if(count($this->errors)>0){
			$this->connection->rollback();
			$this->response = $this->errors;
			return;
		}
		$this->connection->commit();
		$this->done = true;
		$this->statusCode = 200;
		$this->response=$this->policy->to_array(['include'=>['plan'],'methods'=>['company','totals']]);;
	}
}


?>