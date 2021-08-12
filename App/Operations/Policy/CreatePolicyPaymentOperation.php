<?php 
namespace App\Operations\Policy;
use Symfony\Component\Yaml\Yaml;
use App\Operations\{Operation,IOperation};
use Core\ApiException;
class CreatePolicyPaymentOperation extends Operation implements IOperation{
	public function __construct(){
		parent::__construct();
	}

	public function validateRequest(){
		$this->action = 'create_policy_payment';
		$params =  Yaml::parse(file_get_contents('../Config/params.yaml'));
		$action = $params[$this->action];
		if (!empty($action['payload'])) {
			foreach ($action['payload'] as $param) {
				if (!array_key_exists($param, $this->payload)) {
					$this->errors['request']=$param.' is Missing';
					$this->fail(400,'Invalid Params');
				}
			}
		}
		if(count($this->errors)>0){
			$this->statusCode = 403;
			throw new ApiException("Bad Request");
		}
	}

	public function process(){
		try{
			$this->validateRequest();
			$this->findPolicy();
			$this->validateAmountAvailability();
			$this->createPolicyPayment();
			$this->withdrawFromAccount();
			$this->prepareResponse();
		}
		catch(ApiException $e){
			$this->response = $this->errors;
		}
	}


	public function findPolicy(){
		try{
				$this->policy = \App\Models\Policy::find([$this->payload['policy_id']]);
		}
		catch(\ActiveRecord\RecordNotFound $e){
			$this->errors['policy']="Policy not found";
			$this->fail(400,"Policy not found");
		}
	
	}

	public function validateAmountAvailability(){
		$this->account = \App\Models\Account::find([$this->payload['account_id']]);
		if(!$this->account->has($this->payload['amount'],$this->payload['currency'])){
			$this->errors[]="La cuenta seleccionada no posee el monto disponible";
			$this->fail(400,"Insuficcient amount");
		}
	}

	public function createPolicyPayment(){
		$this->payload['user_id']=\Core\Request::instance()->user->id;
		$this->payment = $this->policy->create_policy_payment($this->payload);
	}

	public function withdrawFromAccount() {
        if ($this->payment->account) {
            $this->account->withdraw($this->payment->amount, $this->payment->currency);
        }
    }

	public function prepareResponse(){
		if(count($this->errors)>0){
			$this->connection->rollback();
			$this->response = $this->errors;
			return;
		}
		$this->connection->commit();
		$this->done = true;
		$this->statusCode = 201;
		$this->response="Payment Created Successfully";
	}
}



?>