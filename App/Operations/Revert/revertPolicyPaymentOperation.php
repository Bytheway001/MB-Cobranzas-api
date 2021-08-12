<?php 
namespace App\Operations\Revert;
use App\Operations\{Operation,IOperation};
use Core\ApiException;
class RevertPolicyPaymentOperation extends Operation implements IOperation{
	public function __construct(){
		parent::__construct();
	}

	public function validateRequest(){

	}

	public function process(){
		try{
			$this->findPolicyPayment();
			$this->createCorrectionIncome();
			$this->revertPolicyPayment();
			$this->prepareResponse();

		}
		catch(ApiException $e){
			$this->response = $this->errors;
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
		$this->response="Correccion Realizada";
	}

	public function findPolicyPayment(){
		try{
			$this->policy_payment = \App\Models\PolicyPayment::find([$this->payload['ref']]);
		}
		catch(\ActiveRecord\RecordNotFound $e){
			$this->errors['policy_payment']="Policy payment was not found";
			$this->fail('Policy Payment not found');
		}

	}

	public function revertPolicyPayment(){
		if($this->policy_payment->account_id){
			$this->policy_payment->update_attributes(['corrected_with'=>$this->income->id]);
		}
		else{
			$this->policy_payment->delete();
		}
		
	}

	public function createCorrectionIncome(){
		if($this->policy_payment->account_id){
			$this->income = \App\Models\Income::create([
				'date'       => date('Y-m-d H:i:s'),
				'account_id' => $this->policy_payment->account_id,
				'category_id'=> 98, 
				'user_id'=>\Core\Request::instance()->user->id,
				'description'=> 'Correccion Pago Polizas #'.$this->policy_payment->id,
				'currency'   => $this->policy_payment->currency,
				'amount'     => $this->policy_payment->amount,
				'correcting' => $this->policy_payment->id,
				'correcting_type' => 'policy_payment'
			]);
		}
		
	}
}

?>