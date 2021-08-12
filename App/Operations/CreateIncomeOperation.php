<?php 
namespace App\Operations;
use App\Operations\{Operation,IOperation};
use Symfony\Component\Yaml\Yaml;
use Core\ApiException;
class CreateIncomeOperation extends Operation implements IOperation{

	public function __construct(){
		parent::__construct();
		$this->action = 'create_income';
	}

	public function process(){
	
		try{
			$this->validateRequest();
			$this->setAccounts();

			$this->createIncome();
			$this->prepareResponse();
		}
		catch(ApiException $e){
			$this->response = $this->errors;
		}
	}

	public function validateRequest(){
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

	public function setAccounts(){
		try{
			$this->account = \App\Models\Account::find([$this->payload['account_id']]);
		}
		catch(\ActiveRecord\RecordNotFound $e){
			$this->errors['account']="Account not found";
			$this->fail("Account not found");
		}

	}
	/* Amount available in the account should be higher or equal to the payload amount */
	

	public function createIncome(){
		$this->payload['user_id']=\Core\Request::instance()->user->id;
		$this->expense=$this->account->create_income($this->payload);
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
		$this->response=$this->expense->to_array(['include'=>['account','category']]);
	}
}



?>