<?php 
namespace App\Operations\Revert;
use Core\{Request,Response};
use App\Models\{Payment,Expense,Income,PolicyPayment};
use App\Operations\{Operation,IOperation};
use Core\ApiException;
class RevertPaymentOperation extends Operation implements IOperation{
	private $ref;



	public function __construct(){
		parent::__construct();
		$this->ref = $this->payload['ref'];
	}
	public function process(){
		try{
			$this->findPayment();
			$this->checkIfNotAlreadyReverted();
			$this->destroyChecks();
			$this->createCorrectionExpense();
			$this->revertPolicyPayment();
			$this->prepareResponse();
		}
		catch(ApiException $e){
			$this->response = $this->errors;
		}

	}


	public function checkIfNotAlreadyReverted(){
		if($this->payment->corrected_with !== null){
			$this->errors['payment']="Payment was already reverted";
			$this->fail(400,"Payment Already Reverted");
		}
	}

	public function validateRequest(){

	}

	private function findPayment(){
		$this->payment = Payment::find([$this->ref]);
		if(!$this->payment){
			$this->errors['payment']='Does not exist';
		}
	}
	private function destroyChecks(){
		if($this->payment->isCheck()){
			$this->check = $this->payment->check;

			$operation = new RevertCheckOperation($this->check);
			$operation->process();
			if(!$operation->done){
				$this->errors = array_merge($this->errors,$operation->errors);
			}
		}
	}

	private function createCorrectionExpense(){
		if($this->payment->account_id){
			$this->expense = $this->payment->account->create_expense([
				'date'       => date('Y-m-d H:i:s'),
				'category_id'=> 97,
				'user_id'    => Request::instance()->user->id,
				'description'=> 'Correccion de Cobranzas #'.$this->payment->id,
				'currency'   => $this->payment->currency,
				'amount'     => $this->payment->amount,
				'office'     => 'sc', 'bill_number'=>'S/N',
				'correcting'=>$this->payment->id,
				'correcting_type'=>'payment'
			]);
			$this->payment->corrected_with = $expense->id;
			$this->payment->save();
		}
		else{
			$this->payment->delete();
		}
	}

	public function revertPayment(){

	}
	private function revertPolicyPayment(){
		if($this->payment->payment_method === "Pago con tarjeta de Terceros"){
			$comment = "Pagada con CC del cliente ".$this->payment->policy->client->first_name;
			$policy_payment = PolicyPayment::find(['conditions'=>[
				'payment_date = ? and comment = ?',
				$this->payment->payment_date,
				$comment
			]]);
			$policy_payment->delete();
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

}

?>