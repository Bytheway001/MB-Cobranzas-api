<?php 
namespace App\Operations;
use App\Operations\{Operation,IOperation};
use Symfony\Component\Yaml\Yaml;
use Core\ApiException;
class CreateTransferOperation extends Operation implements IOperation{

	public function __construct(){
		parent::__construct();
		$this->action = 'create_transfer';
	}

	public function process(){
	
		try{
			$this->validateRequest();
			$this->setAccounts();
			$this->validateAmount();
			$this->makeTransfer();
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
		$this->giving_account = \App\Models\Account::find([$this->payload['from']]);
		$this->receiving_account = \App\Models\Account::find([$this->payload['to']]);
	}

	public function validateAmount(){
		if(!$this->giving_account->has($this->payload['amount'],$this->payload['currency'])){
			$this->errors['amount']="Unavailable Amount";
			$this->fail(400,"Unavailable amount");
		}
	}

	public function makeTransfer(){
		$user_id = \Core\Request::instance()->user->id;
		$this->giving_account->create_expense([
			'user_id'    => $user_id,
			'bill_number'=> 'S/N',
			'description'=> 'Transferencia hacia '.$this->receiving_account->name,
			'currency'   => $this->payload['currency'],
			'amount'     => $this->payload['amount'],
			'category_id'=> 73,
			'date'       => date('Y-m-d H:i:s'),
		]);

		$this->receiving_account->create_income([
			'user_id'    => $user_id,
			'description'=> 'Trasferencia desde '.$this->giving_account->name,
			'category_id'=> 89,
			'currency'   => $this->payload['currency'],
			'amount'     => $this->payload['amount'],
			'date'       => date('Y-m-d H:i:s'),
		]);

		
		$this->giving_account->withdraw($this->payload['amount'],$this->payload['currency']);
		$this->receiving_account->deposit($this->payload['amount'],$this->payload['currency']);
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
		$this->response="Transferencia Exitosa";
	}
}



?>