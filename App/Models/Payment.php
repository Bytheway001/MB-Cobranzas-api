<?php 
namespace App\Models;

class Payment extends \ActiveRecord\Model{
	static $belongs_to=[['policy'],['user'],['account']];
	public function serialize(){
		$payment = $this->to_array();
		$payment['payment_date']=$this->payment_date->format('d-m-Y');
		$payment['client']=$this->policy->client->first_name;
		$payment['collector']=$this->policy->client->collector->name??"--";
		$payment['plan']=$this->policy->plan;
		$payment['company']=$this->policy->plan->company->name;
		$payment['account_name']=$this->account?$this->account->name:'--';
		$payment['payment_method']=$this->serializePaymentMethods($this->payment_method);
		return $payment;
	}

	public function calculateDiscount(){
		$discount = $this->agency_discount + $this->agent_discount + $this->company_discount;
		if($this->currency==='BOB'){
			return $discount/$this->change_rate;
		}
		else{
			return $discount;
		}
	}

	private function serializePaymentMethods($method){
		$methods=[
			'cash_to_agency'=>'Efectivo la agencia',
			'check_to_agency_local'=>'Cheque local a la agencia',
			'check_to_agency_foreign'=>'Cheque extranjero a la agencia',
			'transfer_to_agency_foreign'=>'Transferencia a la agencia (Exterior)',
			'transfer_to_agency_local'=>'Transferencia la agencia (Local)',
			'claim_to_company'=>'Abono de reclamo',
			'tdc_to_collector'=>'Tarjeta de Credito A la Aseguradora',
			'check_to_foreign_company'=>'Cheque extranjero a la Aseguradora',
			'transfer_to_company'=>'Transferencia Bancaria a la Aseguradora',
			'tdc_to_company'=>'Pago en portal de cliente(TDC)'
		];
		return $methods[$method];
	}

	public function isCheck(){
		return $this->payment_method == 'check_to_agency_local' || $this->payment_method == 'check_to_agency_foreign';
	}

	public function isAgencyPayment(){
		$agencyMethods = ['cash_to_agency','check_to_agency_foreign','check_to_agency_local','transfer_to_agency_foreign','transfer_to_agency_local'];
		return in_array($this->payment_method, $agengyMethods);
	}

	public function isCash(){
		return $this->payment_method==='cash_to_agency';
	}

	public function process(){
		if($this->isCheck()){
			\App\Models\Check::create(['amount'=>$this->amount,'currency'=>$this->currency,'client_id'=>$this->policy->client_id]);
			$this->account_id = \App\Models\Account::find_by_name("Cheques en transito")->id;
		}
		if($this->account){
			$this->account->deposit($this->amount,$this->currency);
		}
		$this->processed = 1;
		return $this->save();
	}

	public function revert($user_id){
		if($this->isCheck()){
			$check = \App\Models\Check::first(['conditions'=>['client_id = ? and amount = ?',$this->client->id,$this->amount]]);
			$account =\App\Models\Account::find_by_name("Cheques en transito");
			$account->withdraw($this->amount,$this->currency);
			$check->delete();
		}
		if($this->account_id){
			$expense = new \App\Models\Expense([
				'date'=>date('Y-m-d H:i:s'),
				'account_id'=>$this->account->id,
				'category_id'=>97,
				'user_id'=>$user,
				'description'=>"Correccion de Cobranzas #".$this->id,
				'currency'=>$this->currency,
				'amount'=>$this->amount,
				'office'=>'sc','bill_number'=>'S/N'
			]);
			$expense->account->withdraw($this->currency,$this->amount);
			$expense->save();
			$expense->reload();

			$this->corrected_with = $expense->id;
			$this->save();
		}
		else{
			$this->delete();
			
		}
		return true;
	}

}

?>