<?php 
namespace App\Operations\Revert;
use \Core\{Request,Response};
use App\Operations\{Operation,IOperation};
use App\Models\{Check,Account,Income,Expense};

class RevertCheckOperation extends Operation{
	private Check $check;
	public function __construct(Check $check){

		$this->check = $check;
	}

	public function process(){
		
		if($this->check->wasCollected()){
			$this->revertCollection();
			$this->createCorrections();
			$this->destroyChecks();
		}
		
		$this->done = true;
	}

	private function revertCollection(){
		$check = $this->check;
		$description = "Cobro de Cheque ".$check->client->first_name." #$check->id";
		$check_account = $check->account;
		$this->income_created_on_collection = Income::find_by_description($description);
		$this->expense_created_on_collection = Expense::find_by_description($description);
		$reimburse_account = Account::find_by_name("Cheques en Transito");
		$this->expense= $check_account->create_expense(
			[
				'user_id'=>Request::instance()->user->id,
				'bill_number'=>'S/N',
				'description'=>"Correccion de Cobro de Cheque #".$this->check->id,
				'currency'=>$this->check->currency,
				'amount'=>$this->check->amount,
				'office'=>'SC',
				'category_id'=>97,
				'date'=>date('Y-m-d H:i:s'),
				'correcting'=>$this->income_created_on_collection->id,
				'correcting_type'=>'income'
			]);
		$this->income=$reimburse_account->create_income([
			'date'=>date('Y-m-d H:i:s'),
			'user_id'=>Request::instance()->user->id,
			'category_id'=>96,
			'description'=>"Correccion de cobro de cheque #".$this->check->id,
			'currency'=>$this->check->currency,
			'amount'=>$this->check->amount,
			'correcting'=>$this->expense_created_on_collection->id,
			'correcting_type'=>'expense'
		]);

	}

	private function destroyChecks(){
		$this->check->delete();
	}

	private function CreateCorrections(){
		$this->income_created_on_collection->update_attributes(['corrected_with'=>$this->expense->id]);
		$this->expense_created_on_collection->update_attributes(['corrected_with'=>$this->income->id]);
	}


	
}

?>